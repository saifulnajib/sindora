<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    atlet: Object,
    klubs: Array,
    cabors: Array,
    kelurahans: Array,
    pelatihs: Array,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    nama: props.atlet?.nama ?? '',
    nik: props.atlet?.nik ?? '',
    tempat_lahir: props.atlet?.tempat_lahir ?? '',
    tanggal_lahir: props.atlet?.tanggal_lahir ?? '',
    jenis_kelamin: props.atlet?.jenis_kelamin ?? '',
    alamat: props.atlet?.alamat ?? '',
    no_hp: props.atlet?.no_hp ?? '',
    email: props.atlet?.email ?? '',
    klub_id: props.atlet?.klub_id ?? '',
    cabor_id: props.atlet?.cabor_id ?? '',
    pelatih_id: props.atlet?.pelatih_id ?? '',
    kelurahan_id: props.atlet?.kelurahan_id ?? '',
    kelas_tanding: props.atlet?.kelas_tanding ?? '',
    status_pembinaan: props.atlet?.status_pembinaan ?? 'daerah',
    berat_badan: props.atlet?.berat_badan ?? '',
    tinggi_badan: props.atlet?.tinggi_badan ?? '',
    foto: null,
});

const fotoPreview = ref(props.atlet?.foto_url ?? null);

function onFotoChange(e) {
    const f = e.target.files[0];
    form.foto = f || null;
    if (f) fotoPreview.value = URL.createObjectURL(f);
}

function submit() {
    if (props.isShow) return;
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
                <Link :href="route('atlets.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail Atlet' : (isEdit ? 'Edit Atlet' : 'Tambah Atlet') }}</h2>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="nama" value="Nama Atlet *" />
                            <TextInput id="nama" v-model="form.nama" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="nik" value="NIK (16 digit) *" />
                            <TextInput id="nik" v-model="form.nik" class="mt-1 block w-full font-mono text-sm" :disabled="isShow" required maxlength="16" placeholder="32xxxxxxxxxxxxxx" />
                            <p v-if="!isShow" class="mt-1 text-xs text-gray-500 dark:text-gray-400">NIK disimpan terenkripsi; gunakan 16 digit numerik. Unik (soft-delete aware).</p>
                            <p v-if="isShow && atlet?.nik_masked" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Masked: {{ atlet.nik_masked }}</p>
                            <InputError :message="form.errors.nik" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="tempat_lahir" value="Tempat Lahir" />
                                <TextInput id="tempat_lahir" v-model="form.tempat_lahir" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.tempat_lahir" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="tanggal_lahir" value="Tanggal Lahir" />
                                <TextInput id="tanggal_lahir" type="date" v-model="form.tanggal_lahir" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.tanggal_lahir" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="jenis_kelamin" value="Jenis Kelamin" />
                                <select id="jenis_kelamin" v-model="form.jenis_kelamin" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih —</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                                <InputError :message="form.errors.jenis_kelamin" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="kelas_tanding" value="Kelas Tanding" />
                                <TextInput id="kelas_tanding" v-model="form.kelas_tanding" class="mt-1 block w-full" :disabled="isShow" placeholder="mis. 60kg / -60kg" />
                                <InputError :message="form.errors.kelas_tanding" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="alamat" value="Alamat" />
                            <textarea id="alamat" v-model="form.alamat" :disabled="isShow" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                            <InputError :message="form.errors.alamat" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="no_hp" value="No HP" />
                                <TextInput id="no_hp" v-model="form.no_hp" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.no_hp" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput id="email" type="email" v-model="form.email" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="cabor_id" value="Cabor *" />
                                <select id="cabor_id" v-model="form.cabor_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih Cabor —</option>
                                    <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                                </select>
                                <InputError :message="form.errors.cabor_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="klub_id" value="Klub" />
                                <select id="klub_id" v-model="form.klub_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Tanpa Klub —</option>
                                    <option v-for="k in klubs" :key="k.id" :value="k.id">{{ k.nama }}</option>
                                </select>
                                <InputError :message="form.errors.klub_id" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="pelatih_id" value="Pelatih (SDM)" />
                                <select id="pelatih_id" v-model="form.pelatih_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Tanpa Pelatih —</option>
                                    <option v-for="p in pelatihs" :key="p.id" :value="p.id">{{ p.nama }}</option>
                                </select>
                                <InputError :message="form.errors.pelatih_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="kelurahan_id" value="Kelurahan (domisili)" />
                                <select id="kelurahan_id" v-model="form.kelurahan_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih Kelurahan —</option>
                                    <option v-for="k in kelurahans" :key="k.id" :value="k.id">{{ k.nama }}</option>
                                </select>
                                <InputError :message="form.errors.kelurahan_id" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <InputLabel for="status_pembinaan" value="Status Pembinaan" />
                                <select id="status_pembinaan" v-model="form.status_pembinaan" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="daerah">Daerah</option>
                                    <option value="provinsi">Provinsi</option>
                                    <option value="nasional">Nasional</option>
                                </select>
                                <InputError :message="form.errors.status_pembinaan" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="berat_badan" value="Berat (kg)" />
                                <TextInput id="berat_badan" type="number" step="0.01" v-model="form.berat_badan" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.berat_badan" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="tinggi_badan" value="Tinggi (cm)" />
                                <TextInput id="tinggi_badan" type="number" step="0.01" v-model="form.tinggi_badan" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.tinggi_badan" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="foto" value="Foto (jpg/png max 2MB)" />
                            <div v-if="fotoPreview" class="mt-2">
                                <img :src="fotoPreview" alt="foto preview" class="h-24 w-24 rounded object-cover ring-1 ring-gray-200 dark:ring-gray-600" />
                            </div>
                            <input v-if="!isShow" id="foto" type="file" accept="image/jpeg,image/png,image/jpg" class="mt-2 block w-full text-sm text-gray-700 dark:text-gray-300" @change="onFotoChange" />
                            <InputError :message="form.errors.foto" class="mt-2" />
                            <p v-if="!isShow" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload foto atlet untuk profil.</p>
                        </div>
                        <div v-if="!isShow" class="flex gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('atlets.index')" class="self-center text-sm text-gray-600 dark:text-gray-400">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
