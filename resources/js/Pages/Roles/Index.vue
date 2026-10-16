<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, reactive } from 'vue';

const props = defineProps({
    roles: Array,
    modules: Array,
    allPermissions: Array,
});

// Local state for permissions matrix: { [roleName]: Set of permission names }
const matrix = reactive({});
props.roles.forEach(role => {
    matrix[role.name] = new Set(role.permissions || []);
});

const searchQuery = ref('');
const isSaving = ref(false);
const showResetModal = ref(false);
const activeModuleFilter = ref('all');

// Check if a permission is checked for a role
function hasPermission(roleName, permName) {
    return matrix[roleName]?.has(permName) ?? false;
}

// Toggle permission for a role
function togglePermission(roleName, permName) {
    if (!matrix[roleName]) {
        matrix[roleName] = new Set();
    }

    // Safety guardrail: Super Admin cannot uncheck role.manage or user.manage
    if (roleName === 'super_admin' && (permName === 'role.manage' || permName === 'user.manage')) {
        alert('Demi keamanan sistem, role Super Admin wajib memiliki hak akses role.manage dan user.manage.');
        return;
    }

    if (matrix[roleName].has(permName)) {
        matrix[roleName].delete(permName);
    } else {
        matrix[roleName].add(permName);
    }
}

// Select all permissions for a specific role
function selectAllForRole(roleName) {
    if (!matrix[roleName]) matrix[roleName] = new Set();
    props.allPermissions.forEach(p => matrix[roleName].add(p));
}

// Clear all permissions for a specific role (except critical super_admin permissions)
function clearAllForRole(roleName) {
    if (!matrix[roleName]) return;
    if (roleName === 'super_admin') {
        matrix[roleName] = new Set(['role.manage', 'user.manage']);
    } else {
        matrix[roleName].clear();
    }
}

// Toggle an entire module for a specific role
function toggleModuleForRole(roleName, modulePerms) {
    if (!matrix[roleName]) matrix[roleName] = new Set();
    const permNames = modulePerms.map(p => p.name);
    const allChecked = permNames.every(name => matrix[roleName].has(name));

    permNames.forEach(name => {
        if (allChecked) {
            if (roleName === 'super_admin' && (name === 'role.manage' || name === 'user.manage')) {
                // keep protected
            } else {
                matrix[roleName].delete(name);
            }
        } else {
            matrix[roleName].add(name);
        }
    });
}

// Check if all permissions in a module are checked for a role
function isModuleAllChecked(roleName, modulePerms) {
    if (!matrix[roleName]) return false;
    return modulePerms.every(p => matrix[roleName].has(p.name));
}

// Select a permission across all roles
function toggleRowAllRoles(permName) {
    const allHaveIt = props.roles.every(r => matrix[r.name]?.has(permName));
    props.roles.forEach(r => {
        if (!matrix[r.name]) matrix[r.name] = new Set();
        if (allHaveIt) {
            if (r.name === 'super_admin' && (permName === 'role.manage' || permName === 'user.manage')) {
                // keep
            } else {
                matrix[r.name].delete(permName);
            }
        } else {
            matrix[r.name].add(permName);
        }
    });
}

// Filtered modules and permissions based on search query and active module
const filteredModules = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    
    return props.modules.map(mod => {
        if (activeModuleFilter.value !== 'all' && mod.id !== activeModuleFilter.value) {
            return null;
        }

        const filteredPerms = mod.permissions.filter(p => {
            if (!q) return true;
            return p.name.toLowerCase().includes(q) ||
                   p.label.toLowerCase().includes(q) ||
                   p.desc.toLowerCase().includes(q) ||
                   mod.label.toLowerCase().includes(q);
        });

        if (filteredPerms.length === 0) return null;

        return {
            ...mod,
            permissions: filteredPerms,
        };
    }).filter(Boolean);
});

// Calculate total matched permissions count
const totalMatchedPermissions = computed(() => {
    return filteredModules.value.reduce((acc, mod) => acc + mod.permissions.length, 0);
});

// Count active permissions per role
function countActivePerms(roleName) {
    return matrix[roleName]?.size ?? 0;
}

// Check if matrix has unsaved changes compared to initial props
const hasUnsavedChanges = computed(() => {
    for (const r of props.roles) {
        const initialSet = new Set(r.permissions || []);
        const currentSet = matrix[r.name] || new Set();
        if (initialSet.size !== currentSet.size) return true;
        for (const p of currentSet) {
            if (!initialSet.has(p)) return true;
        }
    }
    return false;
});

// Save matrix changes
function saveMatrix() {
    isSaving.value = true;
    const payload = {};
    props.roles.forEach(r => {
        payload[r.name] = Array.from(matrix[r.name] || []);
    });

    router.put(route('roles.update'), { matrix: payload }, {
        preserveScroll: true,
        onFinish: () => {
            isSaving.value = false;
        },
    });
}

// Reset to default
function confirmResetDefault() {
    isSaving.value = true;
    showResetModal.value = false;
    router.post(route('roles.reset-default'), {}, {
        preserveScroll: true,
        onFinish: () => {
            isSaving.value = false;
            // update local matrix with props
            props.roles.forEach(role => {
                matrix[role.name] = new Set(role.permissions || []);
            });
        },
    });
}
</script>

<template>
    <Head title="Hak Akses & Role (ACL)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-800 text-white shadow-sm ring-1 ring-red-900/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                                Matriks Hak Akses & Role (ACL)
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Konfigurasi izin modul per role. Perubahan langsung berlaku ke middleware dan policies sistem.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="showResetModal = true"
                        :disabled="isSaving"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        title="Kembalikan konfigurasi permission ke bawaan standar sistem"
                    >
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Standar
                    </button>

                    <button
                        type="button"
                        @click="saveMatrix"
                        :disabled="isSaving || !hasUnsavedChanges"
                        :class="[
                            'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2',
                            hasUnsavedChanges
                                ? 'bg-red-800 hover:bg-red-900 ring-2 ring-red-600 ring-offset-1 animate-pulse'
                                : 'bg-gray-400 cursor-not-allowed dark:bg-gray-700'
                        ]"
                    >
                        <svg v-if="isSaving" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ isSaving ? 'Menyimpan...' : 'Simpan Hak Akses' }}</span>
                        <span v-if="hasUnsavedChanges" class="rounded-full bg-white/25 px-1.5 py-0.5 text-[10px] font-extrabold">
                            Ada Perubahan
                        </span>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- 4 Official Roles Summary Cards -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="role in roles"
                        :key="role.name"
                        class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <span :class="['inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset', role.badge_class]">
                                    {{ role.label }}
                                </span>
                                <p class="mt-2 text-xs text-gray-500 line-clamp-2 dark:text-gray-400">
                                    {{ role.desc }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 text-xs dark:border-gray-700">
                            <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ role.users_count }}</span> Pengguna
                            </div>

                            <div class="flex items-center gap-1">
                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ countActivePerms(role.name) }}</span>
                                <span class="text-gray-400">/ {{ allPermissions.length }} izin</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unsaved Changes Banner -->
                <div
                    v-if="hasUnsavedChanges"
                    class="flex items-center justify-between rounded-xl border border-amber-300 bg-amber-50 p-4 text-amber-900 shadow-sm dark:border-amber-700/60 dark:bg-amber-950/40 dark:text-amber-200"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-200 text-amber-900 dark:bg-amber-900/60 dark:text-amber-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold">Terdapat perubahan checklist hak akses yang belum disimpan!</p>
                            <p class="text-xs text-amber-800 dark:text-amber-300">
                                Klik tombol <b>"Simpan Hak Akses"</b> di sudut kanan atas untuk mengaplikasikan aturan hak akses baru.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="saveMatrix"
                        :disabled="isSaving"
                        class="rounded-lg bg-amber-700 px-3.5 py-1.5 text-xs font-bold text-white shadow hover:bg-amber-800 focus:outline-none focus:ring-2 focus:ring-amber-500"
                    >
                        {{ isSaving ? 'Menyimpan...' : 'Simpan Sekarang' }}
                    </button>
                </div>

                <!-- Controls & Search Bar -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <!-- Search Box -->
                        <div class="relative flex-1 max-w-md">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari izin (misal: atlet, cabor, verifikasi, user)..."
                                class="w-full rounded-lg border border-gray-300 bg-gray-50 py-2 pl-9 pr-8 text-xs text-gray-900 placeholder-gray-400 focus:border-red-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                            />
                            <button
                                v-if="searchQuery"
                                @click="searchQuery = ''"
                                class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Module Filter Pills -->
                        <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto text-xs">
                            <button
                                type="button"
                                @click="activeModuleFilter = 'all'"
                                :class="[
                                    'rounded-lg px-2.5 py-1.5 font-medium transition',
                                    activeModuleFilter === 'all'
                                        ? 'bg-red-800 text-white shadow-sm'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Semua Modul
                            </button>
                            <button
                                v-for="mod in modules"
                                :key="mod.id"
                                type="button"
                                @click="activeModuleFilter = mod.id"
                                :class="[
                                    'rounded-lg px-2.5 py-1.5 font-medium transition whitespace-nowrap',
                                    activeModuleFilter === mod.id
                                        ? 'bg-red-800 text-white shadow-sm'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                {{ mod.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Matrix Table Container -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <!-- Table Header with Roles -->
                            <thead class="sticky top-0 z-10 border-b border-gray-200 bg-gray-50/95 backdrop-blur dark:border-gray-700 dark:bg-gray-900/95">
                                <tr>
                                    <th class="py-3 px-5 font-bold text-gray-900 dark:text-gray-100 min-w-[340px] align-middle">
                                        <div class="flex items-center gap-2">
                                            <span>Fitur & Modul Izin</span>
                                            <span class="rounded bg-gray-200 px-1.5 py-0.5 text-[10px] font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                {{ totalMatchedPermissions }} izin
                                            </span>
                                        </div>
                                    </th>

                                    <!-- Column for each role -->
                                    <th
                                        v-for="role in roles"
                                        :key="role.name"
                                        class="py-3 px-4 text-center min-w-[150px] align-middle border-l border-gray-200 dark:border-gray-700/60"
                                    >
                                        <div class="flex flex-col items-center justify-center gap-1.5">
                                            <span :class="['inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold ring-1 ring-inset', role.badge_class]">
                                                {{ role.label }}
                                            </span>

                                            <!-- Column Quick Actions -->
                                            <div class="flex items-center gap-1">
                                                <button
                                                    type="button"
                                                    @click="selectAllForRole(role.name)"
                                                    class="rounded px-1.5 py-0.5 text-[10.5px] font-medium text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200 transition"
                                                    title="Centang semua izin untuk role ini"
                                                >
                                                    Pilih Semua
                                                </button>
                                                <span class="text-gray-300 dark:text-gray-600 text-[10px]">·</span>
                                                <button
                                                    type="button"
                                                    @click="clearAllForRole(role.name)"
                                                    class="rounded px-1.5 py-0.5 text-[10.5px] font-medium text-gray-500 hover:bg-red-50 hover:text-red-700 dark:text-gray-400 dark:hover:bg-red-950/40 dark:hover:text-red-300 transition"
                                                    title="Kosongkan izin untuk role ini"
                                                >
                                                    Kosongkan
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>

                            <!-- Table Body grouped by Modules -->
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                <template v-for="mod in filteredModules" :key="mod.id">
                                    <!-- Module Group Header Row -->
                                    <tr class="bg-gray-50 font-semibold border-y border-gray-200/80 dark:bg-gray-800/80 dark:border-gray-700/80">
                                        <td class="py-2.5 px-5 text-gray-900 dark:text-gray-100 align-middle">
                                            <div class="flex items-center gap-2.5">
                                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                                    <svg v-if="mod.icon === 'building'" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                                    <svg v-else-if="mod.icon === 'trophy'" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 21h8M12 17a5 5 0 005-5V7H7v5a5 5 0 005 5zM7 7H5a3 3 0 003 6M17 7h2a3 3 0 01-3 6" /></svg>
                                                    <svg v-else-if="mod.icon === 'users'" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                    <svg v-else-if="mod.icon === 'medal'" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V3m0 5l3-3m-3 3L9 5" /></svg>
                                                    <svg v-else-if="mod.icon === 'check-badge'" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    <svg v-else-if="mod.icon === 'chart-bar'" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                                    <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                                </span>
                                                <div class="flex items-baseline gap-2 flex-wrap">
                                                    <span class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">{{ mod.label }}</span>
                                                    <span class="text-[11px] font-normal text-gray-500 dark:text-gray-400">({{ mod.description }})</span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Quick toggle module for each role -->
                                        <td
                                            v-for="role in roles"
                                            :key="role.name"
                                            class="py-2 px-4 text-center align-middle border-l border-gray-200/60 dark:border-gray-700/50"
                                        >
                                            <button
                                                type="button"
                                                @click="toggleModuleForRole(role.name, mod.permissions)"
                                                :class="[
                                                    'inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10.5px] font-semibold transition',
                                                    isModuleAllChecked(role.name, mod.permissions)
                                                        ? 'bg-red-800 text-white shadow-xs'
                                                        : 'bg-white text-gray-600 ring-1 ring-gray-300 hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600'
                                                ]"
                                                :title="'Centang/batalkan semua izin modul ' + mod.label + ' untuk role ' + role.label"
                                            >
                                                <svg v-if="isModuleAllChecked(role.name, mod.permissions)" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span>{{ isModuleAllChecked(role.name, mod.permissions) ? 'Semua Aktif' : 'Pilih Modul' }}</span>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Permission Rows -->
                                    <tr
                                        v-for="perm in mod.permissions"
                                        :key="perm.name"
                                        class="group transition hover:bg-red-50/30 dark:hover:bg-red-950/20"
                                    >
                                        <!-- Permission Info -->
                                        <td class="py-2.5 pl-10 pr-5 align-middle">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="font-semibold text-gray-900 dark:text-gray-100 text-xs">
                                                            {{ perm.label }}
                                                        </span>
                                                        <code class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-mono text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                            {{ perm.name }}
                                                        </code>
                                                    </div>
                                                    <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                                        {{ perm.desc }}
                                                    </p>
                                                </div>

                                                <!-- Row Quick Action: toggle all roles for this permission -->
                                                <button
                                                    type="button"
                                                    @click="toggleRowAllRoles(perm.name)"
                                                    class="opacity-0 group-hover:opacity-100 transition rounded px-1.5 py-0.5 text-[10px] text-gray-400 hover:bg-gray-200 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200 shrink-0"
                                                    title="Centang/hapus izin ini untuk semua role sekaligus"
                                                >
                                                    Semua Role
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Checkboxes for each role -->
                                        <td
                                            v-for="role in roles"
                                            :key="role.name"
                                            class="py-2 px-4 text-center align-middle border-l border-gray-100 dark:border-gray-700/40"
                                        >
                                            <div class="flex items-center justify-center">
                                                <!-- Protected Super Admin Locks -->
                                                <div
                                                    v-if="role.name === 'super_admin' && (perm.name === 'role.manage' || perm.name === 'user.manage')"
                                                    class="flex items-center justify-center"
                                                    title="Izin mutlak Super Admin untuk keamanan sistem (terkunci)"
                                                >
                                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-red-100 text-red-700 ring-1 ring-red-200 dark:bg-red-900/60 dark:text-red-300">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                        </svg>
                                                    </span>
                                                </div>

                                                <!-- Interactive Toggle Checkbox -->
                                                <label
                                                    v-else
                                                    class="relative inline-flex items-center justify-center p-1 cursor-pointer rounded hover:bg-gray-100 dark:hover:bg-gray-700/50 transition"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :checked="hasPermission(role.name, perm.name)"
                                                        @change="togglePermission(role.name, perm.name)"
                                                        class="h-4 w-4 rounded border-gray-300 text-red-800 transition focus:ring-red-500 focus:ring-offset-0 dark:border-gray-600 dark:bg-gray-700 dark:checked:bg-red-700 cursor-pointer"
                                                    />
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Empty State when Search has no matches -->
                                <tr v-if="filteredModules.length === 0">
                                    <td :colspan="roles.length + 1" class="py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="rounded-full bg-gray-100 p-3 dark:bg-gray-700">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </div>
                                            <p class="mt-3 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                Tidak ada izin yang cocok
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Tidak ditemukan permission dengan kata kunci "{{ searchQuery }}"
                                            </p>
                                            <button
                                                type="button"
                                                @click="searchQuery = ''; activeModuleFilter = 'all'"
                                                class="mt-3 text-xs font-bold text-red-700 hover:text-red-900 dark:text-red-400"
                                            >
                                                Bersihkan pencarian & filter
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Guidance & Information Note -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <svg class="h-4 w-4 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Catatan Teknis & Mekanisme Pengamanan
                    </h3>
                    <ul class="mt-2.5 space-y-2 text-xs text-gray-600 dark:text-gray-400 list-disc list-inside">
                        <li>
                            <b>Pengurus Cabor (Pengkot):</b> Selain pembatasan permission di matriks ini, data cabor yang dapat dilihat dan dikelola oleh akun Pengkot dibatasi secara ketat berdasarkan penugasan cabor (multi-cabor pivot) yang ditentukan oleh Super Admin di halaman Manajemen User.
                        </li>
                        <li>
                            <b>Pimpinan:</b> Hak akses default pimpinan bersifat read-only (pemantauan, dashboard, laporan, WebGIS, dan audit trail).
                        </li>
                        <li>
                            <b>Invalidation Cache:</b> Setiap kali Anda menekan tombol "Simpan Hak Akses", cache izin Spatie secara otomatis di-reset sehingga langsung berlaku ke semua pengguna tanpa perlu login ulang.
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Sticky Floating Save Bar on Scroll when modified -->
        <div
            v-if="hasUnsavedChanges"
            class="fixed bottom-6 right-8 z-30 flex items-center gap-3 rounded-2xl bg-gray-900/95 p-3 px-5 text-white shadow-2xl backdrop-blur ring-1 ring-white/10"
        >
            <div class="flex items-center gap-2.5">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
                <span class="text-xs font-semibold">Ada perubahan belum disimpan</span>
            </div>

            <button
                type="button"
                @click="saveMatrix"
                :disabled="isSaving"
                class="rounded-xl bg-red-700 px-4 py-2 text-xs font-bold text-white shadow hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-400 transition"
            >
                {{ isSaving ? 'Menyimpan...' : 'Simpan Sekarang' }}
            </button>
        </div>

        <!-- Confirm Reset Default Modal -->
        <div
            v-if="showResetModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                            Reset ke Konfigurasi Standar?
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Tindakan ini akan mengembalikan seluruh izin role (Super Admin, Verifikator, Pimpinan, Pengurus Cabor) ke konfigurasi default seeder sistem.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2.5">
                    <button
                        type="button"
                        @click="showResetModal = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="confirmResetDefault"
                        class="rounded-lg bg-red-800 px-4 py-2 text-xs font-bold text-white hover:bg-red-900 shadow-sm"
                    >
                        Ya, Reset ke Standar
                    </button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
