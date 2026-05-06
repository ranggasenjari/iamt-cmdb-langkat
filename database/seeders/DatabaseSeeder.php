<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $opdNames = OpdSeeder::NAMES;
        $opdIds = [];

        foreach ($opdNames as $opdName) {
            $opdIds[$opdName] = (string) Str::uuid();
        }

        $opdDiskominfo = $opdIds['Dinas Komunikasi Dan Informatika'];
        $opdDukcapil = $opdIds['Dinas Kependudukan Dan Pencatatan Sipil'];
        $dcUtama = (string) Str::uuid();
        $rackA = (string) Str::uuid();
        $serverMain = (string) Str::uuid();
        $serverBackup = (string) Str::uuid();
        $vmPse = (string) Str::uuid();
        $vmPortal = (string) Str::uuid();
        $vmBackup = (string) Str::uuid();
        $isp = (string) Str::uuid();
        $ipPublic = (string) Str::uuid();
        $ipPrivate = (string) Str::uuid();
        $appPse = (string) Str::uuid();
        $appPortal = (string) Str::uuid();
        $layanan = (string) Str::uuid();
        $edr = (string) Str::uuid();
        $waf = (string) Str::uuid();
        $backupPolicy = (string) Str::uuid();
        $backupTarget = (string) Str::uuid();

        DB::table('opd')->insert(array_map(fn (string $opdName) => [
            'id' => $opdIds[$opdName],
            'nama' => $opdName,
            'kontak' => $opdName === 'Dinas Komunikasi Dan Informatika' ? 'diskominfo@langkatkab.go.id' : null,
            'created_at' => $now,
        ], $opdNames));

        DB::table('pengguna')->insert([
            ['id' => (string) Str::uuid(), 'nama' => 'Administrator CMDB', 'email' => 'admin@langkatkab.go.id', 'password' => Hash::make('password'), 'opd_id' => $opdDiskominfo, 'role' => 'full', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['id' => (string) Str::uuid(), 'nama' => 'Viewer CMDB', 'email' => 'viewer@langkatkab.go.id', 'password' => Hash::make('password'), 'opd_id' => $opdDiskominfo, 'role' => 'read_only', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('data_center')->insert([
            ['id' => $dcUtama, 'nama' => 'Data Center Pemkab Langkat', 'lokasi' => 'Stabat', 'tipe' => 'utama'],
        ]);

        DB::table('rack')->insert([
            ['id' => $rackA, 'dc_id' => $dcUtama, 'nama' => 'Rack A01', 'kapasitas_u' => 42],
        ]);

        DB::table('server')->insert([
            ['id' => $serverMain, 'nama' => 'SRV-PROD-01', 'dc_id' => $dcUtama, 'rack_id' => $rackA, 'rack_size_u' => 2, 'merk' => 'Dell', 'tipe' => 'PowerEdge R750', 'serial_number' => 'LKT-PRD-001', 'merk_processor' => 'Intel Xeon Silver', 'cpu_core' => 48, 'ram_gb' => 256, 'storage_gb' => 8192, 'kondisi' => 'baik', 'status' => 'aktif', 'tahun' => 2024, 'penanggung_jawab' => 'Bidang Infrastruktur TIK', 'created_at' => $now],
            ['id' => $serverBackup, 'nama' => 'SRV-BACKUP-01', 'dc_id' => $dcUtama, 'rack_id' => $rackA, 'rack_size_u' => 2, 'merk' => 'HPE', 'tipe' => 'DL380 Gen10', 'serial_number' => 'LKT-BCK-001', 'merk_processor' => 'Intel Xeon Gold', 'cpu_core' => 32, 'ram_gb' => 128, 'storage_gb' => 16384, 'kondisi' => 'baik', 'status' => 'maintenance', 'tahun' => 2022, 'penanggung_jawab' => 'Bidang Infrastruktur TIK', 'created_at' => $now],
        ]);

        DB::table('vm')->insert([
            ['id' => $vmPse, 'nama' => 'VM-PSE-REGISTRY', 'server_id' => $serverMain, 'os' => 'Ubuntu Server 24.04 LTS', 'vcpu' => 8, 'ram_gb' => 16, 'storage_gb' => 240, 'status' => 'running'],
            ['id' => $vmPortal, 'nama' => 'VM-PORTAL-LANGKAT', 'server_id' => $serverMain, 'os' => 'Debian 12', 'vcpu' => 6, 'ram_gb' => 12, 'storage_gb' => 180, 'status' => 'running'],
            ['id' => $vmBackup, 'nama' => 'VM-BACKUP-VAULT', 'server_id' => $serverBackup, 'os' => 'Rocky Linux 9', 'vcpu' => 4, 'ram_gb' => 8, 'storage_gb' => 2048, 'status' => 'maintenance'],
        ]);

        DB::table('isp')->insert([
            ['id' => $isp, 'nama' => 'LangkatNet', 'tipe' => 'Fiber Dedicated', 'bandwidth' => '500 Mbps', 'kontak' => 'noc@langkatnet.id'],
        ]);

        DB::table('ip_address')->insert([
            ['id' => $ipPublic, 'ip' => '103.180.10.24', 'jenis' => 'publik', 'isp_id' => $isp],
            ['id' => $ipPrivate, 'ip' => '10.30.1.24', 'jenis' => 'private', 'isp_id' => null],
        ]);

        DB::table('vm_ip')->insert([
            ['vm_id' => $vmPse, 'ip_id' => $ipPrivate],
            ['vm_id' => $vmPortal, 'ip_id' => $ipPublic],
        ]);

        DB::table('aplikasi')->insert([
            ['id' => $appPse, 'nama' => 'Register PSE Langkat', 'url' => 'https://pse.langkatkab.go.id', 'opd_id' => $opdDiskominfo, 'deskripsi' => 'Portal pendataan dan kepatuhan layanan elektronik daerah.', 'jenis_aplikasi' => 'web', 'pengembang' => 'diskominfo_langkat', 'tech_stack' => 'Laravel, Vue, MySQL', 'status' => 'aktif', 'sla_persen' => 99.50, 'jam_operasional' => '24x7', 'kategori_data' => 'terbatas', 'mengandung_data_pribadi' => true, 'jenis_data_pribadi' => 'Nama, NIK, email, nomor telepon PIC', 'retensi_data' => '5 tahun', 'pic_nama' => 'Admin PSE', 'pic_kontak' => '0812-0000-0001', 'tanggal_go_live' => '2025-01-10', 'created_at' => $now, 'updated_at' => $now],
            ['id' => $appPortal, 'nama' => 'Portal Kabupaten Langkat', 'url' => 'https://langkatkab.go.id', 'opd_id' => $opdDiskominfo, 'deskripsi' => 'Portal informasi publik dan layanan dasar Pemkab Langkat.', 'jenis_aplikasi' => 'web', 'pengembang' => 'diskominfo_langkat', 'tech_stack' => 'CMS, Nginx, MySQL', 'status' => 'aktif', 'sla_persen' => 99.00, 'jam_operasional' => '24x7', 'kategori_data' => 'publik', 'mengandung_data_pribadi' => false, 'jenis_data_pribadi' => null, 'retensi_data' => 'Arsip berkala', 'pic_nama' => 'Webmaster', 'pic_kontak' => '0812-0000-0002', 'tanggal_go_live' => '2024-06-15', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('aplikasi_vm')->insert([
            ['aplikasi_id' => $appPse, 'vm_id' => $vmPse],
            ['aplikasi_id' => $appPortal, 'vm_id' => $vmPortal],
        ]);

        DB::table('aplikasi_server')->insert([
            ['aplikasi_id' => $appPse, 'server_id' => $serverMain],
            ['aplikasi_id' => $appPortal, 'server_id' => $serverMain],
        ]);

        DB::table('aplikasi_ip')->insert([
            ['aplikasi_id' => $appPortal, 'ip_id' => $ipPublic],
        ]);

        $limitedClass = DB::table('data_classifications')->where('code', 'LIMITED')->value('id');
        $restrictedClass = DB::table('data_classifications')->where('code', 'RESTRICTED')->value('id');
        $openClass = DB::table('data_classifications')->where('code', 'OPEN')->value('id');

        if ($limitedClass && $restrictedClass && $openClass) {
            DB::table('data_assets')->insert([
                [
                    'aplikasi_id' => $appPse,
                    'classification_id' => $restrictedClass,
                    'name' => 'pse_contacts.nik',
                    'type' => 'COLUMN',
                    'attributes' => "Nama\nNIK\nAlamat\nKontak PIC",
                    'owner_agency' => 'Diskominfo Kabupaten Langkat',
                    'confidentiality_score' => 5,
                    'integrity_score' => 5,
                    'availability_score' => 3,
                    'risk_total' => 13,
                    'table_name' => 'pse_contacts',
                    'column_name' => 'nik',
                    'contains_personal_data' => true,
                    'personal_data_type' => 'NIK dan identitas PIC',
                    'processing_purpose' => 'Verifikasi penanggung jawab layanan elektronik',
                    'retention_period' => '5 tahun',
                    'storage_location' => 'Database produksi Register PSE Langkat',
                    'data_owner' => 'Diskominfo Kabupaten Langkat',
                    'access_policy' => 'Hanya admin PSE dan pejabat berwenang; MFA wajib.',
                    'description' => 'Kolom identitas sensitif untuk kebutuhan registrasi PSE.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'aplikasi_id' => $appPse,
                    'classification_id' => $limitedClass,
                    'name' => 'api/pse/submissions',
                    'type' => 'API',
                    'attributes' => "Nama\nEmail\nNomor telepon PIC\nStatus permohonan",
                    'owner_agency' => 'Diskominfo Kabupaten Langkat',
                    'confidentiality_score' => 3,
                    'integrity_score' => 5,
                    'availability_score' => 3,
                    'risk_total' => 11,
                    'table_name' => null,
                    'column_name' => null,
                    'contains_personal_data' => true,
                    'personal_data_type' => 'Nama, email, nomor telepon PIC',
                    'processing_purpose' => 'Pertukaran data permohonan dan status PSE',
                    'retention_period' => '5 tahun',
                    'storage_location' => 'VM-PSE-REGISTRY',
                    'data_owner' => 'Diskominfo Kabupaten Langkat',
                    'access_policy' => 'Token aplikasi dan audit log wajib.',
                    'description' => 'Endpoint integrasi untuk data permohonan PSE.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'aplikasi_id' => $appPortal,
                    'classification_id' => $openClass,
                    'name' => 'news_articles',
                    'type' => 'TABLE',
                    'attributes' => "Judul berita\nIsi berita\nTanggal publikasi\nKategori",
                    'owner_agency' => 'Diskominfo Kabupaten Langkat',
                    'confidentiality_score' => 1,
                    'integrity_score' => 3,
                    'availability_score' => 3,
                    'risk_total' => 7,
                    'table_name' => 'news_articles',
                    'column_name' => null,
                    'contains_personal_data' => false,
                    'personal_data_type' => null,
                    'processing_purpose' => 'Publikasi informasi pemerintah daerah',
                    'retention_period' => 'Arsip berkala',
                    'storage_location' => 'VM-PORTAL-LANGKAT',
                    'data_owner' => 'Diskominfo Kabupaten Langkat',
                    'access_policy' => 'Publik untuk baca, admin web untuk ubah.',
                    'description' => 'Konten berita publik portal resmi.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            foreach ([
                ['Peminjaman ruangan', "Nama peminjam\nTanggal pemakaian\nLama pemakaian\nTujuan pemakaian", 'Semua K/L/D', 1, 1, 3, 'OPEN'],
                ['KTP', "Nama\nNIK\nAlamat\nTempat dan tanggal lahir\nAgama\nPekerjaan", 'Kementerian Dalam Negeri', 3, 5, 3, 'LIMITED'],
                ['Beban Kerja Pegawai', "Fungsi unit organisasi\nRincian tugas\nJangka waktu pekerjaan\nJenis pekerjaan\nNama pegawai", 'BKN', 1, 3, 3, 'OPEN'],
                ['Alutsista', "Nama sistem senjata\nPengelola\nJumlah\nLokasi penyimpanan\nKondisi", 'Kementerian Pertahanan', 5, 5, 5, 'RESTRICTED'],
                ['Daftar Fasilitas Kesehatan', "Nama faskes\nTipe faskes\nLokasi\nAlamat\nKontak", 'Kementerian Kesehatan', 1, 3, 3, 'OPEN'],
                ['Penerima Manfaat Bantuan Sosial', "Nama penerima\nNIK\nTempat dan tanggal lahir\nAlamat\nKontak\nBesaran manfaat", 'Kementerian Sosial', 3, 3, 3, 'LIMITED'],
                ['Undangan Rapat', "Hari dan tanggal\nWaktu\nLokasi\nAgenda\nDaftar undangan", 'Semua K/L/D', 1, 3, 3, 'OPEN'],
            ] as [$name, $attributes, $owner, $confidentiality, $integrity, $availability, $code]) {
                DB::table('data_assets')->insert([
                    'aplikasi_id' => $appPortal,
                    'classification_id' => DB::table('data_classifications')->where('code', $code)->value('id'),
                    'name' => $name,
                    'type' => 'DATASET',
                    'attributes' => $attributes,
                    'owner_agency' => $owner,
                    'confidentiality_score' => $confidentiality,
                    'integrity_score' => $integrity,
                    'availability_score' => $availability,
                    'risk_total' => $confidentiality + $integrity + $availability,
                    'contains_personal_data' => in_array($name, ['KTP', 'Penerima Manfaat Bantuan Sosial', 'Beban Kerja Pegawai'], true),
                    'personal_data_type' => in_array($name, ['KTP', 'Penerima Manfaat Bantuan Sosial'], true) ? 'NIK, identitas, alamat, kontak' : null,
                    'processing_purpose' => 'Contoh studi kasus klasifikasi data sesuai Lampiran II Permenkomdigi No. 5 Tahun 2025',
                    'retention_period' => 'Mengikuti kebijakan retensi produsen data',
                    'storage_location' => 'Contoh regulasi',
                    'data_owner' => $owner,
                    'access_policy' => 'Disesuaikan dengan hasil klasifikasi risiko.',
                    'description' => 'Contoh data elektronik pada studi kasus regulasi.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        DB::table('layanan')->insert([
            ['id' => $layanan, 'nama' => 'Layanan Informasi Publik Digital', 'deskripsi' => 'Layanan akses informasi resmi pemerintah daerah.', 'opd_id' => $opdDiskominfo, 'status' => 'aktif', 'kategori_data' => 'publik', 'pic_nama' => 'Sekretariat Diskominfo', 'pic_kontak' => '061-000000', 'tanggal_go_live' => '2024-06-15', 'risiko' => 'Disinformasi jika portal tidak tersedia.'],
        ]);

        DB::table('layanan_aplikasi')->insert([
            ['layanan_id' => $layanan, 'aplikasi_id' => $appPortal],
        ]);

        DB::table('security_tools')->insert([
            ['id' => $edr, 'nama' => 'Endpoint Detection Response', 'jenis' => 'EDR'],
            ['id' => $waf, 'nama' => 'Web Application Firewall', 'jenis' => 'WAF'],
        ]);

        DB::table('security_server')->insert([
            ['tool_id' => $edr, 'server_id' => $serverMain],
        ]);

        DB::table('security_aplikasi')->insert([
            ['tool_id' => $waf, 'aplikasi_id' => $appPortal],
        ]);

        DB::table('backup_policy')->insert([
            ['id' => $backupPolicy, 'nama' => 'Backup Harian Produksi', 'frekuensi' => 'harian', 'retensi' => '30 hari'],
        ]);

        DB::table('backup_target')->insert([
            ['id' => $backupTarget, 'jenis' => 'NAS', 'lokasi' => 'SRV-BACKUP-01 / Vault A'],
        ]);

        DB::table('backup_aplikasi')->insert([
            ['aplikasi_id' => $appPse, 'policy_id' => $backupPolicy, 'target_id' => $backupTarget],
        ]);

        DB::table('audit_log')->insert([
            ['aksi' => 'seed', 'tabel' => 'cmdb', 'record_id' => null, 'before_data' => null, 'after_data' => json_encode(['message' => 'Demo CMDB Kabupaten Langkat dibuat']), 'ip_address' => '127.0.0.1', 'user_agent' => 'DatabaseSeeder', 'created_at' => $now],
        ]);

        \App\Support\AssetCodeGenerator::backfillAll();
    }
}
