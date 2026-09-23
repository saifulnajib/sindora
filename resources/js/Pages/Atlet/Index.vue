<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    atlets: Object,
    filters: Object,
    klubs: Array,
    cabors: Array,
    can: Object,
});

const search = ref(props.filters?.search ?? '');
const klub_id = ref(props.filters?.klub_id ?? '');
const cabor_id = ref(props.filters?.cabor_id ?? '');
const status_pembinaan = ref(props.filters?.status_pembinaan ?? '');

let t = null;
watch([search, klub_id, cabor_id, status_pembinaan], ([s, kid, cid, sp]) => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('atlets.index'), { search: s, klub_id: kid, cabor_id: cid, status_pembinaan: sp }, { preserveState: true, replace: true }), 300);
});

function destroyRow(id) {
    if (!confirm('Hapus atlet ini?')) return;
    router.delete(route('atlets.destroy', id));
}
</script>

<template>
    <Head title="Atlet" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Atlet</h2>
                <Link v-if="can?.manage" :href="route('atlets.create')" class="rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Tambah Atlet</Link>
            </div>
        </template>
        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card overflow-hidden">
                    <div class="flex flex-wrap gap-3 p-4">
                        <input v-model="search" placeholder="Cari nama / kelas..." class="w-56 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <select v-model="klub_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Klub</option>
                            <option v-for="k in klubs" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                        <select v-model="cabor_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Cabor</option>
                            <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                        </select>
                        <select v-model="status_pembinaan" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Status</option>
                            <option value="daerah">Daerah</option>
                            <option value="provinsi">Provinsi</option>
                            <option value="nasional">Nasional</option>
                        </select>
                        <span class="ml-auto self-center text-xs text-gray-500 dark:text-gray-400">Total: {{ atlets.meta?.total ?? 0 }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Foto</th> <th class="px-4 py-3">Nama</th> <th class="px-4 py-3">NIK</th> <th class="px-4 py-3">Klub</th> <th class="px-4 py-3">Cabor</th> <th class="px-4 py-3">Status</th> <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="a in atlets.data" :key="a.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3"> <img v-if="a.foto_url" :src="a.foto_url" :alt="a.nama" class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-200" />
                                        <span v-else class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400">{{ a.nama.charAt(0).toUpperCase() }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">{{ a.nama }}</td>
                                    <td class="px-4 py-3 font-mono text-xs" :title="a.can_view_sensitive ? 'full' : 'masked'">{{ a.nik }}</td>
                                    <td class="px-4 py-3">{{ a.klub?.nama ?? '—' }}</td> <td class="px-4 py-3">{{ a.cabor?.nama ?? '—' }}</td> <td class="px-4 py-3"><span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-700">{{ a.status_pembinaan ?? '—' }}</span></td>
                                    <td class="px-4 py-3 text-right">
                                        <Link :href="route('atlets.show', a.id)" class="mr-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Lihat</Link>
                                        <Link v-if="can?.manage" :href="route('atlets.edit', a.id)" class="mr-2 text-sm font-medium dark:text-gray-100 text-blue-600 hover:text-blue-800">Edit</Link>
                                        <button v-if="can?.manage" @click="destroyRow(a.id)" class="text-sm font-medium dark:text-gray-100 text-red-600 hover:text-red-800">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="!atlets.data || atlets.data.length===0"><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="atlets.meta && atlets.meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300"><span>Hal {{ atlets.meta.current_page }} / {{ atlets.meta.last_page }}</span><div class="flex gap-2"><Link v-if="atlets.links.prev" :href="atlets.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link><Link v-if="atlets.links.next" :href="atlets.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link></div></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
