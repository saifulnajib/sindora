<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    kecamatan: Object,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    nama: props.kecamatan?.nama ?? '',
    kode: props.kecamatan?.kode ?? '',
});

function submit() {
    if (props.isShow) return;
    if (props.isEdit) form.put(route('kecamatans.update', props.kecamatan.id));
    else form.post(route('kecamatans.store'));
}
</script>

<template>
    <Head :title="isShow ? 'Detail Kecamatan' : (isEdit ? 'Edit Kecamatan' : 'Tambah Kecamatan')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('kecamatans.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail Kecamatan' : (isEdit ? 'Edit Kecamatan' : 'Tambah Kecamatan') }}</h2>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="nama" value="Nama Kecamatan" />
                            <TextInput id="nama" v-model="form.nama" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="kode" value="Kode (opsional)" />
                            <TextInput id="kode" v-model="form.kode" class="mt-1 block w-full" :disabled="isShow" placeholder="mis. BKS" />
                            <InputError :message="form.errors.kode" class="mt-2" />
                        </div>
                        <div v-if="!isShow" class="flex gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('kecamatans.index')" class="text-sm text-gray-600 dark:text-gray-400 self-center hover:text-gray-800 dark:hover:text-gray-200">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
