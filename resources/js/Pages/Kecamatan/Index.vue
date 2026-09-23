<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    kecamatans: Object,
    filters: Object,
    can: Object,
});

const search = ref(props.filters?.search ?? '');
let t = null;
watch(search, (v) => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('kecamatans.index'), { search: v }, { preserveState: true, replace: true }), 300);
});

function destroyRow(id) {
    if (!confirm('Hapus kecamatan ini?')) return;
    router.delete(route('kecamatans.destroy', id));
}
</script>

<template>
    <Head title="Kecamatan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Kecamatan</h2>
                <Link v-if="can?.manage" :href="route('kecamatans.create')" class="rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Tambah Kecamatan</Link>
            </div>
        </template>

        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card overflow-hidden">
                    <div class="flex gap-3 p-4">
                        <input v-model="search" placeholder="Cari nama / kode..." class="w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400 self-center">Total: {{ kecamatans.meta?.total ?? 0 }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Nama</th> <th class="px-4 py-3">Kode</th> <th class="px-4 py-3">Kelurahan</th> <th class="px-4 py-3">Klub</th> <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="k in kecamatans.data" :key="k.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">{{ k.nama }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ k.kode ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ k.kelurahans_count ?? 0 }}</td> <td class="px-4 py-3">{{ k.klubs_count ?? 0 }}</td> <td class="px-4 py-3 text-right">
                                        <Link v-if="can?.manage" :href="route('kecamatans.edit', k.id)" class="mr-2 text-blue-600 hover:text-blue-800 text-sm font-medium dark:text-gray-100">Edit</Link>
                                        <button v-if="can?.manage" @click="destroyRow(k.id)" class="text-red-600 hover:text-red-800 text-sm font-medium dark:text-gray-100">Hapus</button>
                                        <Link v-else :href="route('kecamatans.show', k.id)" class="text-blue-600 text-sm">Lihat</Link>
                                    </td>
                                </tr>
                                <tr v-if="!kecamatans.data || kecamatans.data.length===0"><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="kecamatans.meta && kecamatans.meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300">
                        <span>Hal {{ kecamatans.meta.current_page }} / {{ kecamatans.meta.last_page }}</span>
                        <div class="flex gap-2"> <Link v-if="kecamatans.links.prev" :href="kecamatans.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link>
                            <Link v-if="kecamatans.links.next" :href="kecamatans.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
