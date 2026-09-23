<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? null);

const props = defineProps({
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: false },
    stats: {
        type: Object,
        default: () => ({
            atlet_total: 0,
            atlet_putra: 0,
            atlet_putri: 0,
            pelatih_total: 0,
            pelatih_bersertifikat: 0,
            prestasi_total: 0,
            medali_emas: 0,
            medali_perak: 0,
            medali_perunggu: 0,
            cabor_total: 0,
        }),
    },
    caborStats: { type: Array, default: () => [] },
    trenMedali: { type: Array, default: () => [] },
    prestasiByTingkat: { type: Array, default: () => [] },
    recentPrestasis: { type: Array, default: () => [] },
    atlets: { type: Array, default: () => [] },
    pelatihs: { type: Array, default: () => [] },
    caborsList: { type: Array, default: () => [] },
});

// Active tab for explorer
const activeTab = ref('prestasi'); // 'prestasi' | 'atlet' | 'pelatih' | 'cabor'

// Search & Cabor filter state
const searchQuery = ref('');
const selectedCabor = ref('');

// Computed filtered Prestasi
const filteredPrestasi = computed(() => {
    return props.recentPrestasis.filter((item) => {
        const matchCabor = !selectedCabor.value || item.cabor_nama === selectedCabor.value;
        const q = searchQuery.value.toLowerCase();
        const matchQuery =
            !q ||
            item.atlet_nama.toLowerCase().includes(q) ||
            item.kejuaraan_nama.toLowerCase().includes(q) ||
            item.cabor_nama.toLowerCase().includes(q) ||
            (item.kategori_kelas && item.kategori_kelas.toLowerCase().includes(q));
        return matchCabor && matchQuery;
    });
});

// Computed filtered Atlets
const filteredAtlets = computed(() => {
    return props.atlets.filter((item) => {
        const matchCabor = !selectedCabor.value || item.cabor_nama === selectedCabor.value;
        const q = searchQuery.value.toLowerCase();
        const matchQuery =
            !q ||
            item.nama.toLowerCase().includes(q) ||
            item.cabor_nama.toLowerCase().includes(q) ||
            item.klub_nama.toLowerCase().includes(q) ||
            item.kecamatan_nama.toLowerCase().includes(q);
        return matchCabor && matchQuery;
    });
});

// Computed filtered Pelatihs
const filteredPelatihs = computed(() => {
    return props.pelatihs.filter((item) => {
        const matchCabor = !selectedCabor.value || item.cabor_nama === selectedCabor.value;
        const q = searchQuery.value.toLowerCase();
        const matchQuery =
            !q ||
            item.nama.toLowerCase().includes(q) ||
            item.cabor_nama.toLowerCase().includes(q) ||
            (item.level && item.level.toLowerCase().includes(q)) ||
            (item.spesialisasi && item.spesialisasi.toLowerCase().includes(q));
        return matchCabor && matchQuery;
    });
});

// Computed filtered Cabor Stats
const filteredCaborStats = computed(() => {
    return props.caborStats.filter((item) => {
        const q = searchQuery.value.toLowerCase();
        const matchQuery = !q || item.nama.toLowerCase().includes(q) || (item.kode && item.kode.toLowerCase().includes(q));
        const matchCabor = !selectedCabor.value || item.nama === selectedCabor.value;
        return matchQuery && matchCabor;
    });
});

// Max value for tren medali chart calculation
const maxTrenMedali = computed(() => {
    if (!props.trenMedali.length) return 1;
    return Math.max(1, ...props.trenMedali.map((t) => t.total || 0));
});

// Max value for tingkat chart calculation
const maxTingkatMedali = computed(() => {
    if (!props.prestasiByTingkat.length) return 1;
    return Math.max(1, ...props.prestasiByTingkat.map((t) => t.total || 0));
});

// Helper for medal colors & icons
function getMedaliBadge(medali) {
    const m = (medali || '').toLowerCase();
    if (m === 'emas') {
        return {
            bg: 'bg-amber-100 text-amber-900 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700/60',
            icon: '🥇',
            glow: 'shadow-amber-500/20',
            label: 'Medali Emas',
        };
    }
    if (m === 'perak') {
        return {
            bg: 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800/80 dark:text-slate-200 dark:border-slate-600',
            icon: '🥈',
            glow: 'shadow-slate-400/20',
            label: 'Medali Perak',
        };
    }
    if (m === 'perunggu') {
        return {
            bg: 'bg-orange-100 text-orange-900 border-orange-300 dark:bg-orange-950/60 dark:text-orange-300 dark:border-orange-700/60',
            icon: '🥉',
            glow: 'shadow-orange-500/20',
            label: 'Medali Perunggu',
        };
    }
    return {
        bg: 'bg-blue-100 text-blue-900 border-blue-300 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-700/60',
        icon: '🏅',
        glow: 'shadow-blue-500/20',
        label: medali ? medali.toUpperCase() : 'Prestasi',
    };
}
</script>

<template>
    <Head title="SINDORA — Dinas Pemuda dan Olahraga Kota Tanjungpinang" />

    <div class="min-h-screen bg-slate-50 text-slate-900 selection:bg-blue-600 selection:text-white dark:bg-slate-950 dark:text-slate-100">
        <!-- TOP NAVIGATION BAR -->
        <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/90">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3.5 sm:px-6 lg:px-8">
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-600 shadow-md shadow-blue-500/25">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871a2.25 2.25 0 0 1-1.591-.659L12 11.25m0 0l-1.914 1.916a2.25 2.25 0 0 1-1.591.659h-.87C7.003 13.825 6.5 14.329 6.5 14.95v3.8m5.5-7.5V3.75m0 0a3 3 0 0 1 3 3v.75m-3-3.75a3 3 0 0 0-3 3v.75m6 0a3 3 0 0 1 3 3v.75a3 3 0 0 1-3 3h-.75m-6-7.5a3 3 0 0 0-3 3v.75a3 3 0 0 0 3 3h.75" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-black tracking-tight text-blue-900 dark:text-white">SINDORA</span>
                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-800 dark:bg-blue-950 dark:text-blue-300">DISPORA TANJUNGPINANG</span>
                        </div>
                        <p class="hidden text-xs text-slate-500 sm:block dark:text-slate-400">Dinas Pemuda dan Olahraga Kota Tanjungpinang</p>
                    </div>
                </div>

                <!-- Nav links & CTA -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <nav class="hidden items-center gap-6 md:flex">
                        <a href="#statistik" class="text-sm font-medium text-slate-600 transition hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400">Statistik</a>
                        <a href="#explorer" class="text-sm font-medium text-slate-600 transition hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400">Data Publik</a>
                    </nav>

                    <div class="h-5 w-px bg-slate-200 dark:bg-slate-800 hidden sm:block"></div>
                </div>
            </div>
        </header>

        <!-- HERO SECTION -->
        <section class="relative overflow-hidden bg-gradient-to-b from-blue-900 via-blue-950 to-slate-950 py-16 text-white sm:py-24">
            <!-- Decorative background glows -->
            <div class="pointer-events-none absolute -top-40 right-1/4 h-96 w-96 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-0 left-10 h-80 w-80 rounded-full bg-amber-500/10 blur-3xl"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(#ffffff0a_1px,transparent_1px)] [background-size:24px_24px]"></div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-400/30 bg-blue-800/40 px-3.5 py-1 text-xs font-semibold text-blue-200 backdrop-blur-md">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Dinas Pemuda dan Olahraga Pemerintah Kota Tanjungpinang
                    </div>

                    <h1 class="mt-6 text-3xl font-black tracking-tight sm:text-5xl lg:text-6xl text-white">
                        Pusat Data & Prestasi <br />
                        <span class="bg-gradient-to-r from-blue-300 via-amber-300 to-amber-400 bg-clip-text text-transparent">Keolahragaan Kota Tanjungpinang</span>
                    </h1>

                    <p class="mt-5 text-base leading-relaxed text-blue-100/90 sm:text-lg">
                        Sistem Informasi Database Olahraga (SINDORA) menyajikan data resmi atlit binaan, pelatih bersertifikasi, serta capaian medali kejuaraan di bawah naungan Dinas Pemuda dan Olahraga Pemerintah Kota Tanjungpinang.
                    </p>

                    <!-- Action buttons -->
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                        <a
                            href="#explorer"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 px-6 py-3.5 text-sm font-bold text-slate-950 shadow-lg shadow-amber-500/25 transition hover:brightness-105 active:scale-95"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                            <span>Eksplorasi Data Atlet & Prestasi</span>
                        </a>
                        <a
                            href="#statistik"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur-md transition hover:bg-white/20 active:scale-95"
                        >
                            <span>Lihat Statistik & Tren</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- KEY METRIC CARDS (HERO HIGHLIGHTS) -->
                <div class="mt-14 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- CARD 1: ATLIT -->
                    <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-b from-white/10 to-white/5 p-6 backdrop-blur-md transition hover:-translate-y-1 hover:border-blue-400/40 hover:shadow-xl hover:shadow-blue-500/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-blue-200">Total Atlit Terbina</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/20 text-blue-300">
                                🏃
                            </div>
                        </div>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span class="text-4xl font-black text-white">{{ stats.atlet_total.toLocaleString() }}</span>
                            <span class="text-xs text-blue-200 font-medium">Orang</span>
                        </div>
                        <div class="mt-4 flex items-center gap-3 border-t border-white/10 pt-3 text-xs text-blue-200">
                            <span class="inline-flex items-center gap-1 font-medium">
                                <span class="h-2 w-2 rounded-full bg-cyan-400"></span> {{ stats.atlet_putra }} Putra
                            </span>
                            <span class="text-white/30">•</span>
                            <span class="inline-flex items-center gap-1 font-medium">
                                <span class="h-2 w-2 rounded-full bg-pink-400"></span> {{ stats.atlet_putri }} Putri
                            </span>
                        </div>
                    </div>

                    <!-- CARD 2: PELATIH -->
                    <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-b from-white/10 to-white/5 p-6 backdrop-blur-md transition hover:-translate-y-1 hover:border-emerald-400/40 hover:shadow-xl hover:shadow-emerald-500/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Pelatih Terdaftar</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-300">
                                🧑‍🏫
                            </div>
                        </div>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span class="text-4xl font-black text-white">{{ stats.pelatih_total.toLocaleString() }}</span>
                            <span class="text-xs text-emerald-200 font-medium">Pelatih</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-3 text-xs text-emerald-200">
                            <span class="inline-flex items-center gap-1 font-medium">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span> {{ stats.pelatih_bersertifikat }} Bersertifikat
                            </span>
                            <span class="text-[11px] text-emerald-300/80 font-medium">Terverifikasi</span>
                        </div>
                    </div>

                    <!-- CARD 3: PRESTASI & MEDALI -->
                    <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-b from-white/10 to-white/5 p-6 backdrop-blur-md transition hover:-translate-y-1 hover:border-amber-400/40 hover:shadow-xl hover:shadow-amber-500/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-amber-200">Total Prestasi</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/20 text-amber-300">
                                🏆
                            </div>
                        </div>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span class="text-4xl font-black text-white">{{ stats.prestasi_total.toLocaleString() }}</span>
                            <span class="text-xs text-amber-200 font-medium">Capaian</span>
                        </div>
                        <div class="mt-4 flex items-center gap-2 border-t border-white/10 pt-3 text-xs">
                            <span class="rounded bg-amber-400/20 px-1.5 py-0.5 font-bold text-amber-300">🥇 {{ stats.medali_emas }}</span>
                            <span class="rounded bg-slate-400/20 px-1.5 py-0.5 font-bold text-slate-300">🥈 {{ stats.medali_perak }}</span>
                            <span class="rounded bg-orange-400/20 px-1.5 py-0.5 font-bold text-orange-300">🥉 {{ stats.medali_perunggu }}</span>
                        </div>
                    </div>

                    <!-- CARD 4: CABANG OLAHRAGA -->
                    <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-b from-white/10 to-white/5 p-6 backdrop-blur-md transition hover:-translate-y-1 hover:border-purple-400/40 hover:shadow-xl hover:shadow-purple-500/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-purple-200">Cabang Olahraga</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/20 text-purple-300">
                                ⚽
                            </div>
                        </div>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span class="text-4xl font-black text-white">{{ stats.cabor_total }}</span>
                            <span class="text-xs text-purple-200 font-medium">Cabor Aktif</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-3 text-xs text-purple-200">
                            <span>DISPORA Kota Tanjungpinang</span>
                            <span class="font-medium text-purple-300">Resmi</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION: STATISTIK & TREN PRESTASI -->
        <section id="statistik" class="py-16 sm:py-20 border-b border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                            📊 Analitik Olahraga
                        </div>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                            Statistik & Rekam Jejak Prestasi
                        </h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            Perolehan medali menurut tahun dan jenjang kompetisi kejuaraan.
                        </p>
                    </div>

                    <!-- Medal Summary Badges -->
                    <div class="flex flex-wrap gap-2">
                        <div class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50/80 px-3.5 py-2 dark:border-amber-900/50 dark:bg-amber-950/30">
                            <span class="text-xl">🥇</span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-800 dark:text-amber-300">Emas</p>
                                <p class="text-lg font-black text-amber-900 dark:text-amber-200">{{ stats.medali_emas }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-100/80 px-3.5 py-2 dark:border-slate-700 dark:bg-slate-800/50">
                            <span class="text-xl">🥈</span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">Perak</p>
                                <p class="text-lg font-black text-slate-900 dark:text-slate-200">{{ stats.medali_perak }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-xl border border-orange-200 bg-orange-50/80 px-3.5 py-2 dark:border-orange-900/50 dark:bg-orange-950/30">
                            <span class="text-xl">🥉</span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-orange-800 dark:text-orange-300">Perunggu</p>
                                <p class="text-lg font-black text-orange-900 dark:text-orange-200">{{ stats.medali_perunggu }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHARTS & BREAKDOWN GRID -->
                <div class="mt-10 grid grid-cols-1 gap-8 lg:grid-cols-12">
                    <!-- TREN MEDALI TAHUNAN (BAR CHART) -->
                    <div class="lg:col-span-7 rounded-2xl border border-slate-200 bg-slate-50/50 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/40">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Tren Medali Tahunan</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pertumbuhan medali dari tahun ke tahun</p>
                            </div>
                            <div class="flex items-center gap-3 text-xs">
                                <span class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-sm bg-amber-400"></span> Emas</span>
                                <span class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-sm bg-slate-400"></span> Perak</span>
                                <span class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-sm bg-orange-400"></span> Perunggu</span>
                            </div>
                        </div>

                        <div class="mt-8 space-y-5">
                            <template v-if="trenMedali.length > 0">
                                <div v-for="t in trenMedali" :key="t.tahun" class="space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-800 dark:text-slate-200">Tahun {{ t.tahun }}</span>
                                        <span class="font-semibold text-slate-600 dark:text-slate-400">{{ t.total }} Medali</span>
                                    </div>
                                    <!-- Multi-segmented Progress bar -->
                                    <div class="flex h-4 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                        <div
                                            :style="{ width: ((t.emas / maxTrenMedali) * 100) + '%' }"
                                            class="bg-amber-400 transition-all duration-500 hover:brightness-110"
                                            :title="`Emas: ${t.emas}`"
                                        ></div>
                                        <div
                                            :style="{ width: ((t.perak / maxTrenMedali) * 100) + '%' }"
                                            class="bg-slate-400 transition-all duration-500 hover:brightness-110"
                                            :title="`Perak: ${t.perak}`"
                                        ></div>
                                        <div
                                            :style="{ width: ((t.perunggu / maxTrenMedali) * 100) + '%' }"
                                            class="bg-orange-400 transition-all duration-500 hover:brightness-110"
                                            :title="`Perunggu: ${t.perunggu}`"
                                        ></div>
                                    </div>
                                </div>
                            </template>
                            <div v-else class="flex flex-col items-center justify-center py-10 text-center">
                                <div class="text-3xl">🏅</div>
                                <p class="mt-2 text-sm font-medium text-slate-500 dark:text-slate-400">Belum ada riwayat tren tahunan yang tercatat</p>
                            </div>
                        </div>
                    </div>

                    <!-- PRESTASI MENURUT TINGKAT KEJUARAAN -->
                    <div class="lg:col-span-5 rounded-2xl border border-slate-200 bg-slate-50/50 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/40">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Capaian per Tingkat Kejuaraan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Distribusi medali berdasarkan level kompetisi</p>
                        </div>

                        <div class="mt-6 space-y-4">
                            <div
                                v-for="lvl in prestasiByTingkat"
                                :key="lvl.tingkat"
                                class="rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-xs dark:border-slate-800 dark:bg-slate-900"
                            >
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ lvl.label }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ lvl.total }} total</span>
                                        <span v-if="lvl.emas > 0" class="rounded bg-amber-100 px-1.5 py-0.2 text-[10px] font-bold text-amber-800 dark:bg-amber-950/80 dark:text-amber-300">
                                            🥇 {{ lvl.emas }} Emas
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div
                                        :style="{ width: maxTingkatMedali > 0 ? ((lvl.total / maxTingkatMedali) * 100) + '%' : '0%' }"
                                        class="h-full rounded-full bg-gradient-to-r from-blue-600 to-indigo-500 transition-all duration-500"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION: DATA EXPLORER & SHOWCASE -->
        <section id="explorer" class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header & Tabs -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-md bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                            🔍 Direktori Publik
                        </div>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                            Jelajahi Data Atlet, Pelatih & Prestasi
                        </h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            Telusuri profil atlet, pelatih berlisensi, dan capaian medali secara real-time.
                        </p>
                    </div>

                    <!-- TAB SELECTORS -->
                    <div class="inline-flex rounded-xl bg-slate-200/80 p-1 dark:bg-slate-850 dark:border dark:border-slate-800">
                        <button
                            @click="activeTab = 'prestasi'"
                            :class="[
                                activeTab === 'prestasi'
                                    ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-800 dark:text-white'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                                'flex items-center gap-2 rounded-lg px-3.5 py-2 text-xs font-bold transition'
                            ]"
                        >
                            <span>🏆 Prestasi</span>
                            <span class="rounded-full bg-blue-50 px-1.5 py-0.5 text-[10px] dark:bg-slate-700">{{ recentPrestasis.length }}</span>
                        </button>
                        <button
                            @click="activeTab = 'atlet'"
                            :class="[
                                activeTab === 'atlet'
                                    ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-800 dark:text-white'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                                'flex items-center gap-2 rounded-lg px-3.5 py-2 text-xs font-bold transition'
                            ]"
                        >
                            <span>🏃 Atlit</span>
                            <span class="rounded-full bg-blue-50 px-1.5 py-0.5 text-[10px] dark:bg-slate-700">{{ atlets.length }}</span>
                        </button>
                        <button
                            @click="activeTab = 'pelatih'"
                            :class="[
                                activeTab === 'pelatih'
                                    ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-800 dark:text-white'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                                'flex items-center gap-2 rounded-lg px-3.5 py-2 text-xs font-bold transition'
                            ]"
                        >
                            <span>🧑‍🏫 Pelatih</span>
                            <span class="rounded-full bg-blue-50 px-1.5 py-0.5 text-[10px] dark:bg-slate-700">{{ pelatihs.length }}</span>
                        </button>
                        <button
                            @click="activeTab = 'cabor'"
                            :class="[
                                activeTab === 'cabor'
                                    ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-800 dark:text-white'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                                'flex items-center gap-2 rounded-lg px-3.5 py-2 text-xs font-bold transition'
                            ]"
                        >
                            <span>📊 Per Cabor</span>
                            <span class="rounded-full bg-blue-50 px-1.5 py-0.5 text-[10px] dark:bg-slate-700">{{ caborStats.length }}</span>
                        </button>
                    </div>
                </div>

                <!-- FILTERS BAR -->
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="relative flex-1 max-w-md">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama atlet, pelatih, cabor, atau kejuaraan..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-xs text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>

                    <div class="flex items-center gap-3">
                        <select
                            v-model="selectedCabor"
                            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="">Semua Cabang Olahraga</option>
                            <option v-for="c in caborsList" :key="c.id" :value="c.nama">
                                {{ c.nama }}
                            </option>
                        </select>

                        <button
                            v-if="searchQuery || selectedCabor"
                            @click="searchQuery = ''; selectedCabor = ''"
                            class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Reset
                        </button>
                    </div>
                </div>

                <!-- TAB CONTENT 1: PRESTASI -->
                <div v-show="activeTab === 'prestasi'" class="mt-8">
                    <div v-if="filteredPrestasi.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="item in filteredPrestasi"
                            :key="item.id"
                            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition hover:-translate-y-0.5 hover:border-blue-400/50 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                        >
                            <!-- Medal Badge Top -->
                            <div class="flex items-start justify-between gap-2">
                                <span class="rounded-lg border px-2.5 py-1 text-xs font-bold" :class="getMedaliBadge(item.medali).bg">
                                    {{ getMedaliBadge(item.medali).icon }} {{ item.medali_label }}
                                </span>
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold text-slate-600 uppercase tracking-wider dark:bg-slate-800 dark:text-slate-400">
                                    {{ item.kejuaraan_tingkat }}
                                </span>
                            </div>

                            <!-- Kejuaraan & Kategori -->
                            <div class="mt-3">
                                <h4 class="text-base font-bold text-slate-900 line-clamp-1 group-hover:text-blue-600 transition dark:text-white dark:group-hover:text-blue-400">
                                    {{ item.kejuaraan_nama }}
                                </h4>
                                <p class="mt-0.5 text-xs text-slate-500 line-clamp-1 dark:text-slate-400">
                                    {{ item.kategori_kelas ? 'Kategori ' + item.kategori_kelas : 'Kejuaraan Resmi' }}
                                </p>
                            </div>

                            <!-- Atlet / Peraih -->
                            <div class="mt-4 flex items-center gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700 text-xs dark:bg-blue-950 dark:text-blue-300">
                                    {{ item.atlet_nama.charAt(0) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-slate-800 truncate dark:text-slate-200">
                                        {{ item.atlet_nama }}
                                    </p>
                                    <p class="text-[11px] text-blue-600 font-medium truncate dark:text-blue-400">
                                        {{ item.cabor_nama }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-slate-400">{{ item.tanggal }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center dark:border-slate-800 dark:bg-slate-900">
                        <div class="text-4xl">🏆</div>
                        <h4 class="mt-3 text-base font-bold text-slate-800 dark:text-slate-200">Tidak ada data prestasi ditemukan</h4>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Coba ubah kata kunci pencarian atau filter cabang olahraga.</p>
                    </div>
                </div>

                <!-- TAB CONTENT 2: ATLET -->
                <div v-show="activeTab === 'atlet'" class="mt-8">
                    <div v-if="filteredAtlets.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div
                            v-for="atlet in filteredAtlets"
                            :key="atlet.id"
                            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition hover:-translate-y-0.5 hover:border-emerald-400/50 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 font-black text-emerald-800 text-sm shadow-xs dark:bg-emerald-950 dark:text-emerald-300">
                                    {{ atlet.nama.charAt(0) }}
                                </div>
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                    {{ atlet.jenis_kelamin === 'L' ? 'Putra' : 'Putri' }}
                                </span>
                            </div>

                            <div class="mt-3">
                                <h4 class="text-sm font-bold text-slate-900 truncate group-hover:text-emerald-600 transition dark:text-white dark:group-hover:text-emerald-400">
                                    {{ atlet.nama }}
                                </h4>
                                <p class="mt-0.5 text-xs font-semibold text-blue-600 dark:text-blue-400">
                                    {{ atlet.cabor_nama }}
                                </p>
                            </div>

                            <div class="mt-4 space-y-1.5 border-t border-slate-100 pt-3 text-[11px] text-slate-500 dark:border-slate-800 dark:text-slate-400">
                                <div class="flex items-center justify-between">
                                    <span>Klub:</span>
                                    <span class="font-medium text-slate-700 truncate max-w-[140px] dark:text-slate-300">{{ atlet.klub_nama }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Wilayah:</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ atlet.kecamatan_nama }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <span>Prestasi Diraih:</span>
                                    <span class="inline-flex items-center gap-1 rounded bg-amber-50 px-1.5 py-0.5 font-bold text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                        🏆 {{ atlet.prestasi_count }} Medali
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center dark:border-slate-800 dark:bg-slate-900">
                        <div class="text-4xl">🏃</div>
                        <h4 class="mt-3 text-base font-bold text-slate-800 dark:text-slate-200">Tidak ada data atlit ditemukan</h4>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Coba sesuaikan kata kunci pencarian atau cabang olahraga.</p>
                    </div>
                </div>

                <!-- TAB CONTENT 3: PELATIH -->
                <div v-show="activeTab === 'pelatih'" class="mt-8">
                    <div v-if="filteredPelatihs.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div
                            v-for="pelatih in filteredPelatihs"
                            :key="pelatih.id"
                            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition hover:-translate-y-0.5 hover:border-indigo-400/50 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 font-black text-indigo-800 text-sm shadow-xs dark:bg-indigo-950 dark:text-indigo-300">
                                    {{ pelatih.nama.charAt(0) }}
                                </div>
                                <span v-if="pelatih.has_license" class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    ✓ Berlisensi
                                </span>
                                <span v-else class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                    Pelatih
                                </span>
                            </div>

                            <div class="mt-3">
                                <h4 class="text-sm font-bold text-slate-900 truncate group-hover:text-indigo-600 transition dark:text-white dark:group-hover:text-indigo-400">
                                    {{ pelatih.nama }}
                                </h4>
                                <p class="mt-0.5 text-xs font-semibold text-blue-600 dark:text-blue-400">
                                    {{ pelatih.cabor_nama }}
                                </p>
                            </div>

                            <div class="mt-4 space-y-1.5 border-t border-slate-100 pt-3 text-[11px] text-slate-500 dark:border-slate-800 dark:text-slate-400">
                                <div class="flex items-center justify-between">
                                    <span>Tingkat Lisensi:</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ pelatih.level || 'Daerah / Dasar' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Klub Naungan:</span>
                                    <span class="font-medium text-slate-700 truncate max-w-[140px] dark:text-slate-300">{{ pelatih.klub_nama }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <span>Atlet Dibina:</span>
                                    <span class="font-bold text-indigo-700 dark:text-indigo-300">{{ pelatih.binaan_count }} Atlet</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center dark:border-slate-800 dark:bg-slate-900">
                        <div class="text-4xl">🧑‍🏫</div>
                        <h4 class="mt-3 text-base font-bold text-slate-800 dark:text-slate-200">Tidak ada data pelatih ditemukan</h4>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Coba sesuaikan kata kunci pencarian atau cabang olahraga.</p>
                    </div>
                </div>

                <!-- TAB CONTENT 4: STATISTIK CABANG OLAHRAGA -->
                <div v-show="activeTab === 'cabor'" class="mt-8">
                    <div v-if="filteredCaborStats.length > 0" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                                <thead class="border-b border-slate-200 bg-slate-50 font-bold uppercase tracking-wider text-[11px] text-slate-700 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                                    <tr>
                                        <th class="py-3.5 px-4">Cabang Olahraga</th>
                                        <th class="py-3.5 px-4 text-center">Atlit Terdaftar</th>
                                        <th class="py-3.5 px-4 text-center">Pelatih</th>
                                        <th class="py-3.5 px-4 text-center">🥇 Emas</th>
                                        <th class="py-3.5 px-4 text-center">🥈 Perak</th>
                                        <th class="py-3.5 px-4 text-center">🥉 Perunggu</th>
                                        <th class="py-3.5 px-4 text-center">Total Medali</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <tr
                                        v-for="cabor in filteredCaborStats"
                                        :key="cabor.id"
                                        class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition"
                                    >
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            {{ cabor.nama }}
                                            <span v-if="cabor.kode" class="ml-1 text-[10px] text-slate-400 font-normal">({{ cabor.kode }})</span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-semibold">{{ cabor.atlet_count }}</td>
                                        <td class="py-3.5 px-4 text-center font-semibold">{{ cabor.pelatih_count }}</td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="rounded-md bg-amber-100 px-2 py-0.5 font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                                {{ cabor.emas_count }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="rounded-md bg-slate-200 px-2 py-0.5 font-bold text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                                                {{ cabor.perak_count }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="rounded-md bg-orange-100 px-2 py-0.5 font-bold text-orange-800 dark:bg-orange-950 dark:text-orange-300">
                                                {{ cabor.perunggu_count }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-black text-blue-700 dark:text-blue-400">
                                            {{ cabor.prestasi_count }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center dark:border-slate-800 dark:bg-slate-900">
                        <div class="text-4xl">📊</div>
                        <h4 class="mt-3 text-base font-bold text-slate-800 dark:text-slate-200">Tidak ada cabang olahraga ditemukan</h4>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA & INFO BANNER -->
        <section class="border-t border-slate-200 bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 py-12 text-white dark:border-slate-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <h3 class="text-xl font-bold tracking-tight text-white sm:text-2xl">
                            Pengurus Cabor & Klub Olahraga Kota Tanjungpinang?
                        </h3>
                        <p class="mt-1 text-sm text-blue-200 max-w-2xl">
                            Masuk ke dashboard resmi Dinas Pemuda dan Olahraga Pemerintah Kota Tanjungpinang untuk pemutakhiran data atlit binaan, pelatih, pengajuan verifikasi prestasi, serta jadwal sarana olahraga.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-xs font-bold text-blue-900 shadow-md transition hover:bg-blue-50 active:scale-95"
                        >
                            <span>Masuk ke Portal SINDORA</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-t border-slate-200 bg-white py-8 text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 sm:flex-row sm:px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-800 dark:text-white">SINDORA</span>
                    <span>• Dinas Pemuda dan Olahraga Pemerintah Kota Tanjungpinang</span>
                </div>
                <p>© {{ new Date().getFullYear() }} SINDORA — Dinas Pemuda dan Olahraga Kota Tanjungpinang. Seluruh hak cipta dilindungi.</p>
            </div>
        </footer>
    </div>
</template>
