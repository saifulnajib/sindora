<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Object,
    filters: Object,
    roles: Array,
    can: Object,
});

const page = usePage();
const search = ref(props.filters?.search ?? '');
const role = ref(props.filters?.role ?? '');

let debounce = null;
watch([search, role], ([s, r]) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('users.index'), { search: s, role: r }, { preserveState: true, replace: true });
    }, 300);
});

function destroyUser(id) {
    if (!confirm('Hapus user ini?')) return;
    router.delete(route('users.destroy', id));
}
</script>

<template>
    <Head title="Manajemen User" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Manajemen User</h2>
                <Link v-if="can?.create" :href="route('users.create')" class="rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Tambah User</Link>
            </div>
        </template>

        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card overflow-hidden">
                    <div class="flex flex-wrap gap-3 p-4">
                        <input v-model="search" placeholder="Cari nama / email..." class="w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <select v-model="role" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua role</option>
                            <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                        </select>
                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400 self-center">Total: {{ users.meta?.total ?? 0 }}</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Nama</th> <th class="px-4 py-3">Email</th> <th class="px-4 py-3">Roles</th> <th class="px-4 py-3">Konteks</th> <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ u.name }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ u.email }}</td>
                                    <td class="px-4 py-3"> <span v-for="r in (u.roles || [])" :key="r" class="mr-1 inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-blue-200">{{ r }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <span v-if="u.klub">Klub: {{ u.klub.nama }}</span>
                                        <span v-if="u.organisasi"> Org: {{ u.organisasi.nama }}</span>
                                        <span v-if="!u.klub && !u.organisasi" class="text-gray-400">—</span> </td> <td class="px-4 py-3 text-right">
                                        <Link :href="route('users.edit', u.id)" class="mr-2 text-sm font-medium text-blue-600 hover:text-blue-800">Edit</Link>
                                        <button @click="destroyUser(u.id)" class="text-sm font-medium text-red-600 hover:text-red-800">Hapus</button>
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
