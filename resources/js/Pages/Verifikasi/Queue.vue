<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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

const showRejectModal = ref(false);
const showRevisionModal = ref(false);
const modalEntity = ref('');
const modalId = ref(null);
const catatan = ref('');
const modalError = ref('');

function openReject(entity, id) {
    modalEntity.value = entity;
    modalId.value = id;
    catatan.value = '';
    modalError.value = '';
    showRejectModal.value = true;
}
function openRevision(entity, id) {
    modalEntity.value = entity;
    modalId.value = id;
    catatan.value = '';
    modalError.value = '';
    showRevisionModal.value = true;
}
function approveRow(entity, id) {
    if (!confirm(`Setujui ${entity} #${id}?`)) return;
    router.post(route('verifikasi.approve', { entity, id }), { catatan_verifikator: null }, { preserveScroll: true });
}
function submitReject() {
    if (!catatan.value || catatan.value.length < 10) {
        modalError.value = 'Catatan minimal 10 karakter.';
        return;
    }
    router.post(route('verifikasi.reject', { entity: modalEntity.value, id: modalId.value }), { catatan_verifikator: catatan.value }, {
        preserveScroll: true,
        onSuccess: () => { showRejectModal.value = false; catatan.value=''; },
        onError: (e) => { modalError.value = e.catatan_verifikator || 'Gagal menolak.'; }
    });
}
function submitRevision() {
    if (!catatan.value || catatan.value.length < 10) {
        modalError.value = 'Catatan minimal 10 karakter.';
        return;
    }
    router.post(route('verifikasi.requestRevision', { entity: modalEntity.value, id: modalId.value }), { catatan_verifikator: catatan.value }, {
        preserveScroll: true,
        onSuccess: () => { showRevisionModal.value = false; catatan.value=''; },
        onError: (e) => { modalError.value = e.catatan_verifikator || 'Gagal meminta perbaikan.'; }
    });
}

function displayName(row) {
    return row.nama ?? row.name ?? `ID ${row.id}`;
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
        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
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
                            :class="['rounded-t-lg border-b-2 px-4 py-2 text-sm font-medium dark:text-gray-100', activeTab===tab.key ? 'border-[#1e3a8a] text-[#1e3a8a] bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 dark:hover:text-gray-200']"
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
                                    <td class="px-4 py-3"> <span class="rounded-full px-2 py-0.5 text-xs" :class="badgeClass(row.verification_color)">
                                            {{ row.verification_label ?? row.verification_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ row.updated_at ?? '—' }}</td>
                                    <td v-if="can?.manage" class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-1">
                                            <button @click="approveRow(entity, row.id)" class="rounded bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-700">Setujui</button>
                                            <button @click="openRevision(entity, row.id)" class="rounded bg-orange-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-orange-600">Perbaikan</button>
                                            <button @click="openReject(entity, row.id)" class="rounded bg-red-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-red-700">Tolak</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!(queues[entity]?.data) || queues[entity].data.length===0"><td :colspan="can?.manage ? 6 : 5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data menunggu verifikasi untuk {{ entity }}.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="queues[entity]?.meta && queues[entity].meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300">
                        <span>Hal {{ queues[entity].meta.current_page }} / {{ queues[entity].meta.last_page }}</span>
                        <div class="flex gap-2"> <Link v-if="queues[entity].links.prev" :href="queues[entity].links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link>
                            <Link v-if="queues[entity].links.next" :href="queues[entity].links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link>
                        </div>
                    </div>
                </div>

                <div v-if="visibleQueues.length===0" class="sindora-card p-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada antrian.</div>
            </div>
        </div>

        <!-- Reject modal -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-md rounded-lg bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Tolak {{ modalEntity }} #{{ modalId }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Wajib isi catatan minimal 10 karakter.</p>
                <textarea v-model="catatan" rows="4" placeholder="Alasan penolakan..." class="mt-3 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                <p v-if="modalError" class="mt-1 text-xs text-red-600">{{ modalError }}</p>
                <div class="mt-4 flex justify-end gap-2">
                    <button @click="showRejectModal=false" class="rounded border px-4 py-2 text-sm">Batal</button>
                    <button @click="submitReject" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Tolak</button>
                </div>
            </div>
        </div>
        <!-- Revision modal -->
        <div v-if="showRevisionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-md rounded-lg bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Minta Perbaikan {{ modalEntity }} #{{ modalId }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Wajib isi catatan minimal 10 karakter.</p>
                <textarea v-model="catatan" rows="4" placeholder="Catatan perbaikan..." class="mt-3 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                <p v-if="modalError" class="mt-1 text-xs text-red-600">{{ modalError }}</p>
                <div class="mt-4 flex justify-end gap-2">
                    <button @click="showRevisionModal=false" class="rounded border px-4 py-2 text-sm">Batal</button>
                    <button @click="submitRevision" class="rounded bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">Kirim</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
