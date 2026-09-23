<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const showingSidebar = ref(false);
const isDark = ref(false);

function applyTheme(dark) {
    isDark.value = dark;
    const root = document.documentElement;
    if (dark) root.classList.add('dark');
    else root.classList.remove('dark');
    try { localStorage.setItem('sindora-theme', dark ? 'dark' : 'light'); } catch(e) {}
}
function toggleTheme() { applyTheme(!isDark.value); }
onMounted(() => {
    try {
        const stored = localStorage.getItem('sindora-theme');
        if (stored) isDark.value = stored === 'dark';
        else isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(isDark.value);
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('sindora-theme')) applyTheme(e.matches);
        });
    } catch(e) {}
});

const user = computed(() => page.props.auth?.user ?? null);
const roles = computed(() => user.value?.roles ?? []);
const can = computed(() => page.props.auth?.can ?? {});
const unreadCount = computed(() => page.props.auth?.unread_notifications_count ?? 0);
const notificationsPreview = computed(() => page.props.auth?.notifications ?? []);

function markRead(id) {
    router.post(route('notifications.read', id), {}, { preserveScroll: true });
}
function markAllRead() {
    router.post(route('notifications.readAll'), {}, { preserveScroll: true });
}

function hasRole(name) {
    return roles.value.includes(name);
}
function hasAnyRole(names) {
    return names.some((r) => roles.value.includes(r));
}

// Helper to decide active: simplistic check on current route name
function isRouteActive(name) {
    try {
        return route().current(name);
    } catch {
        return false;
    }
}

const navMasterData = computed(() => [
    { label: 'Kecamatan', href: route('kecamatans.index'), route: 'kecamatans.*', icon: 'map', can: can.value.view_wilayah ?? true, soon: false },
    { label: 'Kelurahan', href: route('kelurahans.index'), route: 'kelurahans.*', icon: 'map', can: can.value.view_wilayah ?? true, soon: false },
    { label: 'Organisasi', href: route('organisasis.index'), route: 'organisasis.*', icon: 'building', can: can.value.view_organisasi ?? true, soon: false },
    { label: 'Cabor', href: route('cabors.index'), route: 'cabors.*', icon: 'trophy', can: can.value.view_cabor ?? true, soon: false },
    { label: 'Klub', href: route('klubs.index'), route: 'klubs.*', icon: 'shield', can: can.value.view_klub ?? true, soon: false },
    { label: 'Atlet', href: route('atlets.index'), route: 'atlets.*', icon: 'users', can: can.value.view_atlet ?? true, soon: false },
    { label: 'SDM', href: route('sdms.index'), route: 'sdms.*', icon: 'user-group', can: can.value.view_sdm ?? true, soon: false },
    { label: 'Sarpras', href: route('sarpras.index'), route: 'sarpras.*', icon: 'stadium', can: can.value.view_sarpras ?? true, soon: false },
]);
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Mobile drawer backdrop -->
        <div
            v-show="showingSidebar"
            class="fixed inset-0 z-30 bg-gray-900/50 backdrop-blur-sm lg:hidden"
            @click="showingSidebar = false"
        />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-[#1e3a8a] text-white transition-transform duration-200 ease-in-out lg:translate-x-0',
                showingSidebar ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Brand -->
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-blue-700/60 px-4">
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 ring-1 ring-white/20">
                        <!-- placeholder logo icon -->
                        <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 4v5c0 4.5-3 8-7 9-4-1-7-4.5-7-9V7l7-4z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <div class="text-sm font-extrabold tracking-wide">SINDORA</div>
                        <div class="text-[11px] font-medium text-blue-200">Dispora Tanjungpinang</div>
                    </div>
                </Link>
                <button
                    class="ml-auto rounded-md p-1.5 text-blue-200 hover:bg-white/10 hover:text-white lg:hidden"
                    @click="showingSidebar = false"
                    aria-label="Close sidebar"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- User mini card -->
            <div v-if="user" class="border-b border-blue-700/60 px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-sm font-bold text-[#1e3a8a]">
                        {{ (user.name || 'U').charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-semibold">{{ user.name }}</div>
                        <div class="truncate text-xs text-blue-200">{{ user.email }}</div>
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <span
                        v-for="r in roles"
                        :key="r"
                        class="inline-flex rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-blue-100 ring-1 ring-white/15"
                    >{{ r.replaceAll('_',' ') }}</span>
                </div>
            </div>

            <!-- Navigation scrollable -->
            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <!-- Dashboard -->
                <div class="mb-4">
                    <Link
                        :href="route('dashboard')"
                        :class="[
                            'sindora-sidebar-link',
                            isRouteActive('dashboard') ? 'sindora-sidebar-link-active' : 'sindora-sidebar-link-inactive',
                        ]"
                        @click="showingSidebar = false"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M4 10v10a1 1 0 001 1h4a1 1 0 001-1v-4h4v4a1 1 0 001 1h4a1 1 0 001-1V10" />
                        </svg>
                        Dashboard
                    </Link>
                </div>

                <!-- Master Data group -->
                <div class="mb-4">
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-widest text-blue-300">Master Data</p>
                    <ul class="space-y-1">
                        <li v-for="item in navMasterData" :key="item.label" v-show="item.can">
                            <Link
                                :href="item.href"
                                :class="[
                                    'sindora-sidebar-link',
                                    item.route && isRouteActive(item.route) ? 'sindora-sidebar-link-active' : 'sindora-sidebar-link-inactive',
                                    item.soon ? 'opacity-90' : '',
                                ]"
                                :title="item.soon ? item.label + ' (coming soon)' : item.label"
                                @click="showingSidebar=false"
                            >
                                <svg v-if="item.icon==='map'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.4-2.3A1 1 0 013 16.8V5a1 1 0 011.6-.8L9 7l5.4-2.3A1 1 0 0116 5v11.8a1 1 0 01-.6.9L9 20z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 7v13M16 5v13M3 5l5.4 2.3M16 17.8L21 20V8.2L16 5" /></svg>
                                <svg v-else-if="item.icon==='building'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21V5a2 2 0 012-2h4a2 2 0 012 2v16M9 21h6M15 21V9a2 2 0 012-2h2a2 2 0 012 2v12" /><path stroke-linecap="round" stroke-linejoin="round" d="M7 10h2M7 13h2M7 16h2M17 13h2M17 16h2" /></svg>
                                <svg v-else-if="item.icon==='trophy'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17a5 5 0 005-5V7H7v5a5 5 0 005 5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M7 7H5a3 3 0 003 6M17 7h2a3 3 0 01-3 6" /></svg>
                                <svg v-else-if="item.icon==='shield'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 4v5c0 4-2.8 7-7 9-4.2-2-7-5-7-9V7l7-4z" /></svg>
                                <svg v-else-if="item.icon==='users'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" /><circle cx="9" cy="7" r="4" /><path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" /></svg>
                                <svg v-else-if="item.icon==='user-group'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11a3 3 0 100-6 3 3 0 000 6z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 21v-2a4 4 0 014-4h0M18 21v-2a4 4 0 00-4-4h0" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 8a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM18 8a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" /></svg>
                                <svg v-else class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" /></svg>
                                <span class="flex-1">{{ item.label }}</span>
                                <span v-if="item.soon" class="rounded bg-white/10 px-1.5 py-0.5 text-[10px] font-medium text-blue-200">soon</span>
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Operations -->
                <div class="mb-4">
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-widest text-blue-300">Operasional</p>
                    <ul class="space-y-1">
                        <li v-show="can.view_verifikasi ?? hasAnyRole(['super_admin','verifikator'])">
                            <Link :href="route('verifikasi.queue')" :class="['sindora-sidebar-link', isRouteActive('verifikasi.*') ? 'sindora-sidebar-link-active' : 'sindora-sidebar-link-inactive']" @click="showingSidebar=false">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 22a10 10 0 100-20 10 10 0 000 20z" /></svg>
                                Verifikasi
                            </Link>
                        </li>
                        <li v-show="can.view_kejuaraan ?? true">
                            <Link :href="route('kejuaraans.index')" :class="['sindora-sidebar-link', isRouteActive('kejuaraans.*') ? 'sindora-sidebar-link-active' : 'sindora-sidebar-link-inactive']" @click="showingSidebar=false">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2M3 10h18M7 14h3m-3 4h3M15 14h2m-2 4h2" /></svg>
                                Kejuaraan
                            </Link>
                        </li>
                        <li v-show="can.view_prestasi ?? true">
                            <Link :href="route('prestasis.index')" :class="['sindora-sidebar-link', isRouteActive('prestasis.*') ? 'sindora-sidebar-link-active' : 'sindora-sidebar-link-inactive']" @click="showingSidebar=false">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8l-2.5 4.5L12 15l2.5-2.5L12 8z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l3 2-3 3-3-3 3-2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M5 8l2 2 2-2M15 10l2 2 2-2M7 16h10" /></svg>
                                Prestasi
                            </Link>
                        </li>
                        <li v-show="can.view_pembinaan ?? true">
                            <Link :href="route('pembinaans.index')" :class="['sindora-sidebar-link', isRouteActive('pembinaans.*') ? 'sindora-sidebar-link-active' : 'sindora-sidebar-link-inactive']" @click="showingSidebar=false">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a5.25 5.25 0 015.25 5.25v3.5L12 21l-5.25-5.5v-3.5A5.25 5.25 0 0112 6.75z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 11v4" /></svg>
                                Pembinaan
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('kalender.index')" :class="['sindora-sidebar-link', isRouteActive('kalender.*') ? 'sindora-sidebar-link-active' : 'sindora-sidebar-link-inactive']" @click="showingSidebar=false">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M3 11h18M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                Kalender
                            </Link>
                        </li>
                        <li v-show="can.view_sarpras ?? true">
                            <Link :href="route('sarpras-jadwals.index')" :class="['sindora-sidebar-link', isRouteActive('sarpras-jadwals.*') ? 'sindora-sidebar-link-active' : 'sindora-sidebar-link-inactive']" @click="showingSidebar=false">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Jadwal Sarpras
                            </Link>
                        </li>
                    </ul>
                </div>

                <div class="mb-4">
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-widest text-blue-300">Analitik & Peta</p>
                    <ul class="space-y-1">
                        <li v-show="can.view_dashboard ?? hasAnyRole(['super_admin','verifikator','viewer','operator_organisasi'])">
                            <Link href="#" class="sindora-sidebar-link sindora-sidebar-link-inactive">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10zM13 21h8V3h-8v18zM3 21h8v-6H3v6z" /></svg>
                                Dashboard Eksekutif
                            </Link>
                        </li>
                        <li v-show="can.view_laporan ?? true">
                            <Link href="#" class="sindora-sidebar-link sindora-sidebar-link-inactive">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l5 5v11a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14 3v6h6M9 13h6M9 17h6" /></svg>
                                Laporan
                            </Link>
                        </li>
                        <li v-show="can.view_gis ?? true">
                            <Link :href="route('gis.index')" :class="['sindora-sidebar-link', isRouteActive('gis.*') ? 'sindora-sidebar-link-active' : 'sindora-sidebar-link-inactive']" @click="showingSidebar=false">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5 7-10a7 7 0 10-14 0c0 5 7 10 7 10z" /><circle cx="12" cy="11" r="2.5" /></svg>
                                GIS
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Role-based admin section -->
                <div v-if="hasRole('super_admin') || can.manage_user || can.view_user" class="mb-2">
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-widest text-blue-300">Administrasi</p>
                    <ul class="space-y-1">
                        <li v-show="can.manage_user || can.view_user || hasRole('super_admin')">
                            <Link :href="route('users.index')" :class="['sindora-sidebar-link', isRouteActive('users.*') ? 'sindora-sidebar-link-active' : 'sindora-sidebar-link-inactive']" @click="showingSidebar=false">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06A1.65 1.65 0 0015 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 009 15a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 15z" /></svg>
                                Manajemen User
                            </Link>
                        </li>
                        <li>
                            <Link href="#" class="sindora-sidebar-link sindora-sidebar-link-inactive">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H7a2 2 0 00-2 2v16a2 2 0 002 2h10a2 2 0 002-2V8z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6M10 13H8m8 0h-2m-6 4h8" /></svg>
                                Audit Log
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Footer -->
            <div class="border-t border-blue-700/60 px-4 py-3">
                <p class="text-[11px] leading-tight text-blue-200">SINDORA v1 — Sprint 1</p>
                <p class="text-[11px] text-blue-300">16 tables · 5 roles · 18 wilayah</p>
            </div>
        </aside>

        <!-- Main column -->
        <div class="lg:pl-64">
            <!-- Top navbar -->
            <header class="sticky top-0 z-20 border-b border-gray-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80 dark:border-gray-700 dark:bg-gray-800/95">
                <div class="flex h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">
                    <!-- Hamburger (mobile) -->
                    <button
                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 lg:hidden dark:text-gray-400 dark:hover:bg-gray-700"
                        @click="showingSidebar = !showingSidebar"
                        aria-label="Toggle sidebar"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" v-if="!showingSidebar" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" v-else />
                        </svg>
                    </button>

                    <!-- Breadcrumb / title slot trigger -->
                    <div class="hidden items-center gap-2 lg:flex">
                        <div class="hidden h-6 w-px bg-gray-200 dark:bg-gray-700 lg:block" />
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">Sistem Informasi Keolahragaan</div>
                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/40 dark:text-blue-200 dark:ring-blue-800">Dispora</span>
                    </div>

                    <div class="flex-1" />

                    <!-- Flash (optional, auto shown via page props but header shows icon) -->
                    <div class="hidden items-center gap-3 sm:flex">
                        <span class="hidden text-xs text-gray-500 dark:text-gray-400 sm:inline">Tanjungpinang · Kepri</span>
                        <span class="h-6 w-px bg-gray-200 dark:bg-gray-700" />
                    </div>

                    <!-- Theme switcher -->
                    <button
                        type="button"
                        @click="toggleTheme"
                        :title="isDark ? 'Ganti ke Light Mode' : 'Ganti ke Dark Mode'"
                        class="relative inline-flex items-center justify-center rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                        aria-label="Toggle dark mode"
                    >
                        <svg v-if="!isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0112 21.75 9.75 9.75 0 1115.002 2.248 7.5 7.5 0 0021.752 15.002z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M4.5 12H3m18 0h-1.5M5.636 5.636l1.06 1.06M17.303 17.303l1.06 1.06M5.636 18.364l1.06-1.06M17.303 6.696l1.06-1.06" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4" />
                        </svg>
                    </button>

                    <!-- Notification bell -->
                    <Dropdown v-if="user" align="right" width="96">
                        <template #trigger>
                            <button type="button" class="relative inline-flex items-center justify-center rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-gray-400 dark:hover:bg-gray-700">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span v-if="unreadCount > 0" class="absolute -right-0.5 -top-0.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white ring-2 ring-white dark:ring-gray-800">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
                            </button>
                        </template>
                        <template #content>
                            <div class="max-h-[380px] overflow-y-auto">
                                <div class="flex items-center justify-between px-4 py-3">
                                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Notifikasi</div>
                                    <button v-if="unreadCount > 0" @click="markAllRead" class="text-xs font-medium text-blue-600 hover:text-blue-800">Tandai semua dibaca</button>
                                </div>
                                <div v-if="notificationsPreview.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada notifikasi.</div>
                                <div v-for="n in notificationsPreview" :key="n.id" class="border-t border-gray-100 px-4 py-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50">
                                    <div class="flex gap-2">
                                        <span v-if="!n.read_at" class="mt-1 h-2 w-2 shrink-0 rounded-full bg-blue-600"></span>
                                        <span v-else class="mt-1 h-2 w-2 shrink-0 rounded-full bg-gray-300"></span>
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-sm text-gray-800 dark:text-gray-200">{{ n.data?.message ?? n.data?.action ?? 'Notifikasi' }}</div>
                                            <div class="text-xs text-gray-500">{{ n.created_at_human ?? n.created_at }}</div>
                                            <div class="mt-1 flex gap-2">
                                                <button v-if="!n.read_at" @click="markRead(n.id)" class="text-xs text-blue-600 hover:text-blue-800">Tandai dibaca</button>
                                                <Link v-if="n.data?.url" :href="n.data.url" class="text-xs text-gray-600 hover:text-gray-800 dark:text-gray-400">Lihat</Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="border-t border-gray-100 px-4 py-2 text-center dark:border-gray-700">
                                    <Link :href="route('notifications.index')" class="text-sm font-medium text-blue-600 hover:text-blue-800">Lihat semua →</Link>
                                </div>
                            </div>
                        </template>
                    </Dropdown>

                    <!-- User dropdown -->
                    <Dropdown v-if="user" align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-2 py-1.5 text-sm font-medium leading-4 text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#1e3a8a] text-xs font-bold text-white">{{ (user.name||'U').charAt(0).toUpperCase() }}</span>
                                <span class="hidden max-w-[12ch] truncate md:inline">{{ user.name }}</span>
                                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </button>
                        </template>
                        <template #content>
                            <div class="px-4 py-2">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ user.name }}</div>
                                <div class="truncate text-xs text-gray-500">{{ user.email }}</div>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <span v-for="r in roles" :key="r" class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium uppercase text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ r }}</span>
                                </div>
                            </div>
                            <div class="border-t border-gray-100 dark:border-gray-700" />
                            <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                        </template>
                    </Dropdown>
                    <Link v-else :href="route('login')" class="text-sm font-medium text-blue-700 hover:text-blue-800">Log in</Link>
                </div>
            </header>

            <!-- Flash banners -->
            <div v-if="page.props.flash?.success" class="mx-4 mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 sm:mx-6 lg:mx-8 dark:border-green-800 dark:bg-green-900/30 dark:text-green-200">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="mx-4 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 sm:mx-6 lg:mx-8 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
                {{ page.props.flash.error }}
            </div>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow-sm dark:bg-gray-800">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="min-h-[calc(100vh-4rem)]">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="border-t border-gray-200 bg-white px-4 py-4 text-center text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:px-6 lg:px-8">
                © 2026 Dispora Kota Tanjungpinang — SINDORA · Sprint 1 · Laravel 12 + Inertia Vue
            </footer>
        </div>
    </div>
</template>
