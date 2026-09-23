<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    kelurahans: Object,
    filters: Object,
    kecamatans: Array,
    can: Object,
});

const search = ref(props.filters?.search ?? '');
const kecamatan_id = ref(props.filters?.kecamatan_id ?? '');

let t=null;
watch([search, kecamatan_id], ([s, kid]) => {
    clearTimeout(t);
    t=setTimeout(()=> router.get(route('kelurahans.index'), { search: s, kecamatan_id: kid }, { preserveState:true, replace:true }),300);
});

function destroyRow(id){ if(!confirm('Hapus kelurahan ini?')) return; router.delete(route('kelurahans.destroy', id)); }
</script>

<template>
    <Head title="Kelurahan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Kelurahan</h2>
                <Link v-if="can?.manage" :href="route('kelurahans.create')" class="rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Tambah Kelurahan</Link>
            </div>
        </template>
        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card overflow-hidden">
                    <div class="flex flex-wrap gap-3 p-4">
                        <input v-model="search" placeholder="Cari nama / kode..." class="w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <select v-model="kecamatan_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Kecamatan</option>
                            <option v-for="k in kecamatans" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400 self-center">Total: {{ kelurahans.meta?.total ?? 0 }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Kode</th><th class="px-4 py-3">Kecamatan</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="k in kelurahans.data" :key="k.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">{{ k.nama }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ k.kode ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ k.kecamatan?.nama ?? '—' }}</td> <td class="px-4 py-3 text-right">
                                        <Link v-if="can?.manage" :href="route('kelurahans.edit', k.id)" class="mr-2 text-sm font-medium dark:text-gray-100 text-blue-600 hover:text-blue-800">Edit</Link>
                                        <button v-if="can?.manage" @click="destroyRow(k.id)" class="text-sm font-medium dark:text-gray-100 text-red-600 hover:text-red-800">Hapus</button>
                                        <Link v-else :href="route('kelurahans.show', k.id)" class="text-sm text-blue-600">Lihat</Link>
                                    </td>
                                </tr>
                                <tr v-if="!kelurahans.data || kelurahans.data.length===0"><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="kelurahans.meta && kelurahans.meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300"><span>Hal {{ kelurahans.meta.current_page }} / {{ kelurahans.meta.last_page }}</span><div class="flex gap-2"><Link v-if="kelurahans.links.prev" :href="kelurahans.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link><Link v-if="kelurahans.links.next" :href="kelurahans.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link></div></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
