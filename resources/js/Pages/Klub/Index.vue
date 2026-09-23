<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    klubs: Object,
    filters: Object,
    cabors: Array,
    kelurahans: Array,
    kecamatans: Array,
    can: Object,
});

const search = ref(props.filters?.search ?? '');
const cabor_id = ref(props.filters?.cabor_id ?? '');

let t = null;
watch([search, cabor_id], ([s, cid]) => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('klubs.index'), { search: s, cabor_id: cid }, { preserveState: true, replace: true }), 300);
});

function destroyRow(id) {
    if (!confirm('Hapus klub ini?')) return;
    router.delete(route('klubs.destroy', id));
}
</script>

<template>
    <Head title="Klub" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Klub</h2>
                <Link v-if="can?.manage" :href="route('klubs.create')" class="rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Tambah Klub</Link>
            </div>
        </template>
        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card overflow-hidden">
                    <div class="flex flex-wrap gap-3 p-4">
                        <input v-model="search" placeholder="Cari nama / ketua..." class="w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <select v-model="cabor_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Cabor</option>
                            <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                        </select>
                        <span class="ml-auto self-center text-xs text-gray-500 dark:text-gray-400">Total: {{ klubs.meta?.total ?? 0 }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Logo</th> <th class="px-4 py-3">Nama</th> <th class="px-4 py-3">Cabor</th> <th class="px-4 py-3">Wilayah</th> <th class="px-4 py-3">Ketua</th> <th class="px-4 py-3">Status</th> <th class="px-4 py-3">Atlet</th> <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="k in klubs.data" :key="k.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3"> <img v-if="k.logo_url" :src="k.logo_url" :alt="k.nama" class="h-8 w-8 rounded object-cover ring-1 ring-gray-200" />
                                        <span v-else class="inline-flex h-8 w-8 items-center justify-center rounded bg-gray-100 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400">—</span>
                                    </td>
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">{{ k.nama }}</td>
                                    <td class="px-4 py-3">{{ k.cabor?.nama ?? '—' }}</td> <td class="px-4 py-3 text-xs">{{ k.kelurahan?.nama ?? '—' }}<span v-if="k.kecamatan"> — {{ k.kecamatan.nama }}</span></td>
                                    <td class="px-4 py-3">{{ k.ketua ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs"
                                              :class="{ 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200': k.verification_color==='gray', 'bg-yellow-100 text-yellow-800': k.verification_color==='yellow', 'bg-green-100 text-green-800': k.verification_color==='green', 'bg-orange-100 text-orange-800': k.verification_color==='orange', 'bg-red-100 text-red-800': k.verification_color==='red', }">
                                            {{ k.verification_label ?? k.verification_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ k.atlets_count ?? 0 }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <Link :href="route('klubs.show', k.id)" class="mr-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Lihat</Link>
                                        <Link v-if="can?.manage" :href="route('klubs.edit', k.id)" class="mr-2 text-sm font-medium dark:text-gray-100 text-blue-600 hover:text-blue-800">Edit</Link>
                                        <button v-if="can?.manage" @click="destroyRow(k.id)" class="text-sm font-medium dark:text-gray-100 text-red-600 hover:text-red-800">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="!klubs.data || klubs.data.length===0"><td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="klubs.meta && klubs.meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300"><span>Hal {{ klubs.meta.current_page }} / {{ klubs.meta.last_page }}</span><div class="flex gap-2"><Link v-if="klubs.links.prev" :href="klubs.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link><Link v-if="klubs.links.next" :href="klubs.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link></div></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
