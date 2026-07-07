<?php

namespace Database\Seeders;

use App\Models\Aplikasi;
use App\Models\AppDatabaseDoc;
use Illuminate\Database\Seeder;

class AppDatabaseDocSeeder extends Seeder
{
    public function run(): void
    {
        Aplikasi::each(function (Aplikasi $aplikasi) {
            AppDatabaseDoc::create([
                'aplikasi_id' => $aplikasi->id,
                'nama_database' => 'db_' . str($aplikasi->nama)->slug('_'),
                'tipe_dbms' => collect(['MySQL', 'PostgreSQL', 'MariaDB', 'MongoDB'])->random(),
                'versi' => collect(['8.0', '14', '10.11', '7.0'])->random(),
                'host' => 'localhost',
                'port' => collect([3306, 5432, 3307, 27017])->random(),
                'nama_db_asli' => 'db_' . str($aplikasi->nama)->slug('_'),
                'jumlah_tabel' => rand(5, 50),
                'keterangan' => 'Database untuk ' . $aplikasi->nama,
            ]);
        });
    }
}
