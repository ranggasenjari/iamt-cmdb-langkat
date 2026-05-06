<?php

namespace Tests\Feature;

use App\Models\Server;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\OpdSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CmdbApiTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAs(string $role = 'full'): void
    {
        $email = $role === 'full' ? 'admin@langkatkab.go.id' : 'viewer@langkatkab.go.id';

        $token = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => 'password',
        ])->assertOk()->json('token');

        $this->withHeader('Authorization', "Bearer {$token}");
    }

    public function test_dashboard_returns_cmdb_metrics(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->authenticateAs();

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('metrics.0.label', 'Server')
            ->assertJsonPath('capacity.cpu_core', 80);

        $this->getJson('/api/servers')
            ->assertOk()
            ->assertJsonPath('0.rack_size_u', 2);
    }

    public function test_dependency_map_and_compliance_are_available(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->authenticateAs();

        $this->getJson('/api/dependency-map')
            ->assertOk()
            ->assertJsonCount(2);

        $this->getJson('/api/compliance')
            ->assertOk()
            ->assertJsonPath('summary.data_pribadi', 2);

        $this->getJson('/api/references')
            ->assertOk()
            ->assertJsonFragment(['nama' => 'Dinas Komunikasi Dan Informatika'])
            ->assertJsonFragment(['nama' => 'Puskes. Tungkit']);
    }

    public function test_server_impact_lists_affected_applications(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->authenticateAs();
        $server = Server::where('nama', 'SRV-PROD-01')->firstOrFail();

        $this->getJson("/api/impact/server/{$server->id}")
            ->assertOk()
            ->assertJsonPath('summary.total_aplikasi', 2)
            ->assertJsonPath('summary.risk_level', 'tinggi');
    }

    public function test_infrastructure_master_data_can_be_managed(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->authenticateAs();

        $dcId = $this->postJson('/api/data-centers', [
            'nama' => 'DR Center Langkat',
            'lokasi' => 'Tanjung Pura',
            'tipe' => 'dr',
        ])->assertCreated()->json('id');

        $this->postJson('/api/racks', [
            'dc_id' => $dcId,
            'nama' => 'Rack DR-01',
            'kapasitas_u' => 24,
        ])->assertCreated()->assertJsonPath('nama', 'Rack DR-01');

        $ispId = $this->postJson('/api/isps', [
            'nama' => 'Metro Langkat Backup',
            'tipe' => 'Wireless',
            'bandwidth' => '100 Mbps',
            'kontak' => 'noc@example.test',
        ])->assertCreated()->json('id');

        $this->postJson('/api/ip-addresses', [
            'ip' => '10.99.10.12',
            'jenis' => 'private',
            'isp_id' => $ispId,
        ])->assertCreated()->assertJsonPath('ip', '10.99.10.12');

        $this->getJson('/api/data-centers')->assertOk()->assertJsonFragment(['nama' => 'DR Center Langkat']);
        $this->getJson('/api/racks')->assertOk()->assertJsonFragment(['nama' => 'Rack DR-01']);
        $this->getJson('/api/isps')->assertOk()->assertJsonFragment(['nama' => 'Metro Langkat Backup']);
        $this->getJson('/api/ip-addresses')->assertOk()->assertJsonFragment(['ip' => '10.99.10.12']);
    }

    public function test_application_data_assets_can_be_classified(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->authenticateAs();

        $application = \App\Models\Aplikasi::where('nama', 'Register PSE Langkat')->firstOrFail();
        $applicationId = $application->id;
        $classificationId = \App\Models\DataClassification::where('code', 'RESTRICTED')->firstOrFail()->id;

        $this->putJson("/api/applications/{$applicationId}", [
            'nama' => $application->nama,
            'url' => $application->url,
            'opd_id' => $application->opd_id,
            'jenis_aplikasi' => 'web',
            'pengembang' => 'pihak_ketiga',
            'tech_stack' => 'Laravel, Vue, MariaDB',
            'status' => 'aktif',
        ])->assertOk()
            ->assertJsonPath('pengembang', 'pihak_ketiga')
            ->assertJsonPath('tech_stack', 'Laravel, Vue, MariaDB');

        $assetId = $this->postJson('/api/data-assets', [
            'aplikasi_id' => $applicationId,
            'classification_id' => $classificationId,
            'name' => 'users.email',
            'type' => 'COLUMN',
            'attributes' => "Nama\nEmail",
            'owner_agency' => 'Diskominfo Kabupaten Langkat',
            'confidentiality_score' => 3,
            'integrity_score' => 3,
            'availability_score' => 3,
            'table_name' => 'users',
            'column_name' => 'email',
            'contains_personal_data' => true,
            'personal_data_type' => 'Email pengguna',
            'processing_purpose' => 'Autentikasi dan notifikasi',
            'retention_period' => '5 tahun',
            'storage_location' => 'Database produksi',
            'data_owner' => 'Diskominfo Kabupaten Langkat',
            'access_policy' => 'Admin aplikasi dan audit log wajib.',
        ])->assertCreated()->assertJsonPath('name', 'users.email')->json('id');

        $this->getJson('/api/data-assets')
            ->assertOk()
            ->assertJsonFragment(['name' => 'users.email'])
            ->assertJsonFragment(['code' => 'RESTRICTED']);

        $this->putJson("/api/data-assets/{$assetId}", [
            'aplikasi_id' => $applicationId,
            'classification_id' => $classificationId,
            'name' => 'users.email',
            'type' => 'COLUMN',
            'confidentiality_score' => 3,
            'integrity_score' => 5,
            'availability_score' => 3,
            'contains_personal_data' => true,
            'retention_period' => 'Selama akun aktif',
        ])->assertOk()->assertJsonPath('retention_period', 'Selama akun aktif');
    }

    public function test_application_documents_integrations_and_backup_can_be_managed(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->authenticateAs();

        $sourceApp = \App\Models\Aplikasi::where('nama', 'Register PSE Langkat')->firstOrFail();
        $targetApp = \App\Models\Aplikasi::where('nama', 'Portal Kabupaten Langkat')->firstOrFail();
        $dataAsset = \App\Models\DataAsset::where('aplikasi_id', $sourceApp->id)->firstOrFail();

        $this->post('/api/application-documents', [
            'aplikasi_id' => $sourceApp->id,
            'document_category' => 'keamanan',
            'files' => [UploadedFile::fake()->create('sop-keamanan.pdf', 64, 'application/pdf')],
        ])->assertCreated()->assertJsonFragment(['document_category' => 'keamanan']);

        $this->post('/api/app-integrations', [
            'aplikasi_id' => $sourceApp->id,
            'deskripsi' => 'Integrasi status layanan ke portal.',
            'jenis_integrasi' => 'berbagi_data',
            'metode_integrasi' => 'spl',
            'external_endpoints' => 'https://api.example.test/status',
            'target_application_ids' => [$targetApp->id],
            'data_asset_ids' => [$dataAsset->id],
            'documents' => [UploadedFile::fake()->create('desain-integrasi.pdf', 32, 'application/pdf')],
        ])->assertCreated()->assertJsonPath('jenis_integrasi', 'berbagi_data');

        $mediaId = $this->postJson('/api/backup-media', [
            'nama' => 'NAS Backup DC',
            'location' => 'local',
            'jenis_media' => 'NAS',
            'kapasitas_gb' => 4096,
            'address_url' => '10.30.10.20',
        ])->assertCreated()->json('id');

        $this->postJson('/api/backup-jobs', [
            'aplikasi_id' => $sourceApp->id,
            'backup_media_id' => $mediaId,
            'retensi_n' => 30,
            'retensi_unit' => 'hari',
            'repetisi_n' => 1,
            'repetisi_unit' => 'hari',
        ])->assertCreated()->assertJsonPath('retensi_unit', 'hari');
    }

    public function test_ups_and_soc_modules_can_be_managed(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->authenticateAs();

        $dc = \App\Models\DataCenter::firstOrFail();
        $server = \App\Models\Server::firstOrFail();
        $vm = \App\Models\VirtualMachine::firstOrFail();
        $app = \App\Models\Aplikasi::firstOrFail();

        $this->postJson('/api/ups-devices', [
            'nama' => 'UPS Rack A01',
            'kapasitas_va' => 3000,
            'kondisi' => 'baik',
            'dc_id' => $dc->id,
        ])->assertCreated()->assertJsonPath('nama', 'UPS Rack A01');

        $this->postJson('/api/soc-tools', [
            'nama' => 'SIEM Kabupaten Langkat',
            'deskripsi_fungsi' => 'Korelasi log keamanan infrastruktur.',
            'jenis' => 'SIEM',
            'dc_ids' => [$dc->id],
            'server_ids' => [$server->id],
            'vm_ids' => [$vm->id],
            'application_ids' => [$app->id],
        ])->assertCreated()->assertJsonPath('jenis', 'SIEM');

        $this->getJson('/api/soc-tools')
            ->assertOk()
            ->assertJsonFragment(['nama' => 'SIEM Kabupaten Langkat']);
    }

    public function test_server_and_vm_specification_changes_are_logged_with_reason(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->authenticateAs();

        $server = Server::where('nama', 'SRV-PROD-01')->firstOrFail();
        $newRam = $server->ram_gb + 16;

        $this->putJson("/api/servers/{$server->id}", [
            'nama' => $server->nama,
            'ram_gb' => $newRam,
            'merk_processor' => 'AMD EPYC',
            'rack_size_u' => 4,
            'change_reason' => 'Upgrade RAM untuk beban layanan meningkat.',
            'changed_by' => 'Admin Infrastruktur',
        ])->assertOk()
            ->assertJsonPath('ram_gb', $newRam)
            ->assertJsonPath('merk_processor', 'AMD EPYC')
            ->assertJsonPath('rack_size_u', 4);

        $this->getJson("/api/asset-change-logs?asset_type=server&asset_id={$server->id}")
            ->assertOk()
            ->assertJsonPath('0.asset_type', 'server')
            ->assertJsonPath('0.asset_name', 'SRV-PROD-01')
            ->assertJsonPath('0.change_type', 'spesifikasi')
            ->assertJsonPath('0.reason', 'Upgrade RAM untuk beban layanan meningkat.')
            ->assertJsonPath('0.changed_by', 'Admin Infrastruktur')
            ->assertJsonPath('0.changed_fields.ram_gb.before', $server->ram_gb)
            ->assertJsonPath('0.changed_fields.ram_gb.after', $newRam)
            ->assertJsonPath('0.changed_fields.merk_processor.after', 'AMD EPYC')
            ->assertJsonPath('0.changed_fields.rack_size_u.after', 4);

        $vm = \App\Models\VirtualMachine::where('nama', 'VM-PSE-REGISTRY')->firstOrFail();
        $newVcpu = $vm->vcpu + 2;

        $this->putJson("/api/vms/{$vm->id}", [
            'nama' => $vm->nama,
            'vcpu' => $newVcpu,
            'status' => 'maintenance',
            'change_reason' => 'Penyesuaian resource saat maintenance aplikasi.',
            'changed_by' => 'Admin Virtualisasi',
        ])->assertOk()->assertJsonPath('vcpu', $newVcpu);

        $this->getJson("/api/asset-change-logs?asset_type=vm&asset_id={$vm->id}")
            ->assertOk()
            ->assertJsonPath('0.asset_type', 'vm')
            ->assertJsonPath('0.asset_name', 'VM-PSE-REGISTRY')
            ->assertJsonPath('0.change_type', 'spesifikasi')
            ->assertJsonPath('0.reason', 'Penyesuaian resource saat maintenance aplikasi.')
            ->assertJsonPath('0.changed_by', 'Admin Virtualisasi')
            ->assertJsonPath('0.changed_fields.vcpu.before', $vm->vcpu)
            ->assertJsonPath('0.changed_fields.vcpu.after', $newVcpu);
    }

    public function test_authentication_and_read_only_role_enforcement(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->getJson('/api/dashboard')->assertUnauthorized();

        $this->authenticateAs('read_only');

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('metrics.0.label', 'Server');

        $this->postJson('/api/data-centers', [
            'nama' => 'DC Read Only',
            'lokasi' => 'Stabat',
            'tipe' => 'utama',
        ])->assertForbidden();
    }

    public function test_full_access_user_can_manage_auth_users(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->authenticateAs();

        $userId = $this->postJson('/api/users', [
            'nama' => 'Auditor CMDB',
            'email' => 'auditor@langkatkab.go.id',
            'password' => 'password',
            'role' => 'read_only',
            'status' => 'aktif',
        ])->assertCreated()->assertJsonPath('role', 'read_only')->json('id');

        $this->putJson("/api/users/{$userId}", [
            'nama' => 'Auditor CMDB',
            'email' => 'auditor@langkatkab.go.id',
            'role' => 'full',
            'status' => 'aktif',
        ])->assertOk()->assertJsonPath('role', 'full');

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonFragment(['email' => 'auditor@langkatkab.go.id']);
    }

    public function test_opd_seeder_only_adds_missing_opd_records(): void
    {
        DB::table('opd')->insert([
            'id' => (string) Str::uuid(),
            'nama' => 'Dinas Kesehatan',
            'kontak' => 'existing@example.test',
            'created_at' => now(),
        ]);

        $this->seed(OpdSeeder::class);
        $firstCount = DB::table('opd')->count();

        $this->seed(OpdSeeder::class);

        $this->assertSame($firstCount, DB::table('opd')->count());
        $this->assertSame('existing@example.test', DB::table('opd')->where('nama', 'Dinas Kesehatan')->value('kontak'));
        $this->assertDatabaseHas('opd', ['nama' => 'Puskes. Tungkit']);
    }

    public function test_delete_returns_clear_message_when_entity_has_child_records(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->authenticateAs();

        $server = Server::where('nama', 'SRV-PROD-01')->firstOrFail();

        $this->deleteJson("/api/servers/{$server->id}")
            ->assertStatus(409)
            ->assertJsonPath('type', 'constraint_violation')
            ->assertJsonPath('message', 'Terdapat data dibawah entitas ini. Hapus atau lepaskan relasi data terkait terlebih dahulu sebelum menghapus data utama.');
    }

    public function test_rest_api_detail_endpoints_are_available_for_all_managed_modules(): void
    {
        $this->seed(DatabaseSeeder::class);
        $application = \App\Models\Aplikasi::firstOrFail();
        $dataCenter = \App\Models\DataCenter::firstOrFail();
        $backupMedia = \App\Models\BackupMedia::create([
            'nama' => 'Backup Media Detail Test',
            'location' => 'local',
            'jenis_media' => 'NAS',
            'kapasitas_gb' => 512,
        ]);
        $backupJob = \App\Models\BackupJob::create([
            'aplikasi_id' => $application->id,
            'backup_media_id' => $backupMedia->id,
            'retensi_n' => 7,
            'retensi_unit' => 'hari',
            'repetisi_n' => 1,
            'repetisi_unit' => 'hari',
        ]);
        $applicationDocument = \App\Models\ApplicationDocument::create([
            'aplikasi_id' => $application->id,
            'jenis' => 'keamanan',
            'document_category' => 'keamanan',
            'nama' => 'Dokumen Detail Test',
            'path' => 'uploads/testing/detail.pdf',
            'original_name' => 'detail.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'tanggal' => now()->toDateString(),
        ]);
        $appIntegration = \App\Models\AppIntegration::create([
            'aplikasi_id' => $application->id,
            'jenis_integrasi' => 'berbagi_data',
            'metode_integrasi' => 'spl',
            'deskripsi' => 'Detail test',
        ]);
        $upsDevice = \App\Models\UpsDevice::create([
            'nama' => 'UPS Detail Test',
            'kapasitas_va' => 1000,
            'kondisi' => 'baik',
            'dc_id' => $dataCenter->id,
        ]);
        $socTool = \App\Models\SocTool::create([
            'nama' => 'SOC Detail Test',
            'jenis' => 'SIEM',
            'deskripsi_fungsi' => 'Detail endpoint test',
        ]);

        $this->authenticateAs('read_only');

        $ids = [
            'data-centers' => $dataCenter->id,
            'racks' => \App\Models\Rack::firstOrFail()->id,
            'servers' => \App\Models\Server::firstOrFail()->id,
            'vms' => \App\Models\VirtualMachine::firstOrFail()->id,
            'isps' => \App\Models\Isp::firstOrFail()->id,
            'ip-addresses' => \App\Models\IpAddress::firstOrFail()->id,
            'applications' => $application->id,
            'data-assets' => \App\Models\DataAsset::firstOrFail()->id,
            'data-classifications' => \App\Models\DataClassification::firstOrFail()->id,
            'application-documents' => $applicationDocument->id,
            'app-integrations' => $appIntegration->id,
            'backup-media' => $backupMedia->id,
            'backup-jobs' => $backupJob->id,
            'ups-devices' => $upsDevice->id,
            'soc-tools' => $socTool->id,
            'users' => \App\Models\Pengguna::where('email', 'viewer@langkatkab.go.id')->firstOrFail()->id,
        ];

        foreach ($ids as $module => $id) {
            $this->getJson("/api/{$module}/{$id}")->assertOk()->assertJsonPath('id', $id);
        }
    }

    public function test_delete_endpoints_work_for_each_managed_module_without_children(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->authenticateAs();

        $dcId = $this->postJson('/api/data-centers', ['nama' => 'DC Delete Test', 'lokasi' => 'Stabat', 'tipe' => 'utama'])->assertCreated()->json('id');
        $rackId = $this->postJson('/api/racks', ['dc_id' => $dcId, 'nama' => 'Rack Delete Test', 'kapasitas_u' => 12])->assertCreated()->json('id');
        $serverId = $this->postJson('/api/servers', ['nama' => 'SRV-DELETE-TEST', 'dc_id' => $dcId, 'rack_id' => $rackId, 'status' => 'aktif'])->assertCreated()->json('id');
        $vmId = $this->postJson('/api/vms', ['nama' => 'VM-DELETE-TEST', 'server_id' => $serverId, 'status' => 'running'])->assertCreated()->json('id');
        $ispId = $this->postJson('/api/isps', ['nama' => 'ISP Delete Test'])->assertCreated()->json('id');
        $ipId = $this->postJson('/api/ip-addresses', ['ip' => '10.88.88.88', 'jenis' => 'private', 'isp_id' => $ispId])->assertCreated()->json('id');
        $appId = $this->postJson('/api/applications', [
            'nama' => 'App Delete Test',
            'jenis_aplikasi' => 'web',
            'pengembang' => 'diskominfo_langkat',
            'status' => 'aktif',
        ])->assertCreated()->json('id');
        $classificationId = $this->postJson('/api/data-classifications', [
            'code' => 'DELETE_TEST',
            'name' => 'Delete Test',
            'risk_level' => 'LOW',
            'requires_encryption' => false,
            'requires_mfa' => false,
            'requires_audit_log' => true,
        ])->assertCreated()->json('id');
        $assetId = $this->postJson('/api/data-assets', [
            'aplikasi_id' => $appId,
            'classification_id' => $classificationId,
            'name' => 'delete_test.dataset',
            'type' => 'DATASET',
            'confidentiality_score' => 1,
            'integrity_score' => 1,
            'availability_score' => 1,
        ])->assertCreated()->json('id');
        $documentId = $this->post('/api/application-documents', [
            'aplikasi_id' => $appId,
            'document_category' => 'tata_kelola',
            'files' => [UploadedFile::fake()->create('delete-doc.pdf', 8, 'application/pdf')],
        ])->assertCreated()->json('0.id');
        $integrationId = $this->post('/api/app-integrations', [
            'aplikasi_id' => $appId,
            'jenis_integrasi' => 'proses_bisnis',
            'metode_integrasi' => 'host_to_host',
        ])->assertCreated()->json('id');
        $mediaId = $this->postJson('/api/backup-media', [
            'nama' => 'Media Delete Test',
            'location' => 'local',
            'jenis_media' => 'NAS',
        ])->assertCreated()->json('id');
        $backupJobId = $this->postJson('/api/backup-jobs', [
            'aplikasi_id' => $appId,
            'backup_media_id' => $mediaId,
            'retensi_n' => 7,
            'retensi_unit' => 'hari',
            'repetisi_n' => 1,
            'repetisi_unit' => 'hari',
        ])->assertCreated()->json('id');
        $upsId = $this->postJson('/api/ups-devices', [
            'nama' => 'UPS Delete Test',
            'kapasitas_va' => 1500,
            'kondisi' => 'baik',
            'dc_id' => $dcId,
        ])->assertCreated()->json('id');
        $socId = $this->postJson('/api/soc-tools', [
            'nama' => 'SOC Delete Test',
            'jenis' => 'SIEM',
        ])->assertCreated()->json('id');
        $userId = $this->postJson('/api/users', [
            'nama' => 'Delete User',
            'email' => 'delete.user@langkatkab.go.id',
            'password' => 'password',
            'role' => 'read_only',
            'status' => 'aktif',
        ])->assertCreated()->json('id');

        foreach ([
            "application-documents/{$documentId}",
            "app-integrations/{$integrationId}",
            "backup-jobs/{$backupJobId}",
            "backup-media/{$mediaId}",
            "data-assets/{$assetId}",
            "data-classifications/{$classificationId}",
            "soc-tools/{$socId}",
            "ups-devices/{$upsId}",
            "applications/{$appId}",
            "vms/{$vmId}",
            "ip-addresses/{$ipId}",
            "servers/{$serverId}",
            "isps/{$ispId}",
            "racks/{$rackId}",
            "data-centers/{$dcId}",
            "users/{$userId}",
        ] as $endpoint) {
            $this->deleteJson("/api/{$endpoint}")->assertNoContent();
        }
    }

}
