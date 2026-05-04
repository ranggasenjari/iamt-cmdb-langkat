CREATE DATABASE IF NOT EXISTS iamt_refactor 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE iamt_refactor;

SET FOREIGN_KEY_CHECKS = 0;

-- =========================
-- ORGANISASI
-- =========================
CREATE TABLE opd (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(255) NOT NULL,
    kontak VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pengguna (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255) NULL,
    opd_id CHAR(36) NULL,
    role_legacy VARCHAR(50) NULL,
    role ENUM('full','read_only') DEFAULT 'read_only',
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    api_token_hash VARCHAR(64) NULL UNIQUE,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (opd_id) REFERENCES opd(id) ON DELETE SET NULL,
    INDEX idx_pengguna_opd (opd_id)
);

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50)
);

CREATE TABLE user_roles (
    user_id CHAR(36),
    role_id INT,
    PRIMARY KEY(user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES pengguna(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- =========================
-- DATA CENTER & FISIK
-- =========================
CREATE TABLE data_center (
    id CHAR(36) PRIMARY KEY,
    nama VARCHAR(255),
    lokasi VARCHAR(255),
    tipe ENUM('utama','dr','cloud')
);

CREATE TABLE rack (
    id CHAR(36) PRIMARY KEY,
    dc_id CHAR(36),
    nama VARCHAR(100),
    kapasitas_u INT,
    FOREIGN KEY (dc_id) REFERENCES data_center(id)
);

CREATE TABLE server (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(255) NOT NULL,
    dc_id CHAR(36),
    rack_id CHAR(36),
    rack_size_u SMALLINT UNSIGNED NULL,
    merk VARCHAR(100),
    tipe VARCHAR(100),
    serial_number VARCHAR(100),
    merk_processor VARCHAR(100),

    cpu_core INT,
    ram_gb INT,
    storage_gb INT,

    kondisi ENUM('baik','rusak'),
    status ENUM('aktif','nonaktif','maintenance'),

    tahun YEAR,
    penanggung_jawab VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (dc_id) REFERENCES data_center(id),
    FOREIGN KEY (rack_id) REFERENCES rack(id),
    INDEX idx_server_nama (nama),
    INDEX idx_server_dc (dc_id),
    INDEX idx_server_rack (rack_id)
);

-- =========================
-- VIRTUALISASI
-- =========================
CREATE TABLE vm (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(255),
    server_id CHAR(36),

    os VARCHAR(100),
    vcpu INT,
    ram_gb INT,
    storage_gb INT,

    status ENUM('running','stopped','suspended','maintenance'),

    FOREIGN KEY (server_id) REFERENCES server(id) ON DELETE SET NULL,
    INDEX idx_vm_server (server_id)
);

-- =========================
-- NETWORK
-- =========================
CREATE TABLE isp (
    id CHAR(36) PRIMARY KEY,
    nama VARCHAR(255),
    tipe VARCHAR(50),
    bandwidth VARCHAR(50),
    kontak VARCHAR(255)
);

CREATE TABLE ip_address (
    id CHAR(36) PRIMARY KEY,
    ip VARCHAR(45) UNIQUE,
    jenis ENUM('publik','private'),
    isp_id CHAR(36),

    FOREIGN KEY (isp_id) REFERENCES isp(id),
    INDEX idx_ip_isp (isp_id)
);

CREATE TABLE vm_ip (
    vm_id CHAR(36),
    ip_id CHAR(36),
    PRIMARY KEY(vm_id, ip_id),
    FOREIGN KEY (vm_id) REFERENCES vm(id) ON DELETE CASCADE,
    FOREIGN KEY (ip_id) REFERENCES ip_address(id) ON DELETE CASCADE
);

CREATE TABLE network_device (
    id CHAR(36) PRIMARY KEY,
    nama VARCHAR(255),
    tipe VARCHAR(100),
    lokasi VARCHAR(255),
    status ENUM('aktif','nonaktif','maintenance')
);

-- =========================
-- APLIKASI (PSE CORE)
-- =========================
CREATE TABLE aplikasi (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(255) NOT NULL,
    url VARCHAR(255),
    opd_id CHAR(36) NULL,

    deskripsi TEXT,
    jenis_aplikasi VARCHAR(50), -- ['web','mobile','desktop','service','lainnya']
    pengembang VARCHAR(50), -- ['instansi_pusat','diskominfo_langkat','unit_penyelenggara','pihak_ketiga','in_house']
    klasifikasi_fungsi JSON NULL,
    tech_stack TEXT NULL, -- comma separated tags, contoh: Laravel, Vue, MySQL

    status ENUM('aktif','nonaktif','maintenance'),

    -- TARGET SLA (WAJIB PSE)
    sla_persen DECIMAL(5,2),
    jam_operasional VARCHAR(100),

    -- DATA
    kategori_data VARCHAR(50),
    mengandung_data_pribadi TINYINT(1) DEFAULT 0,
    jenis_data_pribadi TEXT,
    retensi_data VARCHAR(100),

    -- PIC
    pic_nama VARCHAR(255),
    pic_kontak VARCHAR(100),

    tanggal_go_live DATE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (opd_id) REFERENCES opd(id) ON DELETE SET NULL,
    INDEX idx_aplikasi_nama (nama),
    INDEX idx_aplikasi_opd (opd_id)
);

-- =========================
-- DOKUMEN APLIKASI
-- =========================
CREATE TABLE aplikasi_dokumen (
    id CHAR(36) PRIMARY KEY,
    aplikasi_id CHAR(36),
    jenis ENUM('keamanan','tata_kelola','modul'),
    document_category VARCHAR(50),
    nama VARCHAR(255),
    url VARCHAR(255),
    path VARCHAR(255),
    original_name VARCHAR(255),
    mime_type VARCHAR(100),
    size_bytes BIGINT,
    versi VARCHAR(50),
    tanggal DATE,

    FOREIGN KEY (aplikasi_id) REFERENCES aplikasi(id)
        ON DELETE CASCADE
);

-- =========================
-- RELASI CMDB (CORE)
-- =========================
CREATE TABLE aplikasi_vm (
    aplikasi_id CHAR(36),
    vm_id CHAR(36),
    PRIMARY KEY (aplikasi_id, vm_id),
    FOREIGN KEY (aplikasi_id) REFERENCES aplikasi(id) ON DELETE CASCADE,
    FOREIGN KEY (vm_id) REFERENCES vm(id) ON DELETE CASCADE
);

CREATE TABLE aplikasi_server (
    aplikasi_id CHAR(36),
    server_id CHAR(36),
    PRIMARY KEY (aplikasi_id, server_id),
    FOREIGN KEY (aplikasi_id) REFERENCES aplikasi(id) ON DELETE CASCADE,
    FOREIGN KEY (server_id) REFERENCES server(id) ON DELETE CASCADE
);

CREATE TABLE aplikasi_ip (
    aplikasi_id CHAR(36),
    ip_id CHAR(36),
    PRIMARY KEY (aplikasi_id, ip_id),
    FOREIGN KEY (aplikasi_id) REFERENCES aplikasi(id) ON DELETE CASCADE,
    FOREIGN KEY (ip_id) REFERENCES ip_address(id) ON DELETE CASCADE
);

-- =========================
-- LAYANAN
-- =========================
CREATE TABLE layanan (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(255),
    deskripsi TEXT,
    opd_id CHAR(36) NULL,

    status ENUM('aktif','nonaktif','maintenance'),

    kategori_data VARCHAR(50), -- ['publik','terbatas','rahasia']

    pic_nama VARCHAR(255),
    pic_kontak VARCHAR(100),

    tanggal_go_live DATE,
    risiko TEXT,

    FOREIGN KEY (opd_id) REFERENCES opd(id) ON DELETE SET NULL,
    INDEX idx_layanan_opd (opd_id)
);

CREATE TABLE layanan_aplikasi (
    layanan_id CHAR(36),
    aplikasi_id CHAR(36),
    PRIMARY KEY(layanan_id, aplikasi_id),
    FOREIGN KEY (layanan_id) REFERENCES layanan(id),
    FOREIGN KEY (aplikasi_id) REFERENCES aplikasi(id)
);

-- =========================
-- SECURITY
-- =========================
CREATE TABLE security_tools (
    id CHAR(36) PRIMARY KEY,
    nama VARCHAR(255),
    jenis VARCHAR(100)
);

CREATE TABLE security_server (
    tool_id CHAR(36),
    server_id CHAR(36),
    PRIMARY KEY(tool_id, server_id),
    FOREIGN KEY (tool_id) REFERENCES security_tools(id),
    FOREIGN KEY (server_id) REFERENCES server(id)
);

CREATE TABLE security_vm (
    tool_id CHAR(36),
    vm_id CHAR(36),
    PRIMARY KEY(tool_id, vm_id),
    FOREIGN KEY (tool_id) REFERENCES security_tools(id),
    FOREIGN KEY (vm_id) REFERENCES vm(id)
);

CREATE TABLE security_aplikasi (
    tool_id CHAR(36),
    aplikasi_id CHAR(36),
    PRIMARY KEY(tool_id, aplikasi_id),
    FOREIGN KEY (tool_id) REFERENCES security_tools(id),
    FOREIGN KEY (aplikasi_id) REFERENCES aplikasi(id)
);

CREATE TABLE security_dc (
    tool_id CHAR(36),
    dc_id CHAR(36),
    PRIMARY KEY(tool_id, dc_id),
    FOREIGN KEY (tool_id) REFERENCES security_tools(id),
    FOREIGN KEY (dc_id) REFERENCES data_center(id)
);

-- =========================
-- BACKUP
-- =========================
CREATE TABLE backup_policy (
    id CHAR(36) PRIMARY KEY,
    nama VARCHAR(255),
    frekuensi VARCHAR(50),
    retensi VARCHAR(50)
);

CREATE TABLE backup_target (
    id CHAR(36) PRIMARY KEY,
    jenis VARCHAR(50),
    lokasi VARCHAR(255)
);

CREATE TABLE backup_aplikasi (
    aplikasi_id CHAR(36),
    policy_id CHAR(36),
    target_id CHAR(36),
    PRIMARY KEY(aplikasi_id, policy_id, target_id),
    FOREIGN KEY (aplikasi_id) REFERENCES aplikasi(id),
    FOREIGN KEY (policy_id) REFERENCES backup_policy(id),
    FOREIGN KEY (target_id) REFERENCES backup_target(id)
);

CREATE TABLE backup_media (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    location ENUM('local','remote') NOT NULL,
    jenis_media ENUM('NAS','Disk','Cloud','Replication','Tape','Object Storage') NOT NULL,
    kapasitas_gb BIGINT UNSIGNED NULL,
    address_url VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE backup_jobs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    aplikasi_id CHAR(36) NOT NULL,
    backup_media_id BIGINT NOT NULL,
    retensi_n INT UNSIGNED DEFAULT 1,
    retensi_unit ENUM('realtime','menit','jam','hari','minggu','bulan') NOT NULL,
    repetisi_n INT UNSIGNED DEFAULT 1,
    repetisi_unit ENUM('realtime','menit','jam','hari','minggu','bulan') NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (aplikasi_id) REFERENCES aplikasi(id) ON DELETE CASCADE,
    FOREIGN KEY (backup_media_id) REFERENCES backup_media(id),
    INDEX idx_backup_jobs_app (aplikasi_id),
    INDEX idx_backup_jobs_media (backup_media_id)
);

-- =========================
-- UPS / POWER BACKUP
-- =========================
CREATE TABLE ups_devices (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    kapasitas_va INT UNSIGNED NOT NULL,
    kondisi ENUM('baik','kurang_baik','rusak') NOT NULL,
    dc_id CHAR(36) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (dc_id) REFERENCES data_center(id) ON DELETE SET NULL,
    INDEX idx_ups_dc (dc_id)
);

-- =========================
-- DATA APLIKASI & KLASIFIKASI DATA
-- Mengacu klasifikasi Data Elektronik terbuka, terbatas, tertutup
-- =========================
CREATE TABLE data_classifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    risk_level ENUM('LOW','MEDIUM','HIGH') NOT NULL,
    description TEXT NULL,
    requires_encryption BOOLEAN DEFAULT FALSE,
    requires_mfa BOOLEAN DEFAULT FALSE,
    requires_audit_log BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE data_assets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    aplikasi_id CHAR(36) NOT NULL,
    classification_id BIGINT NOT NULL,
    name VARCHAR(150) NOT NULL,
    type ENUM('TABLE','COLUMN','API','FILE','FORM','DATASET') NOT NULL,
    attributes TEXT NULL,
    owner_agency VARCHAR(150) NULL,
    confidentiality_score TINYINT UNSIGNED NULL,
    integrity_score TINYINT UNSIGNED NULL,
    availability_score TINYINT UNSIGNED NULL,
    risk_total TINYINT UNSIGNED NULL,
    table_name VARCHAR(100) NULL,
    column_name VARCHAR(100) NULL,
    contains_personal_data BOOLEAN DEFAULT FALSE,
    personal_data_type VARCHAR(255) NULL,
    processing_purpose VARCHAR(255) NULL,
    retention_period VARCHAR(100) NULL,
    storage_location VARCHAR(255) NULL,
    data_owner VARCHAR(150) NULL,
    access_policy TEXT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (aplikasi_id) REFERENCES aplikasi(id) ON DELETE CASCADE,
    FOREIGN KEY (classification_id) REFERENCES data_classifications(id),
    INDEX idx_data_assets_aplikasi (aplikasi_id),
    INDEX idx_data_assets_classification (classification_id)
);

-- =========================
-- INTEROPERABILITAS
-- =========================
CREATE TABLE app_integrations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    aplikasi_id CHAR(36) NOT NULL,
    deskripsi TEXT NULL,
    jenis_integrasi ENUM('proses_bisnis','berbagi_data') NOT NULL,
    metode_integrasi ENUM('spl','host_to_host') NOT NULL,
    external_endpoints TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (aplikasi_id) REFERENCES aplikasi(id) ON DELETE CASCADE,
    INDEX idx_app_integrations_app (aplikasi_id)
);

CREATE TABLE app_integration_targets (
    integration_id BIGINT NOT NULL,
    target_aplikasi_id CHAR(36) NOT NULL,
    PRIMARY KEY(integration_id, target_aplikasi_id),
    FOREIGN KEY (integration_id) REFERENCES app_integrations(id) ON DELETE CASCADE,
    FOREIGN KEY (target_aplikasi_id) REFERENCES aplikasi(id) ON DELETE CASCADE
);

CREATE TABLE app_integration_data_assets (
    integration_id BIGINT NOT NULL,
    data_asset_id BIGINT NOT NULL,
    PRIMARY KEY(integration_id, data_asset_id),
    FOREIGN KEY (integration_id) REFERENCES app_integrations(id) ON DELETE CASCADE,
    FOREIGN KEY (data_asset_id) REFERENCES data_assets(id) ON DELETE CASCADE
);

CREATE TABLE app_integration_documents (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    integration_id BIGINT NOT NULL,
    path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100),
    size_bytes BIGINT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (integration_id) REFERENCES app_integrations(id) ON DELETE CASCADE
);

-- =========================
-- SOC / SECURITY OPERATIONS
-- =========================
CREATE TABLE soc_tools (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    deskripsi_fungsi TEXT NULL,
    jenis ENUM('Firewall','IDS','IPS','Antivirus','EDR','SIEM','WAF','NDR','Vulnerability Scanner','Log Management') NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE soc_tool_data_center (
    soc_tool_id BIGINT NOT NULL,
    dc_id CHAR(36) NOT NULL,
    PRIMARY KEY(soc_tool_id, dc_id),
    FOREIGN KEY (soc_tool_id) REFERENCES soc_tools(id) ON DELETE CASCADE,
    FOREIGN KEY (dc_id) REFERENCES data_center(id) ON DELETE CASCADE
);

CREATE TABLE soc_tool_server (
    soc_tool_id BIGINT NOT NULL,
    server_id CHAR(36) NOT NULL,
    PRIMARY KEY(soc_tool_id, server_id),
    FOREIGN KEY (soc_tool_id) REFERENCES soc_tools(id) ON DELETE CASCADE,
    FOREIGN KEY (server_id) REFERENCES server(id) ON DELETE CASCADE
);

CREATE TABLE soc_tool_vm (
    soc_tool_id BIGINT NOT NULL,
    vm_id CHAR(36) NOT NULL,
    PRIMARY KEY(soc_tool_id, vm_id),
    FOREIGN KEY (soc_tool_id) REFERENCES soc_tools(id) ON DELETE CASCADE,
    FOREIGN KEY (vm_id) REFERENCES vm(id) ON DELETE CASCADE
);

CREATE TABLE soc_tool_aplikasi (
    soc_tool_id BIGINT NOT NULL,
    aplikasi_id CHAR(36) NOT NULL,
    PRIMARY KEY(soc_tool_id, aplikasi_id),
    FOREIGN KEY (soc_tool_id) REFERENCES soc_tools(id) ON DELETE CASCADE,
    FOREIGN KEY (aplikasi_id) REFERENCES aplikasi(id) ON DELETE CASCADE
);

-- =========================
-- AUDIT LOG (WAJIB PSE)
-- =========================
CREATE TABLE audit_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36),
    aksi VARCHAR(255),
    tabel VARCHAR(100),
    record_id CHAR(36),
    before_data JSON NULL,
    after_data JSON NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES pengguna(id) ON DELETE SET NULL
);

CREATE TABLE asset_change_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    asset_type ENUM('server','vm') NOT NULL,
    asset_id CHAR(36) NOT NULL,
    asset_name VARCHAR(255) NOT NULL,
    user_id CHAR(36) NULL,
    change_type VARCHAR(50) NOT NULL DEFAULT 'spesifikasi',
    changed_fields JSON NOT NULL,
    reason TEXT NULL,
    changed_by VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_asset_change_asset (asset_type, asset_id),
    INDEX idx_asset_change_asset_id (asset_id),
    INDEX idx_asset_change_user (user_id),
    FOREIGN KEY (user_id) REFERENCES pengguna(id) ON DELETE SET NULL
);

SET FOREIGN_KEY_CHECKS = 1;
