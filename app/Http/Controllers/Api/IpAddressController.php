<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\IpAddress;
use App\Support\Ping\IpPingService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IpAddressController extends Controller
{
    public function index()
    {
        return IpAddress::with($this->relations())
            ->withCount(['vms'])
            ->orderBy('ip')
            ->get();
    }

    public function show(IpAddress $ipAddress)
    {
        return $ipAddress->load($this->relations())->loadCount(['vms']);
    }

    public function store(Request $request)
    {
        if ($this->isCidr((string) $request->input('ip'))) {
            return $this->storeCidr($request);
        }

        $ipAddress = IpAddress::create($this->validated($request));
        $this->audit('create', $ipAddress, null, $ipAddress->toArray(), $request);

        return response()->json($ipAddress->load($this->relations())->loadCount('vms'), 201);
    }

    public function update(Request $request, IpAddress $ipAddress)
    {
        $before = $ipAddress->toArray();
        $ipAddress->update($this->validated($request, $ipAddress));
        $this->audit('update', $ipAddress, $before, $ipAddress->fresh()->toArray(), $request);

        return $ipAddress->fresh($this->relations())->loadCount('vms');
    }

    public function ping(Request $request, IpAddress $ipAddress, IpPingService $pingService)
    {
        $before = $ipAddress->toArray();
        $result = $pingService->ping($ipAddress->ip);

        $ipAddress->update([
            'ping_status' => $result->status,
            'ping_latency_ms' => $result->latencyMs,
            'ping_checked_at' => now(),
        ]);

        $this->audit('ping', $ipAddress, $before, $ipAddress->fresh()->toArray(), $request);

        return $ipAddress->fresh($this->relations())->loadCount('vms');
    }

    public function pingAll(Request $request, IpPingService $pingService)
    {
        $rows = IpAddress::orderBy('ip')->get();
        $updated = [];

        foreach ($rows as $ipAddress) {
            $before = $ipAddress->toArray();
            $result = $pingService->ping($ipAddress->ip);

            $ipAddress->update([
                'ping_status' => $result->status,
                'ping_latency_ms' => $result->latencyMs,
                'ping_checked_at' => now(),
            ]);

            $fresh = $ipAddress->fresh($this->relations())->loadCount('vms');
            $this->audit('ping', $ipAddress, $before, $fresh->toArray(), $request);
            $updated[] = $fresh;
        }

        return [
            'total' => count($updated),
            'up' => collect($updated)->where('ping_status', 'up')->count(),
            'down' => collect($updated)->where('ping_status', 'down')->count(),
            'items' => $updated,
        ];
    }

    public function destroy(Request $request, IpAddress $ipAddress)
    {
        try {
            $before = $ipAddress->toArray();
            $ipAddress->delete();
            $this->audit('delete', $ipAddress, $before, null, $request);

            return response()->noContent();
        } catch (QueryException) {
            return response()->json(['message' => 'IP address masih dipakai oleh VM atau aplikasi.'], 409);
        }
    }

    private function validated(Request $request, ?IpAddress $ipAddress = null): array
    {
        return $request->validate([
            'ip' => ['required', 'ip', 'max:45', Rule::unique('ip_address', 'ip')->ignore($ipAddress?->id)],
            'jenis' => ['required', 'in:publik,private'],
            'assignment' => ['nullable', 'string', 'max:255'],
            'isp_id' => ['nullable', 'uuid', 'exists:isp,id'],
        ]);
    }

    private function storeCidr(Request $request)
    {
        $data = $request->validate([
            'ip' => ['required', 'string', 'max:45'],
            'jenis' => ['required', 'in:publik,private'],
            'assignment' => ['nullable', 'string', 'max:255'],
            'isp_id' => ['nullable', 'uuid', 'exists:isp,id'],
        ]);

        $hosts = $this->hostsFromCidr($data['ip']);
        $existing = IpAddress::whereIn('ip', $hosts)->pluck('ip')->all();
        $existingLookup = array_flip($existing);
        $created = [];
        $skipped = [];

        foreach ($hosts as $host) {
            if (isset($existingLookup[$host])) {
                $skipped[] = ['ip' => $host, 'reason' => 'already_exists'];
                continue;
            }

            $ipAddress = IpAddress::create([
                'ip' => $host,
                'jenis' => $data['jenis'],
                'assignment' => $data['assignment'] ?? null,
                'isp_id' => $data['isp_id'] ?? null,
            ]);
            $this->audit('create', $ipAddress, null, $ipAddress->toArray(), $request);
            $created[] = $ipAddress->load($this->relations())->loadCount('vms');
        }

        return response()->json([
            'created' => $created,
            'skipped' => $skipped,
            'total_created' => count($created),
            'total_skipped' => count($skipped),
        ], 201);
    }

    private function isCidr(string $value): bool
    {
        return str_contains($value, '/');
    }

    private function hostsFromCidr(string $cidr): array
    {
        if (! preg_match('/^(\d{1,3}(?:\.\d{1,3}){3})\/(\d{1,2})$/', $cidr, $matches)) {
            throw ValidationException::withMessages(['ip' => ['Format network CIDR tidak valid.']]);
        }

        $networkIp = $matches[1];
        $prefix = (int) $matches[2];

        if (! filter_var($networkIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || $prefix < 0 || $prefix > 32) {
            throw ValidationException::withMessages(['ip' => ['Network CIDR harus berupa IPv4 yang valid.']]);
        }

        if ($prefix < 24) {
            throw ValidationException::withMessages(['ip' => ['Bulk input network dibatasi maksimal /24.']]);
        }

        $networkLong = (int) sprintf('%u', ip2long($networkIp));
        $hostCount = 2 ** (32 - $prefix);

        if ($hostCount <= 2) {
            return [];
        }

        $mask = (0xFFFFFFFF << (32 - $prefix)) & 0xFFFFFFFF;
        $network = $networkLong & $mask;
        $broadcast = $network + $hostCount - 1;
        $hosts = [];

        for ($ip = $network + 1; $ip <= $broadcast - 1; $ip++) {
            $hosts[] = long2ip($ip);
        }

        return $hosts;
    }

    private function relations(): array
    {
        return [
            'isp:id,nama,tipe,bandwidth',
            'vms:id,nama,status',
        ];
    }

    private function audit(string $action, IpAddress $ipAddress, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'ip_address',
            'record_id' => $ipAddress->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

