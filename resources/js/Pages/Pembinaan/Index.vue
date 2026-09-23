<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    pembinaans: Object,
    filters: Object,
    cabors: Array,
    organisasis: Array,
    can: Object,
});

const search = ref(props.filters?.search ?? '');
const tahun_anggaran = ref(props.filters?.tahun_anggaran ?? '');
const status = ref(props.filters?.status ?? '');
const organisasi_id = ref(props.filters?.organisasi_id ?? '');
const cabor_id = ref(props.filters?.cabor_id ?? '');
const verification_status = ref(props.filters?.verification_status ?? '');

let t = null;
watch([search, tahun_anggaran, status, organisasi_id, cabor_id, verification_status], ([s, ta, st, oid, cid, vs]) => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('pembinaans.index'), {
        search: s,
        tahun_anggaran: ta,
        status: st,
        organisasi_id: oid,
        cabor_id: cid,
        verification_status: vs,
    }, { preserveState: true, replace: true }), 300);
});

function destroyRow(id) {
    if (!confirm('Hapus pembinaan ini?')) return;
    router.delete(route('pembinaans.destroy', id));
}

function getVerificationBadge(statusVal) {
    const badges = {
        draft: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
        menunggu_verifikasi: 'bg-yellow-100 text-yellow-800',
        terverifikasi: 'bg-green-100 text-green-800',
        perlu_perbaikan: 'bg-orange-100 text-orange-800',
        ditolak: 'bg-red-100 text-red-800',
    };
    return badges[statusVal] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
}

function getVerificationLabel(statusVal) {
    const labels = {
        draft: 'Draft',
        menunggu_verifikasi: 'Menunggu Verifikasi',
        terverifikasi: 'Terverifikasi',
        perlu_perbaikan: 'Perlu Perbaikan',
        ditolak: 'Ditolak',
    };
    return labels[statusVal] ?? statusVal;
}

function getStatusBadge(statusVal) {
    const badges = {
        draft: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
        aktif: 'bg-blue-100 text-blue-800',
        selesai: 'bg-green-100 text-green-800',
        ditunda: 'bg-yellow-100 text-yellow-800',
        berjalan: 'bg-blue-100 text-blue-800',
        dibatalkan: 'bg-red-100 text-red-800',
    };
    return badges[statusVal] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
}
</script>

<template>
    <Head title="Pembinaan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Pembinaan</h2>
                <Link v-if="can?.manage" :href="route('pembinaans.create')" class="rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Tambah Pembinaan</Link>
            </div>
        </template>
        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card overflow-hidden">
                    <div class="flex flex-wrap gap-3 p-4">
                        <input v-model="search" placeholder="Cari nama program..." class="w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <select v-model="tahun_anggaran" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Tahun</option>
                            <option v-for="y in [2020,2021,2022,2023,2024,2025,2026,2027,2028,2029,2030]" :key="y" :value="y">{{ y }}</option>
                        </select>
                        <select v-model="status" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="aktif">Aktif</option>
                            <option value="selesai">Selesai</option>
                            <option value="ditunda">Ditunda</option>
                        </select>
                        <select v-model="organisasi_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Organisasi</option>
                            <option v-for="o in organisasis" :key="o.id" :value="o.id">{{ o.nama }}</option>
                        </select>
                        <select v-model="cabor_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Cabor</option>
                            <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                        </select>
                        <select v-model="verification_status" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Verifikasi</option>
                            <option value="draft">Draft</option>
                            <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                            <option value="terverifikasi">Terverifikasi</option>
                            <option value="perlu_perbaikan">Perlu Perbaikan</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                        <span class="ml-auto self-center text-xs text-gray-500 dark:text-gray-400">Total: {{ pembinaans.meta?.total ?? 0 }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Nama Program</th> <th class="px-4 py-3">Anggaran</th> <th class="px-4 py-3">Periode</th> <th class="px-4 py-3">Status</th> <th class="px-4 py-3">Verifikasi</th> <th class="px-4 py-3">Organisasi / Cabor</th> <th class="px-4 py-3 text-center">Peserta</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="p in pembinaans.data" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">
                                        <div>{{ p.nama_program }}</div>
                                        <div v-if="p.sumber_anggaran" class="text-xs text-gray-500 dark:text-gray-400">{{ p.sumber_anggaran }} · {{ p.tahun_anggaran ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ p.anggaran_formatted ?? '—' }}</td> <td class="px-4 py-3">{{ p.periode ?? '—' }}</td> <td class="px-4 py-3"> <span :class="['rounded-full px-2 py-0.5 text-xs font-medium dark:text-gray-100', getStatusBadge(p.status)]">{{ p.status ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-3"> <span :class="['rounded-full px-2 py-0.5 text-xs font-medium dark:text-gray-100', p.verification_badge ?? getVerificationBadge(p.verification_status)]">
                                            {{ p.verification_label ?? getVerificationLabel(p.verification_status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3"> <div class="text-xs">{{ p.organisasi?.nama ?? '—' }}</div> <div class="text-xs text-gray-500 dark:text-gray-400">{{ p.cabor?.nama ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs">
                                        <span class="inline-flex gap-1">
                                            <span class="rounded bg-blue-50 px-1.5 py-0.5 text-blue-700" title="Atlet">{{ p.atlets_count ?? p.peserta_counts?.atlets_count ?? 0 }}A</span>
                                            <span class="rounded bg-green-50 px-1.5 py-0.5 text-green-700" title="Klub">{{ p.klubs_count ?? p.peserta_counts?.klubs_count ?? 0 }}K</span>
                                            <span class="rounded bg-purple-50 px-1.5 py-0.5 text-purple-700" title="SDM">{{ p.sdms_count ?? p.peserta_counts?.sdms_count ?? 0 }}S</span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <Link :href="route('pembinaans.show', p.id)" class="mr-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Lihat</Link>
                                        <Link v-if="can?.manage" :href="route('pembinaans.edit', p.id)" class="mr-2 text-sm font-medium dark:text-gray-100 text-blue-600 hover:text-blue-800">Edit</Link>
                                        <button v-if="can?.manage" @click="destroyRow(p.id)" class="text-sm font-medium dark:text-gray-100 text-red-600 hover:text-red-800">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="!pembinaans.data || pembinaans.data.length===0"><td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="pembinaans.meta && pembinaans.meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300">
                        <span>Hal {{ pembinaans.meta.current_page }} / {{ pembinaans.meta.last_page }}</span>
                        <div class="flex gap-2"> <Link v-if="pembinaans.links.prev" :href="pembinaans.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link>
                            <Link v-if="pembinaans.links.next" :href="pembinaans.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
