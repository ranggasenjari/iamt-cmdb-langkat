<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Models\VirtualMachine;
use Illuminate\Console\Command;

class SyncProxmoxVms extends Command
{
    protected $signature = 'proxmox:sync-vms {--stdin : Read VM data as JSON from STDIN}';

    protected $description = 'Sync VM data from Proxmox nodes via SSH or STDIN';

    protected array $nodes = [
        ['host' => '192.168.4.1',  'label' => 'Node 1', 'server_nama' => 'NODE-01'],
        ['host' => '192.168.4.3',  'label' => 'Node 3', 'server_nama' => 'NODE-03'],
        ['host' => '192.168.4.4',  'label' => 'Node 4', 'server_nama' => 'NODE-04'],
        ['host' => '192.168.4.5',  'label' => 'Node 5', 'server_nama' => 'NODE-05'],
        ['host' => '192.168.4.6',  'label' => 'Node 6', 'server_nama' => 'NODE-06'],
    ];

    public function handle(): int
    {
        if ($this->option('stdin')) {
            return $this->handleStdin();
        }

        return $this->handleSsh();
    }

    protected function handleStdin(): int
    {
        $json = file_get_contents('php://stdin');
        $allData = json_decode($json, true);

        if (!$allData) {
            $this->error('Invalid JSON');
            return 1;
        }

        // Accept both single node object or array of nodes
        $nodes = isset($allData['node']) ? [$allData] : $allData;
        $total = 0;

        foreach ($nodes as $data) {
            if (!isset($data['node'], $data['vms'])) continue;

            $server = Server::firstOrCreate(
                ['nama' => $data['server_nama'] ?? $data['label'] ?? $data['node']],
                ['nama' => $data['server_nama'] ?? $data['label'] ?? $data['node'], 'status' => 'aktif']
            );

            $count = 0;
            foreach ($data['vms'] as $vm) {
                $this->syncVm($vm, $server->id);
                $count++;
                $total++;
            }

            $this->line("  {$data['label']}: {$count} VM");
        }

        $this->info("Total {$total} VM tersinkronisasi.");
        return 0;
    }

    protected function handleSsh(): int
    {
        $this->info('Memulai sinkronisasi VM dari Proxmox...');
        $total = 0;

        foreach ($this->nodes as $node) {
            $this->line("  → {$node['label']} ({$node['host']})");
            $vms = $this->fetchVms($node['host']);

            if ($vms === null) {
                $this->warn('    Gagal terkoneksi, lewati.');
                continue;
            }

            if (empty($vms)) {
                $this->line('    Tidak ada VM.');
                continue;
            }

            $server = $this->resolveServer($node);

            foreach ($vms as $vmData) {
                $this->syncVm($vmData, $server->id);
                $total++;
            }

            $this->info("    {$total} VM diproses.");
        }

        $this->info("Selesai. Total {$total} VM tersinkronisasi.");
        return 0;
    }

    protected function fetchVms(string $host): ?array
    {
        $sshBase = sprintf('ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new root@%s', $host);
        $list = shell_exec("{$sshBase} 'qm list 2>&1; echo __END__' 2>/dev/null");

        if ($list === null) return null;

        // Strip the sentinel and trailing whitespace
        $list = str_replace("__END__\n", '', $list);
        $list = str_replace("__END__", '', $list);
        $list = trim($list);

        $lines = explode("\n", $list);
        $header = true;
        $vms = [];

        foreach ($lines as $line) {
            if ($header) { $header = false; continue; }
            if (!trim($line)) continue;

            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) < 2) continue;

            $vmid = $parts[0];
            $name = $parts[1] ?? '';
            $status = $parts[2] ?? '';

            $config = shell_exec("{$sshBase} 'qm config {$vmid}' 2>/dev/null");
            $specs = $this->parseConfig($config ?? '');

            $vms[] = [
                'vmid' => $vmid,
                'nama' => $name,
                'status' => $status,
                'os' => $specs['os'] ?? null,
                'vcpu' => $specs['vcpu'] ?? null,
                'ram_mb' => $specs['ram_mb'] ?? null,
                'storage_gb' => $specs['storage_gb'] ?? null,
            ];
        }

        return $vms;
    }

    protected function parseConfig(string $config): array
    {
        $result = [];
        foreach (explode("\n", trim($config)) as $line) {
            if (str_contains($line, ':')) {
                [$key, $val] = explode(':', $line, 2);
                $key = trim($key);
                $val = trim($val);

                if ($key === 'ostype') $result['os'] = $val;
                if ($key === 'cores') $result['vcpu'] = (int) $val;
                if ($key === 'memory') $result['ram_mb'] = (int) $val;
                if ($key === 'name') $result['name'] = $val;
                if (str_starts_with($key, 'scsi') || str_starts_with($key, 'virtio') || str_starts_with($key, 'ide') || str_starts_with($key, 'sata')) {
                    if (preg_match('/size=(\d+)G/', $val, $m)) {
                        $result['storage_gb'] = ($result['storage_gb'] ?? 0) + (int) $m[1];
                    }
                }
            }
        }
        return $result;
    }

    protected function resolveServer(array $node): Server
    {
        $nama = $node['server_nama'] ?? $node['label'];
        return Server::firstOrCreate(
            ['nama' => $nama],
            ['nama' => $nama, 'status' => 'aktif']
        );
    }

    protected function syncVm(array $vmData, string $serverId): void
    {
        $statusMap = [
            'running' => 'running',
            'stopped' => 'stopped',
            'suspended' => 'suspended',
        ];

        $status = $statusMap[$vmData['status']] ?? 'stopped';
        $ramGb = $vmData['ram_mb'] ? round($vmData['ram_mb'] / 1024, 1) : null;

        VirtualMachine::updateOrCreate(
            ['nama' => $vmData['nama']],
            [
                'server_id' => $serverId,
                'os' => $vmData['os'],
                'vcpu' => $vmData['vcpu'],
                'ram_gb' => $ramGb,
                'storage_gb' => $vmData['storage_gb'],
                'status' => $status,
            ]
        );
    }
}
