<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    prestasis: Object,
    filters: Object,
    cabors: Array,
    kejuaraans: Array,
    atlets: Array,
    can: Object,
});

const search = ref(props.filters?.search ?? '');
const medali = ref(props.filters?.medali ?? '');
const cabor_id = ref(props.filters?.cabor_id ?? '');
const kejuaraan_id = ref(props.filters?.kejuaraan_id ?? '');
const verification_status = ref(props.filters?.verification_status ?? '');

let t = null;
watch([search, medali, cabor_id, kejuaraan_id, verification_status], ([s, m, cid, kid, vs]) => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('prestasis.index'), {
        search: s,
        medali: m,
        cabor_id: cid,
        kejuaraan_id: kid,
        verification_status: vs,
    }, { preserveState: true, replace: true }), 300);
});

function destroyRow(id) {
    if (!confirm('Hapus prestasi ini?')) return;
    router.delete(route('prestasis.destroy', id));
}

function submitRow(id) {
    if (!confirm('Ajukan prestasi ini untuk verifikasi?')) return;
    router.post(route('verifikasi.submit'), {
        entity_type: 'prestasi',
        ids: [id],
    }, { preserveScroll: true });
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

function getMedaliBadge(medaliVal) {
    const badges = {
        emas: 'bg-yellow-100 text-yellow-800',
        perak: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
        perunggu: 'bg-orange-100 text-orange-800',
        juara_harapan: 'bg-blue-100 text-blue-800',
    };
    return badges[medaliVal] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
}

function getMedaliLabel(medaliVal) {
    const labels = {
        emas: 'Emas',
        perak: 'Perak',
        perunggu: 'Perunggu',
        juara_harapan: 'Juara Harapan',
    };
    return labels[medaliVal] ?? medaliVal;
}
</script>

<template>
    <Head title="Prestasi" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Prestasi</h2>
                <Link v-if="can?.manage" :href="route('prestasis.create')" class="rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Tambah Prestasi</Link>
            </div>
        </template>
        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card overflow-hidden">
                    <div class="flex flex-wrap gap-3 p-4">
                        <input v-model="search" placeholder="Cari atlet / kejuaraan..." class="w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <select v-model="medali" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Medali</option>
                            <option value="emas">Emas</option>
                            <option value="perak">Perak</option>
                            <option value="perunggu">Perunggu</option>
                            <option value="juara_harapan">Juara Harapan</option>
                        </select>
                        <select v-model="cabor_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Cabor</option>
                            <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                        </select>
                        <select v-model="kejuaraan_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Kejuaraan</option>
                            <option v-for="k in kejuaraans" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                        <select v-model="verification_status" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                            <option value="terverifikasi">Terverifikasi</option>
                            <option value="perlu_perbaikan">Perlu Perbaikan</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                        <span class="ml-auto self-center text-xs text-gray-500 dark:text-gray-400">Total: {{ prestasis.meta?.total ?? 0 }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Atlet</th> <th class="px-4 py-3">Cabor</th> <th class="px-4 py-3">Kejuaraan</th> <th class="px-4 py-3">Medali</th> <th class="px-4 py-3">Peringkat</th> <th class="px-4 py-3">Tanggal</th> <th class="px-4 py-3">No. Sertifikat</th> <th class="px-4 py-3">Sertifikat</th> <th class="px-4 py-3">Status</th> <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="p in prestasis.data" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">{{ p.atlet?.nama ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ p.cabor?.nama ?? '—' }}</td> <td class="px-4 py-3">{{ p.kejuaraan?.nama ?? '—' }}</td> <td class="px-4 py-3"> <span :class="['rounded-full px-2 py-0.5 text-xs font-medium dark:text-gray-100', p.medali_badge ?? getMedaliBadge(p.medali)]">
                                            {{ p.medali_label ?? getMedaliLabel(p.medali) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ p.peringkat ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ p.tanggal ?? '—' }}</td> <td class="px-4 py-3 font-mono text-xs dark:text-gray-200" :title="p.nomor_sertifikat ?? ''">{{ p.nomor_sertifikat ? (p.nomor_sertifikat.length > 18 ? p.nomor_sertifikat.slice(0,18)+'…' : p.nomor_sertifikat) : '—' }}</td> <td class="px-4 py-3 text-center">
                                        <a v-if="p.sertifikat_url" :href="p.sertifikat_url" target="_blank" class="inline-flex items-center justify-center rounded p-1 text-blue-600 hover:bg-blue-50" title="Lihat sertifikat">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </a>
                                        <span v-else class="text-xs text-gray-400">—</span>
                                    </td>
                                    <td class="px-4 py-3"> <span :class="['rounded-full px-2 py-0.5 text-xs font-medium dark:text-gray-100', p.verification_badge ?? getVerificationBadge(p.verification_status)]">
                                            {{ p.verification_label ?? getVerificationLabel(p.verification_status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('prestasis.show', p.id)" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Lihat</Link>
                                            <Link v-if="can?.manage" :href="route('prestasis.edit', p.id)" class="text-sm font-medium dark:text-gray-100 text-blue-600 hover:text-blue-800">Edit</Link>
                                            <button v-if="can?.manage" @click="destroyRow(p.id)" class="text-sm font-medium dark:text-gray-100 text-red-600 hover:text-red-800">Hapus</button>
                                            <button v-if="can?.manage && (p.verification_status === 'draft' || p.verification_status === 'perlu_perbaikan')" @click="submitRow(p.id)" class="rounded bg-yellow-500 px-2 py-0.5 text-xs font-semibold text-white hover:bg-yellow-600">Submit</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!prestasis.data || prestasis.data.length===0"><td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="prestasis.meta && prestasis.meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300">
                        <span>Hal {{ prestasis.meta.current_page }} / {{ prestasis.meta.last_page }}</span>
                        <div class="flex gap-2"> <Link v-if="prestasis.links.prev" :href="prestasis.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link>
                            <Link v-if="prestasis.links.next" :href="prestasis.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
