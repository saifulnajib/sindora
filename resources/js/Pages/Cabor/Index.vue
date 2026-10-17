<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useConfirm } from '@/Composables/useConfirm';

const { deleteRecord } = useConfirm();

const props = defineProps({
    cabors: Object,
    filters: Object,
    organisasis: Array,
    kategoriList: Array,
    can: Object,
});

const search = ref(props.filters?.search ?? '');
const organisasi_id = ref(props.filters?.organisasi_id ?? '');
const kategori = ref(props.filters?.kategori ?? '');

let debounceTimer = null;
watch([search, organisasi_id, kategori], ([s, oid, kat]) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(route('cabors.index'), { search: s, organisasi_id: oid, kategori: kat }, { preserveState: true, replace: true });
    }, 300);
});

function destroyRow(id, name) {
    deleteRecord(route('cabors.destroy', id), {
        title: 'Hapus Cabang Olahraga',
        message: 'Apakah Anda yakin ingin menghapus cabang olahraga ini?',
        itemName: name,
        warningDetail: 'Seluruh data klub binaan, atlet, dan prestasi di bawah cabor ini akan ikut terhapus.',
        confirmText: 'Ya, Hapus Cabor',
    });
}

function getKategoriBadge(kat) {
    const k = (kat || '').toLowerCase();
    if (k === 'koni') return 'bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-300 ring-red-300';
    if (k === 'kormi') return 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 ring-amber-300';
    if (k === 'disabilitas') return 'bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300 ring-blue-300';
    return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 ring-gray-300';
}
</script>

<template>
    <Head title="Cabang Olahraga & SK Kepengurusan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                        Cabang Olahraga & Kepengurusan (Pengkot)
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Kelola data cabor, SK kepengurusan pengkot, masa berlaku, serta logo organisasi
                    </p>
                </div>

                <div v-if="can?.create">
                    <Link
                        :href="route('cabors.create')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-red-800 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Cabor
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">

                <!-- Filter & Search Toolbar -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative flex-1 min-w-[220px]">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari nama cabor, kode, nomor SK, ketua..."
                                class="w-full rounded-lg border-gray-300 py-2 pl-9 pr-4 text-xs text-gray-900 placeholder-gray-400 focus:border-red-500 focus:ring-1 focus:ring-red-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>

                        <select
                            v-model="organisasi_id"
                            class="rounded-lg border-gray-300 py-2 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-red-500 focus:ring-red-500"
                        >
                            <option value="">Semua Induk Organisasi</option>
                            <option v-for="o in organisasis" :key="o.id" :value="o.id">
                                {{ o.nama }} ({{ o.singkatan ?? '-' }})
                            </option>
                        </select>

                        <select
                            v-model="kategori"
                            class="rounded-lg border-gray-300 py-2 text-xs font-medium dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-red-500 focus:ring-red-500"
                        >
                            <option value="">Semua Kategori (Bidang)</option>
                            <option value="koni">KONI (Olahraga Prestasi)</option>
                            <option value="kormi">KORMI (Olahraga Rekreasi / Masyarakat)</option>
                            <option value="disabilitas">DISABILITAS (Olahraga Disabilitas / NPCI)</option>
                        </select>

                        <span class="ml-auto text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Total: {{ cabors.meta?.total ?? 0 }} Cabor
                        </span>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-gray-200 bg-gray-50/90 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-900/90 dark:text-gray-400">
                                <tr>
                                    <th class="py-3 px-4">Cabang Olahraga</th>
                                    <th class="py-3 px-4">Bidang & Induk</th>
                                    <th class="py-3 px-4">Dokumen SK Kepengurusan</th>
                                    <th class="py-3 px-4">Masa Berlaku SK</th>
                                    <th class="py-3 px-4 text-center">Data Binaan</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr
                                    v-for="c in cabors.data"
                                    :key="c.id"
                                    class="hover:bg-red-50/30 transition dark:hover:bg-red-950/20"
                                >
                                    <!-- Cabor & Logo -->
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 overflow-hidden dark:border-gray-700 dark:bg-gray-900">
                                                <img v-if="c.logo_url" :src="c.logo_url" :alt="c.nama" class="h-full w-full object-contain p-0.5" />
                                                <span v-else class="text-xs font-bold text-gray-400">
                                                    {{ (c.kode || c.nama || 'C').substring(0, 3).toUpperCase() }}
                                                </span>
                                            </div>

                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-gray-900 dark:text-gray-100">
                                                        {{ c.nama }}
                                                    </span>
                                                    <span v-if="c.kode" class="rounded bg-gray-100 px-1.5 py-0.2 font-mono text-[10px] text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                        {{ c.kode }}
                                                    </span>
                                                </div>
                                                <p v-if="c.nama_ketua" class="text-[11px] text-gray-500 dark:text-gray-400">
                                                    Ketua: <span class="font-medium text-gray-700 dark:text-gray-300">{{ c.nama_ketua }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Bidang & Organisasi -->
                                    <td class="py-3 px-4">
                                        <div>
                                            <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10.5px] font-bold ring-1 ring-inset uppercase tracking-wide', getKategoriBadge(c.kategori)]">
                                                {{ c.kategori_label || c.kategori || '—' }}
                                            </span>
                                            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                                {{ c.organisasi?.nama ?? '—' }}
                                            </p>
                                        </div>
                                    </td>

                                    <!-- SK Kepengurusan & File Link -->
                                    <td class="py-3 px-4">
                                        <div class="flex items-start gap-2">
                                            <div class="flex-1">
                                                <div v-if="c.sk_nomor" class="font-mono text-xs font-semibold text-gray-800 dark:text-gray-200">
                                                    {{ c.sk_nomor }}
                                                </div>
                                                <div v-else class="text-gray-400 italic text-[11px]">
                                                    Belum ada nomor SK
                                                </div>

                                                <!-- Download Link Button -->
                                                <a
                                                    v-if="c.sk_file_url"
                                                    :href="c.sk_file_url"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-red-700 hover:text-red-900 hover:underline dark:text-red-400"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Unduh Dokumen SK (PDF)
                                                </a>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Masa Berlaku SK Badge -->
                                    <td class="py-3 px-4">
                                        <div>
                                            <span :class="['inline-flex items-center rounded-md px-2 py-0.5 text-[10.5px] font-bold ring-1 ring-inset', c.sk_status_badge]">
                                                {{ c.sk_status_label }}
                                            </span>
                                            <div v-if="c.sk_tgl_mulai && c.sk_tgl_selesai" class="mt-1 text-[10px] text-gray-400">
                                                TMT: {{ c.sk_tgl_mulai }} s/d {{ c.sk_tgl_selesai }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Counter Klub & Atlet -->
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2 text-[11px]">
                                            <span class="rounded bg-gray-100 px-1.5 py-0.5 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                <b>{{ c.klubs_count ?? 0 }}</b> klub
                                            </span>
                                            <span class="rounded bg-gray-100 px-1.5 py-0.5 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                <b>{{ c.atlets_count ?? 0 }}</b> atlet
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <Link
                                                :href="route('cabors.show', c.id)"
                                                class="rounded px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700"
                                            >
                                                Lihat
                                            </Link>

                                            <Link
                                                v-if="c.can_update"
                                                :href="route('cabors.edit', c.id)"
                                                class="rounded bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300 dark:hover:bg-red-900/60"
                                            >
                                                Kelola SK & Logo
                                            </Link>

                                            <button
                                                v-if="c.can_delete"
                                                @click="destroyRow(c.id, c.nama)"
                                                class="rounded px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 hover:text-red-800 dark:hover:bg-red-950/40"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="!cabors.data || cabors.data.length === 0">
                                    <td colspan="6" class="py-12 text-center text-xs text-gray-500 dark:text-gray-400">
                                        <p class="font-semibold text-sm">Tidak ada cabang olahraga yang sesuai kriteria.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="cabors.meta && cabors.meta.last_page > 1"
                        class="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-xs text-gray-600 dark:border-gray-700 dark:text-gray-300"
                    >
                        <span>Halaman {{ cabors.meta.current_page }} dari {{ cabors.meta.last_page }} (Total {{ cabors.meta.total }} cabor)</span>
                        <div class="flex gap-2">
                            <Link
                                v-if="cabors.links.prev"
                                :href="cabors.links.prev"
                                preserve-state
                                class="rounded border border-gray-300 px-3 py-1 font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                            >
                                Sebelumnya
                            </Link>
                            <Link
                                v-if="cabors.links.next"
                                :href="cabors.links.next"
                                preserve-state
                                class="rounded border border-gray-300 px-3 py-1 font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                            >
                                Selanjutnya
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
