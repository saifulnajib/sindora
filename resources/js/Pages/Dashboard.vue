<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const roles = computed(() => user.value?.roles ?? []);
const can = computed(() => page.props.auth?.can ?? {});

const props = defineProps({
    widgets: { type: Object, default: () => ({}) },
    pendingVerifikasi: { type: Object, default: () => ({}) },
    trenMedali: { type: Array, default: () => [] },
    rasioPelatihAtlet: { type: Array, default: () => [] },
    distribusiKecamatan: { type: Array, default: () => [] },
    distribusiCabor: { type: Array, default: () => [] },
    caborUnggulan: { type: Array, default: () => [] },
    lisensi: { type: Object, default: () => ({ counts: {}, kritis: [], expired: [], peringatan: [], top_urgent: [] }) },
});

const w = computed(() => props.widgets || {});
const pending = computed(() => props.pendingVerifikasi || {});

const tren = computed(() => props.trenMedali || []);
const trenMax = computed(() => Math.max(1, ...tren.value.map((t) => t.total || 0)));

const rasio = computed(() => props.rasioPelatihAtlet || []);
const distKec = computed(() => props.distribusiKecamatan || []);
const distCabor = computed(() => props.distribusiCabor || []);
const unggulan = computed(() => props.caborUnggulan || []);
const lisensi = computed(() => props.lisensi || { counts: {}, kritis: [], expired: [], peringatan: [] });

const maxKec = computed(() => Math.max(1, ...distKec.value.map((d) => d.count || 0)));
const maxCabor = computed(() => Math.max(1, ...distCabor.value.map((d) => d.count || 0)));

const sortUnggulanAsc = ref(false);
const sortedUnggulan = computed(() => {
    const arr = [...unggulan.value];
    arr.sort((a, b) => sortUnggulanAsc.value ? a.score - b.score : b.score - a.score);
    return arr;
});

function toggleSortUnggulan() {
    sortUnggulanAsc.value = !sortUnggulanAsc.value;
}

const widgetCards = computed(() => [
    { label: 'Klub', value: w.value.klub ?? 0, href: route('klubs.index'), color: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200', icon: 'K' },
    { label: 'Atlet', value: w.value.atlet ?? 0, href: route('atlets.index'), color: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200', icon: 'A' },
    { label: 'Pelatih', value: w.value.pelatih ?? 0, href: route('sdms.index'), color: 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200', icon: 'P' },
    { label: 'Wasit', value: w.value.wasit ?? 0, href: route('sdms.index'), color: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-200', icon: 'W' },
    { label: 'Tenaga', value: w.value.tenaga ?? 0, href: route('sdms.index'), color: 'bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-200', icon: 'T' },
    { label: 'Sarpras', value: w.value.sarpras ?? 0, href: route('sarpras.index'), color: 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-200', icon: 'S' },
    { label: 'Kejuaraan', value: w.value.kejuaraan ?? 0, href: route('kejuaraans.index'), color: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200', icon: 'Kj' },
    { label: 'Prestasi', value: w.value.prestasi ?? 0, href: route('prestasis.index'), color: 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200', icon: 'Pr' },
]);

const pendingTotal = computed(() => pending.value.total ?? 0);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Dashboard
                </h2>
                <div class="flex items-center gap-2">
                    <span v-if="pendingTotal > 0" class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:ring-amber-800">
                        <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                        {{ pendingTotal }} menunggu verifikasi
                    </span>
                    <Link :href="route('verifikasi.queue')" class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">
                        Verifikasi →
                    </Link>
                    <span class="hidden sm:inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:ring-blue-800">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Sprint 6 — Dashboard & Analitik
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Welcome -->
                <div class="sindora-card">
                    <div class="bg-gradient-to-r from-[#1e3a8a] to-blue-600 px-6 py-5 text-white">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-blue-100">Selamat datang,</p>
                                <h3 class="mt-1 text-2xl font-bold tracking-tight">{{ user?.name ?? 'Tamu' }}</h3>
                                <p class="mt-1 text-sm text-blue-100">{{ user?.email }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-for="r in roles"
                                        :key="r"
                                        class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-[#1e3a8a] shadow-sm"
                                    >
                                        {{ r.replaceAll('_', ' ') }}
                                    </span>
                                    <span v-if="roles.length===0" class="text-xs text-blue-200">no role</span>
                                </div>
                            </div>
                            <div class="rounded-xl bg-white/10 px-4 py-3 text-right backdrop-blur ring-1 ring-white/20">
                                <p class="text-xs uppercase tracking-widest text-blue-100">Kota</p>
                                <p class="text-sm font-semibold">Tanjungpinang</p>
                                <p class="text-xs text-blue-200">Kepulauan Riau</p>
                                <p class="mt-2 text-[11px] text-blue-200">4 Kecamatan · 18 Kelurahan</p>
                                <p v-if="w.pembinaan !== undefined" class="mt-1 text-[11px] text-blue-100">{{ w.pembinaan }} pembinaan · {{ w.cabor ?? '-' }} cabor</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6.1 Widgets Ringkasan -->
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Ringkasan (terverifikasi)</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Cache 60 dtk · pending <span class="font-semibold text-amber-600">{{ pendingTotal }}</span></span>
                    </div>
                    <div class="mt-3 grid gap-3 grid-cols-2 md:grid-cols-4">
                        <Link
                            v-for="card in widgetCards"
                            :key="card.label"
                            :href="card.href"
                            class="group rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div class="flex items-center justify-between">
                                <span :class="['inline-flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold', card.color]">{{ card.icon }}</span>
                                <span class="text-[11px] font-medium text-gray-400 group-hover:text-blue-600">Lihat →</span>
                            </div>
                            <p class="mt-3 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                            <p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white">{{ card.value }}</p>
                            <p v-if="pending[card.label.toLowerCase()] !== undefined" class="mt-1 text-xs">
                                <span v-if="pending[card.label.toLowerCase()] > 0" class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-900/30 dark:text-amber-200">{{ pending[card.label.toLowerCase()] }} menunggu</span>
                                <span v-else class="text-gray-400">aman</span>
                            </p>
                            <p v-else-if="card.label==='Pelatih' && pending.sdm" class="mt-1 text-xs text-gray-400">sdm: {{ pending.sdm }} menunggu</p>
                        </Link>
                    </div>
                    <!-- extra row for Pembinaan if exists -->
                    <div v-if="w.pembinaan !== undefined" class="mt-3 grid gap-3 grid-cols-2 md:grid-cols-4">
                        <Link :href="route('pembinaans.index')" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Pembinaan</p>
                            <p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white">{{ w.pembinaan }}</p>
                            <p class="mt-1 text-xs">
                                <span v-if="(pending.pembinaan ?? 0) > 0" class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800">{{ pending.pembinaan }} menunggu</span>
                                <span v-else class="text-gray-400">terverifikasi</span>
                            </p>
                        </Link>
                        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700/40">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Pending</p>
                            <p class="mt-1 text-2xl font-extrabold text-amber-600">{{ pendingTotal }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">butuh verifikasi</p>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- 6.2 Tren Medali -->
                    <div class="sindora-card p-5">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Tren Medali (6.2)</h4>
                            <span class="text-xs text-gray-500 dark:text-gray-400">per tahun · terverifikasi</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Stacked bar: emas / perak / perunggu</p>
                        <div v-if="tren.length===0" class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-700/30 dark:text-gray-400">
                            Belum ada data prestasi terverifikasi.
                        </div>
                        <div v-else class="mt-4 space-y-3">
                            <div v-for="row in tren" :key="row.tahun" class="space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-gray-700 dark:text-gray-200">{{ row.tahun }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">total {{ row.total }} <span class="hidden sm:inline">· emas {{ row.emas }} · perak {{ row.perak }} · perunggu {{ row.perunggu }}</span></span>
                                </div>
                                <div class="flex h-6 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700 ring-1 ring-gray-200 dark:ring-gray-600">
                                    <div
                                        v-if="row.emas>0"
                                        class="flex items-center justify-center bg-yellow-400 text-[11px] font-bold text-yellow-900 transition-all"
                                        :style="{ width: (row.emas/ trenMax *100) + '%' }"
                                        :title="`Emas ${row.emas}`"
                                    >
                                        <span v-if="row.emas/trenMax > 0.12">{{ row.emas }}</span>
                                    </div>
                                    <div
                                        v-if="row.perak>0"
                                        class="flex items-center justify-center bg-gray-300 text-[11px] font-bold text-gray-800 transition-all dark:bg-gray-400"
                                        :style="{ width: (row.perak/ trenMax *100) + '%' }"
                                        :title="`Perak ${row.perak}`"
                                    >
                                        <span v-if="row.perak/trenMax > 0.12">{{ row.perak }}</span>
                                    </div>
                                    <div
                                        v-if="row.perunggu>0"
                                        class="flex items-center justify-center bg-amber-600 text-[11px] font-bold text-white transition-all"
                                        :style="{ width: (row.perunggu/ trenMax *100) + '%' }"
                                        :title="`Perunggu ${row.perunggu}`"
                                    >
                                        <span v-if="row.perunggu/trenMax > 0.12">{{ row.perunggu }}</span>
                                    </div>
                                    <div v-if="row.total===0" class="flex-1"></div>
                                </div>
                                <div class="flex gap-3 text-[11px]">
                                    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-yellow-400"></span> Emas {{ row.emas }}</span>
                                    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-gray-300"></span> Perak {{ row.perak }}</span>
                                    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-600"></span> Perunggu {{ row.perunggu }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6.3 Rasio Pelatih:Atlet -->
                    <div class="sindora-card p-5">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Rasio Pelatih:Atlet (6.3)</h4>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Per Cabor · terverifikasi</p>
                        <div v-if="rasio.length===0" class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-700/30">Tidak ada data cabor.</div>
                        <div v-else class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                                    <tr>
                                        <th class="pb-2 pr-2">Cabor</th>
                                        <th class="pb-2 text-center">Atlet</th>
                                        <th class="pb-2 text-center">Pelatih</th>
                                        <th class="pb-2 text-center">Rasio</th>
                                        <th class="pb-2 text-right">%</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="r in rasio" :key="r.cabor_id" class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                        <td class="py-2 pr-2 font-medium text-gray-800 dark:text-gray-200">{{ r.cabor_nama }}</td>
                                        <td class="py-2 text-center text-gray-700 dark:text-gray-300">{{ r.atlet_count }}</td>
                                        <td class="py-2 text-center text-gray-700 dark:text-gray-300">{{ r.pelatih_count }}</td>
                                        <td class="py-2 text-center">
                                            <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-semibold', r.pelatih_count===0 ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200' : r.atlet_count===0 ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' : 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200']">
                                                {{ r.ratio_label }}
                                            </span>
                                        </td>
                                        <td class="py-2 text-right text-xs text-gray-500 dark:text-gray-400">{{ r.ratio_percent !== null ? r.ratio_percent + '%' : '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 6.4 Distribusi -->
                    <div class="sindora-card p-5">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Distribusi Atlet (6.4)</h4>
                        <div class="mt-4 space-y-6">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Per Kecamatan</p>
                                <div v-if="distKec.length===0" class="mt-2 text-xs text-gray-500">Belum ada data.</div>
                                <div v-else class="mt-3 space-y-2">
                                    <div v-for="d in distKec" :key="d.nama" class="space-y-1">
                                        <div class="flex justify-between text-xs">
                                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ d.nama }}</span>
                                            <span class="text-gray-500 dark:text-gray-400">{{ d.count }}</span>
                                        </div>
                                        <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                                            <div class="h-2 rounded-full bg-[#1e3a8a] transition-all" :style="{ width: (d.count / maxKec * 100) + '%' }"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Per Cabor</p>
                                <div v-if="distCabor.length===0" class="mt-2 text-xs text-gray-500">Belum ada data.</div>
                                <div v-else class="mt-3 space-y-2">
                                    <div v-for="d in distCabor" :key="d.nama" class="space-y-1">
                                        <div class="flex justify-between text-xs">
                                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ d.nama }}</span>
                                            <span class="text-gray-500 dark:text-gray-400">{{ d.count }}</span>
                                        </div>
                                        <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                                            <div class="h-2 rounded-full bg-emerald-500 transition-all" :style="{ width: (d.count / maxCabor * 100) + '%' }"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6.6 Lisensi Kadaluarsa -->
                    <div class="sindora-card p-5">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Lisensi Kadaluarsa (6.6)</h4>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Threshold: aman &gt;90, peringatan 31-90, kritis 1-30, expired ≤0</p>
                        <div class="mt-4 grid grid-cols-4 gap-2 text-center">
                            <div class="rounded-lg bg-green-50 px-2 py-3 dark:bg-green-900/20">
                                <p class="text-lg font-extrabold text-green-700 dark:text-green-300">{{ lisensi.counts?.aman ?? 0 }}</p>
                                <p class="text-[11px] font-medium uppercase tracking-wide text-green-700 dark:text-green-300">Aman</p>
                            </div>
                            <div class="rounded-lg bg-yellow-50 px-2 py-3 dark:bg-yellow-900/20">
                                <p class="text-lg font-extrabold text-yellow-700 dark:text-yellow-300">{{ lisensi.counts?.peringatan ?? 0 }}</p>
                                <p class="text-[11px] font-medium uppercase tracking-wide text-yellow-700 dark:text-yellow-300">Peringatan</p>
                            </div>
                            <div class="rounded-lg bg-orange-50 px-2 py-3 dark:bg-orange-900/20">
                                <p class="text-lg font-extrabold text-orange-700 dark:text-orange-300">{{ lisensi.counts?.kritis ?? 0 }}</p>
                                <p class="text-[11px] font-medium uppercase tracking-wide text-orange-700 dark:text-orange-300">Kritis</p>
                            </div>
                            <div class="rounded-lg bg-red-50 px-2 py-3 dark:bg-red-900/20">
                                <p class="text-lg font-extrabold text-red-700 dark:text-red-300">{{ lisensi.counts?.expired ?? 0 }}</p>
                                <p class="text-[11px] font-medium uppercase tracking-wide text-red-700 dark:text-red-300">Expired</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-4">
                            <div v-if="(lisensi.expired||[]).length>0">
                                <p class="text-xs font-semibold text-red-700 dark:text-red-300">Kadaluarsa (max 5)</p>
                                <ul class="mt-2 space-y-2">
                                    <li v-for="s in lisensi.expired" :key="s.id" class="flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-3 py-2 dark:border-red-800 dark:bg-red-900/20">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ s.nama }} <span class="text-xs text-gray-500">· {{ s.tipe }}</span></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ s.cabor ?? '-' }} · {{ s.expired_at }} · {{ s.days_until_expired }} hari</p>
                                        </div>
                                        <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-semibold', s.badge]">{{ s.label }}</span>
                                    </li>
                                </ul>
                            </div>
                            <div v-if="(lisensi.kritis||[]).length>0">
                                <p class="text-xs font-semibold text-orange-700 dark:text-orange-300">Kritis — Segera Perpanjang (max 5)</p>
                                <ul class="mt-2 space-y-2">
                                    <li v-for="s in lisensi.kritis" :key="s.id" class="flex items-center justify-between rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 dark:border-orange-800 dark:bg-orange-900/20">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ s.nama }} <span class="text-xs text-gray-500">· {{ s.tipe }}</span></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ s.cabor ?? '-' }} · {{ s.expired_at }} · H-{{ s.days_until_expired }}</p>
                                        </div>
                                        <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-semibold', s.badge]">{{ s.label }}</span>
                                    </li>
                                </ul>
                            </div>
                            <div v-if="(lisensi.peringatan||[]).length>0">
                                <p class="text-xs font-semibold text-yellow-700 dark:text-yellow-300">Perlu Perpanjangan (max 5)</p>
                                <ul class="mt-2 space-y-2">
                                    <li v-for="s in lisensi.peringatan" :key="s.id" class="flex items-center justify-between rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2 dark:border-yellow-800 dark:bg-yellow-900/20">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ s.nama }} <span class="text-xs text-gray-500">· {{ s.tipe }}</span></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ s.cabor ?? '-' }} · {{ s.expired_at }} · H-{{ s.days_until_expired }}</p>
                                        </div>
                                        <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-semibold', s.badge]">{{ s.label }}</span>
                                    </li>
                                </ul>
                            </div>
                            <div v-if="(lisensi.kritis||[]).length===0 && (lisensi.expired||[]).length===0 && (lisensi.peringatan||[]).length===0" class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-center text-xs text-gray-500 dark:border-gray-600 dark:bg-gray-700/30">
                                Tidak ada lisensi kritis / kadaluarsa.
                            </div>
                            <Link :href="route('sdms.index')" class="inline-flex text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-300">Lihat semua SDM →</Link>
                        </div>
                    </div>
                </div>

                <!-- 6.5 Cabor Unggulan -->
                <div class="sindora-card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Cabor Unggulan (6.5)</h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Skor = nasional×40 + provinsi×25 + atlet×20 + sarpras×10 + sdm_sertifikat×5</p>
                        </div>
                        <button @click="toggleSortUnggulan" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                            Sort: {{ sortUnggulanAsc ? 'Terendah ↑' : 'Tertinggi ↓' }}
                        </button>
                    </div>
                    <div v-if="sortedUnggulan.length===0" class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-700/30">Belum ada data cabor.</div>
                    <div v-else class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="pb-2">#</th>
                                    <th class="pb-2">Cabor</th>
                                    <th class="pb-2 text-center">Skor</th>
                                    <th class="pb-2 text-center">Nasional</th>
                                    <th class="pb-2 text-center">Provinsi</th>
                                    <th class="pb-2 text-center">Atlet</th>
                                    <th class="pb-2 text-center">Sarpras</th>
                                    <th class="pb-2 text-center">SDM Sert.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="(c, idx) in sortedUnggulan" :key="c.cabor_id" class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="py-2.5 text-gray-500 dark:text-gray-400">
                                        <span v-if="idx===0" class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 text-xs font-bold text-yellow-900">1</span>
                                        <span v-else-if="idx===1" class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-300 text-xs font-bold text-gray-800">2</span>
                                        <span v-else-if="idx===2" class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-600 text-xs font-bold text-white">3</span>
                                        <span v-else class="px-2 text-xs font-medium">{{ idx+1 }}</span>
                                    </td>
                                    <td class="py-2.5">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ c.cabor_nama }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ c.cabor_kode }}</p>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <span class="inline-flex rounded-full bg-[#1e3a8a] px-2.5 py-1 text-xs font-bold text-white">{{ c.score }}</span>
                                    </td>
                                    <td class="py-2.5 text-center text-gray-700 dark:text-gray-300">{{ c.breakdown.prestasi_nasional }}</td>
                                    <td class="py-2.5 text-center text-gray-700 dark:text-gray-300">{{ c.breakdown.prestasi_provinsi }}</td>
                                    <td class="py-2.5 text-center text-gray-700 dark:text-gray-300">{{ c.breakdown.pembinaan_aktif }}</td>
                                    <td class="py-2.5 text-center text-gray-700 dark:text-gray-300">{{ c.breakdown.sarpras_dukung }}</td>
                                    <td class="py-2.5 text-center text-gray-700 dark:text-gray-300">{{ c.breakdown.sdm_bersertifikat }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick links -->
                <div class="sindora-card p-5">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Quick Links</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Akses cepat sesuai role kamu.</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <Link :href="route('cabors.index')" class="group rounded-xl border border-gray-200 bg-white p-4 transition hover:border-blue-200 hover:bg-blue-50/60 hover:shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                            <p class="text-sm font-semibold text-gray-900 group-hover:text-[#1e3a8a] dark:text-white">Master Data</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cabor · Klub · Atlet · SDM</p>
                            <p class="mt-2 text-xs font-medium text-blue-600">Buka →</p>
                        </Link>
                        <Link v-if="can.view_verifikasi || roles.includes('super_admin') || roles.includes('verifikator')" :href="route('verifikasi.queue')" class="group rounded-xl border border-amber-200 bg-amber-50/60 p-4 transition hover:shadow-sm dark:border-amber-800 dark:bg-amber-900/20">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Verifikasi</p>
                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Antrian persetujuan · {{ pendingTotal }} pending</p>
                            <p class="mt-2 text-xs font-medium text-amber-700">Review →</p>
                        </Link>
                        <div v-else class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4 opacity-60 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Verifikasi</p>
                            <p class="mt-1 text-xs text-gray-400">Butuh role verifikator / super_admin</p>
                        </div>
                        <Link :href="route('kejuaraans.index')" class="group rounded-xl border border-gray-200 bg-white p-4 transition hover:border-blue-200 hover:bg-blue-50/60 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Kejuaraan & Prestasi</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Jadwal & medali</p>
                            <p class="mt-2 text-xs font-medium text-blue-600">Lihat →</p>
                        </Link>
                        <Link :href="route('sarpras.index')" class="group rounded-xl border border-gray-200 bg-white p-4 transition hover:border-blue-200 hover:bg-blue-50/60 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">GIS Peta</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sarpras & sebaran atlet</p>
                            <p class="mt-2 text-xs font-medium text-blue-600">Buka peta →</p>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
