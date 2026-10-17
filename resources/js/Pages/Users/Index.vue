<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useConfirm } from '@/Composables/useConfirm';

const { deleteRecord } = useConfirm();

const props = defineProps({
    users: Object,
    filters: Object,
    roles: Array,
    can: Object,
});

const page = usePage();
const search = ref(props.filters?.search ?? '');
const role = ref(props.filters?.role ?? '');

const roleMeta = {
    super_admin: { label: 'Super Admin', class: 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-800' },
    verifikator: { label: 'Verifikator', class: 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-800' },
    pimpinan: { label: 'Pimpinan', class: 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800' },
    viewer: { label: 'Pimpinan', class: 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800' },
    pengurus_cabor: { label: 'Pengurus Cabor (Pengkot)', class: 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800' },
    pengkot: { label: 'Pengurus Cabor (Pengkot)', class: 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800' },
};

function formatRole(r) {
    return roleMeta[r]?.label ?? r;
}

function roleBadgeClass(r) {
    return roleMeta[r]?.class ?? 'bg-gray-50 text-gray-700 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700';
}

function isPengkotUser(u) {
    const roles = u.roles || [];
    return roles.includes('pengurus_cabor') || roles.includes('pengkot') || roles.includes('operator_organisasi') || roles.includes('operator_klub');
}

let debounce = null;
watch([search, role], ([s, r]) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('users.index'), { search: s, role: r }, { preserveState: true, replace: true });
    }, 300);
});

function destroyUser(id, name) {
    deleteRecord(route('users.destroy', id), {
        title: 'Hapus Akun Pengguna',
        message: 'Apakah Anda yakin ingin menghapus akun pengguna sistem ini?',
        itemName: name,
        warningDetail: 'Akses login pengguna dan seluruh penugasan role/hak akses akan dicabut secara permanen.',
        confirmText: 'Ya, Hapus Pengguna',
    });
}
</script>

<template>
    <Head title="Manajemen User" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Manajemen User</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola akun dan batasan hak akses cabor per pengurus kota</p>
                </div>
                <Link v-if="can?.create" :href="route('users.create')" class="rounded-lg bg-red-800 dark:bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-900 dark:hover:bg-red-600 shadow-sm">Tambah User</Link>
            </div>
        </template>

        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card overflow-hidden">
                    <div class="flex flex-wrap gap-3 p-4">
                        <input v-model="search" placeholder="Cari nama / email..." class="w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <select v-model="role" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua role (4 Role Utama)</option>
                            <option v-for="r in roles" :key="r" :value="r">{{ formatRole(r) }}</option>
                        </select>
                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400 self-center">Total: {{ users.meta?.total ?? 0 }}</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Role</th>
                                    <th class="px-4 py-3">Hak Kelola Cabor / Konteks</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ u.name }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-300">{{ u.email }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="r in (u.roles || [])" :key="r" :class="['inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1', roleBadgeClass(r)]">
                                                {{ formatRole(r) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <!-- Pengurus Cabor / Pengkot cabor scope badges -->
                                        <div v-if="isPengkotUser(u)">
                                            <div v-if="u.assigned_cabors && u.assigned_cabors.length > 0" class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="c in u.assigned_cabors"
                                                    :key="c.id"
                                                    class="inline-flex items-center gap-1 rounded bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 font-medium text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-200 dark:ring-indigo-800"
                                                >
                                                    <span>{{ c.nama }}</span>
                                                    <span class="rounded bg-indigo-200/60 dark:bg-indigo-800 px-1 text-[10px] uppercase text-indigo-800 dark:text-indigo-200 font-semibold">{{ c.kategori }}</span>
                                                </span>
                                            </div>
                                            <span v-else class="inline-flex items-center rounded bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 font-medium text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800">
                                                ⚠️ Belum ada cabor ditetapkan Super Admin
                                            </span>
                                        </div>
                                        <div v-else-if="(u.roles || []).includes('super_admin')">
                                            <span class="font-medium text-red-700 dark:text-red-400">Semua Cabor (Akses Penuh)</span>
                                        </div>
                                        <div v-else-if="(u.roles || []).includes('verifikator')">
                                            <span class="font-medium text-emerald-700 dark:text-emerald-400">Verifikator Semua Cabor</span>
                                        </div>
                                        <div v-else-if="(u.roles || []).includes('pimpinan') || (u.roles || []).includes('viewer')">
                                            <span class="font-medium text-amber-700 dark:text-amber-400">Monitoring Eksekutif</span>
                                        </div>
                                        <div v-else>
                                            <span v-if="u.klub" class="block text-gray-600 dark:text-gray-400">Klub: {{ u.klub.nama }}</span>
                                            <span v-if="u.organisasi" class="block text-gray-600 dark:text-gray-400">Org: {{ u.organisasi.nama }}</span>
                                            <span v-if="!u.klub && !u.organisasi" class="text-gray-400">—</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <Link :href="route('users.edit', u.id)" class="mr-2 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Edit</Link>
                                        <button @click="destroyUser(u.id, u.name)" class="text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="!users.data || users.data.length===0">
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="users.meta && users.meta.last_page > 1" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Hal {{ users.meta.current_page }} dari {{ users.meta.last_page }}</span>
                        <div class="flex gap-2"> <Link v-if="users.links.prev" :href="users.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 text-xs hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link>
                            <span v-else class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 text-xs opacity-50">Prev</span>
                            <Link v-if="users.links.next" :href="users.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 text-xs hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link>
                            <span v-else class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600 text-xs opacity-50">Next</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
