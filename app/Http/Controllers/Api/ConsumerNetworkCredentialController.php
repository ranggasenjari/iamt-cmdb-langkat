<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ConsumerNetworkCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ConsumerNetworkCredentialController extends Controller
{
    public function index()
    {
        return ConsumerNetworkCredential::with($this->relations())
            ->orderByDesc('updated_at')
            ->get();
    }

    public function show(ConsumerNetworkCredential $networkCredential)
    {
        return $networkCredential->load($this->relations());
    }

    public function store(Request $request)
    {
        $credential = ConsumerNetworkCredential::create($this->modelData($this->validated($request)));
        $this->audit('create', $credential, null, $credential->toArray(), $request);

        return response()->json($credential->fresh($this->relations()), 201);
    }

    public function update(Request $request, ConsumerNetworkCredential $networkCredential)
    {
        $before = $networkCredential->toArray();
        $networkCredential->update($this->modelData($this->validated($request), $networkCredential));
        $after = $networkCredential->fresh()->toArray();
        $this->audit('update', $networkCredential, $before, $after, $request);

        return $networkCredential->fresh($this->relations());
    }

    public function destroy(Request $request, ConsumerNetworkCredential $networkCredential)
    {
        $before = $networkCredential->toArray();
        $networkCredential->delete();
        $this->audit('delete', $networkCredential, $before, null, $request);

        return response()->noContent();
    }

    public function revealPassword(Request $request, ConsumerNetworkCredential $networkCredential)
    {
        $data = $request->validate([
            'account_password' => ['required', 'string'],
        ]);

        $user = $request->attributes->get('auth_user');

        if (! Hash::check($data['account_password'], $user?->password ?? '')) {
            $this->audit('reveal_password_failed', $networkCredential, null, [
                'credential_id' => $networkCredential->id,
                'label' => $networkCredential->label,
                'reason' => 'invalid_account_password',
            ], $request);

            throw ValidationException::withMessages([
                'account_password' => ['Password akun tidak sesuai.'],
            ]);
        }

        if (blank($networkCredential->password)) {
            $this->audit('reveal_password_failed', $networkCredential, null, [
                'credential_id' => $networkCredential->id,
                'label' => $networkCredential->label,
                'reason' => 'empty_credential_password',
            ], $request);

            return response()->json(['message' => 'Password kredensial belum tersimpan.'], 422);
        }

        $this->audit('reveal_password', $networkCredential, null, [
            'credential_id' => $networkCredential->id,
            'label' => $networkCredential->label,
            'device_id' => $networkCredential->device_id,
            'site_id' => $networkCredential->site_id,
        ], $request);

        return [
            'id' => $networkCredential->id,
            'label' => $networkCredential->label,
            'password' => $networkCredential->password,
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'device_id' => ['required', 'uuid', 'exists:consumer_network_devices,id'],
            'site_id' => ['nullable', 'uuid', 'exists:consumer_network_sites,id'],
            'label' => ['required', 'string', 'max:255'],
            'access_method' => ['required', 'in:web,ssh,winbox,snmp,api,vpn,lainnya'],
            'management_url' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'last_rotated_at' => ['nullable', 'date'],
        ]);
    }

    private function modelData(array $data, ?ConsumerNetworkCredential $credential = null): array
    {
        if (! array_key_exists('password', $data) || blank($data['password'])) {
            unset($data['password']);
        } else {
            $data['last_rotated_at'] ??= now()->toDateString();
        }

        return $data;
    }

    private function relations(): array
    {
        return [
            'device:id,nama,jenis,asset_code',
            'site:id,nama,kode,jenis,asset_code',
        ];
    }

    private function audit(string $action, ConsumerNetworkCredential $credential, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'consumer_network_credentials',
            'record_id' => $credential->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
