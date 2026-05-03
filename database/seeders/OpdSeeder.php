<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OpdSeeder extends Seeder
{
    public const NAMES = [
        'Sekretariat Daerah',
        'Sekretariat Dewan Perwakilan Rakyat Daerah',
        'Dinas Kearsipan Dan Perpustakaan',
        'Dinas Kebudayaan Dan Pariwisata',
        'Dinas Kesehatan',
        'Dinas Kependudukan Dan Pencatatan Sipil',
        'Dinas Ketenagakerjaan',
        'Dinas Komunikasi Dan Informatika',
        'Dinas Koperasi',
        'Dinas Lingkungan Hidup',
        'Dinas Pekerjaan Umum Dan Tata Ruang',
        'Dinas Pemuda Dan Olahraga',
        'Dinas Pemberdayaan Masyarakat Dan Desa',
        'Dinas Penanaman Modal Dan Pelayanan Perizinan Terpadu Satu Pintu',
        'Dinas Pendidikan',
        'Dinas Pengendalian Penduduk Keluarga Berencana Dan Perempuan Perlindungan Anak',
        'Dinas Perhubungan',
        'Dinas Perikanan Dan Kelautan',
        'Dinas Perindustrian Dan Perdagangan',
        'Dinas Perumahan Dan Kawasan Permukiman',
        'Dinas Pertanian Dan Ketahanan Pangan',
        'Dinas Sosial',
        'Badan Kepegawaian Daerah',
        'Badan Kesatuan Bangsa Dan Politik',
        'Badan Pendapatan Daerah',
        'Badan Penanggulangan Bencana Daerah',
        'Badan Pengelolaan Keuangan Dan Asset Daerah',
        'Badan Perencanaan Pembangunan Daerah Penelitian Dan Pengembangan',
        'Inspektorat',
        'Satuan Polisi Pamong Praja',
        'UPT Rumah Sakit Umum Daerah Tanjung Pura',
        'Kecamatan Babalan',
        'Kecamatan Bahorok',
        'Kecamatan Batang Serangan',
        'Kecamatan Besitang',
        'Kecamatan Binjai',
        'Kecamatan Brandan Barat',
        'Kecamatan Gebang',
        'Kecamatan Hinai',
        'Kecamatan Kuala',
        'Kecamatan Kutambaru',
        'Kecamatan Padang Tualang',
        'Kecamatan Pangkalan Susu',
        'Kecamatan Pematang Jaya',
        'Kecamatan Salapian',
        'Kecamatan Sawit Seberang',
        'Kecamatan Secanggang',
        'Kecamatan Sei Bingai',
        'Kecamatan Sei Lepan',
        'Kecamatan Selesai',
        'Kecamatan Sirapit',
        'Kecamatan Stabat',
        'Kecamatan Tanjung Pura',
        'Kecamatan Wampu',
        'Puskes. Bahorok',
        'Puskes. Beras Basah',
        'Puskes. Besitang',
        'Puskes. Bukit Lawang',
        'Puskes. Desa Lama',
        'Puskes. Desa Teluk',
        'Puskes. Gebang',
        'Puskes. Hinai Kiri',
        'Puskes. Karang Rejo',
        'Puskes. Kuala',
        'Puskes. Marike',
        'Puskes. Namu Ukur',
        'Puskes. Namutrasi',
        'Puskes. Pangkalan Brandan',
        'Puskes. Pangkalan Susu',
        'Puskes. Pantai Cermin',
        'Puskes. Pematang Cengal',
        'Puskes. Pematang Jaya',
        'Puskes. Sambirejo Binjai',
        'Puskes. Sawit Seberang',
        'Puskes. Secanggang',
        'Puskes. Securai',
        'Puskes. Sei Bamban',
        'Puskes. Selesai',
        'Puskes. Sirapit',
        'Puskes. Stabat',
        'Puskes. Stabat Lama',
        'Puskes. Tangkahan Durian',
        'Puskes. Tanjung Beringin',
        'Puskes. Tanjung Langkat',
        'Puskes. Tanjung Selamat',
        'Puskes. Tungkit',
    ];

    public function run(): void
    {
        $now = now();

        foreach (self::NAMES as $name) {
            if (DB::table('opd')->where('nama', $name)->exists()) {
                continue;
            }

            DB::table('opd')->insert([
                'id' => (string) Str::uuid(),
                'nama' => $name,
                'kontak' => $name === 'Dinas Komunikasi Dan Informatika' ? 'diskominfo@langkatkab.go.id' : null,
                'created_at' => $now,
            ]);
        }
    }
}
