# REST API IAMT CMDB Langkat

Dokumen ini menjelaskan endpoint REST API untuk mengelola data CMDB. Semua endpoint berada di bawah prefix `/api`.

## Autentikasi

Login memakai email dan password. Response login berisi bearer token.

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@langkatkab.go.id",
  "password": "password"
}
```

Gunakan token pada request berikutnya:

```http
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

Endpoint auth:

| Method | Endpoint | Keterangan |
| --- | --- | --- |
| POST | `/api/auth/login` | Login dan membuat token |
| GET | `/api/auth/me` | Detail user login |
| POST | `/api/auth/logout` | Logout dan revoke token aktif |

## Role dan Akses

| Role | Akses |
| --- | --- |
| `full` | Baca, tambah, ubah, hapus |
| `read_only` | Baca saja |

Operasi `POST`, `PUT`, `PATCH`, dan `DELETE` membutuhkan role `full`.

## Pola REST Resource

Setiap modul data utama memakai pola REST berikut.

| Method | Pola Endpoint | Akses | Response sukses |
| --- | --- | --- | --- |
| GET | `/api/{resource}` | full/read_only | `200 OK` array data |
| GET | `/api/{resource}/{id}` | full/read_only | `200 OK` detail data |
| POST | `/api/{resource}` | full | `201 Created` |
| PUT/PATCH | `/api/{resource}/{id}` | full | `200 OK` |
| DELETE | `/api/{resource}/{id}` | full | `204 No Content` |

Jika data tidak dapat dihapus karena masih dipakai data anak, API mengembalikan:

```json
{
  "message": "Terdapat data dibawah entitas ini. Hapus atau lepaskan relasi data terkait terlebih dahulu sebelum menghapus data utama.",
  "type": "constraint_violation"
}
```

Status HTTP: `409 Conflict`.

## Kode Aset Inventaris

Setiap aset fisik maupun digital memiliki field `asset_code` yang dibuat otomatis saat data dibuat dan ikut dikembalikan pada response API. Field ini bersifat identitas internal CMDB dan tidak perlu dikirim saat `POST`.

Contoh pola kode:

| Resource | Prefix | Contoh |
| --- | --- | --- |
| `data-centers` | `LKT-DC` | `LKT-DC-000001` |
| `racks` | `LKT-RCK` | `LKT-RCK-000001` |
| `servers` | `LKT-SRV` | `LKT-SRV-000001` |
| `vms` | `LKT-VM` | `LKT-VM-000001` |
| `applications` | `LKT-APP` | `LKT-APP-000001` |
| `data-assets` | `LKT-DATA` | `LKT-DATA-000001` |
| `application-documents` | `LKT-DOC` | `LKT-DOC-000001` |
| `app-integrations` | `LKT-INT` | `LKT-INT-000001` |
| `backup-media` | `LKT-BKM` | `LKT-BKM-000001` |
| `backup-jobs` | `LKT-BKJ` | `LKT-BKJ-000001` |
| `ups-devices` | `LKT-UPS` | `LKT-UPS-000001` |
| `soc-tools` | `LKT-SOC` | `LKT-SOC-000001` |

Setiap modul aset CMDB, baik fisik maupun digital, dapat dicetak label dari UI. Label berisi logo Kabupaten Langkat, kode aset, nama aset, jenis aset, lokasi singkat, dan QR menuju halaman verifikasi publik:

```text
/asset/{resource}/{id}
```

Halaman QR ini dapat diakses tanpa login dan hanya menampilkan identitas publik aset. Resource autentikasi seperti `users` tidak disediakan pada halaman publik.

Ukuran label yang tersedia di UI: `50 x 30 mm`, `60 x 40 mm`, `70 x 50 mm`, dan `90 x 50 mm`.

## Daftar Resource

| Modul UI | Resource | Tipe ID |
| --- | --- | --- |
| Gedung / Ruang DC | `data-centers` | UUID |
| Rack | `racks` | UUID |
| Server | `servers` | UUID |
| VM / CT | `vms` | UUID |
| ISP | `isps` | UUID |
| IP Addr | `ip-addresses` | UUID |
| Aplikasi | `applications` | UUID |
| Dokumen | `application-documents` | UUID |
| Klasifikasi Data | `data-assets` | integer |
| Master Klasifikasi | `data-classifications` | integer |
| Interoperabilitas | `app-integrations` | integer |
| Media Pencadangan | `backup-media` | integer |
| Pencadangan | `backup-jobs` | integer |
| UPS / Power Backup | `ups-devices` | integer |
| SOC | `soc-tools` | integer |
| Pengguna & Role | `users` | UUID |

## Payload Modul

### Data Center: `data-centers`

```json
{
  "nama": "Data Center Pemkab Langkat",
  "lokasi": "Stabat",
  "tipe": "utama"
}
```

Enum `tipe`: `utama`, `dr`, `cloud`.

### Rack: `racks`

```json
{
  "dc_id": "uuid-data-center",
  "nama": "Rack A01",
  "kapasitas_u": 42
}
```

### Server: `servers`

```json
{
  "nama": "SRV-PROD-01",
  "dc_id": "uuid-data-center",
  "rack_id": "uuid-rack",
  "rack_size_u": 2,
  "merk": "Dell",
  "tipe": "PowerEdge R750",
  "serial_number": "LKT-PRD-001",
  "merk_processor": "Intel Xeon Silver",
  "tahun": 2024,
  "cpu_core": 48,
  "ram_gb": 256,
  "storage_gb": 8192,
  "kondisi": "baik",
  "status": "aktif",
  "penanggung_jawab": "Bidang Infrastruktur TIK",
  "change_reason": "Upgrade RAM",
  "changed_by": "Admin Infrastruktur"
}
```

Enum `kondisi`: `baik`, `rusak`.
Enum `status`: `aktif`, `nonaktif`, `maintenance`.

`change_reason` dan `changed_by` dipakai saat update untuk audit perubahan spesifikasi.

### VM / CT: `vms`

```json
{
  "nama": "VM-PORTAL-LANGKAT",
  "server_id": "uuid-server",
  "os": "Debian 12",
  "vcpu": 6,
  "ram_gb": 12,
  "storage_gb": 180,
  "status": "running",
  "ip_ids": ["uuid-ip-1", "uuid-ip-2"],
  "change_reason": "Penyesuaian resource",
  "changed_by": "Admin Virtualisasi"
}
```

Enum `status`: `running`, `stopped`, `suspended`, `maintenance`.

### ISP: `isps`

```json
{
  "nama": "LangkatNet",
  "tipe": "Fiber Dedicated",
  "bandwidth": "500 Mbps",
  "kontak": "noc@example.test"
}
```

### IP Address: `ip-addresses`

```json
{
  "ip": "10.30.1.24",
  "jenis": "private",
  "isp_id": "uuid-isp"
}
```

Enum `jenis`: `publik`, `private`.

### Aplikasi: `applications`

```json
{
  "nama": "Portal Kabupaten Langkat",
  "url": "https://langkatkab.go.id",
  "opd_id": "uuid-opd",
  "deskripsi": "Portal informasi publik.",
  "jenis_aplikasi": "web",
  "pengembang": "diskominfo_langkat",
  "klasifikasi_fungsi": ["layanan_publik", "layanan_internal"],
  "tech_stack": "Laravel, Vue, MySQL",
  "status": "aktif",
  "sla_persen": 99.5,
  "jam_operasional": "24x7",
  "kategori_data": "publik",
  "pic_nama": "Webmaster",
  "pic_kontak": "0812-0000-0002",
  "tanggal_go_live": "2024-06-15",
  "vm_ids": ["uuid-vm"],
  "server_ids": ["uuid-server"],
  "ip_ids": ["uuid-ip"]
}
```

Enum `jenis_aplikasi`: `web`, `mobile`, `desktop`, `service`, `lainnya`.

Enum `pengembang`: `instansi_pusat`, `diskominfo_langkat`, `unit_penyelenggara`, `pihak_ketiga`, `in_house`.

Enum `klasifikasi_fungsi`: `layanan_publik`, `layanan_internal`, `tools_pendukung`, `platform_integrasi`, `low_code_no_code`, `monitoring_observability`, `security_tools`, `kolaborasi_knowledge_base`.

Enum `status`: `aktif`, `nonaktif`, `maintenance`.

### Dokumen Aplikasi: `application-documents`

Create memakai `multipart/form-data`.

| Field | Tipe | Keterangan |
| --- | --- | --- |
| `aplikasi_id` | UUID | Wajib |
| `document_category` | string | Wajib |
| `files[]` | file[] | Wajib, multiple |

Enum `document_category`: `petunjuk_teknis`, `tata_kelola`, `keamanan`.

Update hanya mengubah metadata:

```json
{
  "aplikasi_id": "uuid-aplikasi",
  "document_category": "keamanan"
}
```

### Data Aplikasi / Klasifikasi Data: `data-assets`

```json
{
  "aplikasi_id": "uuid-aplikasi",
  "classification_id": 1,
  "name": "users.email",
  "type": "COLUMN",
  "attributes": "Nama\nEmail",
  "owner_agency": "Diskominfo Kabupaten Langkat",
  "confidentiality_score": 3,
  "integrity_score": 3,
  "availability_score": 3,
  "table_name": "users",
  "column_name": "email",
  "contains_personal_data": true,
  "personal_data_type": "Email pengguna",
  "processing_purpose": "Autentikasi",
  "retention_period": "5 tahun",
  "storage_location": "Database produksi",
  "data_owner": "Diskominfo Kabupaten Langkat",
  "access_policy": "Admin aplikasi dan audit log wajib.",
  "description": "Data akun pengguna"
}
```

Enum `type`: `TABLE`, `COLUMN`, `API`, `FILE`, `FORM`, `DATASET`.

Skor K/I/K hanya menerima nilai `1`, `3`, atau `5`. API menghitung `risk_total` dan memilih klasifikasi otomatis:

| Total | Klasifikasi |
| --- | --- |
| `<= 7` | `OPEN` |
| `8 - 11` | `LIMITED` |
| `>= 12` | `RESTRICTED` |

### Master Klasifikasi: `data-classifications`

```json
{
  "code": "LIMITED",
  "name": "Data Elektronik Terbatas",
  "risk_level": "MEDIUM",
  "description": "Data internal terbatas",
  "requires_encryption": true,
  "requires_mfa": false,
  "requires_audit_log": true
}
```

Enum `risk_level`: `LOW`, `MEDIUM`, `HIGH`.

### Interoperabilitas: `app-integrations`

Create dan update dapat memakai `multipart/form-data` jika mengunggah dokumen integrasi.

| Field | Tipe | Keterangan |
| --- | --- | --- |
| `aplikasi_id` | UUID | Wajib |
| `deskripsi` | string | Opsional |
| `jenis_integrasi` | string | Wajib |
| `metode_integrasi` | string | Wajib |
| `target_application_ids[]` | UUID[] | Opsional |
| `external_endpoints` | string | Opsional |
| `data_asset_ids[]` | integer[] | Opsional |
| `documents[]` | file[] | Opsional |

Enum `jenis_integrasi`: `proses_bisnis`, `berbagi_data`.

Enum `metode_integrasi`: `spl`, `host_to_host`.

### Media Pencadangan: `backup-media`

```json
{
  "nama": "NAS Backup DC",
  "location": "local",
  "jenis_media": "NAS",
  "kapasitas_gb": 4096,
  "address_url": "10.30.10.20"
}
```

Enum `location`: `local`, `remote`.

Enum `jenis_media`: `NAS`, `Disk`, `Cloud`, `Replication`, `Tape`, `Object Storage`.

### Pencadangan: `backup-jobs`

```json
{
  "aplikasi_id": "uuid-aplikasi",
  "backup_media_id": 1,
  "retensi_n": 30,
  "retensi_unit": "hari",
  "repetisi_n": 1,
  "repetisi_unit": "hari"
}
```

Enum unit: `realtime`, `menit`, `jam`, `hari`, `minggu`, `bulan`.

### UPS / Power Backup: `ups-devices`

```json
{
  "nama": "UPS Rack A01",
  "kapasitas_va": 3000,
  "kondisi": "baik",
  "dc_id": "uuid-data-center"
}
```

Enum `kondisi`: `baik`, `kurang_baik`, `rusak`.

### SOC Tools: `soc-tools`

```json
{
  "nama": "SIEM Kabupaten Langkat",
  "deskripsi_fungsi": "Korelasi log keamanan infrastruktur.",
  "jenis": "SIEM",
  "dc_ids": ["uuid-dc"],
  "server_ids": ["uuid-server"],
  "vm_ids": ["uuid-vm"],
  "application_ids": ["uuid-aplikasi"]
}
```

Enum `jenis`: `Firewall`, `IDS`, `IPS`, `Antivirus`, `EDR`, `SIEM`, `WAF`, `NDR`, `Vulnerability Scanner`, `Log Management`.

### Pengguna & Role: `users`

```json
{
  "nama": "Auditor CMDB",
  "email": "auditor@langkatkab.go.id",
  "password": "password",
  "opd_id": "uuid-opd",
  "role": "read_only",
  "status": "aktif"
}
```

Enum `role`: `full`, `read_only`.

Enum `status`: `aktif`, `nonaktif`.

Saat update, `password` boleh dikosongkan jika tidak ingin mengganti password.

## Endpoint Baca Pendukung

| Method | Endpoint | Keterangan |
| --- | --- | --- |
| GET | `/api/dashboard` | Metrik dashboard |
| GET | `/api/references` | Referensi OPD, DC, rack, server, VM, IP, klasifikasi |
| GET | `/api/dependency-map` | Mapping aplikasi, VM, server, IP |
| GET | `/api/impact/server/{server}` | Analisis dampak server |
| GET | `/api/compliance` | Ringkasan compliance |
| GET | `/api/audit-log` | Audit log umum |
| GET | `/api/asset-change-logs` | Audit perubahan spesifikasi server/VM |

Filter audit perubahan aset:

```http
GET /api/asset-change-logs?asset_type=server&asset_id={uuid-server}
```

## Contoh Curl

```bash
curl -X POST https://domain.example/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@langkatkab.go.id","password":"password"}'
```

```bash
curl https://domain.example/api/servers \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

```bash
curl -X DELETE https://domain.example/api/servers/{id} \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

Contoh upload dokumen:

```bash
curl -X POST https://domain.example/api/application-documents \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}" \
  -F "aplikasi_id={uuid-aplikasi}" \
  -F "document_category=keamanan" \
  -F "files[]=@dokumen-keamanan.pdf"
```

## Status Error Umum

| Status | Arti |
| --- | --- |
| `401` | Token tidak ada atau tidak valid |
| `403` | Role tidak boleh melakukan operasi tulis |
| `404` | Data tidak ditemukan |
| `409` | Data tidak dapat dihapus karena masih dipakai data anak |
| `422` | Validasi payload gagal |
