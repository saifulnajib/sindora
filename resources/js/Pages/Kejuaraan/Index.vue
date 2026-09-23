<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    kejuaraans: Object,
    filters: Object,
    cabors: Array,
    organisasis: Array,
    can: Object,
});

const search = ref(props.filters?.search ?? '');
const tingkat = ref(props.filters?.tingkat ?? '');
const cabor_id = ref(props.filters?.cabor_id ?? '');
const organisasi_id = ref(props.filters?.organisasi_id ?? '');
const date_from = ref(props.filters?.date_from ?? '');
const date_to = ref(props.filters?.date_to ?? '');
const verification_status = ref(props.filters?.verification_status ?? '');

let t = null;
watch([search, tingkat, cabor_id, organisasi_id, date_from, date_to, verification_status], ([s, tkt, cid, oid, df, dt, vs]) => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('kejuaraans.index'), {
        search: s,
        tingkat: tkt,
        cabor_id: cid,
        organisasi_id: oid,
        date_from: df,
        date_to: dt,
        verification_status: vs,
    }, { preserveState: true, replace: true }), 300);
});

function destroyRow(id) {
    if (!confirm('Hapus kejuaraan ini?')) return;
    router.delete(route('kejuaraans.destroy', id));
}

function getVerificationBadge(status) {
    const badges = {
        draft: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
        menunggu_verifikasi: 'bg-yellow-100 text-yellow-800',
        terverifikasi: 'bg-green-100 text-green-800',
        perlu_perbaikan: 'bg-orange-100 text-orange-800',
        ditolak: 'bg-red-100 text-red-800',
    };
    return badges[status] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
}

function getVerificationLabel(status) {
    const labels = {
        draft: 'Draft',
        menunggu_verifikasi: 'Menunggu Verifikasi',
        terverifikasi: 'Terverifikasi',
        perlu_perbaikan: 'Perlu Perbaikan',
        ditolak: 'Ditolak',
    };
    return labels[status] ?? status;
}
</script>

<template>
    <Head title="Kejuaraan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Kejuaraan</h2>
                <Link v-if="can?.manage" :href="route('kejuaraans.create')" class="rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Tambah Kejuaraan</Link>
            </div>
        </template>
        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card overflow-hidden">
                    <div class="flex flex-wrap gap-3 p-4">
                        <input v-model="search" placeholder="Cari nama / penyelenggara..." class="w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <select v-model="tingkat" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Tingkat</option>
                            <option value="kabupaten">Kabupaten</option>
                            <option value="kota">Kota</option>
                            <option value="kabupaten_kota">Kabupaten/Kota</option>
                            <option value="kecamatan">Kecamatan</option>
                            <option value="provinsi">Provinsi</option>
                            <option value="nasional">Nasional</option>
                            <option value="internasional">Internasional</option>
                        </select>
                        <select v-model="cabor_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Cabor</option>
                            <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                        </select>
                        <select v-model="organisasi_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Organisasi</option>
                            <option v-for="o in organisasis" :key="o.id" :value="o.id">{{ o.nama }}</option>
                        </select>
                        <input v-model="date_from" type="date" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" placeholder="Tgl Mulai" />
                        <input v-model="date_to" type="date" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" placeholder="Tgl Selesai" />
                        <select v-model="verification_status" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                            <option value="terverifikasi">Terverifikasi</option>
                            <option value="perlu_perbaikan">Perlu Perbaikan</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                        <span class="ml-auto self-center text-xs text-gray-500 dark:text-gray-400">Total: {{ kejuaraans.meta?.total ?? 0 }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Poster</th> <th class="px-4 py-3">Nama</th> <th class="px-4 py-3">Jenis</th> <th class="px-4 py-3">Tingkat</th> <th class="px-4 py-3">Penyelenggara</th> <th class="px-4 py-3">Tanggal</th> <th class="px-4 py-3">Status</th> <th class="px-4 py-3">Prestasi</th> <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="k in kejuaraans.data" :key="k.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3"> <img v-if="k.poster_url" :src="k.poster_url" alt="poster" class="h-10 w-auto rounded object-cover ring-1 ring-gray-200" />
                                        <span v-else class="inline-flex h-10 w-10 items-center justify-center rounded bg-gray-100 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400">—</span>
                                    </td>
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">{{ k.nama }}</td>
                                    <td class="px-4 py-3">{{ k.jenis ?? '—' }}</td> <td class="px-4 py-3"> <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-700">{{ k.tingkat }}</span>
                                    </td>
                                    <td class="px-4 py-3">{{ k.penyelenggara }}</td> <td class="px-4 py-3">{{ k.tanggal_range ?? (k.tanggal_mulai + ' - ' + k.tanggal_selesai) }}</td> <td class="px-4 py-3"> <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', getVerificationBadge(k.verification_status)]">
                                            {{ getVerificationLabel(k.verification_status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ k.prestasis_count ?? 0 }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <Link :href="route('kejuaraans.show', k.id)" class="mr-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Lihat</Link>
                                        <Link v-if="can?.manage" :href="route('kejuaraans.edit', k.id)" class="mr-2 text-sm font-medium dark:text-gray-100 text-blue-600 hover:text-blue-800">Edit</Link>
                                        <button v-if="can?.manage" @click="destroyRow(k.id)" class="text-sm font-medium dark:text-gray-100 text-red-600 hover:text-red-800">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="!kejuaraans.data || kejuaraans.data.length===0"><td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="kejuaraans.meta && kejuaraans.meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300">
                        <span>Hal {{ kejuaraans.meta.current_page }} / {{ kejuaraans.meta.last_page }}</span>
                        <div class="flex gap-2"> <Link v-if="kejuaraans.links.prev" :href="kejuaraans.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link>
                            <Link v-if="kejuaraans.links.next" :href="kejuaraans.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>