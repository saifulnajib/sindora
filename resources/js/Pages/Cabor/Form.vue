<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    cabor: Object,
    organisasis: Array,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    organisasi_id: props.cabor?.organisasi_id ?? '',
    nama: props.cabor?.nama ?? '',
    kode: props.cabor?.kode ?? '',
    kategori: props.cabor?.kategori ?? '',
    deskripsi: props.cabor?.deskripsi ?? '',
});

function submit(){
    if(props.isShow) return;
    if(props.isEdit) form.put(route('cabors.update', props.cabor.id));
    else form.post(route('cabors.store'));
}
</script>

<template>
    <Head :title="isShow ? 'Detail Cabor' : (isEdit ? 'Edit Cabor' : 'Tambah Cabor')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('cabors.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail Cabor' : (isEdit ? 'Edit Cabor' : 'Tambah Cabor') }}</h2>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="organisasi_id" value="Organisasi" />
                            <select id="organisasi_id" v-model="form.organisasi_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                <option value="">— Pilih Organisasi —</option>
                                <option v-for="o in organisasis" :key="o.id" :value="o.id">{{ o.nama }} ({{ o.singkatan ?? '-' }})</option>
                            </select>
                            <InputError :message="form.errors.organisasi_id" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="nama" value="Nama Cabor" />
                            <TextInput id="nama" v-model="form.nama" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="kode" value="Kode (opsional)" />
                                <TextInput id="kode" v-model="form.kode" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.kode" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="kategori" value="Kategori" />
                                <select id="kategori" v-model="form.kategori" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih —</option>
                                    <option value="prestasi">Prestasi</option>
                                    <option value="massal">Massal</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                                <InputError :message="form.errors.kategori" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="deskripsi" value="Deskripsi" />
                            <textarea id="deskripsi" v-model="form.deskripsi" :disabled="isShow" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                            <InputError :message="form.errors.deskripsi" class="mt-2" />
                        </div>
                        <div v-if="!isShow" class="flex gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('cabors.index')" class="text-sm text-gray-600 dark:text-gray-400 self-center">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
