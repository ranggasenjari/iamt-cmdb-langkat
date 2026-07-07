<?php

namespace Database\Seeders;

use App\Models\Aplikasi;
use App\Models\AppDatabaseDoc;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AppDatabaseDocSeeder extends Seeder
{
    public function run(): void
    {
        Aplikasi::each(function (Aplikasi $aplikasi) {
            $dbName = 'db_' . Str::slug($aplikasi->nama, '_');
            AppDatabaseDoc::create([
                'aplikasi_id' => $aplikasi->id,
                'nama_database' => $dbName,
                'tipe_dbms' => collect(['MySQL', 'PostgreSQL', 'MariaDB', 'MongoDB'])->random(),
                'versi' => collect(['8.0', '14', '10.11', '7.0'])->random(),
                'host' => 'localhost',
                'port' => collect([3306, 5432, 3307, 27017])->random(),
                'nama_db_asli' => $dbName,
                'jumlah_tabel' => rand(5, 50),
                'keterangan' => 'Database untuk ' . $aplikasi->nama,
            ]);
        });
    }
}
