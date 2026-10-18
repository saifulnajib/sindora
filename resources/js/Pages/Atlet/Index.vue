<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useConfirm } from '@/Composables/useConfirm';

const { deleteRecord } = useConfirm();

const props = defineProps({
    atlets: Object,
    filters: Object,
    klubs: Array,
    cabors: Array,
    kategoriList: Array,
    can: Object,
});

const search = ref(props.filters?.search ?? '');
const klub_id = ref(props.filters?.klub_id ?? '');
const cabor_id = ref(props.filters?.cabor_id ?? '');
const kategori = ref(props.filters?.kategori ?? '');
const status_pembinaan = ref(props.filters?.status_pembinaan ?? '');
const verification_status = ref(props.filters?.verification_status ?? '');

let t = null;
watch([search, klub_id, cabor_id, kategori, status_pembinaan, verification_status], ([s, kid, cid, kat, sp, vs]) => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('atlets.index'), { search: s, klub_id: kid, cabor_id: cid, kategori: kat, status_pembinaan: sp, verification_status: vs }, { preserveState: true, replace: true }), 300);
});

function destroyRow(id, name) {
    deleteRecord(route('atlets.destroy', id), {
        title: 'Hapus Data Atlet',
        message: 'Apakah Anda yakin ingin menghapus data atlet ini?',
        itemName: name,
        warningDetail: 'Profil atlet, riwayat prestasi, dan keanggotaan cabor terkait akan ikut terhapus.',
        confirmText: 'Ya, Hapus Atlet',
    });
}

const showSubmitModal = ref(false);
const submittingAtlet = ref(null);
const isSubmitting = ref(false);

function openSubmitModal(atlet) {
    submittingAtlet.value = atlet;
    showSubmitModal.value = true;
}

function confirmSubmit() {
    if (!submittingAtlet.value) return;
    router.post(route('verifikasi.submit'), {
        entity_type: 'atlet',
        ids: [submittingAtlet.value.id],
    }, {
        preserveScroll: true,
        onStart: () => { isSubmitting.value = true; },
        onSuccess: () => { showSubmitModal.value = false; },
        onFinish: () => { isSubmitting.value = false; },
    });
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
    <Head title="Atlet" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Atlet</h2>
                <Link v-if="can?.manage" :href="route('atlets.create')" class="rounded-lg bg-red-800 dark:bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-900 dark:hover:bg-red-600 shadow-sm">Tambah Atlet</Link>
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
                        <select v-model="kategori" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Kategori (Bidang)</option>
                            <option v-for="k in kategoriList" :key="k.value" :value="k.value">{{ k.label }}</option>
                        </select>
                        <select v-model="status_pembinaan" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Status</option>
                            <option value="daerah">Daerah</option>
                            <option value="provinsi">Provinsi</option>
                            <option value="nasional">Nasional</option>
                        </select>
                        <select v-model="verification_status" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Verifikasi</option>
                            <option value="draft">Draft</option>
                            <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                            <option value="terverifikasi">Terverifikasi</option>
                            <option value="perlu_perbaikan">Perlu Perbaikan</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                        <span class="ml-auto self-center text-xs text-gray-500 dark:text-gray-400">Total: {{ atlets.meta?.total ?? 0 }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Foto</th> <th class="px-4 py-3">Nama</th> <th class="px-4 py-3">NIK</th> <th class="px-4 py-3">Klub</th> <th class="px-4 py-3">Cabor & Bidang</th> <th class="px-4 py-3">Status</th> <th class="px-4 py-3">Verifikasi</th> <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="a in atlets.data" :key="a.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3"> <img v-if="a.foto_url" :src="a.foto_url" :alt="a.nama" class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-200" />
                                        <span v-else class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400">{{ a.nama.charAt(0).toUpperCase() }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">{{ a.nama }}</td>
                                    <td class="px-4 py-3 font-mono text-xs" :title="a.can_view_sensitive ? 'full' : 'masked'">{{ a.nik }}</td>
                                    <td class="px-4 py-3">
                                        <div v-if="a.cabors && a.cabors.length" class="space-y-0.5">
                                            <div v-for="(c, cIdx) in a.cabors" :key="cIdx" class="text-xs text-gray-700 dark:text-gray-300 truncate max-w-[140px]">
                                                <span v-if="c.klub_nama" :title="c.klub_nama">{{ c.klub_nama }}</span>
                                                <span v-else class="text-gray-400 italic">Tanpa klub</span>
                                            </div>
                                        </div>
                                        <span v-else>{{ a.klub?.nama ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="space-y-1">
                                            <div v-if="a.cabors && a.cabors.length" class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="(c, cIdx) in a.cabors"
                                                    :key="cIdx"
                                                    class="inline-flex items-center rounded-md bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-800 ring-1 ring-inset ring-red-200 dark:bg-red-950/60 dark:text-red-300 dark:ring-red-900/60"
                                                    :title="c.kelas_tanding ? `Kelas: ${c.kelas_tanding}` : ''"
                                                >
                                                    {{ c.cabor_nama }}
                                                    <span v-if="c.kelas_tanding" class="ml-1 text-[10px] text-red-600 dark:text-red-400 font-normal">({{ c.kelas_tanding }})</span>
                                                </span>
                                            </div>
                                            <span v-else class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ a.cabor?.nama ?? '—' }}</span>

                                            <!-- Badge Kategori Bidang (KONI, KORMI, DISABILITAS) -->
                                            <div v-if="a.cabor_kategori" class="mt-0.5">
                                                <span class="inline-flex items-center rounded-full px-2 py-0.2 text-[9.5px] font-bold uppercase tracking-wider ring-1 ring-inset"
                                                    :class="{
                                                        'bg-red-100 text-red-800 ring-red-200 dark:bg-red-950/80 dark:text-red-300 dark:ring-red-800': a.cabor_kategori === 'koni',
                                                        'bg-amber-100 text-amber-800 ring-amber-200 dark:bg-amber-950/80 dark:text-amber-300 dark:ring-amber-800': a.cabor_kategori === 'kormi',
                                                        'bg-blue-100 text-blue-800 ring-blue-200 dark:bg-blue-950/80 dark:text-blue-300 dark:ring-blue-800': a.cabor_kategori === 'disabilitas',
                                                    }">
                                                    {{ a.cabor_kategori }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3"><span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-700">{{ a.status_pembinaan ?? '—' }}</span></td>
                                    <td class="px-4 py-3">
                                        <span :class="['rounded-full px-2 py-0.5 text-xs font-medium dark:text-gray-100', a.verification_badge ?? getVerificationBadge(a.verification_status)]">
                                            {{ a.verification_label ?? getVerificationLabel(a.verification_status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('atlets.show', a.id)" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Lihat</Link>
                                            <Link v-if="can?.manage" :href="route('atlets.edit', a.id)" class="text-sm font-medium dark:text-gray-100 text-blue-600 hover:text-blue-800">Edit</Link>
                                            <button v-if="can?.manage" @click="destroyRow(a.id, a.nama)" class="text-sm font-medium dark:text-gray-100 text-red-600 hover:text-red-800">Hapus</button>
                                            <button v-if="can?.manage && (a.verification_status === 'draft' || a.verification_status === 'perlu_perbaikan')" @click="openSubmitModal(a)" class="rounded bg-amber-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-amber-600">Ajukan Verifikasi</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!atlets.data || atlets.data.length===0"><td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="atlets.meta && atlets.meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300"><span>Hal {{ atlets.meta.current_page }} / {{ atlets.meta.last_page }}</span><div class="flex gap-2"><Link v-if="atlets.links.prev" :href="atlets.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link><Link v-if="atlets.links.next" :href="atlets.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link></div></div>
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="showSubmitModal"
            title="Ajukan Verifikasi Atlet"
            :message="`Apakah Anda yakin ingin mengajukan atlet '${submittingAtlet?.nama}' untuk verifikasi? Data akan masuk ke antrian verifikator.`"
            confirm-text="Ya, Ajukan"
            cancel-text="Batal"
            type="warning"
            :processing="isSubmitting"
            @close="showSubmitModal = false"
            @confirm="confirmSubmit"
        />
    </AuthenticatedLayout>
</template>
