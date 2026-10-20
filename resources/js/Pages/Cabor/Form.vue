<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    cabor: Object,
    organisasis: Array,
    kategoriList: {
        type: Array,
        default: () => [
            { value: 'koni', label: 'KONI (Olahraga Prestasi)' },
            { value: 'kormi', label: 'KORMI (Olahraga Rekreasi / Masyarakat)' },
            { value: 'disabilitas', label: 'DISABILITAS (Olahraga Disabilitas / NPCI)' },
        ],
    },
    isEdit: Boolean,
    isShow: Boolean,
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const isSuperAdmin = computed(() => page.props.auth?.can?.is_super_admin ?? false);
const isPengkot = computed(() => page.props.auth?.can?.is_pengkot ?? false);

// Core cabor data can ONLY be edited by Super Admin
const canEditCore = computed(() => isSuperAdmin.value && !props.isShow);
// Legalitas & kepengurusan can be edited by Super Admin and Pengkot
const canEditLegalitas = computed(() => (isSuperAdmin.value || isPengkot.value) && !props.isShow);

const form = useForm({
    organisasi_id: props.cabor?.organisasi_id ?? '',
    nama: props.cabor?.nama ?? '',
    kode: props.cabor?.kode ?? '',
    kategori: props.cabor?.kategori ?? '',
    deskripsi: props.cabor?.deskripsi ?? '',
    logo: null,
    sk_nomor: props.cabor?.sk_nomor ?? '',
    sk_tgl_mulai: props.cabor?.sk_tgl_mulai ?? '',
    sk_tgl_selesai: props.cabor?.sk_tgl_selesai ?? '',
    sk_file: null,
    nama_ketua: props.cabor?.nama_ketua ?? '',
    kontak_pengurus: props.cabor?.kontak_pengurus ?? '',
});

const logoPreview = ref(props.cabor?.logo_url ?? null);
const skFileUrl = ref(props.cabor?.sk_file_url ?? null);

function onLogoChange(e) {
    const file = e.target.files[0];
    form.logo = file || null;
    if (file) {
        logoPreview.value = URL.createObjectURL(file);
    }
}

function onSkFileChange(e) {
    const file = e.target.files[0];
    form.sk_file = file || null;
}

// Realtime SK Validity calculation
const skStatusInfo = computed(() => {
    if (!form.sk_nomor && !form.sk_file && !skFileUrl.value) {
        return { status: 'empty', label: 'Belum Ada SK', class: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' };
    }

    if (!form.sk_tgl_selesai) {
        return { status: 'active', label: 'Aktif (Tanpa batas akhir)', class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' };
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const end = new Date(form.sk_tgl_selesai);
    end.setHours(0, 0, 0, 0);

    const diffDays = Math.ceil((end - today) / (1000 * 60 * 60 * 24));

    if (diffDays < 0) {
        return { status: 'expired', label: `Kedaluwarsa (${Math.abs(diffDays)} hari lalu)`, class: 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300' };
    }

    if (diffDays <= 60) {
        return { status: 'warning', label: `Segera Berakhir (${diffDays} hari lagi)`, class: 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' };
    }

    return { status: 'active', label: `Masa Berlaku Aktif (${diffDays} hari lagi)`, class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' };
});

function submit() {
    if (props.isShow) return;

    if (props.isEdit) {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('cabors.update', props.cabor.id), {
            forceFormData: true,
        });
    } else {
        form.post(route('cabors.store'), {
            forceFormData: true,
        });
    }
}
</script>

<template>
    <Head :title="isShow ? 'Detail Cabor & Legalitas' : (isEdit ? (canEditCore ? 'Edit Cabor & Dokumen SK' : 'Kelola Dokumen SK & Logo Pengkot') : 'Tambah Cabor')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('cabors.index')" class="text-sm font-semibold text-red-800 hover:text-red-900 dark:text-red-400">
                        ← Kembali
                    </Link>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                            {{ isShow ? 'Detail Cabang Olahraga & Dokumen Pengurus' : (isEdit ? (canEditCore ? 'Edit Cabor & Dokumen Pengurus' : 'Kelola Dokumen Legalitas & Pengurus Pengkot') : 'Tambah Cabang Olahraga') }}
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ isPengkot && !canEditCore ? 'Unggah dan perbarui SK kepengurusan, masa berlaku SK, susunan ketua, serta logo cabor Anda.' : 'Kelola data cabor, SK kepengurusan pengkot, masa berlaku, dan logo organisasi.' }}
                        </p>
                    </div>
                </div>

                <div v-if="isShow && cabor?.can_update">
                    <Link :href="route('cabors.edit', cabor.id)" class="rounded-lg bg-red-800 px-3.5 py-1.5 text-xs font-semibold text-white shadow hover:bg-red-900">
                        Edit & Unggah Dokumen
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Section 1: Data Pokok Cabor (Super Admin Only) -->
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4 dark:border-gray-700">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                    <svg class="h-4 w-4 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Informasi Utama Cabang Olahraga
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Identitas pokok cabor dan induk asosiasi (KONI, KORMI, DISABILITAS)
                                </p>
                            </div>

                            <span v-if="!canEditCore" class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-800 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-800">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Terkunci (Hanya Super Admin)
                            </span>
                        </div>

                        <!-- Info Banner when locked for Pengkot -->
                        <div v-if="!canEditCore && !isShow" class="mb-4 flex items-center gap-2.5 rounded-lg bg-gray-50 border border-gray-200 p-3 text-xs text-gray-600 dark:bg-gray-800/80 dark:border-gray-700 dark:text-gray-400">
                            <svg class="h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>
                                <b>Informasi Utama Terkunci:</b> Nama cabor, induk organisasi, kode, dan kategori hanya dapat diubah oleh Super Admin. Anda dapat mengubah dan melengkapi dokumen SK kepengurusan serta logo pada bagian di bawah.
                            </span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <InputLabel for="organisasi_id" value="Induk Organisasi Keolahragaan" />
                                <SearchableSelect
                                    id="organisasi_id"
                                    v-model="form.organisasi_id"
                                    :options="organisasis"
                                    sub-label-key="singkatan"
                                    :disabled="!canEditCore"
                                    :required="canEditCore"
                                    placeholder="— Pilih Induk Organisasi —"
                                    search-placeholder="Ketik nama atau singkatan induk organisasi..."
                                    :error="!!form.errors.organisasi_id"
                                    class="mt-1"
                                />
                                <InputError :message="form.errors.organisasi_id" class="mt-1" />
                            </div>

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="sm:col-span-2">
                                    <InputLabel for="nama" value="Nama Cabang Olahraga" />
                                    <TextInput
                                        id="nama"
                                        v-model="form.nama"
                                        class="mt-1 block w-full text-xs disabled:opacity-60 disabled:cursor-not-allowed disabled:bg-gray-50 dark:disabled:bg-gray-800"
                                        :disabled="!canEditCore"
                                        placeholder="Contoh: Bulu Tangkis (PBSI)"
                                        :required="canEditCore"
                                    />
                                    <InputError :message="form.errors.nama" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="kode" value="Kode / Singkatan" />
                                    <TextInput
                                        id="kode"
                                        v-model="form.kode"
                                        class="mt-1 block w-full text-xs uppercase disabled:opacity-60 disabled:cursor-not-allowed disabled:bg-gray-50 dark:disabled:bg-gray-800"
                                        :disabled="!canEditCore"
                                        placeholder="Contoh: PBSI"
                                    />
                                    <InputError :message="form.errors.kode" class="mt-1" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="kategori" value="Kategori Bidang Olahraga" />
                                <select
                                    id="kategori"
                                    v-model="form.kategori"
                                    :disabled="!canEditCore"
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-red-500 focus:ring-red-500 disabled:opacity-60 disabled:cursor-not-allowed disabled:bg-gray-50 dark:disabled:bg-gray-800"
                                >
                                    <option value="">— Pilih Kategori Bidang —</option>
                                    <option v-for="k in kategoriList" :key="k.value" :value="k.value">
                                        {{ k.label }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.kategori" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel for="deskripsi" value="Deskripsi / Profil Cabor" />
                                <textarea
                                    id="deskripsi"
                                    v-model="form.deskripsi"
                                    :disabled="!canEditCore"
                                    rows="3"
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-red-500 focus:ring-red-500 disabled:opacity-60 disabled:cursor-not-allowed disabled:bg-gray-50 dark:disabled:bg-gray-800"
                                    placeholder="Keterangan singkat sejarah atau profil cabor di Tanjungpinang..."
                                ></textarea>
                                <InputError :message="form.errors.deskripsi" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Legalitas & Dokumen Pengurusan Organisasi (Pengkot) -->
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b border-gray-100 pb-3 mb-5 dark:border-gray-700 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                    <svg class="h-4 w-4 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Dokumen Legalitas & Pengurusan Organisasi (Pengkot)
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Surat Keputusan (SK) kepengurusan pengurus kota, tanggal berlaku, dan logo cabor
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <span v-if="isPengkot && !canEditCore" class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-800">
                                    Dapat Diubah oleh Pengkot
                                </span>

                                <!-- Live SK Status Badge -->
                                <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset', skStatusInfo.class]">
                                    {{ skStatusInfo.label }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <!-- Logo Upload & Preview -->
                            <div>
                                <InputLabel value="Logo Cabang Olahraga / Pengkot" />
                                <div class="mt-2 flex items-center gap-4">
                                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 overflow-hidden dark:border-gray-700 dark:bg-gray-900">
                                        <img v-if="logoPreview" :src="logoPreview" alt="Logo Cabor" class="h-full w-full object-contain p-1" />
                                        <svg v-else class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>

                                    <div v-if="canEditLegalitas" class="flex-1">
                                        <input
                                            type="file"
                                            accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                                            @change="onLogoChange"
                                            class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 dark:file:bg-red-950/50 dark:file:text-red-300 cursor-pointer"
                                        />
                                        <p class="mt-1 text-[11px] text-gray-400">Format: PNG, JPG, WebP, SVG. Maksimal 5MB.</p>
                                        <InputError :message="form.errors.logo" class="mt-1" />
                                    </div>
                                    <div v-else class="text-xs text-gray-500">
                                        {{ logoPreview ? 'Logo terunggah' : 'Belum ada logo terunggah' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Detail SK (Nomor, Tanggal Mulai, Tanggal Selesai) -->
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <InputLabel for="sk_nomor" value="Nomor SK Kepengurusan" />
                                    <TextInput
                                        id="sk_nomor"
                                        v-model="form.sk_nomor"
                                        class="mt-1 block w-full text-xs font-mono"
                                        :disabled="!canEditLegalitas"
                                        placeholder="Contoh: SK/012/PENGPROV/2024"
                                    />
                                    <InputError :message="form.errors.sk_nomor" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="sk_tgl_mulai" value="Tanggal Mulai SK (TMT Mulai)" />
                                    <TextInput
                                        id="sk_tgl_mulai"
                                        type="date"
                                        v-model="form.sk_tgl_mulai"
                                        class="mt-1 block w-full text-xs"
                                        :disabled="!canEditLegalitas"
                                    />
                                    <InputError :message="form.errors.sk_tgl_mulai" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="sk_tgl_selesai" value="Tanggal Selesai SK (Masa Berakhir)" />
                                    <TextInput
                                        id="sk_tgl_selesai"
                                        type="date"
                                        v-model="form.sk_tgl_selesai"
                                        class="mt-1 block w-full text-xs"
                                        :disabled="!canEditLegalitas"
                                    />
                                    <InputError :message="form.errors.sk_tgl_selesai" class="mt-1" />
                                </div>
                            </div>

                            <!-- File Dokumen SK Upload & Current Document Download -->
                            <div>
                                <InputLabel value="File Dokumen SK (PDF / Dokumen Legalitas)" />

                                <!-- Current Document Download Card -->
                                <div v-if="skFileUrl" class="mt-2 mb-3 flex items-center justify-between rounded-lg border border-red-200 bg-red-50/50 p-3 dark:border-red-900/50 dark:bg-red-950/20">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-100 text-red-700 dark:bg-red-900/60 dark:text-red-300">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-900 dark:text-gray-100">Dokumen SK Kepengurusan Tersimpan</p>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400">File dokumen legalitas SK aktif di server</p>
                                        </div>
                                    </div>

                                    <a
                                        :href="skFileUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-red-800 shadow-xs ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-red-300 dark:ring-gray-700 dark:hover:bg-gray-700"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        Lihat / Unduh SK
                                    </a>
                                </div>

                                <div v-if="canEditLegalitas">
                                    <input
                                        type="file"
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                        @change="onSkFileChange"
                                        class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 dark:file:bg-red-950/50 dark:file:text-red-300 cursor-pointer"
                                    />
                                    <p class="mt-1 text-[11px] text-gray-400">
                                        {{ skFileUrl ? 'Pilih file baru jika ingin mengganti dokumen SK di atas.' : 'Format: PDF, DOC, DOCX, JPG, PNG. Maksimal 10MB.' }}
                                    </p>
                                    <InputError :message="form.errors.sk_file" class="mt-1" />
                                </div>
                            </div>

                            <!-- Detail Susunan Kepengurusan -->
                            <div class="grid gap-4 sm:grid-cols-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                <div>
                                    <InputLabel for="nama_ketua" value="Nama Ketua Pengurus Kota (Pengkot)" />
                                    <TextInput
                                        id="nama_ketua"
                                        v-model="form.nama_ketua"
                                        class="mt-1 block w-full text-xs"
                                        :disabled="!canEditLegalitas"
                                        placeholder="Contoh: H. Ahmad Fauzi, S.Pd"
                                    />
                                    <InputError :message="form.errors.nama_ketua" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="kontak_pengurus" value="Kontak Sekretariat / No. HP Pengurus" />
                                    <TextInput
                                        id="kontak_pengurus"
                                        v-model="form.kontak_pengurus"
                                        class="mt-1 block w-full text-xs"
                                        :disabled="!canEditLegalitas"
                                        placeholder="Contoh: 0812-3456-7890"
                                    />
                                    <InputError :message="form.errors.kontak_pengurus" class="mt-1" />
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div v-if="!isShow" class="flex items-center gap-3 justify-end pt-2">
                        <Link
                            :href="route('cabors.index')"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            Batal
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="text-xs">
                            <svg v-if="form.processing" class="h-4 w-4 animate-spin mr-1.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ canEditCore ? (isEdit ? 'Simpan Perubahan & Dokumen SK' : 'Simpan Cabor Baru') : 'Simpan Dokumen SK & Logo Pengkot' }}
                        </PrimaryButton>
                    </div>

                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
