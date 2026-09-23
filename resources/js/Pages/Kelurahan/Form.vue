<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    kelurahan: Object,
    kecamatans: Array,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    kecamatan_id: props.kelurahan?.kecamatan_id ?? '',
    nama: props.kelurahan?.nama ?? '',
    kode: props.kelurahan?.kode ?? '',
});

function submit(){
    if(props.isShow) return;
    if(props.isEdit) form.put(route('kelurahans.update', props.kelurahan.id));
    else form.post(route('kelurahans.store'));
}
</script>

<template>
    <Head :title="isShow ? 'Detail Kelurahan' : (isEdit ? 'Edit Kelurahan' : 'Tambah Kelurahan')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('kelurahans.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail Kelurahan' : (isEdit ? 'Edit Kelurahan' : 'Tambah Kelurahan') }}</h2>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="kecamatan_id" value="Kecamatan" />
                            <select id="kecamatan_id" v-model="form.kecamatan_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                <option value="">— Pilih Kecamatan —</option>
                                <option v-for="k in kecamatans" :key="k.id" :value="k.id">{{ k.nama }}</option>
                            </select>
                            <InputError :message="form.errors.kecamatan_id" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="nama" value="Nama Kelurahan" />
                            <TextInput id="nama" v-model="form.nama" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="kode" value="Kode (opsional)" />
                            <TextInput id="kode" v-model="form.kode" class="mt-1 block w-full" :disabled="isShow" />
                            <InputError :message="form.errors.kode" class="mt-2" />
                        </div>
                        <div v-if="!isShow" class="flex gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('kelurahans.index')" class="text-sm text-gray-600 dark:text-gray-400 self-center">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
