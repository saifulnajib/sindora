<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    kejuaraan: Object,
    cabors: Array,
    organisasis: Array,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    nama: props.kejuaraan?.nama ?? '',
    jenis: props.kejuaraan?.jenis ?? '',
    tingkat: props.kejuaraan?.tingkat ?? '',
    penyelenggara: props.kejuaraan?.penyelenggara ?? '',
    organisasi_id: props.kejuaraan?.organisasi_id ?? '',
    cabor_id: props.kejuaraan?.cabor_id ?? '',
    lokasi: props.kejuaraan?.lokasi ?? '',
    tanggal_mulai: props.kejuaraan?.tanggal_mulai ?? '',
    tanggal_selesai: props.kejuaraan?.tanggal_selesai ?? '',
    deskripsi: props.kejuaraan?.deskripsi ?? '',
    poster: null,
});

const posterPreview = ref(props.kejuaraan?.poster_url ?? null);

function onPosterChange(e) {
    const f = e.target.files[0];
    form.poster = f || null;
    if (f) posterPreview.value = URL.createObjectURL(f);
}

function submit() {
    if (props.isShow) return;
    if (props.isEdit) {
        form.post(route('kejuaraans.update', props.kejuaraan.id), { _method: 'put', forceFormData: true });
    } else {
        form.post(route('kejuaraans.store'), { forceFormData: true });
    }
}

const tingkatOptions = [
    { value: 'kabupaten', label: 'Kabupaten' },
    { value: 'kota', label: 'Kota' },
    { value: 'kabupaten_kota', label: 'Kabupaten/Kota' },
    { value: 'kecamatan', label: 'Kecamatan' },
    { value: 'provinsi', label: 'Provinsi' },
    { value: 'nasional', label: 'Nasional' },
    { value: 'internasional', label: 'Internasional' },
];

const jenisOptions = [
    { value: 'turnamen', label: 'Turnamen' },
    { value: 'liga', label: 'Liga' },
    { value: 'festival', label: 'Festival' },
    { value: 'kejuaraan', label: 'Kejuaraan' },
    { value: 'kegiatan', label: 'Kegiatan' },
];
</script>

<template>
    <Head :title="isShow ? 'Detail Kejuaraan' : (isEdit ? 'Edit Kejuaraan' : 'Tambah Kejuaraan')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('kejuaraans.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail Kejuaraan' : (isEdit ? 'Edit Kejuaraan' : 'Tambah Kejuaraan') }}</h2>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5" enctype="multipart/form-data">
                        <div>
                            <InputLabel for="nama" value="Nama Kejuaraan *" />
                            <TextInput id="nama" v-model="form.nama" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="jenis" value="Jenis" />
                                <select id="jenis" v-model="form.jenis" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih Jenis —</option>
                                    <option v-for="j in jenisOptions" :key="j.value" :value="j.value">{{ j.label }}</option>
                                </select>
                                <InputError :message="form.errors.jenis" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="tingkat" value="Tingkat *" />
                                <select id="tingkat" v-model="form.tingkat" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" required>
                                    <option value="">— Pilih Tingkat —</option>
                                    <option v-for="t in tingkatOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
                                </select>
                                <InputError :message="form.errors.tingkat" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="penyelenggara" value="Penyelenggara *" />
                            <TextInput id="penyelenggara" v-model="form.penyelenggara" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.penyelenggara" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="organisasi_id" value="Organisasi" />
                                <select id="organisasi_id" v-model="form.organisasi_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Tanpa Organisasi —</option>
                                    <option v-for="o in organisasis" :key="o.id" :value="o.id">{{ o.nama }}</option>
                                </select>
                                <InputError :message="form.errors.organisasi_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="cabor_id" value="Cabor" />
                                <select id="cabor_id" v-model="form.cabor_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih Cabor —</option>
                                    <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                                </select>
                                <InputError :message="form.errors.cabor_id" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="lokasi" value="Lokasi *" />
                            <TextInput id="lokasi" v-model="form.lokasi" class="mt-1 block w-full" :disabled="isShow" required placeholder="Nama会場, Alamat lengkap" />
                            <InputError :message="form.errors.lokasi" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="tanggal_mulai" value="Tanggal Mulai *" />
                                <TextInput id="tanggal_mulai" type="date" v-model="form.tanggal_mulai" class="mt-1 block w-full" :disabled="isShow" required />
                                <InputError :message="form.errors.tanggal_mulai" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="tanggal_selesai" value="Tanggal Selesai *" />
                                <TextInput id="tanggal_selesai" type="date" v-model="form.tanggal_selesai" class="mt-1 block w-full" :disabled="isShow" required />
                                <InputError :message="form.errors.tanggal_selesai" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="deskripsi" value="Deskripsi" />
                            <textarea id="deskripsi" v-model="form.deskripsi" :disabled="isShow" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                            <InputError :message="form.errors.deskripsi" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="poster" value="Poster (jpg/png max 2MB)" />
                            <div v-if="posterPreview" class="mt-2">
                                <img :src="posterPreview" alt="poster preview" class="h-32 w-auto rounded object-cover ring-1 ring-gray-200" />
                            </div>
                            <input v-if="!isShow" id="poster" type="file" accept="image/jpeg,image/png,image/jpg" class="mt-2 block w-full text-sm" @change="onPosterChange" />
                            <InputError :message="form.errors.poster" class="mt-2" />
                            <p v-if="!isShow" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload poster kejuaraan (max 2MB, jpg/png).</p>
                        </div>
                        <div v-if="!isShow" class="flex gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('kejuaraans.index')" class="self-center text-sm text-gray-600 dark:text-gray-400">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
