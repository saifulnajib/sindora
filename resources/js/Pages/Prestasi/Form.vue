<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    prestasi: Object,
    atlets: Array,
    cabors: Array,
    kejuaraans: Array,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    atlet_id: props.prestasi?.atlet_id ?? '',
    cabor_id: props.prestasi?.cabor_id ?? '',
    kejuaraan_id: props.prestasi?.kejuaraan_id ?? '',
    medali: props.prestasi?.medali ?? '',
    peringkat: props.prestasi?.peringkat ?? '',
    tanggal: props.prestasi?.tanggal ?? '',
    kategori_kelas: props.prestasi?.kategori_kelas ?? '',
    nomor_sertifikat: props.prestasi?.nomor_sertifikat ?? '',
    keterangan: props.prestasi?.keterangan ?? '',
    sertifikat: null,
});

const sertifikatPreview = ref(props.prestasi?.sertifikat_url ?? null);
const isImagePreview = ref(false);

function onSertifikatChange(e) {
    const f = e.target.files[0];
    form.sertifikat = f || null;
    if (f && f.type.startsWith('image/')) {
        sertifikatPreview.value = URL.createObjectURL(f);
        isImagePreview.value = true;
    } else if (f) {
        sertifikatPreview.value = null;
        isImagePreview.value = false;
    } else {
        sertifikatPreview.value = props.prestasi?.sertifikat_url ?? null;
        isImagePreview.value = false;
    }
}

function submit() {
    if (props.isShow) return;
    if (props.isEdit) {
        form.post(route('prestasis.update', props.prestasi.id), { _method: 'put', forceFormData: true });
    } else {
        form.post(route('prestasis.store'), { forceFormData: true });
    }
}

const medaliOptions = [
    { value: 'emas', label: 'Emas' },
    { value: 'perak', label: 'Perak' },
    { value: 'perunggu', label: 'Perunggu' },
    { value: 'juara_harapan', label: 'Juara Harapan' },
];
</script>

<template>
    <Head :title="isShow ? 'Detail Prestasi' : (isEdit ? 'Edit Prestasi' : 'Tambah Prestasi')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('prestasis.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail Prestasi' : (isEdit ? 'Edit Prestasi' : 'Tambah Prestasi') }}</h2>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5" enctype="multipart/form-data">
                        <div>
                            <InputLabel for="atlet_id" value="Atlet *" />
                            <select id="atlet_id" v-model="form.atlet_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" required>
                                <option value="">— Pilih Atlet —</option>
                                <option v-for="a in atlets" :key="a.id" :value="a.id">{{ a.nama }}</option>
                            </select>
                            <InputError :message="form.errors.atlet_id" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="cabor_id" value="Cabor *" />
                                <select id="cabor_id" v-model="form.cabor_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" required>
                                    <option value="">— Pilih Cabor —</option>
                                    <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                                </select>
                                <InputError :message="form.errors.cabor_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="kejuaraan_id" value="Kejuaraan *" />
                                <select id="kejuaraan_id" v-model="form.kejuaraan_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" required>
                                    <option value="">— Pilih Kejuaraan —</option>
                                    <option v-for="k in kejuaraans" :key="k.id" :value="k.id">{{ k.nama }}<span v-if="k.tingkat"> — {{ k.tingkat }}</span></option>
                                </select>
                                <InputError :message="form.errors.kejuaraan_id" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="medali" value="Medali *" />
                                <select id="medali" v-model="form.medali" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" required>
                                    <option value="">— Pilih Medali —</option>
                                    <option v-for="m in medaliOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
                                </select>
                                <InputError :message="form.errors.medali" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="peringkat" value="Peringkat" />
                                <TextInput id="peringkat" type="number" min="1" max="999" v-model="form.peringkat" class="mt-1 block w-full" :disabled="isShow" placeholder="1" />
                                <InputError :message="form.errors.peringkat" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="tanggal" value="Tanggal *" />
                                <TextInput id="tanggal" type="date" v-model="form.tanggal" class="mt-1 block w-full" :disabled="isShow" required />
                                <InputError :message="form.errors.tanggal" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="kategori_kelas" value="Kategori / Kelas" />
                                <TextInput id="kategori_kelas" v-model="form.kategori_kelas" class="mt-1 block w-full" :disabled="isShow" placeholder="Contoh: -60kg Putra / Kelas A" />
                                <InputError :message="form.errors.kategori_kelas" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="nomor_sertifikat" value="Nomor Sertifikat" />
                                <TextInput id="nomor_sertifikat" v-model="form.nomor_sertifikat" class="mt-1 block w-full" :disabled="isShow" placeholder="Contoh: 123/SERTIF/2024" />
                                <InputError :message="form.errors.nomor_sertifikat" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="sertifikat" value="File Sertifikat (pdf/jpg/png max 5MB)" />
                                <div v-if="prestasi?.sertifikat_url" class="mt-1 text-xs">
                                    <a :href="prestasi.sertifikat_url" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Lihat sertifikat saat ini</a>
                                </div>
                                <div v-if="prestasi?.nomor_sertifikat && isShow" class="mt-1 text-xs text-gray-500 dark:text-gray-400">No: {{ prestasi.nomor_sertifikat }}</div>
                            </div>
                        </div>
                        <div>
                            <InputLabel for="sertifikat" value="Upload Sertifikat" class="sr-only" />
                            <div v-if="sertifikatPreview && isImagePreview" class="mt-2">
                                <img :src="sertifikatPreview" alt="sertifikat preview" class="h-32 w-auto rounded object-cover ring-1 ring-gray-200" />
                            </div>
                            <input v-if="!isShow" id="sertifikat" type="file" accept=".pdf,.jpg,.jpeg,.png" class="mt-2 block w-full text-sm" @change="onSertifikatChange" />
                            <InputError :message="form.errors.sertifikat" class="mt-2" />
                            <p v-if="!isShow" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload bukti sertifikat prestasi (pdf/jpg/png max 5MB).</p>
                        </div>
                        <div>
                            <InputLabel for="keterangan" value="Keterangan" />
                            <textarea id="keterangan" v-model="form.keterangan" :disabled="isShow" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" placeholder="Catatan tambahan..."></textarea>
                            <InputError :message="form.errors.keterangan" class="mt-2" />
                        </div>
                        <div v-if="isShow && prestasi?.verification_status" class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 p-4 text-sm dark:text-gray-200">
                            <div class="flex flex-wrap gap-4 text-xs">
                                <span>Status: <strong>{{ prestasi.verification_label ?? prestasi.verification_status }}</strong></span>
                                <span v-if="prestasi.catatan_verifikator">Catatan: {{ prestasi.catatan_verifikator }}</span>
                            </div>
                        </div>
                        <div v-if="!isShow" class="flex gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('prestasis.index')" class="self-center text-sm text-gray-600 dark:text-gray-400">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
