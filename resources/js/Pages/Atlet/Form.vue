<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    atlet: Object,
    klubs: Array,
    cabors: Array,
    kelurahans: Array,
    pelatihs: Array,
    isEdit: Boolean,
    isShow: Boolean,
});

const page = usePage();
const isPengkot = computed(() => page.props.auth?.can?.is_pengkot ?? false);

const initialCabors = (props.atlet?.cabors && props.atlet.cabors.length > 0)
    ? props.atlet.cabors.map(c => ({
        cabor_id: c.cabor_id ?? '',
        klub_id: c.klub_id ?? '',
        pelatih_id: c.pelatih_id ?? '',
        kelas_tanding: c.kelas_tanding ?? '',
        status_pembinaan: c.status_pembinaan ?? 'daerah',
    }))
    : [
        {
            cabor_id: props.atlet?.cabor_id ?? '',
            klub_id: props.atlet?.klub_id ?? '',
            pelatih_id: props.atlet?.pelatih_id ?? '',
            kelas_tanding: props.atlet?.kelas_tanding ?? '',
            status_pembinaan: props.atlet?.status_pembinaan ?? 'daerah',
        }
    ];

const form = useForm({
    nama: props.atlet?.nama ?? '',
    nik: props.atlet?.nik ?? '',
    tempat_lahir: props.atlet?.tempat_lahir ?? '',
    tanggal_lahir: props.atlet?.tanggal_lahir ?? '',
    jenis_kelamin: props.atlet?.jenis_kelamin ?? '',
    alamat: props.atlet?.alamat ?? '',
    no_hp: props.atlet?.no_hp ?? '',
    email: props.atlet?.email ?? '',
    kelurahan_id: props.atlet?.kelurahan_id ?? '',
    status_pembinaan: props.atlet?.status_pembinaan ?? 'daerah',
    berat_badan: props.atlet?.berat_badan ?? '',
    tinggi_badan: props.atlet?.tinggi_badan ?? '',
    cabors: initialCabors,
    foto: null,
});

const fotoPreview = ref(props.atlet?.foto_url ?? null);

// State for Live NIK Verification & Existing Athlete Detection
const isCheckingNik = ref(false);
const existingAtlet = ref(null);
const nikFeedback = ref(null);

// Cabors available to be added (excluding cabors the existing athlete is already registered in)
const availableCabors = computed(() => {
    if (!props.cabors) return [];
    if (!existingAtlet.value || !existingAtlet.value.existing_cabor_ids) {
        return props.cabors;
    }
    const registeredIds = existingAtlet.value.existing_cabor_ids;
    return props.cabors.filter(c => !registeredIds.includes(c.id));
});

const isAlreadyEnrolledInAllCabors = computed(() => {
    if (!existingAtlet.value) return false;
    return availableCabors.value.length === 0;
});

// Function to check NIK with backend
async function checkNik(force = false) {
    if (props.isEdit || props.isShow) return;
    const nikVal = (form.nik || '').trim();

    if (nikVal.length !== 16) {
        existingAtlet.value = null;
        nikFeedback.value = null;
        return;
    }

    isCheckingNik.value = true;
    nikFeedback.value = null;

    try {
        const response = await axios.post(route('atlets.check-nik'), { nik: nikVal });
        if (response.data.exists && response.data.atlet) {
            existingAtlet.value = response.data.atlet;

            // Autofill master profile from existing athlete
            form.nama = existingAtlet.value.nama || '';
            form.tempat_lahir = existingAtlet.value.tempat_lahir || '';
            form.tanggal_lahir = existingAtlet.value.tanggal_lahir || '';
            form.jenis_kelamin = existingAtlet.value.jenis_kelamin || '';
            form.alamat = existingAtlet.value.alamat || '';
            form.no_hp = existingAtlet.value.no_hp || '';
            form.email = existingAtlet.value.email || '';
            form.kelurahan_id = existingAtlet.value.kelurahan_id || '';
            form.berat_badan = existingAtlet.value.berat_badan || '';
            form.tinggi_badan = existingAtlet.value.tinggi_badan || '';
            if (existingAtlet.value.foto_url) {
                fotoPreview.value = existingAtlet.value.foto_url;
            }

            // Reset cabor selection to 1 empty cabor for adding
            form.cabors = [
                {
                    cabor_id: '',
                    klub_id: '',
                    pelatih_id: '',
                    kelas_tanding: '',
                    status_pembinaan: 'daerah',
                }
            ];

            nikFeedback.value = {
                type: 'info',
                message: `Data master ditemukan: ${existingAtlet.value.nama}. Silakan tambahkan cabang olahraga baru untuk atlet ini di bawah.`,
            };
        } else {
            existingAtlet.value = null;
            nikFeedback.value = {
                type: 'success',
                message: 'NIK belum terdaftar. Silakan lengkapi biodata atlet baru di bawah.',
            };
        }
    } catch (err) {
        console.error('Gagal memeriksa NIK:', err);
    } finally {
        isCheckingNik.value = false;
    }
}

// Watch NIK change
watch(() => form.nik, (newVal) => {
    if (props.isEdit || props.isShow) return;
    const clean = (newVal || '').trim();
    if (clean.length === 16) {
        checkNik();
    } else {
        if (existingAtlet.value) {
            existingAtlet.value = null;
            nikFeedback.value = null;
        }
    }
});

function onFotoChange(e) {
    const f = e.target.files[0];
    form.foto = f || null;
    if (f) fotoPreview.value = URL.createObjectURL(f);
}

function addCabor() {
    form.cabors.push({
        cabor_id: '',
        klub_id: '',
        pelatih_id: '',
        kelas_tanding: '',
        status_pembinaan: 'daerah',
    });
}

function removeCabor(index) {
    if (form.cabors.length > 1) {
        form.cabors.splice(index, 1);
    }
}

function getFilteredKlubs(caborId) {
    if (!caborId) return props.klubs;
    return props.klubs.filter(k => !k.cabor_id || k.cabor_id == caborId);
}

function getFilteredPelatihs(caborId) {
    if (!caborId) return props.pelatihs;
    return props.pelatihs.filter(p => !p.cabor_id || p.cabor_id == caborId);
}

function submit() {
    if (props.isShow) return;
    if (isAlreadyEnrolledInAllCabors.value) return;

    if (props.isEdit) {
        form.post(route('atlets.update', props.atlet.id), { _method: 'put', forceFormData: true });
    } else {
        form.post(route('atlets.store'), { forceFormData: true });
    }
}
</script>

<template>
    <Head :title="isShow ? 'Detail Atlet' : (isEdit ? 'Edit Atlet' : 'Tambah Atlet')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('atlets.index')" class="text-sm font-semibold text-red-800 hover:text-red-900 dark:text-red-400">
                    ← Kembali
                </Link>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                        {{ isShow ? 'Detail Data Atlet' : (isEdit ? 'Edit Data Atlet' : (existingAtlet ? 'Tambah Cabang Olahraga Atlet' : 'Pendaftaran Atlet')) }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ existingAtlet ? 'Atlet master terdaftar ditemukan. Tambahkan cabang olahraga baru untuk atlet ini.' : 'Kelola identitas master atlet dan pendaftaran cabang olahraga.' }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Alert Feedback NIK -->
                <div v-if="!isEdit && !isShow && nikFeedback" class="rounded-xl border p-4 text-xs transition"
                     :class="{
                         'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300': nikFeedback.type === 'success',
                         'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300': nikFeedback.type === 'info',
                     }">
                    <div class="flex items-start gap-2.5">
                        <span v-if="nikFeedback.type === 'success'" class="text-base">✓</span>
                        <span v-else class="text-base">ℹ️</span>
                        <div class="flex-1 font-medium leading-relaxed">
                            {{ nikFeedback.message }}
                        </div>
                    </div>
                </div>

                <!-- Existing Atlet Master Summary Card (When NIK already exists in system) -->
                <div v-if="existingAtlet" class="rounded-xl border border-blue-200 bg-white p-5 shadow-sm dark:border-blue-900/60 dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-800 text-xs font-bold dark:bg-blue-900 dark:text-blue-200">
                                ✓
                            </span>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                Profil Master Atlet Ditemukan di Database
                            </h3>
                        </div>
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-800 ring-1 ring-inset ring-blue-300 dark:bg-blue-950/50 dark:text-blue-300 dark:ring-blue-800">
                            1 NIK = 1 Master Atlet
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 items-start">
                        <div class="shrink-0">
                            <img
                                v-if="existingAtlet.foto_url"
                                :src="existingAtlet.foto_url"
                                alt="Foto Atlet"
                                class="h-20 w-20 rounded-xl object-cover ring-2 ring-blue-200 dark:ring-blue-800"
                            />
                            <div v-else class="h-20 w-20 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-950 dark:to-indigo-950 flex items-center justify-center text-blue-700 dark:text-blue-300 font-bold text-2xl ring-1 ring-blue-200 dark:ring-blue-800">
                                {{ existingAtlet.nama ? existingAtlet.nama.charAt(0).toUpperCase() : 'A' }}
                            </div>
                        </div>

                        <div class="flex-1 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ existingAtlet.nama }}</h4>
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-mono text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    NIK: {{ existingAtlet.nik }}
                                </span>
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    {{ existingAtlet.jenis_kelamin === 'L' ? 'Laki-laki' : (existingAtlet.jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                                </span>
                            </div>

                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                <b>Lahir:</b> {{ existingAtlet.tempat_lahir || '-' }}, {{ existingAtlet.tanggal_lahir || '-' }}
                                <span class="mx-1.5 text-gray-300 dark:text-gray-600">•</span>
                                <b>Alamat:</b> {{ existingAtlet.alamat || '-' }}
                            </p>

                            <!-- Current Active Cabors of this athlete -->
                            <div class="pt-2">
                                <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                    Cabang Olahraga yang Saat Ini Sudah Diikuti:
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <div
                                        v-for="(ec, eIdx) in existingAtlet.existing_cabors"
                                        :key="eIdx"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50/80 px-2.5 py-1 text-xs text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200"
                                    >
                                        <span class="font-bold">🏅 {{ ec.cabor_nama }}</span>
                                        <span v-if="ec.klub_nama" class="text-emerald-700 dark:text-emerald-400">({{ ec.klub_nama }})</span>
                                        <span v-if="ec.kelas_tanding" class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">[{{ ec.kelas_tanding }}]</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warning if already enrolled in all cabors available to the user -->
                    <div v-if="isAlreadyEnrolledInAllCabors" class="mt-4 rounded-lg bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800 dark:bg-amber-950/40 dark:border-amber-800 dark:text-amber-200 flex items-center gap-2">
                        <span>⚠️</span>
                        <span>
                            <b>Perhatian:</b> Atlet ini sudah terdaftar pada seluruh cabang olahraga yang dapat Anda kelola. Tidak ada cabang olahraga baru yang dapat ditambahkan.
                        </span>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- SECTION 1: NIK & IDENTITAS MASTER ATLET -->
                        <div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4 dark:border-gray-700">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                        <svg class="h-4 w-4 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        1. Identitas Pokok Atlet (Master Profil)
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Data pribadi terintegrasi dengan NIK Kependudukan
                                    </p>
                                </div>

                                <span v-if="existingAtlet" class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-800 ring-1 ring-inset ring-blue-300 dark:bg-blue-950/50 dark:text-blue-300 dark:ring-blue-800">
                                    🔒 Profil Terkunci (Menggunakan Master)
                                </span>
                            </div>

                            <div class="space-y-4">
                                <!-- NIK Input with verification indicator -->
                                <div>
                                    <div class="flex items-center justify-between">
                                        <InputLabel for="nik" value="NIK (Nomor Induk Kependudukan - 16 Digit) *" />
                                        <span v-if="isCheckingNik" class="text-xs text-blue-600 dark:text-blue-400 flex items-center gap-1 animate-pulse">
                                            Memeriksa database...
                                        </span>
                                    </div>
                                    <div class="mt-1 relative flex rounded-md shadow-xs">
                                        <TextInput
                                            id="nik"
                                            v-model="form.nik"
                                            class="block w-full font-mono text-xs disabled:opacity-75 disabled:bg-gray-50 dark:disabled:bg-gray-900"
                                            :disabled="isShow || isEdit"
                                            required
                                            maxlength="16"
                                            placeholder="Masukkan 16 digit NIK (contoh: 2171012345678901)"
                                        />
                                        <button
                                            v-if="!isEdit && !isShow && form.nik && form.nik.length === 16"
                                            type="button"
                                            @click="checkNik(true)"
                                            :disabled="isCheckingNik"
                                            class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                        >
                                            Cek NIK
                                        </button>
                                    </div>
                                    <p v-if="!isShow && !isEdit" class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                        Ketik 16 digit NIK. Sistem akan otomatis mendeteksi apakah atlet sudah terdaftar sebelumnya untuk mencegah duplikasi data.
                                    </p>
                                    <p v-if="isShow && atlet?.nik_masked" class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        Masked NIK: {{ atlet.nik_masked }}
                                    </p>
                                    <InputError :message="form.errors.nik" class="mt-1" />
                                </div>

                                <!-- Nama Atlet -->
                                <div>
                                    <InputLabel for="nama" value="Nama Lengkap Atlet *" />
                                    <TextInput
                                        id="nama"
                                        v-model="form.nama"
                                        class="mt-1 block w-full text-xs disabled:opacity-75 disabled:bg-gray-50 dark:disabled:bg-gray-900"
                                        :disabled="isShow || Boolean(existingAtlet)"
                                        :required="!existingAtlet"
                                        placeholder="Nama lengkap atlet sesuai KTP/KIA/Akte"
                                    />
                                    <InputError :message="form.errors.nama" class="mt-1" />
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <InputLabel for="tempat_lahir" value="Tempat Lahir" />
                                        <TextInput
                                            id="tempat_lahir"
                                            v-model="form.tempat_lahir"
                                            class="mt-1 block w-full text-xs disabled:opacity-75 disabled:bg-gray-50 dark:disabled:bg-gray-900"
                                            :disabled="isShow || Boolean(existingAtlet)"
                                            placeholder="Kota kelahiran"
                                        />
                                        <InputError :message="form.errors.tempat_lahir" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="tanggal_lahir" value="Tanggal Lahir" />
                                        <TextInput
                                            id="tanggal_lahir"
                                            type="date"
                                            v-model="form.tanggal_lahir"
                                            class="mt-1 block w-full text-xs disabled:opacity-75 disabled:bg-gray-50 dark:disabled:bg-gray-900"
                                            :disabled="isShow || Boolean(existingAtlet)"
                                        />
                                        <InputError :message="form.errors.tanggal_lahir" class="mt-1" />
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <InputLabel for="jenis_kelamin" value="Jenis Kelamin" />
                                        <select
                                            id="jenis_kelamin"
                                            v-model="form.jenis_kelamin"
                                            :disabled="isShow || Boolean(existingAtlet)"
                                            class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-red-500 focus:ring-red-500 disabled:opacity-75 disabled:bg-gray-50 dark:disabled:bg-gray-900"
                                        >
                                            <option value="">— Pilih Jenis Kelamin —</option>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                        <InputError :message="form.errors.jenis_kelamin" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="kelurahan_id" value="Kelurahan Domisili (Tanjungpinang)" />
                                        <select
                                            id="kelurahan_id"
                                            v-model="form.kelurahan_id"
                                            :disabled="isShow || Boolean(existingAtlet)"
                                            class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-red-500 focus:ring-red-500 disabled:opacity-75 disabled:bg-gray-50 dark:disabled:bg-gray-900"
                                        >
                                            <option value="">— Pilih Kelurahan —</option>
                                            <option v-for="k in kelurahans" :key="k.id" :value="k.id">{{ k.nama }}</option>
                                        </select>
                                        <InputError :message="form.errors.kelurahan_id" class="mt-1" />
                                    </div>
                                </div>

                                <div>
                                    <InputLabel for="alamat" value="Alamat Lengkap" />
                                    <textarea
                                        id="alamat"
                                        v-model="form.alamat"
                                        :disabled="isShow || Boolean(existingAtlet)"
                                        rows="2"
                                        class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-red-500 focus:ring-red-500 disabled:opacity-75 disabled:bg-gray-50 dark:disabled:bg-gray-900"
                                        placeholder="Nama jalan, RT/RW, nomor rumah"
                                    ></textarea>
                                    <InputError :message="form.errors.alamat" class="mt-1" />
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <InputLabel for="no_hp" value="No. WhatsApp / HP" />
                                        <TextInput
                                            id="no_hp"
                                            v-model="form.no_hp"
                                            class="mt-1 block w-full text-xs disabled:opacity-75 disabled:bg-gray-50 dark:disabled:bg-gray-900"
                                            :disabled="isShow || Boolean(existingAtlet)"
                                            placeholder="Contoh: 081234567890"
                                        />
                                        <InputError :message="form.errors.no_hp" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="email" value="Alamat Email" />
                                        <TextInput
                                            id="email"
                                            type="email"
                                            v-model="form.email"
                                            class="mt-1 block w-full text-xs disabled:opacity-75 disabled:bg-gray-50 dark:disabled:bg-gray-900"
                                            :disabled="isShow || Boolean(existingAtlet)"
                                            placeholder="email@domain.com"
                                        />
                                        <InputError :message="form.errors.email" class="mt-1" />
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <InputLabel for="berat_badan" value="Berat Badan (kg)" />
                                        <TextInput
                                            id="berat_badan"
                                            type="number"
                                            step="0.01"
                                            v-model="form.berat_badan"
                                            class="mt-1 block w-full text-xs disabled:opacity-75 disabled:bg-gray-50 dark:disabled:bg-gray-900"
                                            :disabled="isShow || Boolean(existingAtlet)"
                                            placeholder="Contoh: 65.5"
                                        />
                                        <InputError :message="form.errors.berat_badan" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="tinggi_badan" value="Tinggi Badan (cm)" />
                                        <TextInput
                                            id="tinggi_badan"
                                            type="number"
                                            step="0.01"
                                            v-model="form.tinggi_badan"
                                            class="mt-1 block w-full text-xs disabled:opacity-75 disabled:bg-gray-50 dark:disabled:bg-gray-900"
                                            :disabled="isShow || Boolean(existingAtlet)"
                                            placeholder="Contoh: 172"
                                        />
                                        <InputError :message="form.errors.tinggi_badan" class="mt-1" />
                                    </div>
                                </div>

                                <div v-if="!existingAtlet">
                                    <InputLabel for="foto" value="Foto Atlet (JPG/PNG max 2MB)" />
                                    <div v-if="fotoPreview" class="mt-2 mb-3">
                                        <img :src="fotoPreview" alt="Foto Atlet" class="h-24 w-24 rounded-lg object-cover ring-1 ring-gray-300 dark:ring-gray-600 shadow-xs" />
                                    </div>
                                    <input
                                        v-if="!isShow"
                                        id="foto"
                                        type="file"
                                        accept="image/jpeg,image/png,image/jpg"
                                        class="block w-full text-xs text-gray-700 dark:text-gray-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100"
                                        @change="onFotoChange"
                                    />
                                    <InputError :message="form.errors.foto" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: CABANG OLAHRAGA (MULTI-CABOR) -->
                        <div class="rounded-xl border border-red-200 bg-red-50/40 p-5 dark:border-red-900/60 dark:bg-red-950/20">
                            <div class="flex items-center justify-between pb-3 border-b border-red-200/80 dark:border-red-900/50">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                        <svg class="h-4 w-4 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                        2. {{ existingAtlet ? 'Daftarkan Cabang Olahraga Baru untuk Atlet Ini' : 'Cabang Olahraga yang Diikuti *' }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ existingAtlet ? 'Pilih cabor baru yang ingin ditambahkan. Klub, pelatih, dan nomor tanding khusus untuk cabor ini.' : '1 Atlet dapat memiliki banyak cabor di tabel relasi dengan klub, pelatih, dan kelas tanding masing-masing.' }}
                                    </p>
                                </div>
                                <button
                                    v-if="!isShow && !isAlreadyEnrolledInAllCabors"
                                    type="button"
                                    @click="addCabor"
                                    class="inline-flex items-center gap-1 rounded-lg bg-red-800 hover:bg-red-900 px-3 py-1.5 text-xs font-semibold text-white shadow-xs transition"
                                >
                                    <span>+ Tambah Cabor Lain</span>
                                </button>
                            </div>

                            <InputError :message="form.errors.cabors" class="mt-2" />

                            <div v-if="isAlreadyEnrolledInAllCabors" class="mt-4 p-4 text-center rounded-lg bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 text-xs text-gray-500">
                                Tidak ada cabang olahraga tambahan yang dapat dipilih untuk atlet ini.
                            </div>

                            <div v-else class="mt-4 space-y-4">
                                <div
                                    v-for="(item, idx) in form.cabors"
                                    :key="idx"
                                    class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-700 dark:bg-gray-800"
                                >
                                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                                        <span class="inline-flex items-center gap-2 text-xs font-bold text-red-900 dark:text-red-300">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-200 text-[11px] font-black">
                                                {{ idx + 1 }}
                                            </span>
                                            {{ existingAtlet ? 'Cabor Baru yang Didaftarkan' : `Cabang Olahraga #${idx + 1}` }}
                                        </span>
                                        <button
                                            v-if="!isShow && form.cabors.length > 1"
                                            type="button"
                                            @click="removeCabor(idx)"
                                            class="inline-flex items-center gap-1 text-xs text-red-600 hover:text-red-800 dark:text-red-400 font-semibold transition"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <!-- Dropdown Cabor -->
                                        <div>
                                            <InputLabel :for="'cabor_' + idx" value="Pilih Cabang Olahraga *" />
                                            <SearchableSelect
                                                :id="'cabor_' + idx"
                                                v-model="item.cabor_id"
                                                :options="availableCabors"
                                                :disabled="isShow"
                                                :disabled-values="form.cabors.filter((_, oIdx) => oIdx !== idx).map(o => o.cabor_id).filter(Boolean)"
                                                required
                                                placeholder="— Pilih Cabang Olahraga —"
                                                search-placeholder="Ketik nama atau singkatan cabor..."
                                                :error="!!form.errors[`cabors.${idx}.cabor_id`]"
                                                class="mt-1"
                                            />
                                            <InputError :message="form.errors[`cabors.${idx}.cabor_id`]" class="mt-1" />
                                        </div>

                                        <!-- Dropdown Klub -->
                                        <div>
                                            <InputLabel :for="'klub_' + idx" value="Klub Naungan di Cabor Ini" />
                                            <select
                                                :id="'klub_' + idx"
                                                v-model="item.klub_id"
                                                :disabled="isShow"
                                                class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-red-500 focus:ring-red-500"
                                            >
                                                <option value="">— Tanpa Klub / Belum Tergabung —</option>
                                                <option
                                                    v-for="k in getFilteredKlubs(item.cabor_id)"
                                                    :key="k.id"
                                                    :value="k.id"
                                                >
                                                    {{ k.nama }}
                                                </option>
                                            </select>
                                            <InputError :message="form.errors[`cabors.${idx}.klub_id`]" class="mt-1" />
                                        </div>

                                        <!-- Dropdown Pelatih -->
                                        <div>
                                            <InputLabel :for="'pelatih_' + idx" value="Pelatih (SDM Cabor Ini)" />
                                            <select
                                                :id="'pelatih_' + idx"
                                                v-model="item.pelatih_id"
                                                :disabled="isShow"
                                                class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-red-500 focus:ring-red-500"
                                            >
                                                <option value="">— Tanpa Pelatih Pribadi —</option>
                                                <option
                                                    v-for="p in getFilteredPelatihs(item.cabor_id)"
                                                    :key="p.id"
                                                    :value="p.id"
                                                >
                                                    {{ p.nama }}
                                                </option>
                                            </select>
                                            <InputError :message="form.errors[`cabors.${idx}.pelatih_id`]" class="mt-1" />
                                        </div>

                                        <!-- Kelas Tanding -->
                                        <div>
                                            <InputLabel :for="'kelas_tanding_' + idx" value="Kelas / Nomor Pertandingan" />
                                            <TextInput
                                                :id="'kelas_tanding_' + idx"
                                                v-model="item.kelas_tanding"
                                                :disabled="isShow"
                                                placeholder="Contoh: Gaya Dada 50m, Tanding Putra Kelas A"
                                                class="mt-1 block w-full text-xs"
                                            />
                                            <InputError :message="form.errors[`cabors.${idx}.kelas_tanding`]" class="mt-1" />
                                        </div>

                                        <!-- Status Pembinaan -->
                                        <div>
                                            <InputLabel :for="'status_pembinaan_' + idx" value="Jenjang Pembinaan" />
                                            <select
                                                :id="'status_pembinaan_' + idx"
                                                v-model="item.status_pembinaan"
                                                :disabled="isShow"
                                                class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-red-500 focus:ring-red-500"
                                            >
                                                <option value="daerah">Daerah (Kota Tanjungpinang)</option>
                                                <option value="provinsi">Provinsi (Kepulauan Riau)</option>
                                                <option value="nasional">Nasional (Pelatnas / Timnas)</option>
                                            </select>
                                            <InputError :message="form.errors[`cabors.${idx}.status_pembinaan`]" class="mt-1" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SUBMIT BUTTONS -->
                        <div v-if="!isShow" class="flex items-center gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <PrimaryButton
                                :disabled="form.processing || isCheckingNik || isAlreadyEnrolledInAllCabors"
                                class="inline-flex items-center gap-2"
                            >
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>
                                    {{ isEdit ? 'Simpan Perubahan Data Atlet' : (existingAtlet ? 'Daftarkan Cabor Baru untuk Atlet Ini' : 'Simpan & Daftarkan Atlet Baru') }}
                                </span>
                            </PrimaryButton>

                            <Link
                                :href="route('atlets.index')"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition"
                            >
                                Batal
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
