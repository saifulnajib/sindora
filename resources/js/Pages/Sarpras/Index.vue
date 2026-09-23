<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    sarpras: Object,
    filters: Object,
    kelurahans: Array,
    kecamatans: Array,
    cabors: Array,
    klubs: Array,
    can: Object,
});

const search = ref(props.filters?.search ?? '');
const kondisi = ref(props.filters?.kondisi ?? '');
const cabor_id = ref(props.filters?.cabor_id ?? '');

let t = null;
watch([search, kondisi, cabor_id], ([s, k, cid]) => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('sarpras.index'), { search: s, kondisi: k, cabor_id: cid }, { preserveState: true, replace: true }), 300);
});

function destroyRow(id) {
    if (!confirm('Hapus sarpras ini?')) return;
    router.delete(route('sarpras.destroy', id));
}
</script>

<template>
    <Head title="Sarpras" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Sarpras</h2>
                <Link v-if="can?.manage" :href="route('sarpras.create')" class="rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Tambah Sarpras</Link>
            </div>
        </template>
        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card overflow-hidden">
                    <div class="flex flex-wrap gap-3 p-4">
                        <input v-model="search" placeholder="Cari nama / jenis..." class="w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <select v-model="kondisi" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Kondisi</option>
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                        </select>
                        <select v-model="cabor_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Cabor</option>
                            <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                        </select>
                        <span class="ml-auto self-center text-xs text-gray-500 dark:text-gray-400">Total: {{ sarpras.meta?.total ?? 0 }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Foto</th> <th class="px-4 py-3">Nama</th> <th class="px-4 py-3">Jenis</th> <th class="px-4 py-3">Kondisi</th> <th class="px-4 py-3">Kapasitas</th> <th class="px-4 py-3">Wilayah</th> <th class="px-4 py-3">Cabor</th> <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="s in sarpras.data" :key="s.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3"> <img v-if="s.foto_url" :src="s.foto_url" :alt="s.nama" class="h-10 w-10 rounded object-cover ring-1 ring-gray-200" />
                                        <span v-else class="inline-flex h-10 w-10 items-center justify-center rounded bg-gray-100 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400">—</span>
                                    </td>
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">{{ s.nama }}</td>
                                    <td class="px-4 py-3">{{ s.jenis ?? '—' }}</td> <td class="px-4 py-3"> <span class="rounded-full px-2 py-0.5 text-xs font-medium dark:text-gray-100"
                                              :class="{ 'bg-green-100 text-green-800': s.kondisi==='baik', 'bg-yellow-100 text-yellow-800': s.kondisi==='rusak_ringan', 'bg-red-100 text-red-800': s.kondisi==='rusak_berat', 'bg-gray-100 text-gray-600 dark:text-gray-400': !s.kondisi }">
                                            {{ s.kondisi_label ?? s.kondisi ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ s.kapasitas ?? '—' }}</td>
                                    <td class="px-4 py-3 text-xs">
                                        <div v-if="s.kelurahan || s.kecamatan">{{ s.kelurahan?.nama ?? '—' }}<span v-if="s.kecamatan"> — {{ s.kecamatan.nama }}</span></div>
                                        <div v-else class="text-gray-400">—</div> <div v-if="s.latitude && s.longitude" class="text-[11px] text-gray-500 dark:text-gray-400">{{ s.latitude }}, {{ s.longitude }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ s.cabor?.nama ?? '—' }}</td> <td class="px-4 py-3 text-right">
                                        <Link :href="route('sarpras.show', s.id)" class="mr-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Lihat</Link>
                                        <Link v-if="can?.manage" :href="route('sarpras.edit', s.id)" class="mr-2 text-sm font-medium dark:text-gray-100 text-blue-600 hover:text-blue-800">Edit</Link>
                                        <button v-if="can?.manage" @click="destroyRow(s.id)" class="text-sm font-medium dark:text-gray-100 text-red-600 hover:text-red-800">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="!sarpras.data || sarpras.data.length===0"><td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="sarpras.meta && sarpras.meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300"><span>Hal {{ sarpras.meta.current_page }} / {{ sarpras.meta.last_page }}</span><div class="flex gap-2"><Link v-if="sarpras.links.prev" :href="sarpras.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link><Link v-if="sarpras.links.next" :href="sarpras.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link></div></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
