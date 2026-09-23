<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sdm: Object,
    cabors: Array,
    klubs: Array,
    kelurahans: Array,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    nama: props.sdm?.nama ?? '',
    nik: props.sdm?.nik ?? '',
    no_hp: props.sdm?.no_hp ?? '',
    tipe: props.sdm?.tipe ?? 'pelatih',
    cabor_id: props.sdm?.cabor_id ?? '',
    klub_id: props.sdm?.klub_id ?? '',
    kelurahan_id: props.sdm?.kelurahan_id ?? '',
    lisensi_nomor: props.sdm?.nomor_lisensi ?? props.sdm?.lisensi_nomor ?? '',
    lisensi_level: props.sdm?.level ?? props.sdm?.lisensi_level ?? '',
    kategori_tenaga: props.sdm?.kategori_tenaga ?? '',
    lisensi_terbit: props.sdm?.tanggal_terbit ?? props.sdm?.lisensi_terbit ?? '',
    expired_at: props.sdm?.expired_at ?? '',
    spesialisasi: props.sdm?.spesialisasi ?? '',
    alamat: props.sdm?.alamat ?? '',
    email: props.sdm?.email ?? '',
    foto: null,
});

const fotoPreview = ref(props.sdm?.foto_url ?? null);

function onFotoChange(e) {
    const f = e.target.files[0];
    form.foto = f || null;
    if (f) fotoPreview.value = URL.createObjectURL(f);
}

function submit() {
    if (props.isShow) return;
    if (props.isEdit) {
        form.post(route('sdms.update', props.sdm.id), { _method: 'put', forceFormData: true });
    } else {
        form.post(route('sdms.store'), { forceFormData: true });
    }
}
</script>

<template>
    <Head :title="isShow ? 'Detail SDM' : (isEdit ? 'Edit SDM' : 'Tambah SDM')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('sdms.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail SDM' : (isEdit ? 'Edit SDM' : 'Tambah SDM') }}</h2>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="nama" value="Nama *" />
                            <TextInput id="nama" v-model="form.nama" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="tipe" value="Tipe *" />
                                <select id="tipe" v-model="form.tipe" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="pelatih">Pelatih</option>
                                    <option value="wasit">Wasit</option>
                                    <option value="tenaga">Tenaga</option>
                                </select>
                                <InputError :message="form.errors.tipe" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="nik" value="NIK (16 digit)" />
                                <TextInput id="nik" v-model="form.nik" class="mt-1 block w-full font-mono text-sm" :disabled="isShow" maxlength="16" placeholder="32xxxxxxxxxxxxxx" />
                                <p v-if="sdm?.nik_masked && isShow" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Masked: {{ sdm.nik_masked }}</p>
                                <InputError :message="form.errors.nik" class="mt-2" />
                            </div>
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
                                <InputLabel for="cabor_id" value="Cabor" />
                                <select id="cabor_id" v-model="form.cabor_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih Cabor —</option>
                                    <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                                </select>
                                <InputError :message="form.errors.cabor_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="klub_id" value="Klub" />
                                <select id="klub_id" v-model="form.klub_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Tanpa Klub —</option>
                                    <option v-for="k in klubs" :key="k.id" :value="k.id">{{ k.nama }}</option>
                                </select>
                                <InputError :message="form.errors.klub_id" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="lisensi_nomor" value="Nomor Lisensi" />
                                <TextInput id="lisensi_nomor" v-model="form.lisensi_nomor" class="mt-1 block w-full" :disabled="isShow" placeholder="mis. LIC-2026-001" />
                                <InputError :message="form.errors.lisensi_nomor" class="mt-2" />
                                <InputError :message="form.errors.nomor_lisensi" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="lisensi_level" value="Level Lisensi" />
                                <select id="lisensi_level" v-model="form.lisensi_level" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih Level —</option>
                                    <option value="C">C</option>
                                    <option value="B">B</option>
                                    <option value="A">A</option>
                                    <option value="Nasional">Nasional</option>
                                    <option value="daerah">Daerah</option>
                                    <option value="provinsi">Provinsi</option>
                                    <option value="internasional">Internasional</option>
                                </select>
                                <InputError :message="form.errors.lisensi_level" class="mt-2" />
                                <InputError :message="form.errors.level" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="lisensi_terbit" value="Tanggal Terbit Lisensi" />
                                <TextInput id="lisensi_terbit" type="date" v-model="form.lisensi_terbit" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.lisensi_terbit" class="mt-2" />
                                <InputError :message="form.errors.tanggal_terbit" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="expired_at" value="Tanggal Kadaluarsa" />
                                <TextInput id="expired_at" type="date" v-model="form.expired_at" class="mt-1 block w-full" :disabled="isShow" />
                                <p v-if="sdm?.days_until_expired !== null && sdm?.days_until_expired !== undefined" class="mt-1 text-xs" :class="sdm.days_until_expired <=0 ? 'text-red-600' : sdm.days_until_expired <=30 ? 'text-orange-600' : 'text-gray-500 dark:text-gray-400'">
                                    <span v-if="sdm.days_until_expired <=0">Kadaluarsa</span>
                                    <span v-else>{{ sdm.days_until_expired }} hari lagi — {{ sdm.lisensi_label }}</span>
                                </p>
                                <InputError :message="form.errors.expired_at" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="spesialisasi" value="Spesialisasi" />
                                <TextInput id="spesialisasi" v-model="form.spesialisasi" class="mt-1 block w-full" :disabled="isShow" placeholder="mis. Fisik, Teknik, dll" />
                                <InputError :message="form.errors.spesialisasi" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="kategori_tenaga" value="Kategori Tenaga (jika tipe tenaga)" />
                                <TextInput id="kategori_tenaga" v-model="form.kategori_tenaga" class="mt-1 block w-full" :disabled="isShow" placeholder="medis/psikolog/gizi" />
                                <InputError :message="form.errors.kategori_tenaga" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="alamat" value="Alamat" />
                            <textarea id="alamat" v-model="form.alamat" :disabled="isShow" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                            <InputError :message="form.errors.alamat" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="foto" value="Foto (jpg/png max 2MB)" />
                            <div v-if="fotoPreview" class="mt-2">
                                <img :src="fotoPreview" alt="foto preview" class="h-24 w-24 rounded object-cover ring-1 ring-gray-200" />
                            </div>
                            <input v-if="!isShow" id="foto" type="file" accept="image/jpeg,image/png,image/jpg" class="mt-2 block w-full text-sm" @change="onFotoChange" />
                            <InputError :message="form.errors.foto" class="mt-2" />
                        </div>
                        <div v-if="!isShow" class="flex gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('sdms.index')" class="self-center text-sm text-gray-600 dark:text-gray-400">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
