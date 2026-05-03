# 🧭 1. OVERVIEW

__NAMA APLIKASI__ adalah sistem **Configuration Management Database (CMDB)** yang dirancang untuk:

- Inventarisasi aset TI (server, VM, jaringan)
- Mapping aplikasi terhadap infrastruktur
- Mendukung audit dan kepatuhan PSE Komdigi
- Menjadi **single source of truth** infrastruktur digital

---

# 🎯 2. TUJUAN SISTEM

## Tujuan Utama
- Sentralisasi data aset TI
- Transparansi hubungan aplikasi ↔ infrastruktur
- Mendukung pengambilan keputusan pimpinan

## Tujuan Tambahan
- Mendukung audit SPBE & PSE
- Menjadi fondasi monitoring & SOC ringan
- Mempermudah impact analysis

---

# 🧱 3. ARSITEKTUR SISTEM

## Stack Teknologi

- Backend: Laravel (PHP)
- Database: MySQL (schema CMDB)
- Frontend: Blade / Inertia (opsional Vue)
- Integrasi:
  - Proxmox API
  - Monitoring (CheckMK / Zabbix)

---

## Arsitektur Umum


[ User / Admin ]
↓
[ Laravel Web App ]
↓
[ MySQL CMDB Database ]
↓
[ External API ]
├── Proxmox
├── Monitoring


---

# 🧩 4. DOMAIN MODEL

## 1. Organisasi
- OPD
- Pengguna
- Role

## 2. Infrastruktur
- Data Center
- Rack
- Server (Baremetal)
- VM
- IP Address
- ISP

## 3. Aplikasi (Core)
- Aplikasi
- Dokumen aplikasi
- Relasi ke VM / Server / IP

## 4. Layanan
- Layanan publik/internal
- Relasi ke aplikasi

## 5. Security
- Security tools
- Coverage (server, VM, aplikasi, DC)

## 6. Backup
- Backup policy
- Backup target

## 7. Audit
- Audit log (PSE requirement)

---

# 🏗️ 5. STRUKTUR PROJECT LARAVEL


app/
├── Domains/
│ ├── Organisasi/
│ ├── Infrastruktur/
│ ├── Aplikasi/
│ ├── Layanan/
│ ├── Security/
│ ├── Backup/
│ ├── Audit/
│
├── Services/
├── Repositories/
├── Actions/
├── DTO/
├── Enums/
├── Policies/
├── Observers/

routes/
├── web.php
├── api.php

resources/
├── views/
├── js/


---

# ⚙️ 6. KOMPONEN UTAMA

## Service Layer
Digunakan untuk logic utama:

- CmdbService
- ImpactAnalysisService
- DependencyMapService
- ProxmoxService

---

## Repository Layer (Opsional)
Untuk query kompleks:
- ServerRepository
- AplikasiRepository

---

## Actions
Untuk proses spesifik:
- AttachVmToAplikasi
- AttachServerToAplikasi

---

## Observer
Untuk audit otomatis:
- AplikasiObserver
- ServerObserver

---

# 🔐 7. FITUR INTI

## 1. Manajemen Aset
- CRUD Server
- CRUD VM
- CRUD Aplikasi

## 2. CMDB Mapping
- Aplikasi → VM → Server
- Aplikasi → IP → ISP

## 3. Audit Log
- Tracking perubahan data
- Simpan before & after

## 4. Dokumen Aplikasi
- SOP keamanan
- Tata kelola
- Modul penggunaan

## 5. Security Coverage
- Mapping tools ke asset

## 6. Backup Management
- Policy
- Target

---

# 📊 8. DASHBOARD (MINIMAL)

- Total server
- Total VM
- Total aplikasi
- Aplikasi aktif
- Aplikasi dengan data pribadi

---

# 🔍 9. USE CASE PENTING

## 1. Impact Analysis
"Jika server X mati, aplikasi apa terdampak?"

## 2. Compliance Check
"Aplikasi mana mengandung data pribadi?"

## 3. Infrastruktur Mapping
"Aplikasi ini berjalan di mana?"

## 4. Security Coverage
"Server mana belum terlindungi?"

---

# 🚀 10. ROADMAP IMPLEMENTASI

## Phase 1 – Core System
- Setup Laravel
- Import schema database
- CRUD dasar (server, VM, aplikasi)

## Phase 2 – CMDB Mapping
- Relasi aplikasi ↔ VM ↔ server
- Tampilan dependency

## Phase 3 – Integrasi
- Proxmox sync VM
- Monitoring integration

## Phase 4 – Audit & Compliance
- Audit log aktif
- Laporan PSE

## Phase 5 – Enhancement
- Dashboard pimpinan
- Analitik lanjutan

---

# 🧠 11. PRINSIP DESAIN

- Relasional > JSON
- Backend-centric logic
- Audit-first approach
- Modular domain architecture

---

# ⚠️ 12. RISIKO & MITIGASI

| Risiko | Mitigasi |
|------|--------|
| Data tidak lengkap | SOP input data |
| Tidak diupdate | Integrasi otomatis |
| Query lambat | Index & optimization |
| Over-engineering | Fokus fase awal |

---

# 🏁 13. KESIMPULAN

__NAMA APLIKASI__ bukan sekadar sistem inventaris, tetapi:

> **Platform kendali infrastruktur digital pemda**

Dengan implementasi yang tepat, sistem ini dapat menjadi:
- dasar SOC
- alat audit
- pusat data strategis TI

---

# 📌 14. NEXT STEP

- Setup project Laravel
- Generate model & relasi
- Implementasi 1 fitur inti:
  → mapping aplikasi ke infrastruktur

---
