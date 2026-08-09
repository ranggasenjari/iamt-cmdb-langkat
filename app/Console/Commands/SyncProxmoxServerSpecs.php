<?php

namespace App\Console\Commands;

use App\Models\Server;
use Illuminate\Console\Command;

class SyncProxmoxServerSpecs extends Command
{
    protected $signature = 'proxmox:sync-server-specs';

    protected $description = 'Update NODE-XX server hardware specs from live Proxmox data';

    protected array $nodes = [
        ['host' => '192.168.4.1',  'server_nama' => 'NODE-01'],
        ['host' => '192.168.4.3',  'server_nama' => 'NODE-03'],
        ['host' => '192.168.4.4',  'server_nama' => 'NODE-04'],
        ['host' => '192.168.4.5',  'server_nama' => 'NODE-05'],
        ['host' => '192.168.4.6',  'server_nama' => 'NODE-06'],
    ];

    public function handle(): int
    {
        $this->info('Mengambil spesifikasi server dari Proxmox...');

        foreach ($this->nodes as $node) {
            $specs = $this->fetchSpecs($node['host']);

            if ($specs === null) {
                $this->warn("  {$node['server_nama']} ({$node['host']}) — gagal terkoneksi.");
                continue;
            }

            $server = Server::where('nama', $node['server_nama'])->first();
            if (!$server) {
                $this->warn("  {$node['server_nama']} tidak ditemukan di tabel server.");
                continue;
            }

            $server->update([
                'merk_processor' => $specs['proc'],
                'cpu_core' => $specs['cores'],
                'ram_gb' => $specs['ram_gb'],
                'storage_gb' => $specs['storage_gb'],
            ]);

            $this->line("  {$node['server_nama']}: {$specs['proc']} | {$specs['cores']} core | {$specs['ram_gb']} GB RAM | {$specs['storage_gb']} GB");
        }

        $this->info('Selesai.');
        return 0;
    }

    protected function fetchSpecs(string $host): ?array
    {
        $sshBase = sprintf('ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new root@%s', $host);

        $proc = shell_exec("{$sshBase} 'cat /proc/cpuinfo | grep -m1 \"model name\" | cut -d: -f2 | xargs' 2>/dev/null");
        $cores = shell_exec("{$sshBase} 'grep -c processor /proc/cpuinfo' 2>/dev/null");
        $ram = shell_exec("{$sshBase} 'free -m | awk \"/Mem:/{print int(\$2/1024+0.5)}\"' 2>/dev/null");
        $storage = shell_exec("{$sshBase} 'lsblk -b -d -o SIZE | awk \"NR>1 {s+=\$1} END {print int(s/1024/1024/1024+0.5)}\"' 2>/dev/null");

        if ($proc === null && $cores === null) return null;

        return [
            'proc' => trim($proc ?? '-'),
            'cores' => (int) trim($cores ?? 0),
            'ram_gb' => (int) trim($ram ?? 0),
            'storage_gb' => (int) trim($storage ?? 0),
        ];
    }
}