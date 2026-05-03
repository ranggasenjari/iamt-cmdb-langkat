<script setup>
import {
  Activity,
  AlertTriangle,
  AppWindow,
  Box,
  Building2,
  CheckCircle2,
  Database,
  FileCheck2,
  GitBranch,
  HardDrive,
  LayoutDashboard,
  Network,
  Pencil,
  Plus,
  RefreshCw,
  Search,
  Server,
  ShieldCheck,
  Trash2,
  Users,
  X,
} from 'lucide-vue-next';
import { computed, onMounted, reactive, ref } from 'vue';

const menuSections = [
  {
    items: [{ id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard }],
  },
  {
    label: 'Pusat Data / DC',
    items: [
      { id: 'data-centers', label: 'Gedung / Ruang DC', icon: Building2 },
      { id: 'racks', label: 'Rack', icon: Database },
      { id: 'servers', label: 'Server', icon: Server },
      { id: 'vms', label: 'VM / CT', icon: Box },
      { id: 'isps', label: 'ISP', icon: HardDrive },
      { id: 'ip-addresses', label: 'IP Addr', icon: Network },
      { id: 'ups-devices', label: 'UPS / Power Backup', icon: ShieldCheck },
    ],
  },
  {
    label: 'Aplikasi',
    items: [
      { id: 'applications', label: 'Aplikasi', icon: AppWindow },
      { id: 'application-documents', label: 'Dokumen', icon: FileCheck2 },
      { id: 'data-assets', label: 'Klasifikasi Data', icon: FileCheck2 },
      { id: 'app-integrations', label: 'Interoperabilitas', icon: GitBranch },
    ],
  },
  {
    label: 'Keamanan Informasi',
    items: [
      { id: 'backup-media', label: 'Media Pencadangan', icon: Database },
      { id: 'backup-jobs', label: 'Pencadangan', icon: HardDrive },
      { id: 'soc-tools', label: 'SOC', icon: ShieldCheck },
      { id: 'users', label: 'Pengguna & Role', icon: Users },
    ],
  },
  {
    items: [
      { id: 'map', label: 'Mapping', icon: GitBranch },
      { id: 'compliance', label: 'Compliance', icon: FileCheck2 },
      { id: 'audit', label: 'Audit', icon: Activity },
    ],
  },
];

const activeTab = ref('dashboard');
const loading = ref(false);
const error = ref('');
const query = ref('');
const selectedServerId = ref('');
const authToken = ref(localStorage.getItem('iamt_token') || '');
const currentUser = ref(null);
const authForm = reactive({
  email: 'admin@langkatkab.go.id',
  password: 'password',
});
const modal = reactive({
  open: false,
  module: '',
  mode: 'create',
  id: null,
});
const alertModal = reactive({
  open: false,
  title: '',
  message: '',
});

const dashboard = ref(null);
const references = ref({ opd: [], classifications: [], data_centers: [], racks: [], isps: [], servers: [], vms: [], ips: [] });
const dataCenters = ref([]);
const racks = ref([]);
const isps = ref([]);
const ipAddresses = ref([]);
const servers = ref([]);
const vms = ref([]);
const applications = ref([]);
const dataAssets = ref([]);
const applicationDocuments = ref([]);
const appIntegrations = ref([]);
const backupMedia = ref([]);
const backupJobs = ref([]);
const upsDevices = ref([]);
const socTools = ref([]);
const users = ref([]);
const dependencyMap = ref([]);
const compliance = ref(null);
const auditLog = ref([]);
const assetChangeLogs = ref([]);
const impact = ref(null);

const canWrite = computed(() => Boolean(currentUser.value?.can_write));

const dataCenterForm = reactive({
  nama: '',
  lokasi: 'Stabat',
  tipe: 'utama',
});

const rackForm = reactive({
  dc_id: '',
  nama: '',
  kapasitas_u: 42,
});

const ispForm = reactive({
  nama: '',
  tipe: 'Fiber Dedicated',
  bandwidth: '',
  kontak: '',
});

const ipAddressForm = reactive({
  ip: '',
  jenis: 'private',
  isp_id: '',
});

const serverForm = reactive({
  nama: '',
  dc_id: '',
  rack_id: '',
  merk: '',
  tipe: '',
  cpu_core: 16,
  ram_gb: 64,
  storage_gb: 1024,
  kondisi: 'baik',
  status: 'aktif',
  penanggung_jawab: 'Bidang Infrastruktur TIK',
  change_reason: '',
  changed_by: '',
});

const vmForm = reactive({
  nama: '',
  server_id: '',
  os: 'Ubuntu Server 24.04 LTS',
  vcpu: 4,
  ram_gb: 8,
  storage_gb: 120,
  status: 'running',
  ip_ids: [],
  change_reason: '',
  changed_by: '',
});

const appForm = reactive({
  nama: '',
  url: '',
      opd_id: '',
      jenis_aplikasi: 'web',
      klasifikasi_fungsi: [],
      tech_stack: '',
  status: 'aktif',
  sla_persen: 99,
  kategori_data: 'terbatas',
  pic_nama: '',
  pic_kontak: '',
  risiko: '',
  vm_ids: [],
  server_ids: [],
  ip_ids: [],
});

const applicationDocumentForm = reactive({
  aplikasi_id: '',
  document_category: 'petunjuk_teknis',
  files: [],
});

const appIntegrationForm = reactive({
  aplikasi_id: '',
  deskripsi: '',
  jenis_integrasi: 'berbagi_data',
  metode_integrasi: 'spl',
  target_application_ids: [],
  external_endpoints: '',
  data_asset_ids: [],
  documents: [],
});

const backupMediaForm = reactive({
  nama: '',
  location: 'local',
  jenis_media: 'NAS',
  kapasitas_gb: 1024,
  address_url: '',
});

const backupJobForm = reactive({
  aplikasi_id: '',
  backup_media_id: '',
  retensi_n: 30,
  retensi_unit: 'hari',
  repetisi_n: 1,
  repetisi_unit: 'hari',
});

const upsDeviceForm = reactive({
  nama: '',
  kapasitas_va: 3000,
  kondisi: 'baik',
  dc_id: '',
});

const socToolForm = reactive({
  nama: '',
  deskripsi_fungsi: '',
  jenis: 'Firewall',
  dc_ids: [],
  server_ids: [],
  vm_ids: [],
  application_ids: [],
});

const userForm = reactive({
  nama: '',
  email: '',
  password: '',
  opd_id: '',
  role: 'read_only',
  status: 'aktif',
});

const dataAssetForm = reactive({
  aplikasi_id: '',
  classification_id: '',
  name: '',
  type: 'COLUMN',
  attributes: '',
  owner_agency: '',
  confidentiality_score: 1,
  integrity_score: 1,
  availability_score: 1,
  table_name: '',
  column_name: '',
  contains_personal_data: false,
  personal_data_type: '',
  processing_purpose: '',
  retention_period: '',
  storage_location: '',
  data_owner: 'Diskominfo Kabupaten Langkat',
  access_policy: '',
  description: '',
});

const functionClassificationOptions = [
  { value: 'layanan_publik', label: 'Layanan Publik' },
  { value: 'layanan_internal', label: 'Layanan Internal' },
  { value: 'tools_pendukung', label: 'Tools Pendukung' },
  { value: 'platform_integrasi', label: 'Platform Integrasi' },
  { value: 'low_code_no_code', label: 'Platform Low-code / No-code' },
  { value: 'monitoring_observability', label: 'Monitoring / Observability' },
  { value: 'security_tools', label: 'Security Tools' },
  { value: 'kolaborasi_knowledge_base', label: 'Kolaborasi / Knowledge Base' },
];

function functionClassificationLabel(value) {
  return functionClassificationOptions.find((option) => option.value === value)?.label || value;
}

const changeFieldLabels = {
  nama: 'Nama',
  dc_id: 'Gedung / Ruang DC',
  rack_id: 'Rack',
  server_id: 'Host Server',
  merk: 'Merk',
  tipe: 'Tipe',
  serial_number: 'Serial Number',
  cpu_core: 'CPU Core',
  vcpu: 'vCPU',
  ram_gb: 'RAM GB',
  storage_gb: 'Storage GB',
  os: 'Operating System',
  kondisi: 'Kondisi',
  status: 'Status',
  tahun: 'Tahun',
  penanggung_jawab: 'Penanggung Jawab',
};

function changeFieldLabel(field) {
  return changeFieldLabels[field] || field;
}

async function api(path, options = {}) {
  const response = await fetch(`/api${path}`, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(authToken.value ? { Authorization: `Bearer ${authToken.value}` } : {}),
      ...(options.headers || {}),
    },
    ...options,
  });

  if (!response.ok) {
    const body = await response.json().catch(() => ({}));
    if (response.status === 401) clearAuth();
    const apiError = new Error(body.message || `API error ${response.status}`);
    apiError.status = response.status;
    apiError.type = body.type || null;
    throw apiError;
  }

  if (response.status === 204) return null;
  return response.json();
}

async function apiForm(path, formData, method = 'POST') {
  if (method !== 'POST') {
    formData.append('_method', method);
  }

  const response = await fetch(`/api${path}`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      ...(authToken.value ? { Authorization: `Bearer ${authToken.value}` } : {}),
    },
    body: formData,
  });

  if (!response.ok) {
    const body = await response.json().catch(() => ({}));
    if (response.status === 401) clearAuth();
    const apiError = new Error(body.message || `API error ${response.status}`);
    apiError.status = response.status;
    apiError.type = body.type || null;
    throw apiError;
  }

  if (response.status === 204) return null;
  return response.json();
}

function clearAuth() {
  authToken.value = '';
  currentUser.value = null;
  localStorage.removeItem('iamt_token');
}

async function login() {
  error.value = '';
  try {
    const response = await api('/auth/login', { method: 'POST', body: JSON.stringify(authForm) });
    authToken.value = response.token;
    currentUser.value = response.user;
    localStorage.setItem('iamt_token', response.token);
    await loadAll();
  } catch (err) {
    error.value = err.message;
  }
}

async function loadCurrentUser() {
  if (!authToken.value) return;
  try {
    const response = await api('/auth/me');
    currentUser.value = response.user;
  } catch {
    clearAuth();
  }
}

async function logout() {
  try {
    await api('/auth/logout', { method: 'POST', body: JSON.stringify({}) });
  } catch {
    // Token may already be invalid; clear local state either way.
  }
  clearAuth();
}

async function bootstrapAuth() {
  await loadCurrentUser();
  if (currentUser.value) await loadAll();
}

async function loadAll() {
  loading.value = true;
  error.value = '';
  try {
    const [dash, refs, dcRows, rackRows, ispRows, ipRows, serverRows, vmRows, appRows, dataAssetRows, documentRows, integrationRows, backupMediaRows, backupJobRows, upsRows, socRows, userRows, mapRows, complianceRows, auditRows, changeRows] = await Promise.all([
      api('/dashboard'),
      api('/references'),
      api('/data-centers'),
      api('/racks'),
      api('/isps'),
      api('/ip-addresses'),
      api('/servers'),
      api('/vms'),
      api('/applications'),
      api('/data-assets'),
      api('/application-documents'),
      api('/app-integrations'),
      api('/backup-media'),
      api('/backup-jobs'),
      api('/ups-devices'),
      api('/soc-tools'),
      api('/users'),
      api('/dependency-map'),
      api('/compliance'),
      api('/audit-log'),
      api('/asset-change-logs'),
    ]);

    dashboard.value = dash;
    references.value = refs;
    dataCenters.value = dcRows;
    racks.value = rackRows;
    isps.value = ispRows;
    ipAddresses.value = ipRows;
    servers.value = serverRows;
    vms.value = vmRows;
    applications.value = appRows;
    dataAssets.value = dataAssetRows;
    applicationDocuments.value = documentRows;
    appIntegrations.value = integrationRows;
    backupMedia.value = backupMediaRows;
    backupJobs.value = backupJobRows;
    upsDevices.value = upsRows;
    socTools.value = socRows;
    users.value = userRows;
    dependencyMap.value = mapRows;
    compliance.value = complianceRows;
    auditLog.value = auditRows;
    assetChangeLogs.value = changeRows;
    selectedServerId.value ||= serverRows[0]?.id || '';
    if (selectedServerId.value) await loadImpact();
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}

async function loadImpact() {
  if (!selectedServerId.value) return;
  impact.value = await api(`/impact/server/${selectedServerId.value}`);
}

const moduleLabels = {
  'data-centers': 'Data Center',
  racks: 'Rack',
  servers: 'Server',
  vms: 'Virtual Machine',
  'ip-addresses': 'IP Address',
  isps: 'ISP',
  applications: 'Aplikasi',
  'data-assets': 'Data Aplikasi',
  'application-documents': 'Dokumen',
  'app-integrations': 'Interoperabilitas',
  'backup-media': 'Media Pencadangan',
  'backup-jobs': 'Pencadangan',
  'ups-devices': 'UPS / Power Backup',
  'soc-tools': 'SOC',
  users: 'Pengguna & Role',
};

const activeModuleLabel = computed(() => moduleLabels[modal.module] || 'Data');

function resetModuleForm(module) {
  if (module === 'data-centers') Object.assign(dataCenterForm, { nama: '', lokasi: 'Stabat', tipe: 'utama' });
  if (module === 'racks') Object.assign(rackForm, { dc_id: '', nama: '', kapasitas_u: 42 });
  if (module === 'isps') Object.assign(ispForm, { nama: '', tipe: 'Fiber Dedicated', bandwidth: '', kontak: '' });
  if (module === 'ip-addresses') Object.assign(ipAddressForm, { ip: '', jenis: 'private', isp_id: '' });
  if (module === 'servers') {
    Object.assign(serverForm, {
      nama: '',
      dc_id: '',
      rack_id: '',
      merk: '',
      tipe: '',
      cpu_core: 16,
      ram_gb: 64,
      storage_gb: 1024,
      kondisi: 'baik',
      status: 'aktif',
      penanggung_jawab: 'Bidang Infrastruktur TIK',
      change_reason: '',
      changed_by: '',
    });
  }
  if (module === 'vms') {
    Object.assign(vmForm, {
      nama: '',
      server_id: '',
      os: 'Ubuntu Server 24.04 LTS',
      vcpu: 4,
      ram_gb: 8,
      storage_gb: 120,
      status: 'running',
      ip_ids: [],
      change_reason: '',
      changed_by: '',
    });
  }
  if (module === 'applications') {
    Object.assign(appForm, {
      nama: '',
      url: '',
      opd_id: '',
  jenis_aplikasi: 'web',
  klasifikasi_fungsi: [],
  tech_stack: '',
      status: 'aktif',
      sla_persen: 99,
      kategori_data: 'terbatas',
      pic_nama: '',
      pic_kontak: '',
      lokasi_data: '',
      risiko: '',
      vm_ids: [],
      server_ids: [],
      ip_ids: [],
    });
  }
  if (module === 'data-assets') {
    Object.assign(dataAssetForm, {
      aplikasi_id: '',
      classification_id: references.value.classifications[0]?.id || '',
      name: '',
      type: 'COLUMN',
      attributes: '',
      owner_agency: '',
      confidentiality_score: 1,
      integrity_score: 1,
      availability_score: 1,
      table_name: '',
      column_name: '',
      contains_personal_data: false,
      personal_data_type: '',
      processing_purpose: '',
      retention_period: '',
      storage_location: '',
      data_owner: 'Diskominfo Kabupaten Langkat',
      access_policy: '',
      description: '',
    });
  }
  if (module === 'application-documents') {
    Object.assign(applicationDocumentForm, { aplikasi_id: '', document_category: 'petunjuk_teknis', files: [] });
  }
  if (module === 'app-integrations') {
    Object.assign(appIntegrationForm, {
      aplikasi_id: '',
      deskripsi: '',
      jenis_integrasi: 'berbagi_data',
      metode_integrasi: 'spl',
      target_application_ids: [],
      external_endpoints: '',
      data_asset_ids: [],
      documents: [],
    });
  }
  if (module === 'backup-media') {
    Object.assign(backupMediaForm, { nama: '', location: 'local', jenis_media: 'NAS', kapasitas_gb: 1024, address_url: '' });
  }
  if (module === 'backup-jobs') {
    Object.assign(backupJobForm, { aplikasi_id: '', backup_media_id: '', retensi_n: 30, retensi_unit: 'hari', repetisi_n: 1, repetisi_unit: 'hari' });
  }
  if (module === 'ups-devices') {
    Object.assign(upsDeviceForm, { nama: '', kapasitas_va: 3000, kondisi: 'baik', dc_id: '' });
  }
  if (module === 'soc-tools') {
    Object.assign(socToolForm, { nama: '', deskripsi_fungsi: '', jenis: 'Firewall', dc_ids: [], server_ids: [], vm_ids: [], application_ids: [] });
  }
  if (module === 'users') {
    Object.assign(userForm, { nama: '', email: '', password: '', opd_id: '', role: 'read_only', status: 'aktif' });
  }
}

function formFor(module) {
  return {
    'data-centers': dataCenterForm,
    racks: rackForm,
    isps: ispForm,
    'ip-addresses': ipAddressForm,
    servers: serverForm,
    vms: vmForm,
    applications: appForm,
    'data-assets': dataAssetForm,
    'application-documents': applicationDocumentForm,
    'app-integrations': appIntegrationForm,
    'backup-media': backupMediaForm,
    'backup-jobs': backupJobForm,
    'ups-devices': upsDeviceForm,
    'soc-tools': socToolForm,
    users: userForm,
  }[module];
}

function openCreate(module) {
  if (!canWrite.value) return;
  resetModuleForm(module);
  Object.assign(modal, { open: true, module, mode: 'create', id: null });
}

function openEdit(module, row) {
  if (!canWrite.value) return;
  resetModuleForm(module);
  if (module === 'data-centers') {
    Object.assign(dataCenterForm, { nama: row.nama || '', lokasi: row.lokasi || '', tipe: row.tipe || 'utama' });
  }
  if (module === 'racks') {
    Object.assign(rackForm, { dc_id: row.dc_id || '', nama: row.nama || '', kapasitas_u: row.kapasitas_u || 42 });
  }
  if (module === 'isps') {
    Object.assign(ispForm, { nama: row.nama || '', tipe: row.tipe || '', bandwidth: row.bandwidth || '', kontak: row.kontak || '' });
  }
  if (module === 'ip-addresses') {
    Object.assign(ipAddressForm, { ip: row.ip || '', jenis: row.jenis || 'private', isp_id: row.isp_id || '' });
  }
  if (module === 'servers') {
    Object.assign(serverForm, {
      nama: row.nama || '',
      dc_id: row.dc_id || '',
      rack_id: row.rack_id || '',
      merk: row.merk || '',
      tipe: row.tipe || '',
      cpu_core: row.cpu_core || 16,
      ram_gb: row.ram_gb || 64,
      storage_gb: row.storage_gb || 1024,
      kondisi: row.kondisi || 'baik',
      status: row.status || 'aktif',
      penanggung_jawab: row.penanggung_jawab || '',
      change_reason: '',
      changed_by: '',
    });
  }
  if (module === 'vms') {
    Object.assign(vmForm, {
      nama: row.nama || '',
      server_id: row.server_id || '',
      os: row.os || '',
      vcpu: row.vcpu || 4,
      ram_gb: row.ram_gb || 8,
      storage_gb: row.storage_gb || 120,
      status: row.status || 'running',
      ip_ids: (row.ip_addresses || []).map((ip) => ip.id),
      change_reason: '',
      changed_by: '',
    });
  }
  if (module === 'applications') {
    Object.assign(appForm, {
      nama: row.nama || '',
      url: row.url || '',
      opd_id: row.opd_id || '',
      jenis_aplikasi: row.jenis_aplikasi || 'web',
      klasifikasi_fungsi: row.klasifikasi_fungsi || [],
      tech_stack: row.tech_stack || '',
      status: row.status || 'aktif',
      sla_persen: Number(row.sla_persen || 99),
      kategori_data: row.kategori_data || 'terbatas',
      pic_nama: row.pic_nama || '',
      pic_kontak: row.pic_kontak || '',
      lokasi_data: row.lokasi_data || '',
      risiko: row.risiko || '',
      vm_ids: (row.vms || []).map((vm) => vm.id),
      server_ids: (row.servers || []).map((server) => server.id),
      ip_ids: (row.ip_addresses || []).map((ip) => ip.id),
    });
  }
  if (module === 'application-documents') {
    Object.assign(applicationDocumentForm, {
      aplikasi_id: row.aplikasi_id || '',
      document_category: row.document_category || 'petunjuk_teknis',
      files: [],
    });
  }
  if (module === 'app-integrations') {
    Object.assign(appIntegrationForm, {
      aplikasi_id: row.aplikasi_id || '',
      deskripsi: row.deskripsi || '',
      jenis_integrasi: row.jenis_integrasi || 'berbagi_data',
      metode_integrasi: row.metode_integrasi || 'spl',
      target_application_ids: (row.target_applications || []).map((app) => app.id),
      external_endpoints: row.external_endpoints || '',
      data_asset_ids: (row.data_assets || []).map((asset) => asset.id),
      documents: [],
    });
  }
  if (module === 'backup-media') {
    Object.assign(backupMediaForm, {
      nama: row.nama || '',
      location: row.location || 'local',
      jenis_media: row.jenis_media || 'NAS',
      kapasitas_gb: row.kapasitas_gb || 1024,
      address_url: row.address_url || '',
    });
  }
  if (module === 'backup-jobs') {
    Object.assign(backupJobForm, {
      aplikasi_id: row.aplikasi_id || '',
      backup_media_id: row.backup_media_id || '',
      retensi_n: row.retensi_n || 30,
      retensi_unit: row.retensi_unit || 'hari',
      repetisi_n: row.repetisi_n || 1,
      repetisi_unit: row.repetisi_unit || 'hari',
    });
  }
  if (module === 'ups-devices') {
    Object.assign(upsDeviceForm, {
      nama: row.nama || '',
      kapasitas_va: row.kapasitas_va || 3000,
      kondisi: row.kondisi || 'baik',
      dc_id: row.dc_id || '',
    });
  }
  if (module === 'soc-tools') {
    Object.assign(socToolForm, {
      nama: row.nama || '',
      deskripsi_fungsi: row.deskripsi_fungsi || '',
      jenis: row.jenis || 'Firewall',
      dc_ids: (row.data_centers || []).map((dc) => dc.id),
      server_ids: (row.servers || []).map((server) => server.id),
      vm_ids: (row.vms || []).map((vm) => vm.id),
      application_ids: (row.applications || []).map((app) => app.id),
    });
  }
  if (module === 'users') {
    Object.assign(userForm, {
      nama: row.nama || '',
      email: row.email || '',
      password: '',
      opd_id: row.opd_id || '',
      role: row.role || 'read_only',
      status: row.status || 'aktif',
    });
  }
  if (module === 'data-assets') {
    Object.assign(dataAssetForm, {
      aplikasi_id: row.aplikasi_id || '',
      classification_id: row.classification_id || '',
      name: row.name || '',
      type: row.type || 'COLUMN',
      attributes: row.attributes || '',
      owner_agency: row.owner_agency || '',
      confidentiality_score: row.confidentiality_score || 1,
      integrity_score: row.integrity_score || 1,
      availability_score: row.availability_score || 1,
      table_name: row.table_name || '',
      column_name: row.column_name || '',
      contains_personal_data: Boolean(row.contains_personal_data),
      personal_data_type: row.personal_data_type || '',
      processing_purpose: row.processing_purpose || '',
      retention_period: row.retention_period || '',
      storage_location: row.storage_location || '',
      data_owner: row.data_owner || '',
      access_policy: row.access_policy || '',
      description: row.description || '',
    });
  }
  Object.assign(modal, { open: true, module, mode: 'edit', id: row.id });
}

function closeModal() {
  Object.assign(modal, { open: false, module: '', mode: 'create', id: null });
}

function showAlert(title, message) {
  Object.assign(alertModal, { open: true, title, message });
}

function closeAlert() {
  Object.assign(alertModal, { open: false, title: '', message: '' });
}

async function saveModal() {
  if (!canWrite.value) {
    error.value = 'Akses read only tidak dapat mengubah data.';
    return;
  }
  error.value = '';
  try {
    if (['application-documents', 'app-integrations'].includes(modal.module)) {
      const formData = formDataFor(modal.module);
      const endpoint = modal.mode === 'edit' ? `/${modal.module}/${modal.id}` : `/${modal.module}`;
      await apiForm(endpoint, formData, modal.mode === 'edit' ? 'PUT' : 'POST');
      closeModal();
      await loadAll();
      return;
    }

    const endpoint = modal.mode === 'edit' ? `/${modal.module}/${modal.id}` : `/${modal.module}`;
    await api(endpoint, {
      method: modal.mode === 'edit' ? 'PUT' : 'POST',
      body: JSON.stringify(cleanPayload(formFor(modal.module))),
    });
    closeModal();
    await loadAll();
  } catch (err) {
    error.value = err.message;
  }
}

function appendArray(formData, key, values) {
  values.forEach((value) => formData.append(`${key}[]`, value));
}

function formDataFor(module) {
  const formData = new FormData();

  if (module === 'application-documents') {
    formData.append('aplikasi_id', applicationDocumentForm.aplikasi_id);
    formData.append('document_category', applicationDocumentForm.document_category);
    applicationDocumentForm.files.forEach((file) => formData.append('files[]', file));
  }

  if (module === 'app-integrations') {
    formData.append('aplikasi_id', appIntegrationForm.aplikasi_id);
    formData.append('deskripsi', appIntegrationForm.deskripsi || '');
    formData.append('jenis_integrasi', appIntegrationForm.jenis_integrasi);
    formData.append('metode_integrasi', appIntegrationForm.metode_integrasi);
    formData.append('external_endpoints', appIntegrationForm.external_endpoints || '');
    appendArray(formData, 'target_application_ids', appIntegrationForm.target_application_ids);
    appendArray(formData, 'data_asset_ids', appIntegrationForm.data_asset_ids);
    appIntegrationForm.documents.forEach((file) => formData.append('documents[]', file));
  }

  return formData;
}

function setFiles(target, event) {
  target.files = Array.from(event.target.files || []);
}

function formatChangeFields(fields) {
  return Object.entries(fields || {}).map(([field, values]) => `${changeFieldLabel(field)}: ${values.before ?? '-'} ke ${values.after ?? '-'}`);
}

async function createDataCenter() {
  if (!canWrite.value) return;
  await api('/data-centers', { method: 'POST', body: JSON.stringify(cleanPayload(dataCenterForm)) });
  Object.assign(dataCenterForm, { nama: '', lokasi: 'Stabat', tipe: 'utama' });
  await loadAll();
}

async function createRack() {
  if (!canWrite.value) return;
  await api('/racks', { method: 'POST', body: JSON.stringify(cleanPayload(rackForm)) });
  Object.assign(rackForm, { dc_id: '', nama: '', kapasitas_u: 42 });
  await loadAll();
}

async function createIsp() {
  if (!canWrite.value) return;
  await api('/isps', { method: 'POST', body: JSON.stringify(cleanPayload(ispForm)) });
  Object.assign(ispForm, { nama: '', tipe: 'Fiber Dedicated', bandwidth: '', kontak: '' });
  await loadAll();
}

async function createIpAddress() {
  if (!canWrite.value) return;
  await api('/ip-addresses', { method: 'POST', body: JSON.stringify(cleanPayload(ipAddressForm)) });
  Object.assign(ipAddressForm, { ip: '', jenis: 'private', isp_id: '' });
  await loadAll();
}

async function createServer() {
  if (!canWrite.value) return;
  await api('/servers', { method: 'POST', body: JSON.stringify(cleanPayload(serverForm)) });
  Object.assign(serverForm, { nama: '', merk: '', tipe: '', cpu_core: 16, ram_gb: 64, storage_gb: 1024 });
  await loadAll();
}

async function createVm() {
  if (!canWrite.value) return;
  await api('/vms', { method: 'POST', body: JSON.stringify(cleanPayload(vmForm)) });
  Object.assign(vmForm, { nama: '', server_id: '', vcpu: 4, ram_gb: 8, storage_gb: 120, ip_ids: [] });
  await loadAll();
}

async function createApplication() {
  if (!canWrite.value) return;
  await api('/applications', { method: 'POST', body: JSON.stringify(cleanPayload(appForm)) });
  Object.assign(appForm, {
    nama: '',
    url: '',
    tech_stack: '',
    klasifikasi_fungsi: [],
    risiko: '',
    pic_nama: '',
    pic_kontak: '',
    vm_ids: [],
    server_ids: [],
    ip_ids: [],
  });
  await loadAll();
}

async function removeRow(kind, id) {
  if (!canWrite.value) {
    error.value = 'Akses read only tidak dapat menghapus data.';
    return;
  }
  error.value = '';
    try {
      await api(`/${kind}/${id}`, { method: 'DELETE' });
      await loadAll();
    } catch (err) {
      if (err.status === 409 || err.type === 'constraint_violation') {
        showAlert('Data tidak dapat dihapus', err.message);
        return;
      }
      error.value = err.message;
    }
  }

function cleanPayload(source) {
  return Object.fromEntries(
    Object.entries(source).filter(([, value]) => {
      if (Array.isArray(value)) return true;
      return value !== '' && value !== null && value !== undefined;
    }),
  );
}

function toggleInArray(list, id) {
  const index = list.indexOf(id);
  if (index >= 0) list.splice(index, 1);
  else list.push(id);
}

const filteredServers = computed(() => {
  const needle = query.value.toLowerCase();
  return servers.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredDataCenters = computed(() => {
  const needle = query.value.toLowerCase();
  return dataCenters.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredRacks = computed(() => {
  const needle = query.value.toLowerCase();
  return racks.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredIsps = computed(() => {
  const needle = query.value.toLowerCase();
  return isps.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredIpAddresses = computed(() => {
  const needle = query.value.toLowerCase();
  return ipAddresses.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredVms = computed(() => {
  const needle = query.value.toLowerCase();
  return vms.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredApplications = computed(() => {
  const needle = query.value.toLowerCase();
  return applications.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredDataAssets = computed(() => {
  const needle = query.value.toLowerCase();
  return dataAssets.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredApplicationDocuments = computed(() => {
  const needle = query.value.toLowerCase();
  return applicationDocuments.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredAppIntegrations = computed(() => {
  const needle = query.value.toLowerCase();
  return appIntegrations.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredBackupMedia = computed(() => {
  const needle = query.value.toLowerCase();
  return backupMedia.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredBackupJobs = computed(() => {
  const needle = query.value.toLowerCase();
  return backupJobs.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredUpsDevices = computed(() => {
  const needle = query.value.toLowerCase();
  return upsDevices.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredSocTools = computed(() => {
  const needle = query.value.toLowerCase();
  return socTools.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const filteredUsers = computed(() => {
  const needle = query.value.toLowerCase();
  return users.value.filter((row) => JSON.stringify(row).toLowerCase().includes(needle));
});

const dataAssetRiskTotal = computed(() => Number(dataAssetForm.confidentiality_score) + Number(dataAssetForm.integrity_score) + Number(dataAssetForm.availability_score));

const dataAssetCalculatedClassification = computed(() => {
  const total = dataAssetRiskTotal.value;
  if (total <= 7) return { code: 'OPEN', name: 'Data Elektronik Terbuka', risk: 'LOW', color: 'Hijau' };
  if (total <= 11) return { code: 'LIMITED', name: 'Data Elektronik Terbatas', risk: 'MEDIUM', color: 'Kuning' };
  return { code: 'RESTRICTED', name: 'Data Elektronik Tertutup', risk: 'HIGH', color: 'Merah' };
});

const statusClass = (status) => `status status-${status || 'unknown'}`;
const yesNo = (value) => (value ? 'Ya' : 'Tidak');

onMounted(bootstrapAuth);
</script>

<template>
  <div v-if="!currentUser" class="login-shell">
    <form class="login-card" @submit.prevent="login">
      <div class="brand login-brand">
        <div class="brand-mark">
          <Database :size="26" />
        </div>
        <div>
          <p class="eyebrow">Kabupaten Langkat</p>
          <h1>IAMT CMDB</h1>
        </div>
      </div>
      <div>
        <p class="eyebrow">Autentikasi</p>
        <h2 class="yellow-title">Masuk Aplikasi</h2>
      </div>
      <input v-model="authForm.email" required type="email" placeholder="Email" />
      <input v-model="authForm.password" required type="password" placeholder="Password" />
      <button class="action-button" type="submit">Masuk</button>
      <p class="login-hint">Demo full: admin@langkatkab.go.id / password</p>
      <p class="login-hint">Demo read only: viewer@langkatkab.go.id / password</p>
      <div v-if="error" class="alert">
        <AlertTriangle :size="18" />
        {{ error }}
      </div>
    </form>
  </div>

  <div v-else class="app-shell">
    <aside class="sidebar">
      <div class="brand">
        <div class="brand-mark">
          <Database :size="26" />
        </div>
        <div>
          <p class="eyebrow">Kabupaten Langkat</p>
          <h1>IAMT CMDB</h1>
        </div>
      </div>

      <nav class="nav-list">
        <section v-for="(section, index) in menuSections" :key="section.label || index" class="nav-section">
          <p v-if="section.label" class="nav-section-title">{{ section.label }}</p>
          <button
            v-for="item in section.items"
            :key="item.id"
            class="nav-item"
            :class="{ active: activeTab === item.id, disabled: item.disabled }"
            type="button"
            :disabled="item.disabled"
            @click="!item.disabled && (activeTab = item.id)"
          >
            <component :is="item.icon" :size="18" />
            <span>{{ item.label }}</span>
            <small v-if="item.disabled">TODO</small>
          </button>
        </section>
      </nav>

      <div class="sidebar-panel">
        <p class="panel-title yellow-title">SPBE & PSE</p>
        <strong>{{ dashboard?.capacity.security_coverage ?? 0 }}%</strong>
        <span>coverage security server</span>
      </div>
    </aside>

    <main class="workspace">
      <header class="topbar">
        <div>
          <p class="eyebrow">Single Source of Truth</p>
          <h2>Manajemen Aset Digital</h2>
        </div>
        <div class="top-actions">
          <div class="user-pill">
            <strong>{{ currentUser.nama }}</strong>
            <span>{{ currentUser.role === 'full' ? 'Full Access' : 'Read Only' }}</span>
          </div>
          <label class="search">
            <Search :size="17" />
            <input v-model="query" type="search" placeholder="Cari aset, OPD, status" />
          </label>
          <button class="icon-button primary" type="button" title="Refresh" @click="loadAll">
            <RefreshCw :size="18" :class="{ spin: loading }" />
          </button>
          <button class="action-button ghost compact-button" type="button" @click="logout">Keluar</button>
        </div>
      </header>

      <div v-if="error" class="alert">
        <AlertTriangle :size="18" />
        {{ error }}
      </div>

      <section v-if="activeTab === 'dashboard'" class="content-grid">
        <div class="metrics-row">
          <article v-for="metric in dashboard?.metrics || []" :key="metric.label" class="metric-card" :class="metric.tone">
            <span>{{ metric.label }}</span>
            <strong>{{ metric.value }}</strong>
            <small>{{ metric.hint }}</small>
          </article>
        </div>

        <section class="surface wide">
          <div class="section-heading">
            <div>
              <p class="eyebrow">Kapasitas Infrastruktur</p>
              <h3 class="yellow-title">Ringkasan Operasional</h3>
            </div>
            <ShieldCheck class="title-icon" :size="30" />
          </div>
          <div class="capacity-grid">
            <div>
              <HardDrive :size="22" />
              <span>CPU</span>
              <strong>{{ dashboard?.capacity.cpu_core || 0 }} core</strong>
            </div>
            <div>
              <Box :size="22" />
              <span>RAM</span>
              <strong>{{ dashboard?.capacity.ram_gb || 0 }} GB</strong>
            </div>
            <div>
              <Database :size="22" />
              <span>Storage</span>
              <strong>{{ dashboard?.capacity.storage_gb || 0 }} GB</strong>
            </div>
            <div>
              <ShieldCheck :size="22" />
              <span>Security</span>
              <strong>{{ dashboard?.capacity.security_coverage || 0 }}%</strong>
            </div>
          </div>
        </section>

        <section class="surface">
          <div class="section-heading compact">
            <h3>Aplikasi Prioritas</h3>
            <FileCheck2 :size="24" />
          </div>
          <div class="priority-list">
            <article v-for="app in dashboard?.priority || []" :key="app.id" class="mini-row">
              <div>
                <strong>{{ app.nama }}</strong>
                <span>{{ app.opd?.nama || 'OPD belum dipilih' }}</span>
              </div>
              <span :class="statusClass(app.status)">{{ app.status }}</span>
            </article>
          </div>
        </section>

        <section class="surface">
          <div class="section-heading compact">
            <h3>Status Layanan</h3>
            <Activity :size="24" />
          </div>
          <div class="status-stack">
            <div v-for="(group, key) in dashboard?.status || {}" :key="key">
              <span>{{ key }}</span>
              <strong>{{ Object.values(group).reduce((a, b) => a + b, 0) }}</strong>
              <small>{{ Object.entries(group).map(([name, total]) => `${name}: ${total}`).join(' / ') }}</small>
            </div>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'assets'" class="content-grid">
        <section class="surface wide">
          <div class="section-heading">
            <div>
              <p class="eyebrow">Infrastruktur</p>
              <h3 class="yellow-title">Data Center, Rack, Server, VM, IP & ISP</h3>
            </div>
            <Network :size="30" />
          </div>

          <div v-if="canWrite" class="split-grid infrastructure-forms">
            <form class="form-panel" @submit.prevent="createDataCenter">
              <h4>Data Center Baru</h4>
              <input v-model="dataCenterForm.nama" required placeholder="Nama data center" />
              <input v-model="dataCenterForm.lokasi" placeholder="Lokasi" />
              <select v-model="dataCenterForm.tipe" required>
                <option value="utama">Utama</option>
                <option value="dr">Disaster Recovery</option>
                <option value="cloud">Cloud</option>
              </select>
              <button class="action-button" type="submit"><Plus :size="17" /> Tambah Data Center</button>
            </form>

            <form class="form-panel" @submit.prevent="createRack">
              <h4>Rack Baru</h4>
              <input v-model="rackForm.nama" required placeholder="Nama rack" />
              <select v-model="rackForm.dc_id" required>
                <option value="">Data center</option>
                <option v-for="dc in references.data_centers" :key="dc.id" :value="dc.id">{{ dc.nama }}</option>
              </select>
              <input v-model.number="rackForm.kapasitas_u" required type="number" min="1" max="60" placeholder="Kapasitas U" />
              <button class="action-button secondary" type="submit"><Plus :size="17" /> Tambah Rack</button>
            </form>

            <form class="form-panel" @submit.prevent="createIsp">
              <h4>ISP Baru</h4>
              <input v-model="ispForm.nama" required placeholder="Nama ISP" />
              <div class="two-col">
                <input v-model="ispForm.tipe" placeholder="Tipe koneksi" />
                <input v-model="ispForm.bandwidth" placeholder="Bandwidth" />
              </div>
              <input v-model="ispForm.kontak" placeholder="Kontak NOC / PIC" />
              <button class="action-button" type="submit"><Plus :size="17" /> Tambah ISP</button>
            </form>

            <form class="form-panel" @submit.prevent="createIpAddress">
              <h4>IP Address Baru</h4>
              <input v-model="ipAddressForm.ip" required placeholder="Alamat IP" />
              <div class="two-col">
                <select v-model="ipAddressForm.jenis" required>
                  <option value="private">Private</option>
                  <option value="publik">Publik</option>
                </select>
                <select v-model="ipAddressForm.isp_id">
                  <option value="">Tanpa ISP</option>
                  <option v-for="isp in references.isps" :key="isp.id" :value="isp.id">{{ isp.nama }}</option>
                </select>
              </div>
              <button class="action-button secondary" type="submit"><Plus :size="17" /> Tambah IP</button>
            </form>
          </div>

          <div class="split-grid">
            <form class="form-panel" @submit.prevent="createServer">
              <h4>Server Baru</h4>
              <input v-model="serverForm.nama" required placeholder="Nama server" />
              <div class="two-col">
                <select v-model="serverForm.dc_id">
                  <option value="">Data center</option>
                  <option v-for="dc in references.data_centers" :key="dc.id" :value="dc.id">{{ dc.nama }}</option>
                </select>
                <select v-model="serverForm.rack_id">
                  <option value="">Rack</option>
                  <option v-for="rack in references.racks" :key="rack.id" :value="rack.id">{{ rack.nama }}</option>
                </select>
              </div>
              <div class="two-col">
                <input v-model="serverForm.merk" placeholder="Merk" />
                <input v-model="serverForm.tipe" placeholder="Tipe" />
              </div>
              <div class="three-col">
                <input v-model.number="serverForm.cpu_core" type="number" min="1" placeholder="Core" />
                <input v-model.number="serverForm.ram_gb" type="number" min="1" placeholder="RAM GB" />
                <input v-model.number="serverForm.storage_gb" type="number" min="1" placeholder="Storage GB" />
              </div>
              <button class="action-button" type="submit"><Plus :size="17" /> Tambah Server</button>
            </form>

            <form class="form-panel" @submit.prevent="createVm">
              <h4>VM Baru</h4>
              <input v-model="vmForm.nama" required placeholder="Nama VM" />
              <select v-model="vmForm.server_id">
                <option value="">Host server</option>
                <option v-for="server in references.servers" :key="server.id" :value="server.id">{{ server.nama }}</option>
              </select>
              <input v-model="vmForm.os" placeholder="Operating system" />
              <div class="three-col">
                <input v-model.number="vmForm.vcpu" type="number" min="1" placeholder="vCPU" />
                <input v-model.number="vmForm.ram_gb" type="number" min="1" placeholder="RAM GB" />
                <input v-model.number="vmForm.storage_gb" type="number" min="1" placeholder="Storage GB" />
              </div>
              <div class="inline-picker">
                <strong>IP Address</strong>
                <button
                  v-for="ip in references.ips"
                  :key="ip.id"
                  class="chip"
                  :class="{ selected: vmForm.ip_ids.includes(ip.id) }"
                  type="button"
                  @click="toggleInArray(vmForm.ip_ids, ip.id)"
                >
                  {{ ip.ip }}
                </button>
              </div>
              <button class="action-button secondary" type="submit"><Plus :size="17" /> Tambah VM</button>
            </form>
          </div>
        </section>

        <section class="surface wide">
          <div class="section-heading compact">
            <h3>Data Center & Rack</h3>
            <Building2 :size="24" />
          </div>
          <div class="two-table-grid">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Data Center</th>
                    <th>Lokasi</th>
                    <th>Tipe</th>
                    <th>Rack</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="dc in filteredDataCenters" :key="dc.id">
                    <td><strong>{{ dc.nama }}</strong></td>
                    <td>{{ dc.lokasi || '-' }}</td>
                    <td><span :class="statusClass(dc.tipe)">{{ dc.tipe }}</span></td>
                    <td>{{ dc.racks_count || 0 }}</td>
                    <td><button v-if="canWrite" class="icon-button danger" title="Hapus data center" @click="removeRow('data-centers', dc.id)"><Trash2 :size="16" /></button></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Rack</th>
                    <th>Data Center</th>
                    <th>Kapasitas</th>
                    <th>Server</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="rack in filteredRacks" :key="rack.id">
                    <td><strong>{{ rack.nama }}</strong></td>
                    <td>{{ rack.data_center?.nama || '-' }}</td>
                    <td>{{ rack.kapasitas_u || 0 }}U</td>
                    <td>{{ rack.servers_count || 0 }}</td>
                    <td><button v-if="canWrite" class="icon-button danger" title="Hapus rack" @click="removeRow('racks', rack.id)"><Trash2 :size="16" /></button></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <section class="surface wide">
          <div class="section-heading compact">
            <h3>IP Address & ISP</h3>
            <Network :size="24" />
          </div>
          <div class="two-table-grid">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>IP Address</th>
                    <th>Jenis</th>
                    <th>ISP</th>
                    <th>VM</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="ip in filteredIpAddresses" :key="ip.id">
                    <td><strong>{{ ip.ip }}</strong></td>
                    <td><span :class="statusClass(ip.jenis)">{{ ip.jenis }}</span></td>
                    <td>{{ ip.isp?.nama || '-' }}<span>{{ ip.isp?.bandwidth || '' }}</span></td>
                    <td>{{ ip.vms_count || 0 }}</td>
                    <td><button v-if="canWrite" class="icon-button danger" title="Hapus IP address" @click="removeRow('ip-addresses', ip.id)"><Trash2 :size="16" /></button></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>ISP</th>
                    <th>Tipe</th>
                    <th>Bandwidth</th>
                    <th>IP</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="isp in filteredIsps" :key="isp.id">
                    <td><strong>{{ isp.nama }}</strong><span>{{ isp.kontak || '-' }}</span></td>
                    <td>{{ isp.tipe || '-' }}</td>
                    <td>{{ isp.bandwidth || '-' }}</td>
                    <td>{{ isp.ip_addresses_count || 0 }}</td>
                    <td><button v-if="canWrite" class="icon-button danger" title="Hapus ISP" @click="removeRow('isps', isp.id)"><Trash2 :size="16" /></button></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <section class="surface wide">
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Server</th>
                  <th>Lokasi</th>
                  <th>Kapasitas</th>
                  <th>Status</th>
                  <th>VM</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="server in filteredServers" :key="server.id">
                  <td><strong>{{ server.nama }}</strong><span>{{ server.merk }} {{ server.tipe }}</span></td>
                  <td>{{ server.data_center?.nama || '-' }}<span>{{ server.rack?.nama || '-' }}</span></td>
                  <td>{{ server.cpu_core }} core / {{ server.ram_gb }} GB<span>{{ server.storage_gb }} GB storage</span></td>
                  <td><span :class="statusClass(server.status)">{{ server.status }}</span></td>
                  <td>{{ server.vms?.length || 0 }}</td>
                  <td><button v-if="canWrite" class="icon-button danger" title="Hapus server" @click="removeRow('servers', server.id)"><Trash2 :size="16" /></button></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <section class="surface wide">
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>VM</th>
                  <th>Host</th>
                  <th>OS</th>
                  <th>Kapasitas</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="vm in filteredVms" :key="vm.id">
                  <td><strong>{{ vm.nama }}</strong></td>
                  <td>{{ vm.server?.nama || '-' }}</td>
                  <td>{{ vm.os }}</td>
                  <td>{{ vm.vcpu }} vCPU / {{ vm.ram_gb }} GB<span>{{ vm.storage_gb }} GB storage</span></td>
                  <td><span :class="statusClass(vm.status)">{{ vm.status }}</span></td>
                  <td><button v-if="canWrite" class="icon-button danger" title="Hapus VM" @click="removeRow('vms', vm.id)"><Trash2 :size="16" /></button></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'data-centers'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div>
              <p class="eyebrow">Master Infrastruktur</p>
              <h3 class="yellow-title">Data Center</h3>
            </div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('data-centers')"><Plus :size="17" /> Tambah Data Center</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Data Center</th>
                  <th>Lokasi</th>
                  <th>Tipe</th>
                  <th>Jumlah Rack</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="dc in filteredDataCenters" :key="dc.id">
                  <td><strong>{{ dc.nama }}</strong></td>
                  <td>{{ dc.lokasi || '-' }}</td>
                  <td><span :class="statusClass(dc.tipe)">{{ dc.tipe }}</span></td>
                  <td>{{ dc.racks_count || 0 }}</td>
                  <td>
                    <div v-if="canWrite" class="row-actions">
                      <button class="icon-button" title="Edit data center" @click="openEdit('data-centers', dc)"><Pencil :size="16" /></button>
                      <button v-if="canWrite" class="icon-button danger" title="Hapus data center" @click="removeRow('data-centers', dc.id)"><Trash2 :size="16" /></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'racks'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div>
              <p class="eyebrow">Master Infrastruktur</p>
              <h3 class="yellow-title">Rack</h3>
            </div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('racks')"><Plus :size="17" /> Tambah Rack</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Rack</th>
                  <th>Data Center</th>
                  <th>Kapasitas</th>
                  <th>Jumlah Server</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="rack in filteredRacks" :key="rack.id">
                  <td><strong>{{ rack.nama }}</strong></td>
                  <td>{{ rack.data_center?.nama || '-' }}<span>{{ rack.data_center?.lokasi || '' }}</span></td>
                  <td>{{ rack.kapasitas_u || 0 }}U</td>
                  <td>{{ rack.servers_count || 0 }}</td>
                  <td>
                    <div v-if="canWrite" class="row-actions">
                      <button class="icon-button" title="Edit rack" @click="openEdit('racks', rack)"><Pencil :size="16" /></button>
                      <button v-if="canWrite" class="icon-button danger" title="Hapus rack" @click="removeRow('racks', rack.id)"><Trash2 :size="16" /></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'servers'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div>
              <p class="eyebrow">Aset Fisik</p>
              <h3 class="yellow-title">Server Baremetal</h3>
            </div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('servers')"><Plus :size="17" /> Tambah Server</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Server</th>
                  <th>Lokasi</th>
                  <th>Kapasitas</th>
                  <th>Kondisi</th>
                  <th>Status</th>
                  <th>VM</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="server in filteredServers" :key="server.id">
                  <td><strong>{{ server.nama }}</strong><span>{{ server.merk }} {{ server.tipe }}</span></td>
                  <td>{{ server.data_center?.nama || '-' }}<span>{{ server.rack?.nama || '-' }}</span></td>
                  <td>{{ server.cpu_core }} core / {{ server.ram_gb }} GB<span>{{ server.storage_gb }} GB storage</span></td>
                  <td><span :class="statusClass(server.kondisi)">{{ server.kondisi || '-' }}</span></td>
                  <td><span :class="statusClass(server.status)">{{ server.status }}</span></td>
                  <td>{{ server.vms?.length || 0 }}</td>
                  <td>
                    <div v-if="canWrite" class="row-actions">
                      <button class="icon-button" title="Edit server" @click="openEdit('servers', server)"><Pencil :size="16" /></button>
                      <button v-if="canWrite" class="icon-button danger" title="Hapus server" @click="removeRow('servers', server.id)"><Trash2 :size="16" /></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'vms'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div>
              <p class="eyebrow">Virtualisasi</p>
              <h3 class="yellow-title">Virtual Machine</h3>
            </div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('vms')"><Plus :size="17" /> Tambah VM</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>VM</th>
                  <th>Host</th>
                  <th>OS</th>
                  <th>Kapasitas</th>
                  <th>IP Address</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="vm in filteredVms" :key="vm.id">
                  <td><strong>{{ vm.nama }}</strong></td>
                  <td>{{ vm.server?.nama || '-' }}</td>
                  <td>{{ vm.os || '-' }}</td>
                  <td>{{ vm.vcpu }} vCPU / {{ vm.ram_gb }} GB<span>{{ vm.storage_gb }} GB storage</span></td>
                  <td>{{ (vm.ip_addresses || []).map((ip) => ip.ip).join(', ') || '-' }}</td>
                  <td><span :class="statusClass(vm.status)">{{ vm.status }}</span></td>
                  <td>
                    <div v-if="canWrite" class="row-actions">
                      <button class="icon-button" title="Edit VM" @click="openEdit('vms', vm)"><Pencil :size="16" /></button>
                      <button v-if="canWrite" class="icon-button danger" title="Hapus VM" @click="removeRow('vms', vm.id)"><Trash2 :size="16" /></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'ip-addresses'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div>
              <p class="eyebrow">Network</p>
              <h3 class="yellow-title">IP Address</h3>
            </div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('ip-addresses')"><Plus :size="17" /> Tambah IP Address</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>IP Address</th>
                  <th>Jenis</th>
                  <th>ISP</th>
                  <th>VM Terkait</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ip in filteredIpAddresses" :key="ip.id">
                  <td><strong>{{ ip.ip }}</strong></td>
                  <td><span :class="statusClass(ip.jenis)">{{ ip.jenis }}</span></td>
                  <td>{{ ip.isp?.nama || '-' }}<span>{{ ip.isp?.bandwidth || '' }}</span></td>
                  <td>{{ ip.vms_count || 0 }}</td>
                  <td>
                    <div v-if="canWrite" class="row-actions">
                      <button class="icon-button" title="Edit IP address" @click="openEdit('ip-addresses', ip)"><Pencil :size="16" /></button>
                      <button v-if="canWrite" class="icon-button danger" title="Hapus IP address" @click="removeRow('ip-addresses', ip.id)"><Trash2 :size="16" /></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'isps'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div>
              <p class="eyebrow">Network</p>
              <h3 class="yellow-title">Internet Service Provider</h3>
            </div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('isps')"><Plus :size="17" /> Tambah ISP</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ISP</th>
                  <th>Tipe</th>
                  <th>Bandwidth</th>
                  <th>Kontak</th>
                  <th>Jumlah IP</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="isp in filteredIsps" :key="isp.id">
                  <td><strong>{{ isp.nama }}</strong></td>
                  <td>{{ isp.tipe || '-' }}</td>
                  <td>{{ isp.bandwidth || '-' }}</td>
                  <td>{{ isp.kontak || '-' }}</td>
                  <td>{{ isp.ip_addresses_count || 0 }}</td>
                  <td>
                    <div v-if="canWrite" class="row-actions">
                      <button class="icon-button" title="Edit ISP" @click="openEdit('isps', isp)"><Pencil :size="16" /></button>
                      <button v-if="canWrite" class="icon-button danger" title="Hapus ISP" @click="removeRow('isps', isp.id)"><Trash2 :size="16" /></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'applications'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div>
              <p class="eyebrow">Core CMDB</p>
              <h3 class="yellow-title">Aplikasi dan Relasi Infrastruktur</h3>
            </div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('applications')"><Plus :size="17" /> Tambah Aplikasi</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Aplikasi</th>
                  <th>OPD</th>
                  <th>Jenis</th>
                  <th>Fungsi</th>
                  <th>Tech Stack</th>
                  <th>SLA</th>
                  <th>Aset Data</th>
                  <th>Relasi</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="app in filteredApplications" :key="app.id">
                  <td><strong>{{ app.nama }}</strong><span>{{ app.url || '-' }}</span></td>
                  <td>{{ app.opd?.nama || '-' }}</td>
                  <td><span class="status">{{ app.jenis_aplikasi || '-' }}</span></td>
                  <td>{{ (app.klasifikasi_fungsi || []).map(functionClassificationLabel).join(', ') || '-' }}</td>
                  <td>{{ app.tech_stack || '-' }}</td>
                  <td>{{ app.sla_persen || 0 }}%</td>
                  <td>{{ app.data_assets?.length || 0 }}</td>
                  <td>{{ app.vms?.length || 0 }} VM / {{ app.servers?.length || 0 }} server</td>
                  <td><span :class="statusClass(app.status)">{{ app.status }}</span></td>
                  <td>
                    <div v-if="canWrite" class="row-actions">
                      <button class="icon-button" title="Edit aplikasi" @click="openEdit('applications', app)"><Pencil :size="16" /></button>
                      <button v-if="canWrite" class="icon-button danger" title="Hapus aplikasi" @click="removeRow('applications', app.id)"><Trash2 :size="16" /></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'data-assets'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div>
              <p class="eyebrow">Permenkomdigi No. 5 Tahun 2025</p>
              <h3 class="yellow-title">Data Aplikasi</h3>
            </div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('data-assets')"><Plus :size="17" /> Tambah Data Aplikasi</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Aset Data</th>
                  <th>Aplikasi</th>
                  <th>Tipe</th>
                  <th>Klasifikasi</th>
                  <th>Risiko</th>
                  <th>K/I/K</th>
                  <th>Data Pribadi</th>
                  <th>Kontrol</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="asset in filteredDataAssets" :key="asset.id">
                  <td><strong>{{ asset.name }}</strong><span>{{ [asset.table_name, asset.column_name].filter(Boolean).join('.') || asset.description || '-' }}</span></td>
                  <td>{{ asset.aplikasi?.nama || '-' }}<span>{{ asset.aplikasi?.jenis_aplikasi || '' }}</span></td>
                  <td><span class="status">{{ asset.type }}</span></td>
                  <td>{{ asset.classification?.name || '-' }}<span>{{ asset.classification?.code || '' }}</span></td>
                  <td><span :class="statusClass(asset.classification?.risk_level)">{{ asset.risk_total || 0 }} / {{ asset.classification?.risk_level || '-' }}</span></td>
                  <td>{{ asset.confidentiality_score || '-' }}/{{ asset.integrity_score || '-' }}/{{ asset.availability_score || '-' }}</td>
                  <td>{{ yesNo(asset.contains_personal_data) }}<span>{{ asset.personal_data_type || '' }}</span></td>
                  <td>
                    <span>Enkripsi: {{ yesNo(asset.classification?.requires_encryption) }}</span>
                    <span>MFA: {{ yesNo(asset.classification?.requires_mfa) }}</span>
                    <span>Audit: {{ yesNo(asset.classification?.requires_audit_log) }}</span>
                  </td>
                  <td>
                    <div v-if="canWrite" class="row-actions">
                      <button class="icon-button" title="Edit data aplikasi" @click="openEdit('data-assets', asset)"><Pencil :size="16" /></button>
                      <button v-if="canWrite" class="icon-button danger" title="Hapus data aplikasi" @click="removeRow('data-assets', asset.id)"><Trash2 :size="16" /></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'application-documents'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div><p class="eyebrow">Dokumen Aplikasi</p><h3 class="yellow-title">Dokumen</h3></div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('application-documents')"><Plus :size="17" /> Tambah Dokumen</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Aplikasi</th><th>Kategori</th><th>File</th><th>Ukuran</th><th>Tanggal</th><th>Aksi</th></tr></thead>
              <tbody>
                <tr v-for="doc in filteredApplicationDocuments" :key="doc.id">
                  <td>{{ doc.aplikasi?.nama || '-' }}</td>
                  <td><span class="status">{{ doc.document_category || doc.jenis }}</span></td>
                  <td><strong>{{ doc.original_name || doc.nama }}</strong><span>{{ doc.path || '-' }}</span></td>
                  <td>{{ doc.size_bytes ? Math.round(doc.size_bytes / 1024) + ' KB' : '-' }}</td>
                  <td>{{ doc.tanggal || '-' }}</td>
                  <td><div v-if="canWrite" class="row-actions"><button class="icon-button" title="Edit dokumen" @click="openEdit('application-documents', doc)"><Pencil :size="16" /></button><button v-if="canWrite" class="icon-button danger" title="Hapus dokumen" @click="removeRow('application-documents', doc.id)"><Trash2 :size="16" /></button></div></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'app-integrations'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div><p class="eyebrow">Interoperabilitas</p><h3 class="yellow-title">Integrasi Aplikasi</h3></div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('app-integrations')"><Plus :size="17" /> Tambah Integrasi</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Aplikasi</th><th>Jenis</th><th>Metode</th><th>Target</th><th>Data</th><th>Dokumen</th><th>Aksi</th></tr></thead>
              <tbody>
                <tr v-for="integration in filteredAppIntegrations" :key="integration.id">
                  <td><strong>{{ integration.aplikasi?.nama || '-' }}</strong><span>{{ integration.deskripsi || '-' }}</span></td>
                  <td><span class="status">{{ integration.jenis_integrasi }}</span></td>
                  <td><span class="status">{{ integration.metode_integrasi }}</span></td>
                  <td>{{ (integration.target_applications || []).map((app) => app.nama).join(', ') || integration.external_endpoints || '-' }}</td>
                  <td>{{ (integration.data_assets || []).map((asset) => asset.name).join(', ') || '-' }}</td>
                  <td>{{ integration.documents?.length || 0 }}</td>
                  <td><div v-if="canWrite" class="row-actions"><button class="icon-button" title="Edit integrasi" @click="openEdit('app-integrations', integration)"><Pencil :size="16" /></button><button v-if="canWrite" class="icon-button danger" title="Hapus integrasi" @click="removeRow('app-integrations', integration.id)"><Trash2 :size="16" /></button></div></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'backup-media'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div><p class="eyebrow">Keamanan Informasi</p><h3 class="yellow-title">Media Pencadangan</h3></div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('backup-media')"><Plus :size="17" /> Tambah Media</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Media</th><th>Lokasi</th><th>Jenis</th><th>Kapasitas</th><th>Address / URL</th><th>Job</th><th>Aksi</th></tr></thead>
              <tbody>
                <tr v-for="media in filteredBackupMedia" :key="media.id">
                  <td><strong>{{ media.nama }}</strong></td>
                  <td><span class="status">{{ media.location }}</span></td>
                  <td>{{ media.jenis_media }}</td>
                  <td>{{ media.kapasitas_gb || 0 }} GB</td>
                  <td>{{ media.address_url || '-' }}</td>
                  <td>{{ media.backup_jobs_count || 0 }}</td>
                  <td><div v-if="canWrite" class="row-actions"><button class="icon-button" title="Edit media" @click="openEdit('backup-media', media)"><Pencil :size="16" /></button><button v-if="canWrite" class="icon-button danger" title="Hapus media" @click="removeRow('backup-media', media.id)"><Trash2 :size="16" /></button></div></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'backup-jobs'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div><p class="eyebrow">Keamanan Informasi</p><h3 class="yellow-title">Pencadangan</h3></div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('backup-jobs')"><Plus :size="17" /> Tambah Pencadangan</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Aplikasi</th><th>Media</th><th>Retensi</th><th>Repetisi</th><th>Aksi</th></tr></thead>
              <tbody>
                <tr v-for="job in filteredBackupJobs" :key="job.id">
                  <td><strong>{{ job.aplikasi?.nama || '-' }}</strong></td>
                  <td>{{ job.media?.nama || '-' }}<span>{{ job.media?.jenis_media || '' }}</span></td>
                  <td>{{ job.retensi_n }} {{ job.retensi_unit }}</td>
                  <td>{{ job.repetisi_n }} {{ job.repetisi_unit }}</td>
                  <td><div v-if="canWrite" class="row-actions"><button class="icon-button" title="Edit pencadangan" @click="openEdit('backup-jobs', job)"><Pencil :size="16" /></button><button v-if="canWrite" class="icon-button danger" title="Hapus pencadangan" @click="removeRow('backup-jobs', job.id)"><Trash2 :size="16" /></button></div></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'ups-devices'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div><p class="eyebrow">Pusat Data / DC</p><h3 class="yellow-title">UPS / Power Backup</h3></div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('ups-devices')"><Plus :size="17" /> Tambah UPS</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Nama</th><th>Kapasitas</th><th>Kondisi</th><th>Lokasi DC</th><th>Aksi</th></tr></thead>
              <tbody>
                <tr v-for="ups in filteredUpsDevices" :key="ups.id">
                  <td><strong>{{ ups.nama }}</strong></td>
                  <td>{{ ups.kapasitas_va }} VA</td>
                  <td><span :class="statusClass(ups.kondisi)">{{ ups.kondisi }}</span></td>
                  <td>{{ ups.data_center?.nama || '-' }}<span>{{ ups.data_center?.lokasi || '' }}</span></td>
                  <td><div v-if="canWrite" class="row-actions"><button class="icon-button" title="Edit UPS" @click="openEdit('ups-devices', ups)"><Pencil :size="16" /></button><button v-if="canWrite" class="icon-button danger" title="Hapus UPS" @click="removeRow('ups-devices', ups.id)"><Trash2 :size="16" /></button></div></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'soc-tools'" class="content-grid">
        <section class="surface wide">
          <div class="module-header">
            <div><p class="eyebrow">Keamanan Informasi</p><h3 class="yellow-title">SOC Platform / Device / Tools</h3></div>
            <button v-if="canWrite" class="action-button" type="button" @click="openCreate('soc-tools')"><Plus :size="17" /> Tambah SOC Tool</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Nama</th><th>Jenis</th><th>Fungsi</th><th>Cakupan</th><th>Aksi</th></tr></thead>
              <tbody>
                <tr v-for="tool in filteredSocTools" :key="tool.id">
                  <td><strong>{{ tool.nama }}</strong></td>
                  <td><span class="status">{{ tool.jenis }}</span></td>
                  <td>{{ tool.deskripsi_fungsi || '-' }}</td>
                  <td>
                    <span>DC: {{ tool.data_centers?.length || 0 }}</span>
                    <span>Server: {{ tool.servers?.length || 0 }}</span>
                    <span>VM: {{ tool.vms?.length || 0 }}</span>
                    <span>Aplikasi: {{ tool.applications?.length || 0 }}</span>
                  </td>
                  <td><div v-if="canWrite" class="row-actions"><button class="icon-button" title="Edit SOC" @click="openEdit('soc-tools', tool)"><Pencil :size="16" /></button><button v-if="canWrite" class="icon-button danger" title="Hapus SOC" @click="removeRow('soc-tools', tool.id)"><Trash2 :size="16" /></button></div></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
        </section>

        <section v-if="activeTab === 'users'" class="content-grid">
          <section class="surface wide">
            <div class="module-header">
              <div><p class="eyebrow">Autentikasi</p><h3 class="yellow-title">Pengguna & Role</h3></div>
              <button v-if="canWrite" class="action-button" type="button" @click="openCreate('users')"><Plus :size="17" /> Tambah Pengguna</button>
            </div>
            <div class="table-wrap">
              <table>
                <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Login Terakhir</th><th>Aksi</th></tr></thead>
                <tbody>
                  <tr v-for="user in filteredUsers" :key="user.id">
                    <td><strong>{{ user.nama }}</strong></td>
                    <td>{{ user.email }}</td>
                    <td><span :class="statusClass(user.role)">{{ user.role === 'full' ? 'Full' : 'Read Only' }}</span></td>
                    <td><span :class="statusClass(user.status)">{{ user.status }}</span></td>
                    <td>{{ user.last_login_at ? new Date(user.last_login_at).toLocaleString('id-ID') : '-' }}</td>
                    <td>
                      <div v-if="canWrite" class="row-actions">
                        <button class="icon-button" title="Edit pengguna" @click="openEdit('users', user)"><Pencil :size="16" /></button>
                        <button v-if="currentUser.id !== user.id" class="icon-button danger" title="Hapus pengguna" @click="removeRow('users', user.id)"><Trash2 :size="16" /></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </section>

        <section v-if="activeTab === 'map'" class="content-grid">
        <section class="surface wide">
          <div class="section-heading">
            <div>
              <p class="eyebrow">Dependency Graph</p>
              <h3 class="yellow-title">Aplikasi â†’ VM â†’ Server â†’ IP</h3>
            </div>
            <GitBranch :size="30" />
          </div>
          <div class="dependency-list">
            <article v-for="app in dependencyMap" :key="app.id" class="dependency-card">
              <div class="dependency-head">
                <div>
                  <strong>{{ app.nama }}</strong>
                  <span>{{ app.opd || 'OPD belum dipilih' }}</span>
                </div>
                <span :class="statusClass(app.status)">{{ app.status }}</span>
              </div>
              <div class="dependency-flow">
                <div class="node app-node"><AppWindow :size="18" />{{ app.nama }}</div>
                <div v-for="vm in app.vms" :key="vm.id" class="node vm-node">
                  <Server :size="18" />{{ vm.nama }}
                  <small>{{ vm.server?.nama || 'host belum dipetakan' }}</small>
                </div>
                <div v-for="ip in app.ips" :key="ip.id" class="node ip-node">
                  <Network :size="18" />{{ ip.ip }}
                  <small>{{ ip.jenis }}</small>
                </div>
              </div>
            </article>
          </div>
        </section>

        <section class="surface wide">
          <div class="section-heading compact">
            <h3>Impact Analysis</h3>
            <AlertTriangle :size="24" />
          </div>
          <div class="impact-toolbar">
            <select v-model="selectedServerId" @change="loadImpact">
              <option v-for="server in references.servers" :key="server.id" :value="server.id">{{ server.nama }}</option>
            </select>
            <button class="action-button compact-button" type="button" @click="loadImpact">Analisis</button>
          </div>
          <div v-if="impact" class="impact-grid">
            <div class="impact-summary">
              <strong>{{ impact.server.nama }}</strong>
              <span>{{ impact.server.lokasi }}</span>
              <small>{{ impact.server.kapasitas }}</small>
            </div>
            <div class="impact-summary warning">
              <strong>{{ impact.summary.total_aplikasi }}</strong>
              <span>Aplikasi terdampak</span>
              <small>Risk level: {{ impact.summary.risk_level }}</small>
            </div>
            <article v-for="app in impact.applications" :key="app.id" class="mini-row">
              <div>
                <strong>{{ app.nama }}</strong>
                <span>{{ app.jalur }} / {{ app.opd || '-' }}</span>
              </div>
              <span :class="statusClass(app.status)">{{ app.status }}</span>
            </article>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'compliance'" class="content-grid">
        <div class="metrics-row">
          <article class="metric-card blue">
            <span>Total Aplikasi</span>
            <strong>{{ compliance?.summary.total || 0 }}</strong>
            <small>Objek PSE</small>
          </article>
          <article class="metric-card red">
            <span>Data Pribadi</span>
            <strong>{{ compliance?.summary.data_pribadi || 0 }}</strong>
            <small>Butuh kontrol ekstra</small>
          </article>
          <article class="metric-card yellow">
            <span>SLA Kritis</span>
            <strong>{{ compliance?.summary.sla_kritis || 0 }}</strong>
            <small>Minimal 99%</small>
          </article>
          <article class="metric-card cyan">
            <span>Tanpa Mapping</span>
            <strong>{{ compliance?.summary.tanpa_vm || 0 }}</strong>
            <small>Perlu dilengkapi</small>
          </article>
        </div>
        <section class="surface wide">
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Aplikasi</th>
                  <th>OPD</th>
                  <th>SLA</th>
                  <th>Kontrol</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in compliance?.items || []" :key="item.id">
                  <td><strong>{{ item.nama }}</strong><span>Data pribadi: {{ yesNo(item.data_pribadi) }}</span></td>
                  <td>{{ item.opd || '-' }}</td>
                  <td>{{ item.sla || 0 }}%</td>
                  <td>
                    <div class="control-dots">
                      <CheckCircle2 v-for="(ok, key) in item.kontrol" :key="key" :size="18" :class="{ ok, miss: !ok }" />
                    </div>
                  </td>
                  <td><span :class="statusClass(item.status)">{{ item.status }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
        <section class="surface wide">
          <div class="section-heading compact">
            <h3>Security Gap</h3>
            <ShieldCheck :size="24" />
          </div>
          <div class="priority-list">
            <article v-for="server in compliance?.security_gap.servers_without_tools || []" :key="server.id" class="mini-row">
              <div>
                <strong>{{ server.nama }}</strong>
                <span>Belum tercatat memiliki security tool</span>
              </div>
              <span :class="statusClass(server.status)">{{ server.status }}</span>
            </article>
          </div>
        </section>
      </section>

      <section v-if="activeTab === 'audit'" class="content-grid">
        <section class="surface wide">
          <div class="section-heading">
            <div>
              <p class="eyebrow">Audit-first</p>
              <h3 class="yellow-title">Riwayat Perubahan Aset</h3>
            </div>
            <Activity :size="30" />
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Waktu</th>
                  <th>Aset</th>
                  <th>Jenis</th>
                  <th>Alasan</th>
                  <th>Field Berubah</th>
                  <th>Operator</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="log in assetChangeLogs" :key="log.id">
                  <td>
                    <strong>{{ new Date(log.created_at).toLocaleDateString('id-ID') }}</strong>
                    <span>{{ new Date(log.created_at).toLocaleTimeString('id-ID') }}</span>
                  </td>
                  <td>
                    <strong>{{ log.asset_name }}</strong>
                    <span>{{ log.asset_type === 'server' ? 'Server' : 'VM / CT' }}</span>
                  </td>
                  <td><span :class="statusClass(log.change_type)">{{ log.change_type }}</span></td>
                  <td>{{ log.reason || '-' }}</td>
                  <td>
                    <div class="stack-list">
                      <span v-for="item in formatChangeFields(log.changed_fields)" :key="item">{{ item }}</span>
                    </div>
                  </td>
                  <td>
                    <strong>{{ log.changed_by || '-' }}</strong>
                    <span>{{ log.ip_address || '-' }}</span>
                  </td>
                </tr>
                <tr v-if="assetChangeLogs.length === 0">
                  <td colspan="6">Belum ada perubahan spesifikasi Server atau VM.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <section class="surface wide">
          <div class="section-heading">
            <div>
              <p class="eyebrow">Audit umum</p>
              <h3 class="yellow-title">Jejak Perubahan Data</h3>
            </div>
            <Activity :size="30" />
          </div>
          <div class="timeline">
            <article v-for="log in auditLog" :key="log.id" class="timeline-item">
              <span class="timeline-dot"></span>
              <div>
                <strong>{{ log.aksi }} / {{ log.tabel }}</strong>
                <span>{{ log.record_id || 'system' }}</span>
                <small>{{ new Date(log.created_at).toLocaleString('id-ID') }} Â· {{ log.ip_address }}</small>
              </div>
            </article>
          </div>
        </section>
      </section>

      <div v-if="modal.open" class="modal-backdrop" @click.self="closeModal">
        <form class="modal-card" @submit.prevent="saveModal">
          <header class="modal-header">
            <div>
              <p class="eyebrow">{{ modal.mode === 'edit' ? 'Edit Data' : 'Tambah Data' }}</p>
              <h3>{{ activeModuleLabel }}</h3>
            </div>
            <button class="icon-button" type="button" title="Tutup form" @click="closeModal"><X :size="18" /></button>
          </header>

          <div v-if="modal.module === 'data-centers'" class="modal-form">
            <input v-model="dataCenterForm.nama" required placeholder="Nama data center" />
            <input v-model="dataCenterForm.lokasi" placeholder="Lokasi" />
            <select v-model="dataCenterForm.tipe" required>
              <option value="utama">Utama</option>
              <option value="dr">Disaster Recovery</option>
              <option value="cloud">Cloud</option>
            </select>
          </div>

          <div v-if="modal.module === 'racks'" class="modal-form">
            <input v-model="rackForm.nama" required placeholder="Nama rack" />
            <select v-model="rackForm.dc_id" required>
              <option value="">Data center</option>
              <option v-for="dc in references.data_centers" :key="dc.id" :value="dc.id">{{ dc.nama }}</option>
            </select>
            <input v-model.number="rackForm.kapasitas_u" required type="number" min="1" max="60" placeholder="Kapasitas U" />
          </div>

          <div v-if="modal.module === 'servers'" class="modal-form">
            <input v-model="serverForm.nama" required placeholder="Nama server" />
            <div class="two-col">
              <select v-model="serverForm.dc_id">
                <option value="">Data center</option>
                <option v-for="dc in references.data_centers" :key="dc.id" :value="dc.id">{{ dc.nama }}</option>
              </select>
              <select v-model="serverForm.rack_id">
                <option value="">Rack</option>
                <option v-for="rack in references.racks" :key="rack.id" :value="rack.id">{{ rack.nama }}</option>
              </select>
            </div>
            <div class="two-col">
              <input v-model="serverForm.merk" placeholder="Merk" />
              <input v-model="serverForm.tipe" placeholder="Tipe" />
            </div>
            <div class="three-col">
              <input v-model.number="serverForm.cpu_core" type="number" min="1" placeholder="Core" />
              <input v-model.number="serverForm.ram_gb" type="number" min="1" placeholder="RAM GB" />
              <input v-model.number="serverForm.storage_gb" type="number" min="1" placeholder="Storage GB" />
            </div>
            <div class="two-col">
              <select v-model="serverForm.kondisi">
                <option value="baik">Baik</option>
                <option value="rusak">Rusak</option>
              </select>
              <select v-model="serverForm.status">
                <option value="aktif">Aktif</option>
                <option value="maintenance">Maintenance</option>
                <option value="nonaktif">Nonaktif</option>
              </select>
            </div>
            <input v-model="serverForm.penanggung_jawab" placeholder="Penanggung jawab" />
            <div v-if="modal.mode === 'edit'" class="two-col">
              <input v-model="serverForm.changed_by" placeholder="Operator / pelaksana perubahan" />
              <input v-model="serverForm.change_reason" placeholder="Alasan perubahan" />
            </div>
          </div>

          <div v-if="modal.module === 'vms'" class="modal-form">
            <input v-model="vmForm.nama" required placeholder="Nama VM" />
            <select v-model="vmForm.server_id">
              <option value="">Host server</option>
              <option v-for="server in references.servers" :key="server.id" :value="server.id">{{ server.nama }}</option>
            </select>
            <input v-model="vmForm.os" placeholder="Operating system" />
            <div class="three-col">
              <input v-model.number="vmForm.vcpu" type="number" min="1" placeholder="vCPU" />
              <input v-model.number="vmForm.ram_gb" type="number" min="1" placeholder="RAM GB" />
              <input v-model.number="vmForm.storage_gb" type="number" min="1" placeholder="Storage GB" />
            </div>
            <select v-model="vmForm.status">
              <option value="running">Running</option>
              <option value="stopped">Stopped</option>
              <option value="suspended">Suspended</option>
              <option value="maintenance">Maintenance</option>
            </select>
            <div class="inline-picker">
              <strong>IP Address</strong>
              <button
                v-for="ip in references.ips"
                :key="ip.id"
                class="chip"
                :class="{ selected: vmForm.ip_ids.includes(ip.id) }"
                type="button"
                @click="toggleInArray(vmForm.ip_ids, ip.id)"
              >
                {{ ip.ip }}
              </button>
            </div>
            <div v-if="modal.mode === 'edit'" class="two-col">
              <input v-model="vmForm.changed_by" placeholder="Operator / pelaksana perubahan" />
              <input v-model="vmForm.change_reason" placeholder="Alasan perubahan" />
            </div>
          </div>

          <div v-if="modal.module === 'ip-addresses'" class="modal-form">
            <input v-model="ipAddressForm.ip" required placeholder="Alamat IP" />
            <div class="two-col">
              <select v-model="ipAddressForm.jenis" required>
                <option value="private">Private</option>
                <option value="publik">Publik</option>
              </select>
              <select v-model="ipAddressForm.isp_id">
                <option value="">Tanpa ISP</option>
                <option v-for="isp in references.isps" :key="isp.id" :value="isp.id">{{ isp.nama }}</option>
              </select>
            </div>
          </div>

          <div v-if="modal.module === 'isps'" class="modal-form">
            <input v-model="ispForm.nama" required placeholder="Nama ISP" />
            <div class="two-col">
              <input v-model="ispForm.tipe" placeholder="Tipe koneksi" />
              <input v-model="ispForm.bandwidth" placeholder="Bandwidth" />
            </div>
            <input v-model="ispForm.kontak" placeholder="Kontak NOC / PIC" />
          </div>

          <div v-if="modal.module === 'applications'" class="modal-form">
            <input v-model="appForm.nama" required placeholder="Nama aplikasi" />
            <input v-model="appForm.url" type="url" placeholder="URL aplikasi" />
            <input v-model="appForm.tech_stack" placeholder="Tech Stack, contoh: Laravel, Vue, MySQL" />
            <select v-model="appForm.opd_id">
              <option value="">OPD pemilik</option>
              <option v-for="opd in references.opd" :key="opd.id" :value="opd.id">{{ opd.nama }}</option>
            </select>
            <div class="three-col">
              <select v-model="appForm.jenis_aplikasi" required>
                <option value="web">Web</option>
                <option value="mobile">Mobile</option>
                <option value="desktop">Desktop</option>
                <option value="service">Service</option>
                <option value="lainnya">Lainnya</option>
              </select>
              <select v-model="appForm.status">
                <option value="aktif">Aktif</option>
                <option value="maintenance">Maintenance</option>
                <option value="nonaktif">Nonaktif</option>
              </select>
              <input v-model.number="appForm.sla_persen" type="number" min="0" max="100" step="0.01" placeholder="SLA %" />
            </div>
            <div class="inline-picker">
              <strong>Klasifikasi Fungsi</strong>
              <button
                v-for="option in functionClassificationOptions"
                :key="option.value"
                class="chip"
                :class="{ selected: appForm.klasifikasi_fungsi.includes(option.value) }"
                type="button"
                @click="toggleInArray(appForm.klasifikasi_fungsi, option.value)"
              >
                {{ option.label }}
              </button>
            </div>
            <div class="two-col">
              <select v-model="appForm.kategori_data">
                <option value="publik">Publik</option>
                <option value="terbatas">Terbatas</option>
                <option value="rahasia">Rahasia</option>
              </select>
              <input v-model="appForm.pic_nama" placeholder="PIC" />
            </div>
            <div class="two-col">
              <input v-model="appForm.pic_kontak" placeholder="Kontak PIC" />
              <input v-model="appForm.lokasi_data" placeholder="Lokasi data" />
            </div>
            <textarea v-model="appForm.risiko" placeholder="Risiko utama"></textarea>
            <div class="picker-grid">
              <div>
                <strong>VM</strong>
                <button
                  v-for="vm in references.vms"
                  :key="vm.id"
                  class="chip"
                  :class="{ selected: appForm.vm_ids.includes(vm.id) }"
                  type="button"
                  @click="toggleInArray(appForm.vm_ids, vm.id)"
                >
                  {{ vm.nama }}
                </button>
              </div>
              <div>
                <strong>Server</strong>
                <button
                  v-for="server in references.servers"
                  :key="server.id"
                  class="chip"
                  :class="{ selected: appForm.server_ids.includes(server.id) }"
                  type="button"
                  @click="toggleInArray(appForm.server_ids, server.id)"
                >
                  {{ server.nama }}
                </button>
              </div>
              <div>
                <strong>IP Address</strong>
                <button
                  v-for="ip in references.ips"
                  :key="ip.id"
                  class="chip"
                  :class="{ selected: appForm.ip_ids.includes(ip.id) }"
                  type="button"
                  @click="toggleInArray(appForm.ip_ids, ip.id)"
                >
                  {{ ip.ip }}
                </button>
              </div>
            </div>
          </div>

          <div v-if="modal.module === 'data-assets'" class="modal-form">
            <div class="risk-result">
              <div>
                <span>Nilai total</span>
                <strong>{{ dataAssetRiskTotal }}</strong>
              </div>
              <div>
                <span>Klasifikasi otomatis</span>
                <strong>{{ dataAssetCalculatedClassification.name }}</strong>
                <small>{{ dataAssetCalculatedClassification.risk }} Â· {{ dataAssetCalculatedClassification.color }}</small>
              </div>
            </div>
            <div class="two-col">
              <select v-model="dataAssetForm.aplikasi_id" required>
                <option value="">Aplikasi</option>
                <option v-for="app in applications" :key="app.id" :value="app.id">{{ app.nama }}</option>
              </select>
              <input v-model="dataAssetForm.owner_agency" placeholder="K/L/D pemilik data" />
            </div>
            <div class="two-col">
              <input v-model="dataAssetForm.name" required placeholder="Nama aset data, contoh: users.email" />
              <select v-model="dataAssetForm.type" required>
                <option value="TABLE">Table</option>
                <option value="COLUMN">Column</option>
                <option value="API">API</option>
                <option value="FILE">File</option>
                <option value="FORM">Form</option>
                <option value="DATASET">Dataset</option>
              </select>
            </div>
            <textarea v-model="dataAssetForm.attributes" placeholder="Atribut data sebagai justifikasi, satu per baris"></textarea>
            <div class="three-col">
              <select v-model.number="dataAssetForm.confidentiality_score" required>
                <option :value="1">Kerahasiaan: Rendah (1)</option>
                <option :value="3">Kerahasiaan: Sedang (3)</option>
                <option :value="5">Kerahasiaan: Tinggi (5)</option>
              </select>
              <select v-model.number="dataAssetForm.integrity_score" required>
                <option :value="1">Integritas: Rendah (1)</option>
                <option :value="3">Integritas: Sedang (3)</option>
                <option :value="5">Integritas: Tinggi (5)</option>
              </select>
              <select v-model.number="dataAssetForm.availability_score" required>
                <option :value="1">Ketersediaan: Rendah (1)</option>
                <option :value="3">Ketersediaan: Sedang (3)</option>
                <option :value="5">Ketersediaan: Tinggi (5)</option>
              </select>
            </div>
            <div class="two-col">
              <input v-model="dataAssetForm.table_name" placeholder="Nama tabel" />
              <input v-model="dataAssetForm.column_name" placeholder="Nama kolom" />
            </div>
            <label class="toggle">
              <input v-model="dataAssetForm.contains_personal_data" type="checkbox" />
              <span>Mengandung data pribadi</span>
            </label>
            <div class="two-col">
              <input v-model="dataAssetForm.personal_data_type" placeholder="Jenis data pribadi" />
              <input v-model="dataAssetForm.processing_purpose" placeholder="Tujuan pemrosesan" />
            </div>
            <div class="two-col">
              <input v-model="dataAssetForm.retention_period" placeholder="Retensi data" />
              <input v-model="dataAssetForm.storage_location" placeholder="Lokasi penyimpanan" />
            </div>
            <input v-model="dataAssetForm.data_owner" placeholder="Pemilik data" />
            <textarea v-model="dataAssetForm.access_policy" placeholder="Kebijakan akses"></textarea>
            <textarea v-model="dataAssetForm.description" placeholder="Deskripsi tambahan"></textarea>
          </div>

          <div v-if="modal.module === 'application-documents'" class="modal-form">
            <select v-model="applicationDocumentForm.aplikasi_id" required>
              <option value="">Aplikasi</option>
              <option v-for="app in applications" :key="app.id" :value="app.id">{{ app.nama }}</option>
            </select>
            <select v-model="applicationDocumentForm.document_category" required>
              <option value="petunjuk_teknis">Dok. Petunjuk Teknis</option>
              <option value="tata_kelola">Dok. Tatakelola</option>
              <option value="keamanan">Dok. Keamanan</option>
            </select>
            <input v-if="modal.mode === 'create'" type="file" multiple @change="setFiles(applicationDocumentForm, $event)" />
          </div>

          <div v-if="modal.module === 'app-integrations'" class="modal-form">
            <select v-model="appIntegrationForm.aplikasi_id" required>
              <option value="">Aplikasi</option>
              <option v-for="app in applications" :key="app.id" :value="app.id">{{ app.nama }}</option>
            </select>
            <textarea v-model="appIntegrationForm.deskripsi" placeholder="Deskripsi integrasi"></textarea>
            <div class="two-col">
              <select v-model="appIntegrationForm.jenis_integrasi" required>
                <option value="proses_bisnis">Proses Bisnis</option>
                <option value="berbagi_data">Berbagi Data</option>
              </select>
              <select v-model="appIntegrationForm.metode_integrasi" required>
                <option value="spl">SPL</option>
                <option value="host_to_host">Host-to-Host</option>
              </select>
            </div>
            <div class="inline-picker">
              <strong>Endpoint Target</strong>
              <button v-for="app in applications" :key="app.id" class="chip" :class="{ selected: appIntegrationForm.target_application_ids.includes(app.id) }" type="button" @click="toggleInArray(appIntegrationForm.target_application_ids, app.id)">
                {{ app.nama }}
              </button>
            </div>
            <textarea v-model="appIntegrationForm.external_endpoints" placeholder="Endpoint lainnya / eksternal"></textarea>
            <div class="inline-picker">
              <strong>Data yang Dibagipakai</strong>
              <button v-for="asset in dataAssets" :key="asset.id" class="chip" :class="{ selected: appIntegrationForm.data_asset_ids.includes(asset.id) }" type="button" @click="toggleInArray(appIntegrationForm.data_asset_ids, asset.id)">
                {{ asset.name }}
              </button>
            </div>
            <input type="file" multiple @change="setFiles(appIntegrationForm, $event)" />
          </div>

          <div v-if="modal.module === 'backup-media'" class="modal-form">
            <input v-model="backupMediaForm.nama" required placeholder="Nama media" />
            <div class="two-col">
              <select v-model="backupMediaForm.location" required>
                <option value="local">Local</option>
                <option value="remote">Remote</option>
              </select>
              <select v-model="backupMediaForm.jenis_media" required>
                <option value="NAS">NAS</option>
                <option value="Disk">Disk</option>
                <option value="Cloud">Cloud</option>
                <option value="Replication">Replication</option>
                <option value="Tape">Tape</option>
                <option value="Object Storage">Object Storage</option>
              </select>
            </div>
            <div class="two-col">
              <input v-model.number="backupMediaForm.kapasitas_gb" type="number" min="1" placeholder="Kapasitas GB" />
              <input v-model="backupMediaForm.address_url" placeholder="Address / URL" />
            </div>
          </div>

          <div v-if="modal.module === 'backup-jobs'" class="modal-form">
            <div class="two-col">
              <select v-model="backupJobForm.aplikasi_id" required>
                <option value="">Aplikasi</option>
                <option v-for="app in applications" :key="app.id" :value="app.id">{{ app.nama }}</option>
              </select>
              <select v-model="backupJobForm.backup_media_id" required>
                <option value="">Media Pencadangan</option>
                <option v-for="media in backupMedia" :key="media.id" :value="media.id">{{ media.nama }}</option>
              </select>
            </div>
            <div class="two-col">
              <input v-model.number="backupJobForm.retensi_n" type="number" min="1" placeholder="n Retensi" />
              <select v-model="backupJobForm.retensi_unit">
                <option value="realtime">Realtime</option>
                <option value="menit">Menit</option>
                <option value="jam">Jam</option>
                <option value="hari">Hari</option>
                <option value="minggu">Minggu</option>
                <option value="bulan">Bulan</option>
              </select>
            </div>
            <div class="two-col">
              <input v-model.number="backupJobForm.repetisi_n" type="number" min="1" placeholder="n Repetisi" />
              <select v-model="backupJobForm.repetisi_unit">
                <option value="realtime">Realtime</option>
                <option value="menit">Menit</option>
                <option value="jam">Jam</option>
                <option value="hari">Hari</option>
                <option value="minggu">Minggu</option>
                <option value="bulan">Bulan</option>
              </select>
            </div>
          </div>

          <div v-if="modal.module === 'ups-devices'" class="modal-form">
            <input v-model="upsDeviceForm.nama" required placeholder="Nama UPS / Power Backup" />
            <div class="two-col">
              <input v-model.number="upsDeviceForm.kapasitas_va" required type="number" min="1" placeholder="Kapasitas VA" />
              <select v-model="upsDeviceForm.kondisi" required>
                <option value="baik">Baik</option>
                <option value="kurang_baik">Kurang Baik</option>
                <option value="rusak">Rusak</option>
              </select>
            </div>
            <select v-model="upsDeviceForm.dc_id">
              <option value="">Gedung / Ruang DC</option>
              <option v-for="dc in references.data_centers" :key="dc.id" :value="dc.id">{{ dc.nama }}</option>
            </select>
          </div>

          <div v-if="modal.module === 'soc-tools'" class="modal-form">
            <input v-model="socToolForm.nama" required placeholder="Nama Platform / Device / Tools" />
            <textarea v-model="socToolForm.deskripsi_fungsi" placeholder="Deskripsi fungsi"></textarea>
            <select v-model="socToolForm.jenis" required>
              <option value="Firewall">Firewall</option>
              <option value="IDS">IDS</option>
              <option value="IPS">IPS</option>
              <option value="Antivirus">Antivirus</option>
              <option value="EDR">EDR</option>
              <option value="SIEM">SIEM</option>
              <option value="WAF">WAF</option>
              <option value="NDR">NDR</option>
              <option value="Vulnerability Scanner">Vulnerability Scanner</option>
              <option value="Log Management">Log Management</option>
            </select>
            <div class="inline-picker">
              <strong>Coverage Data Center</strong>
              <button v-for="dc in references.data_centers" :key="dc.id" class="chip" :class="{ selected: socToolForm.dc_ids.includes(dc.id) }" type="button" @click="toggleInArray(socToolForm.dc_ids, dc.id)">
                {{ dc.nama }}
              </button>
            </div>
            <div class="inline-picker">
              <strong>Coverage Server</strong>
              <button v-for="server in references.servers" :key="server.id" class="chip" :class="{ selected: socToolForm.server_ids.includes(server.id) }" type="button" @click="toggleInArray(socToolForm.server_ids, server.id)">
                {{ server.nama }}
              </button>
            </div>
            <div class="inline-picker">
              <strong>Coverage VM</strong>
              <button v-for="vm in references.vms" :key="vm.id" class="chip" :class="{ selected: socToolForm.vm_ids.includes(vm.id) }" type="button" @click="toggleInArray(socToolForm.vm_ids, vm.id)">
                {{ vm.nama }}
              </button>
            </div>
            <div class="inline-picker">
              <strong>Coverage Aplikasi</strong>
              <button v-for="app in applications" :key="app.id" class="chip" :class="{ selected: socToolForm.application_ids.includes(app.id) }" type="button" @click="toggleInArray(socToolForm.application_ids, app.id)">
                {{ app.nama }}
              </button>
            </div>
          </div>

          <div v-if="modal.module === 'users'" class="modal-form">
            <input v-model="userForm.nama" required placeholder="Nama pengguna" />
            <input v-model="userForm.email" required type="email" placeholder="Email" />
            <input v-model="userForm.password" :required="modal.mode === 'create'" type="password" :placeholder="modal.mode === 'create' ? 'Password' : 'Password baru (opsional)'" />
            <select v-model="userForm.opd_id">
              <option value="">OPD</option>
              <option v-for="opd in references.opd" :key="opd.id" :value="opd.id">{{ opd.nama }}</option>
            </select>
            <div class="two-col">
              <select v-model="userForm.role" required>
                <option value="full">Full</option>
                <option value="read_only">Read Only</option>
              </select>
              <select v-model="userForm.status" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
              </select>
            </div>
          </div>

          <footer class="modal-actions">
            <button class="action-button ghost" type="button" @click="closeModal">Batal</button>
            <button class="action-button" type="submit">{{ modal.mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Data' }}</button>
          </footer>
        </form>
      </div>

      <div v-if="alertModal.open" class="modal-backdrop alert-backdrop" @click.self="closeAlert">
        <section class="modal-card alert-modal-card">
          <header class="modal-header">
            <div>
              <p class="eyebrow">Perhatian</p>
              <h3>{{ alertModal.title }}</h3>
            </div>
            <AlertTriangle :size="28" />
          </header>
          <p class="alert-modal-message">{{ alertModal.message }}</p>
          <footer class="modal-actions">
            <button class="action-button" type="button" @click="closeAlert">Mengerti</button>
          </footer>
        </section>
      </div>
    </main>
  </div>
</template>

