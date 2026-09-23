<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { LMap, LTileLayer, LMarker, LPopup, LIcon } from '@vue-leaflet/vue-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import 'leaflet.markercluster';

const props = defineProps({
    sarpras: { type: Array, default: () => [] },
    klubs: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    cabors: { type: Array, default: () => [] },
    kecamatans: { type: Array, default: () => [] },
    bbox: { type: Object, default: () => ({ center: { lat: 0.917, lng: 104.45 }, zoom_default: 12, tile_url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', attribution: '&copy; OpenStreetMap contributors' }) },
    can: { type: Object, default: () => ({}) },
});

// filter states synced to url via router.get preserveState
const search = ref(props.filters?.search ?? '');
const cabor_id = ref(props.filters?.cabor_id ?? '');
const kecamatan_id = ref(props.filters?.kecamatan_id ?? '');
const kondisi = ref(props.filters?.kondisi ?? '');

let debounceTimer = null;
function applyFilters() {
    const params = {};
    if (search.value) params.search = search.value;
    if (cabor_id.value) params.cabor_id = cabor_id.value;
    if (kecamatan_id.value) params.kecamatan_id = kecamatan_id.value;
    if (kondisi.value) params.kondisi = kondisi.value;
    router.get(route('gis.index'), params, { preserveState: true, replace: true, preserveScroll: true });
}

watch([cabor_id, kecamatan_id, kondisi], () => {
    applyFilters();
});

// search debounced
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 400);
});

function clearFilters() {
    search.value = '';
    cabor_id.value = '';
    kecamatan_id.value = '';
    kondisi.value = '';
    // apply will trigger via watch, but force immediate
    router.get(route('gis.index'), {}, { preserveState: true, replace: true });
}

// tabs for left panel
const activeTab = ref('sarpras'); // sarpras | klub

// map refs
const mapRef = ref(null);
const zoom = computed(() => props.bbox?.zoom_default ?? 12);
const center = computed(() => {
    const c = props.bbox?.center ?? { lat: 0.917, lng: 104.45 };
    return [Number(c.lat), Number(c.lng)];
});
const tileUrl = computed(() => props.bbox?.tile_url ?? 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
const attribution = computed(() => props.bbox?.attribution ?? '&copy; OpenStreetMap contributors');

onMounted(() => {
    // Fix default leaflet icon urls (avoid 404 for marker-icon.png)
    // We use divIcon exclusively, but fix for any fallback LMarker defaults
    try {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });
    } catch (e) {}
});

// icon helpers
function sarprasIcon(s) {
    const k = s.kondisi ?? 'baik';
    let bg = '#16a34a'; // green
    let border = '#15803d';
    if (k === 'rusak_ringan') { bg = '#eab308'; border = '#ca8a04'; }
    else if (k === 'rusak_berat') { bg = '#dc2626'; border = '#b91c1c'; }
    const html = `
        <div style="background:${bg};border:2px solid ${border};width:34px;height:42px;border-radius:17px 17px 17px 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.3);">
            <span style="transform:rotate(45deg);font-size:16px;line-height:1;">🏟️</span>
        </div>
    `;
    return L.divIcon({ className: 'sindora-sarpras-icon', html, iconSize: [34, 42], iconAnchor: [17, 42], popupAnchor: [0, -42] });
}

function klubIcon() {
    const bg = '#2563eb';
    const border = '#1e40af';
    const html = `
        <div style="background:${bg};border:2px solid ${border};width:34px;height:42px;border-radius:17px 17px 17px 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.3);">
            <span style="transform:rotate(45deg);font-size:16px;line-height:1;">🛡️</span>
        </div>
    `;
    return L.divIcon({ className: 'sindora-klub-icon', html, iconSize: [34, 42], iconAnchor: [17, 42], popupAnchor: [0, -42] });
}

function kondisiBadgeClasses(kondisiVal) {
    if (kondisiVal === 'baik') return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200';
    if (kondisiVal === 'rusak_ringan') return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200';
    if (kondisiVal === 'rusak_berat') return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200';
    return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200';
}

function fasilitasList(fasilitas) {
    if (!fasilitas) return [];
    if (Array.isArray(fasilitas)) return fasilitas;
    if (typeof fasilitas === 'string') {
        try { const p = JSON.parse(fasilitas); return Array.isArray(p) ? p : [fasilitas]; } catch { return [fasilitas]; }
    }
    if (typeof fasilitas === 'object') return Object.values(fasilitas);
    return [];
}

// map fly to entity
function flyTo(lat, lng) {
    try {
        // mapRef.value is LMap component; need leafletObject
        const leafletMap = mapRef.value?.leafletObject ?? mapRef.value?.$leafletObject ?? null;
        if (leafletMap && typeof leafletMap.flyTo === 'function') {
            leafletMap.flyTo([Number(lat), Number(lng)], Math.max(zoom.value, 15), { duration: 0.8 });
        }
    } catch (e) {}
}

const sarprasCount = computed(() => props.sarpras?.length ?? 0);
const klubCount = computed(() => props.klubs?.length ?? 0);
const hasAnyData = computed(() => sarprasCount.value > 0 || klubCount.value > 0);

// For note about clustering
const showClusterNote = computed(() => sarprasCount.value + klubCount.value > 50);
</script>

<template>
    <Head title="Peta Olahraga" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Peta Olahraga</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Sebaran Sarpras & Klub Kota Tanjungpinang — bbox [104.30, 0.85, 104.55, 1.05] · center 0.917, 104.45
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:ring-blue-800">
                        {{ sarprasCount }} sarpras · {{ klubCount }} klub
                    </span>
                    <span v-if="!hasAnyData" class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-200">Belum ada koordinat</span>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filter bar -->
                <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                    <div class="relative">
                        <input
                            v-model="search"
                            placeholder="Cari nama / alamat / jenis / ketua..."
                            class="w-64 rounded-lg border-gray-300 pl-9 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                        />
                        <svg class="pointer-events-none absolute left-2.5 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10 18a8 8 0 110-16 8 8 0 010 16z"/></svg>
                    </div>

                    <select v-model="cabor_id" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        <option value="">Semua Cabor</option>
                        <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                    </select>

                    <select v-model="kecamatan_id" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        <option value="">Semua Kecamatan</option>
                        <option v-for="k in kecamatans" :key="k.id" :value="k.id">{{ k.nama }}</option>
                    </select>

                    <select v-model="kondisi" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        <option value="">Semua Kondisi (Sarpras)</option>
                        <option value="baik">Baik</option>
                        <option value="rusak_ringan">Rusak Ringan</option>
                        <option value="rusak_berat">Rusak Berat</option>
                    </select>

                    <button @click="clearFilters" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        Reset
                    </button>

                    <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                        Filter: <span class="font-semibold">{{ sarprasCount + klubCount }}</span> titik di peta
                        <span v-if="showClusterNote" class="ml-2 rounded bg-amber-100 px-2 py-0.5 text-[11px] text-amber-800">cluster via markercluster jika &gt;50 — aktif di Sprint 8.7</span>
                    </span>
                </div>

                <!-- Main flex: list 1/3, map 2/3 -->
                <div class="flex flex-col gap-4 lg:flex-row">
                    <!-- Left: list with tabs -->
                    <div class="flex w-full flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700 lg:w-1/3">
                        <!-- Tabs -->
                        <div class="flex border-b border-gray-200 dark:border-gray-700">
                            <button
                                @click="activeTab = 'sarpras'"
                                :class="['flex-1 px-4 py-3 text-sm font-semibold', activeTab === 'sarpras' ? 'border-b-2 border-[#1e3a8a] bg-blue-50/50 text-[#1e3a8a] dark:bg-blue-900/20 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700/50']"
                            >
                                Sarpras <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-normal dark:bg-gray-700">{{ sarprasCount }}</span>
                            </button>
                            <button
                                @click="activeTab = 'klub'"
                                :class="['flex-1 px-4 py-3 text-sm font-semibold', activeTab === 'klub' ? 'border-b-2 border-[#1e3a8a] bg-blue-50/50 text-[#1e3a8a] dark:bg-blue-900/20 dark:text-blue-200' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700/50']"
                            >
                                Klub <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-normal dark:bg-gray-700">{{ klubCount }}</span>
                            </button>
                        </div>

                        <!-- Scrollable list -->
                        <div class="max-h-[600px] flex-1 overflow-y-auto">
                            <!-- Sarpras list -->
                            <div v-if="activeTab === 'sarpras'" class="divide-y divide-gray-100 dark:divide-gray-700">
                                <div v-if="sarpras.length === 0" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <p class="font-medium">Tidak ada Sarpras dengan koordinat.</p>
                                    <p class="mt-1 text-xs">Coba ubah filter atau tambah data Sarpras dengan latitude/longitude.</p>
                                    <Link v-if="can?.manage_sarpras" :href="route('sarpras.index')" class="mt-3 inline-flex rounded-lg bg-[#1e3a8a] px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-800">Kelola Sarpras →</Link>
                                </div>
                                <div
                                    v-for="s in sarpras"
                                    :key="s.id"
                                    class="group flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/40"
                                >
                                    <img
                                        v-if="s.foto_url"
                                        :src="s.foto_url"
                                        :alt="s.nama"
                                        class="h-12 w-12 shrink-0 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-600"
                                    />
                                    <div v-else class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400 dark:bg-gray-700">—</div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ s.nama }}</p>
                                            <span :class="['inline-flex shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium', kondisiBadgeClasses(s.kondisi)]">{{ s.kondisi_label ?? s.kondisi ?? '-' }}</span>
                                        </div>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ s.alamat ?? '—' }} <span v-if="s.kelurahan">· {{ s.kelurahan.nama }}</span><span v-if="s.kecamatan"> — {{ s.kecamatan.nama }}</span></p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            <span v-if="s.cabor">Cabor: {{ s.cabor.nama }}</span>
                                            <span v-if="s.kapasitas"> · Kapasitas: {{ s.kapasitas }}</span>
                                            <span v-if="s.fasilitas && fasilitasList(s.fasilitas).length"> · Fasilitas: {{ fasilitasList(s.fasilitas).slice(0,2).join(', ') }}</span>
                                        </p>
                                        <p v-if="s.latitude && s.longitude" class="mt-0.5 text-[11px] font-mono text-gray-400">{{ Number(s.latitude).toFixed(5) }}, {{ Number(s.longitude).toFixed(5) }}</p>
                                        <div class="mt-2 flex gap-2">
                                            <button @click="flyTo(s.latitude ?? s.lat, s.longitude ?? s.lng)" class="rounded bg-[#1e3a8a] px-2 py-1 text-xs font-medium text-white hover:bg-blue-800 dark:bg-blue-700">Lihat di peta</button>
                                            <Link :href="route('sarpras.show', s.id)" class="rounded border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Detail</Link>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Klub list -->
                            <div v-if="activeTab === 'klub'" class="divide-y divide-gray-100 dark:divide-gray-700">
                                <div v-if="klubs.length === 0" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <p class="font-medium">Tidak ada Klub dengan koordinat.</p>
                                    <p class="mt-1 text-xs">Coba ubah filter atau tambah data Klub dengan latitude/longitude.</p>
                                    <Link v-if="can?.manage_klub" :href="route('klubs.index')" class="mt-3 inline-flex rounded-lg bg-[#1e3a8a] px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-800">Kelola Klub →</Link>
                                </div>
                                <div
                                    v-for="k in klubs"
                                    :key="k.id"
                                    class="group flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/40"
                                >
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/20 dark:text-blue-200 dark:ring-blue-800">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 4v5c0 4-2.8 7-7 9-4.2-2-7-5-7-9V7l7-4z"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ k.nama }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                            <span v-if="k.cabor">{{ k.cabor.nama }}</span>
                                            <span v-if="k.kelurahan"> · {{ k.kelurahan.nama }}</span>
                                            <span v-if="k.kecamatan"> — {{ k.kecamatan.nama }}</span>
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            <span v-if="k.ketua">Ketua: {{ k.ketua }}</span>
                                            <span v-if="k.atlets_count !== undefined"> · Atlet: {{ k.atlets_count ?? 0 }}</span>
                                        </p>
                                        <p v-if="k.kontak?.hp || k.kontak_hp" class="text-xs text-gray-500 dark:text-gray-400">HP: {{ k.kontak?.hp ?? k.kontak_hp }}<span v-if="k.kontak?.email || k.kontak_email"> · {{ k.kontak?.email ?? k.kontak_email }}</span></p>
                                        <p v-if="k.latitude && k.longitude" class="mt-0.5 text-[11px] font-mono text-gray-400">{{ Number(k.latitude).toFixed(5) }}, {{ Number(k.longitude).toFixed(5) }}</p>
                                        <div class="mt-2 flex gap-2">
                                            <button @click="flyTo(k.latitude ?? k.lat, k.longitude ?? k.lng)" class="rounded bg-[#1e3a8a] px-2 py-1 text-xs font-medium text-white hover:bg-blue-800 dark:bg-blue-700">Lihat di peta</button>
                                            <Link :href="route('klubs.show', k.id)" class="rounded border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Detail</Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer hint -->
                        <div class="border-t border-gray-200 bg-gray-50 px-4 py-2 text-[11px] text-gray-500 dark:border-gray-700 dark:bg-gray-700/30 dark:text-gray-400">
                            List tersinkron dengan filter & marker peta. Klik “Lihat di peta” untuk fokus.
                            <span v-if="showClusterNote" class="ml-1">Marker >50 akan di-cluster (MarkerCluster).</span>
                        </div>
                    </div>

                    <!-- Right: Map -->
                    <div class="w-full overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700 lg:w-2/3">
                        <div class="relative">
                            <LMap
                                ref="mapRef"
                                :zoom="zoom"
                                :center="center"
                                :min-zoom="bbox?.zoom_min ?? 10"
                                :max-zoom="bbox?.zoom_max ?? 17"
                                :use-global-leaflet="false"
                                style="height:600px; width:100%; z-index:0;"
                            >
                                <LTileLayer :url="tileUrl" :attribution="attribution" />

                                <!-- Sarpras markers -->
                                <LMarker
                                    v-for="s in sarpras"
                                    :key="'s-'+s.id"
                                    :lat-lng="[Number(s.latitude ?? s.lat), Number(s.longitude ?? s.lng)]"
                                    :icon="sarprasIcon(s)"
                                >
                                    <LPopup :options="{ maxWidth: 320, className: 'sindora-popup' }">
                                        <div class="min-w-[220px] max-w-[300px]">
                                            <div class="flex gap-2">
                                                <img v-if="s.foto_url" :src="s.foto_url" :alt="s.nama" class="h-14 w-14 shrink-0 rounded object-cover ring-1 ring-gray-200" />
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-bold leading-tight text-gray-900 dark:text-gray-900">{{ s.nama }}</p>
                                                    <p class="text-xs text-gray-600">{{ s.jenis ?? '—' }}<span v-if="s.cabor"> · {{ s.cabor.nama }}</span></p>
                                                    <span :class="['mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-medium', kondisiBadgeClasses(s.kondisi)]">{{ s.kondisi_label ?? s.kondisi }}</span>
                                                </div>
                                            </div>
                                            <p v-if="s.alamat" class="mt-2 text-xs leading-snug text-gray-700">{{ s.alamat }}<span v-if="s.kelurahan"> — {{ s.kelurahan.nama }}</span><span v-if="s.kecamatan">, {{ s.kecamatan.nama }}</span></p>
                                            <p v-if="s.kapasitas" class="mt-1 text-xs text-gray-600">Kapasitas: <span class="font-semibold">{{ s.kapasitas }}</span></p>
                                            <p v-if="s.fasilitas && fasilitasList(s.fasilitas).length" class="mt-1 text-xs text-gray-600">Fasilitas: {{ fasilitasList(s.fasilitas).join(', ') }}</p>
                                            <p class="mt-1 font-mono text-[11px] text-gray-500">{{ Number(s.latitude ?? s.lat).toFixed(6) }}, {{ Number(s.longitude ?? s.lng).toFixed(6) }}</p>
                                            <div class="mt-2 flex gap-2">
                                                <a :href="route('sarpras.show', s.id)" class="inline-flex rounded bg-[#1e3a8a] px-2.5 py-1 text-xs font-semibold text-white hover:bg-blue-800">Detail Sarpras</a>
                                                <a :href="`https://www.google.com/maps?q=${s.latitude ?? s.lat},${s.longitude ?? s.lng}`" target="_blank" rel="noopener" class="inline-flex rounded border border-gray-300 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">Google Maps</a>
                                            </div>
                                        </div>
                                    </LPopup>
                                </LMarker>

                                <!-- Klub markers -->
                                <LMarker
                                    v-for="k in klubs"
                                    :key="'k-'+k.id"
                                    :lat-lng="[Number(k.latitude ?? k.lat), Number(k.longitude ?? k.lng)]"
                                    :icon="klubIcon()"
                                >
                                    <LPopup :options="{ maxWidth: 300 }">
                                        <div class="min-w-[220px] max-w-[300px]">
                                            <p class="text-sm font-bold leading-tight text-gray-900">{{ k.nama }}</p>
                                            <p class="text-xs text-gray-600"><span v-if="k.cabor">{{ k.cabor.nama }}</span><span v-if="k.cabor?.kode"> ({{ k.cabor.kode }})</span></p>
                                            <p class="mt-1 text-xs text-gray-700">
                                                <span v-if="k.kelurahan">{{ k.kelurahan.nama }}</span><span v-if="k.kecamatan"> — {{ k.kecamatan.nama }}</span>
                                                <span v-if="k.alamat"> · {{ k.alamat }}</span>
                                            </p>
                                            <p class="mt-1 text-xs text-gray-600">Atlet: <span class="font-semibold">{{ k.atlets_count ?? 0 }}</span><span v-if="k.ketua"> · Ketua: {{ k.ketua }}</span></p>
                                            <p v-if="k.kontak?.hp || k.kontak_hp" class="text-xs text-gray-600">Kontak: {{ k.kontak?.hp ?? k.kontak_hp }}<span v-if="k.kontak?.email || k.kontak_email"> · {{ k.kontak?.email ?? k.kontak_email }}</span></p>
                                            <p class="mt-1 font-mono text-[11px] text-gray-500">{{ Number(k.latitude ?? k.lat).toFixed(6) }}, {{ Number(k.longitude ?? k.lng).toFixed(6) }}</p>
                                            <div class="mt-2 flex gap-2">
                                                <a :href="route('klubs.show', k.id)" class="inline-flex rounded bg-[#1e3a8a] px-2.5 py-1 text-xs font-semibold text-white hover:bg-blue-800">Detail Klub</a>
                                                <a :href="`https://www.google.com/maps?q=${k.latitude ?? k.lat},${k.longitude ?? k.lng}`" target="_blank" rel="noopener" class="inline-flex rounded border border-gray-300 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">Google Maps</a>
                                            </div>
                                        </div>
                                    </LPopup>
                                </LMarker>
                            </LMap>

                            <!-- Legend overlay -->
                            <div class="absolute bottom-3 left-3 z-[400] rounded-lg border border-gray-200 bg-white/95 p-3 text-xs shadow-lg backdrop-blur dark:border-gray-600 dark:bg-gray-800/95 dark:text-gray-200">
                                <p class="mb-2 font-semibold text-gray-800 dark:text-gray-100">Legenda</p>
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-3 w-3 rounded-full bg-green-500 ring-1 ring-green-700"></span> Sarpras — Baik
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-3 w-3 rounded-full bg-yellow-400 ring-1 ring-yellow-600"></span> Sarpras — Rusak Ringan
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-3 w-3 rounded-full bg-red-500 ring-1 ring-red-700"></span> Sarpras — Rusak Berat
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-3 w-3 rounded-full bg-blue-600 ring-1 ring-blue-800"></span> Klub
                                    </div>
                                </div>
                                <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">Bbox: {{ bbox?.wgs84?.join(', ') ?? '104.30, 0.85, 104.55, 1.05' }}</p>
                            </div>

                            <!-- Empty overlay hint -->
                            <div v-if="!hasAnyData" class="pointer-events-none absolute inset-0 z-[300] flex items-center justify-center bg-white/60 backdrop-blur-sm dark:bg-gray-900/40">
                                <div class="pointer-events-auto rounded-xl border border-amber-200 bg-amber-50 px-6 py-4 text-center shadow dark:border-amber-800 dark:bg-amber-900/30">
                                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">Belum ada data spasial</p>
                                    <p class="mt-1 max-w-sm text-xs text-amber-800 dark:text-amber-200">Saat DB memiliki 0 sarpras/klub berkoordinat, peta tetap tampil di center Tanjungpinang (0.917, 104.45) — zoom {{ zoom }}. Tambahkan latitude/longitude pada Sarpras/Klub untuk muncul di peta & list.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Map footer meta + DOD placeholder -->
                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-200 bg-gray-50 px-4 py-2 text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-700/30 dark:text-gray-300">
                            <span>Leaflet {{ '1.9.4' }} · vue-leaflet 0.10.1 · OSM · Tanjungpinang center</span>
                            <span class="rounded bg-white px-2 py-0.5 text-[11px] font-medium text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-600">DOD overlay (8.7) — placeholder</span>
                        </div>
                    </div>
                </div>

                <!-- Bottom placeholder for DOD -->
                <div class="mt-4 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-center text-xs text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                    Placeholder Sprint 8.7: Overlay DOD / densitas atlet & heatmap akan ditambahkan di sini tanpa mengubah struktur Gis/Index.vue saat ini.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Ensure leaflet container respects dark ring */
:deep(.leaflet-popup-content-wrapper) {
    border-radius: 0.75rem;
}
:deep(.leaflet-popup-content) {
    margin: 12px 16px;
    line-height: 1.4;
}
</style>
