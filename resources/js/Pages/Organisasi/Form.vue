<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    organisasi: Object,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    nama: props.organisasi?.nama ?? '',
    singkatan: props.organisasi?.singkatan ?? '',
    jenis: props.organisasi?.jenis ?? '',
    alamat: props.organisasi?.alamat ?? '',
    ketua: props.organisasi?.ketua ?? '',
    kontak_hp: props.organisasi?.kontak_hp ?? '',
    kontak_email: props.organisasi?.kontak_email ?? '',
    deskripsi: props.organisasi?.deskripsi ?? '',
});

function submit(){
    if(props.isShow) return;
    if(props.isEdit) form.put(route('organisasis.update', props.organisasi.id));
    else form.post(route('organisasis.store'));
}
</script>

<template>
    <Head :title="isShow ? 'Detail Organisasi' : (isEdit ? 'Edit Organisasi' : 'Tambah Organisasi')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('organisasis.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail Organisasi' : (isEdit ? 'Edit Organisasi' : 'Tambah Organisasi') }}</h2>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <InputLabel for="nama" value="Nama Organisasi" />
                                <TextInput id="nama" v-model="form.nama" class="mt-1 block w-full" :disabled="isShow" required />
                                <InputError :message="form.errors.nama" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="singkatan" value="Singkatan" />
                                <TextInput id="singkatan" v-model="form.singkatan" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.singkatan" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="jenis" value="Jenis" />
                                <select id="jenis" v-model="form.jenis" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih —</option>
                                    <option value="KONI">KONI</option>
                                    <option value="KORMI">KORMI</option>
                                    <option value="NPCI">NPCI</option>
                                    <option value="BAPOMI">BAPOMI</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <InputError :message="form.errors.jenis" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <InputLabel for="alamat" value="Alamat" />
                                <textarea id="alamat" v-model="form.alamat" :disabled="isShow" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                                <InputError :message="form.errors.alamat" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="ketua" value="Ketua" />
                                <TextInput id="ketua" v-model="form.ketua" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.ketua" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="kontak_hp" value="Kontak HP" />
                                <TextInput id="kontak_hp" v-model="form.kontak_hp" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.kontak_hp" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <InputLabel for="kontak_email" value="Kontak Email" />
                                <TextInput id="kontak_email" type="email" v-model="form.kontak_email" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.kontak_email" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <InputLabel for="deskripsi" value="Deskripsi" />
                                <textarea id="deskripsi" v-model="form.deskripsi" :disabled="isShow" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                                <InputError :message="form.errors.deskripsi" class="mt-2" />
                            </div>
                        </div>
                        <div v-if="!isShow" class="flex gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('organisasis.index')" class="text-sm text-gray-600 dark:text-gray-400 self-center">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
