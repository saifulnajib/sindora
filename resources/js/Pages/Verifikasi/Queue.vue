<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    queues: Object,
    counts: Object,
    filters: Object,
    can: Object,
});

const page = usePage();

const activeTab = ref(props.filters?.entity ?? 'all');
const search = ref(props.filters?.search ?? '');

const tabs = [
    { key: 'all', label: 'Semua', count: computed(() => props.counts?.all ?? 0) },
    { key: 'klub', label: 'Klub', count: computed(() => props.counts?.klub ?? 0) },
    { key: 'atlet', label: 'Atlet', count: computed(() => props.counts?.atlet ?? 0) },
    { key: 'sdm', label: 'SDM', count: computed(() => props.counts?.sdm ?? 0) },
    { key: 'sarpras', label: 'Sarpras', count: computed(() => props.counts?.sarpras ?? 0) },
];

let t = null;
watch([search], ([s]) => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get(route('verifikasi.queue'), { entity: activeTab.value, search: s }, { preserveState: true, replace: true });
    }, 300);
});

function switchTab(key) {
    activeTab.value = key;
    router.get(route('verifikasi.queue'), { entity: key, search: search.value }, { preserveState: true, replace: true });
}

// Verification Modal State
const showModal = ref(false);
const modalAction = ref('approve'); // 'approve' | 'revision' | 'reject'
const selectedEntity = ref('');
const selectedRow = ref(null);
const catatan = ref('');
const modalError = ref('');
const isProcessing = ref(false);

function openApprove(entity, row) {
    modalAction.value = 'approve';
    selectedEntity.value = entity;
    selectedRow.value = row;
    catatan.value = '';
    modalError.value = '';
    showModal.value = true;
}

function openRevision(entity, row) {
    modalAction.value = 'revision';
    selectedEntity.value = entity;
    selectedRow.value = row;
    catatan.value = '';
    modalError.value = '';
    showModal.value = true;
}

function openReject(entity, row) {
    modalAction.value = 'reject';
    selectedEntity.value = entity;
    selectedRow.value = row;
    catatan.value = '';
    modalError.value = '';
    showModal.value = true;
}

function closeModal() {
    if (isProcessing.value) return;
    showModal.value = false;
    modalError.value = '';
}

function confirmAction() {
    if (!selectedRow.value || isProcessing.value) return;
    modalError.value = '';
    const trimmedCatatan = catatan.value.trim();

    if (modalAction.value === 'revision') {
        if (!trimmedCatatan || trimmedCatatan.length < 10) {
            modalError.value = 'Catatan perbaikan wajib diisi minimal 10 karakter.';
            return;
        }
        router.post(
            route('verifikasi.requestRevision', { entity: selectedEntity.value, id: selectedRow.value.id }),
            { catatan_verifikator: trimmedCatatan },
            {
                preserveScroll: true,
                onStart: () => { isProcessing.value = true; },
                onSuccess: () => { closeModal(); },
                onError: (e) => {
                    modalError.value = e.catatan_verifikator || Object.values(e)[0] || 'Gagal meminta perbaikan.';
                },
                onFinish: () => { isProcessing.value = false; },
            }
        );
    } else if (modalAction.value === 'reject') {
        if (!trimmedCatatan || trimmedCatatan.length < 10) {
            modalError.value = 'Alasan penolakan wajib diisi minimal 10 karakter.';
            return;
        }
        router.post(
            route('verifikasi.reject', { entity: selectedEntity.value, id: selectedRow.value.id }),
            { catatan_verifikator: trimmedCatatan },
            {
                preserveScroll: true,
                onStart: () => { isProcessing.value = true; },
                onSuccess: () => { closeModal(); },
                onError: (e) => {
                    modalError.value = e.catatan_verifikator || Object.values(e)[0] || 'Gagal menolak verifikasi.';
                },
                onFinish: () => { isProcessing.value = false; },
            }
        );
    } else if (modalAction.value === 'approve') {
        if (trimmedCatatan.length > 0 && trimmedCatatan.length < 10) {
            modalError.value = 'Catatan verifikator jika diisi minimal 10 karakter.';
            return;
        }
        router.post(
            route('verifikasi.approve', { entity: selectedEntity.value, id: selectedRow.value.id }),
            { catatan_verifikator: trimmedCatatan.length > 0 ? trimmedCatatan : null },
            {
                preserveScroll: true,
                onStart: () => { isProcessing.value = true; },
                onSuccess: () => { closeModal(); },
                onError: (e) => {
                    modalError.value = e.catatan_verifikator || Object.values(e)[0] || 'Gagal menyetujui verifikasi.';
                },
                onFinish: () => { isProcessing.value = false; },
            }
        );
    }
}

function displayName(row) {
    return row?.nama ?? row?.name ?? row?.nama_program ?? `ID ${row?.id}`;
}

function displayDetail(entity, row) {
    if (!row) return '';
    if (entity === 'klub') {
        return [row.cabor?.nama, row.kecamatan?.nama ?? row.kelurahan?.nama].filter(Boolean).join(' · ');
    }
    if (entity === 'atlet') {
        return [row.klub?.nama, row.cabor?.nama, row.kelas_tanding].filter(Boolean).join(' · ');
    }
    if (entity === 'sdm') {
        return [row.tipe_label ?? row.tipe, row.klub?.nama, row.cabor?.nama].filter(Boolean).join(' · ');
    }
    if (entity === 'sarpras') {
        return [row.jenis, row.kondisi_label ?? row.kondisi, row.cabor?.nama].filter(Boolean).join(' · ');
    }
    if (entity === 'prestasi') {
        return [row.atlet?.nama, row.cabor?.nama, row.kejuaraan?.nama].filter(Boolean).join(' · ');
    }
    if (entity === 'kejuaraan') {
        return [row.penyelenggara, row.tingkat].filter(Boolean).join(' · ');
    }
    if (entity === 'pembinaan') {
        return [row.organisasi?.nama, row.tahun].filter(Boolean).join(' · ');
    }
    return '';
}

function getViewRoute(entity, id) {
    if (entity === 'sarpras') return route('sarpras.show', id);
    return route(`${entity}s.show`, id);
}

const visibleQueues = computed(() => {
    if (activeTab.value === 'all') {
        return ['klub','atlet','sdm','sarpras'];
    }
    return [activeTab.value];
});

function badgeClass(color) {
    return {
        'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200': color==='gray',
        'bg-yellow-100 text-yellow-800': color==='yellow',
        'bg-green-100 text-green-800': color==='green',
        'bg-orange-100 text-orange-800': color==='orange',
        'bg-red-100 text-red-800': color==='red',
    };
}
</script>

<template>
    <Head title="Verifikasi — Antrian" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Verifikasi — Antrian Menunggu</h2>
                <span class="rounded-full bg-yellow-50 px-3 py-1 text-xs font-semibold text-yellow-700 ring-1 ring-yellow-200">Menunggu: {{ counts?.all ?? 0 }}</span>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Search + tabs -->
                <div class="sindora-card mb-4 overflow-hidden p-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <input v-model="search" placeholder="Cari nama..." class="w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">Filter entitas:</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2 border-b">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="switchTab(tab.key)"
                            :class="['rounded-t-lg border-b-2 px-4 py-2 text-sm font-medium dark:text-gray-100', activeTab===tab.key ? 'border-[#7f1d1d] text-[#7f1d1d] bg-red-50 dark:bg-red-950/40 dark:text-red-200' : 'border-transparent text-gray-600 hover:text-gray-800 dark:hover:text-gray-200']"
                        >
                            {{ tab.label }} <span class="ml-1 rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs dark:text-gray-300">{{ typeof tab.count === 'object' ? tab.count.value : tab.count }}</span>
                        </button>
                    </div>
                </div>

                <!-- Queues -->
                <div v-for="entity in visibleQueues" :key="entity" class="sindora-card mb-6 overflow-hidden">
                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 px-4 py-3">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">{{ entity }} <span class="ml-2 text-xs font-normal text-gray-500 dark:text-gray-400">({{ queues[entity]?.meta?.total ?? queues[entity]?.data?.length ?? 0 }} menunggu)</span></h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Hal {{ queues[entity]?.meta?.current_page ?? 1 }} / {{ queues[entity]?.meta?.last_page ?? 1 }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">ID</th> <th class="px-4 py-3">Nama</th> <th class="px-4 py-3">Info</th> <th class="px-4 py-3">Status</th> <th class="px-4 py-3">Updated</th> <th v-if="can?.manage" class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="row in (queues[entity]?.data ?? [])" :key="row.entity_type + '-' + row.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 font-mono text-xs">#{{ row.id }}</td>
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">{{ displayName(row) }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">
                                        <template v-if="entity==='klub'">{{ row.cabor?.nama ?? '—' }} · {{ row.kecamatan?.nama ?? row.kelurahan?.nama ?? '—' }}</template>
                                        <template v-else-if="entity==='atlet'">{{ row.klub?.nama ?? '—' }} · {{ row.cabor?.nama ?? '—' }}</template>
                                        <template v-else-if="entity==='sdm'">{{ row.tipe_label ?? row.tipe ?? '—' }} · {{ row.klub?.nama ?? '—' }}</template>
                                        <template v-else>{{ row.jenis ?? '—' }} · {{ row.kondisi_label ?? row.kondisi ?? '—' }}</template>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs" :class="badgeClass(row.verification_color)">
                                            {{ row.verification_label ?? row.verification_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ row.updated_at ?? '—' }}</td>
                                    <td v-if="can?.manage" class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-1">
                                            <a :href="getViewRoute(entity, row.id)" target="_blank" class="rounded bg-blue-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-blue-700">Detail</a>
                                            <button @click="openApprove(entity, row)" class="rounded bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-emerald-700">Setujui</button>
                                            <button @click="openRevision(entity, row)" class="rounded bg-amber-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-amber-700">Perbaikan</button>
                                            <button @click="openReject(entity, row)" class="rounded bg-rose-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-rose-700">Tolak</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!(queues[entity]?.data) || queues[entity].data.length===0"><td :colspan="can?.manage ? 6 : 5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data menunggu verifikasi untuk {{ entity }}.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="queues[entity]?.meta && queues[entity].meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300">
                        <span>Hal {{ queues[entity].meta.current_page }} / {{ queues[entity].meta.last_page }}</span>
                        <div class="flex gap-2">
                            <Link v-if="queues[entity].links.prev" :href="queues[entity].links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link>
                            <Link v-if="queues[entity].links.next" :href="queues[entity].links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link>
                        </div>
                    </div>
                </div>

                <div v-if="visibleQueues.length===0" class="sindora-card p-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada antrian.</div>
            </div>
        </div>

        <!-- Verification Decision Modal -->
        <Modal :show="showModal" max-width="lg" @close="closeModal">
            <div class="p-6">
                <!-- Header with Icon & Title -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <!-- Icon Approve -->
                        <div
                            v-if="modalAction === 'approve'"
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 ring-4 ring-emerald-50 dark:bg-emerald-900/40 dark:text-emerald-400 dark:ring-emerald-950/30"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                        </div>
                        <!-- Icon Revision -->
                        <div
                            v-else-if="modalAction === 'revision'"
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 ring-4 ring-amber-50 dark:bg-amber-900/40 dark:text-amber-400 dark:ring-amber-950/30"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </div>
                        <!-- Icon Reject -->
                        <div
                            v-else
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 ring-4 ring-rose-50 dark:bg-rose-900/40 dark:text-rose-400 dark:ring-rose-950/30"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                                {{
                                    modalAction === 'approve'
                                        ? 'Setujui Verifikasi'
                                        : modalAction === 'revision'
                                        ? 'Minta Perbaikan Data'
                                        : 'Tolak Verifikasi'
                                }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{
                                    modalAction === 'approve'
                                        ? 'Konfirmasi persetujuan kelayakan data'
                                        : modalAction === 'revision'
                                        ? 'Berikan catatan instruksi perbaikan untuk operator'
                                        : 'Berikan alasan penolakan secara jelas'
                                }}
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        :disabled="isProcessing"
                        @click="closeModal"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Entity Information Card -->
                <div class="mt-4 rounded-xl border border-gray-100 bg-gray-50/90 p-4 dark:border-gray-700/60 dark:bg-gray-700/40">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold uppercase text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                            {{ selectedEntity }}
                        </span>
                        <span class="font-mono text-xs text-gray-500 dark:text-gray-400">ID: #{{ selectedRow?.id }}</span>
                    </div>
                    <div class="mt-2">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ displayName(selectedRow) }}
                        </div>
                        <div v-if="displayDetail(selectedEntity, selectedRow)" class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">
                            {{ displayDetail(selectedEntity, selectedRow) }}
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-gray-200/60 pt-2.5 dark:border-gray-600/50">
                        <span class="text-[11px] text-gray-500 dark:text-gray-400">Diperbarui: {{ selectedRow?.updated_at ?? '—' }}</span>
                        <a
                            v-if="selectedRow"
                            :href="getViewRoute(selectedEntity, selectedRow.id)"
                            target="_blank"
                            class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400"
                        >
                            Lihat Detail Lengkap
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Guidance Alert -->
                <div class="mt-3">
                    <div
                        v-if="modalAction === 'approve'"
                        class="rounded-lg bg-emerald-50 p-3 text-xs text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300"
                    >
                        <span class="font-semibold">Info:</span> Data yang disetujui akan berstatus <strong>Terverifikasi</strong> dan dapat digunakan dalam kegiatan resmi sistem SINDORA.
                    </div>
                    <div
                        v-else-if="modalAction === 'revision'"
                        class="rounded-lg bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-950/30 dark:text-amber-300"
                    >
                        <span class="font-semibold">Perhatian:</span> Status akan diubah menjadi <strong>Perlu Perbaikan</strong>. Catatan Anda akan diteruskan ke operator pengaju untuk segera direvisi.
                    </div>
                    <div
                        v-else
                        class="rounded-lg bg-rose-50 p-3 text-xs text-rose-800 dark:bg-rose-950/30 dark:text-rose-300"
                    >
                        <span class="font-semibold">Peringatan:</span> Status akan diubah menjadi <strong>Ditolak</strong>. Pastikan alasan penolakan sudah tepat dan dapat dipertanggungjawabkan.
                    </div>
                </div>

                <!-- Form Notes Input -->
                <div class="mt-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{
                                modalAction === 'approve'
                                    ? 'Catatan Verifikator (Opsional)'
                                    : modalAction === 'revision'
                                    ? 'Catatan Perbaikan (Wajib)'
                                    : 'Alasan Penolakan (Wajib)'
                            }}
                        </label>
                        <span
                            v-if="modalAction !== 'approve'"
                            class="text-[11px] font-medium"
                            :class="catatan.trim().length >= 10 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                        >
                            {{ catatan.trim().length }}/10 karakter min.
                        </span>
                    </div>

                    <textarea
                        v-model="catatan"
                        rows="3"
                        :disabled="isProcessing"
                        :placeholder="
                            modalAction === 'approve'
                                ? 'Tambahkan catatan opsional jika diperlukan...'
                                : modalAction === 'revision'
                                ? 'Tuliskan secara spesifik bagian data atau dokumen yang harus diperbaiki operator...'
                                : 'Tuliskan alasan lengkap penolakan verifikasi data ini...'
                        "
                        class="mt-1.5 block w-full rounded-lg border-gray-300 text-sm shadow-sm transition placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-60 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 dark:focus:border-indigo-600"
                    ></textarea>

                    <p v-if="modalError" class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-600 dark:text-rose-400">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ modalError }}</span>
                    </p>
                </div>

                <!-- Footer Buttons -->
                <div class="mt-6 flex justify-end gap-2.5">
                    <SecondaryButton :disabled="isProcessing" @click="closeModal">
                        Batal
                    </SecondaryButton>

                    <button
                        type="button"
                        :disabled="isProcessing"
                        @click="confirmAction"
                        :class="[
                            'inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-offset-2',
                            modalAction === 'approve'
                                ? 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500'
                                : modalAction === 'revision'
                                ? 'bg-amber-600 text-white hover:bg-amber-700 focus:ring-amber-500'
                                : 'bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-500'
                        ]"
                    >
                        <svg
                            v-if="isProcessing"
                            class="h-4 w-4 animate-spin text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>
                            {{
                                isProcessing
                                    ? 'Memproses...'
                                    : modalAction === 'approve'
                                    ? 'Ya, Setujui'
                                    : modalAction === 'revision'
                                    ? 'Kirim Permintaan'
                                    : 'Tolak Data'
                            }}
                        </span>
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
