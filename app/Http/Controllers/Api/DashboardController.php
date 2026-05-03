<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aplikasi;
use App\Models\Server;
use App\Models\VirtualMachine;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): array
    {
        $totalServer = Server::count();
        $totalVm = VirtualMachine::count();
        $totalAplikasi = Aplikasi::count();
        $appsAktif = Aplikasi::where('status', 'aktif')->count();
        $appsDataPribadi = Aplikasi::whereHas('dataAssets', fn ($query) => $query->where('contains_personal_data', true))->count();
        $serversProtected = DB::table('security_server')->distinct('server_id')->count('server_id');

        return [
            'metrics' => [
                ['label' => 'Server', 'value' => $totalServer, 'hint' => 'Baremetal terdaftar', 'tone' => 'blue'],
                ['label' => 'VM', 'value' => $totalVm, 'hint' => 'Beban virtualisasi', 'tone' => 'cyan'],
                ['label' => 'Aplikasi', 'value' => $totalAplikasi, 'hint' => "{$appsAktif} aktif", 'tone' => 'yellow'],
                ['label' => 'Data Pribadi', 'value' => $appsDataPribadi, 'hint' => 'Butuh kontrol PSE', 'tone' => 'red'],
            ],
            'capacity' => [
                'cpu_core' => (int) Server::sum('cpu_core'),
                'ram_gb' => (int) Server::sum('ram_gb'),
                'storage_gb' => (int) Server::sum('storage_gb'),
                'security_coverage' => $totalServer === 0 ? 0 : round(($serversProtected / $totalServer) * 100),
            ],
            'status' => [
                'server' => Server::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
                'vm' => VirtualMachine::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
                'aplikasi' => Aplikasi::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
            ],
            'priority' => Aplikasi::with(['opd:id,nama', 'dataAssets:id,aplikasi_id,classification_id,contains_personal_data,risk_total'])
                ->whereHas('dataAssets', fn ($query) => $query->where('contains_personal_data', true))
                ->orderByDesc('sla_persen')
                ->limit(5)
                ->get(['id', 'nama', 'opd_id', 'sla_persen', 'status']),
        ];
    }
}
