<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    jadwal: Object,
    sarprasList: Array,
    klubs: Array,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    sarpras_id: props.jadwal?.sarpras_id ?? '',
    klub_id: props.jadwal?.klub_id ?? '',
    tanggal: props.jadwal?.tanggal ?? '',
    jam_mulai: props.jadwal?.jam_mulai ?? '',
    jam_selesai: props.jadwal?.jam_selesai ?? '',
    kegiatan: props.jadwal?.kegiatan ?? props.jadwal?.keperluan ?? '',
    hari: props.jadwal?.hari ?? '',
});

function submit() {
    if (props.isShow) return;
    if (props.isEdit) {
        form.put(route('sarpras-jadwals.update', props.jadwal.id));
    } else {
        form.post(route('sarpras-jadwals.store'));
    }
}
</script>

<template>
    <Head :title="isShow ? 'Detail Jadwal Sarpras' : (isEdit ? 'Edit Jadwal Sarpras' : 'Tambah Jadwal Sarpras')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('sarpras-jadwals.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail Jadwal' : (isEdit ? 'Edit Jadwal' : 'Tambah Jadwal') }}</h2>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="sarpras_id" value="Sarpras *" />
                            <select id="sarpras_id" v-model="form.sarpras_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" required>
                                <option value="">— Pilih Sarpras —</option>
                                <option v-for="s in sarprasList" :key="s.id" :value="s.id">{{ s.nama }} ({{ s.jenis }})</option>
                            </select>
                            <InputError :message="form.errors.sarpras_id" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="klub_id" value="Klub (opsional)" />
                            <select id="klub_id" v-model="form.klub_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                <option value="">— Tanpa Klub —</option>
                                <option v-for="k in klubs" :key="k.id" :value="k.id">{{ k.nama }}</option>
                            </select>
                            <InputError :message="form.errors.klub_id" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="tanggal" value="Tanggal *" />
                            <TextInput id="tanggal" v-model="form.tanggal" type="date" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.tanggal" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="jam_mulai" value="Jam Mulai *" />
                                <TextInput id="jam_mulai" v-model="form.jam_mulai" type="time" class="mt-1 block w-full" :disabled="isShow" required />
                                <InputError :message="form.errors.jam_mulai" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="jam_selesai" value="Jam Selesai *" />
                                <TextInput id="jam_selesai" v-model="form.jam_selesai" type="time" class="mt-1 block w-full" :disabled="isShow" required />
                                <InputError :message="form.errors.jam_selesai" class="mt-2" />
                            </div>
                        </div>
                        <div v-if="form.errors.jam_mulai || form.errors.jam_selesai" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                            Slot bentrok — pilih jam yang tidak tumpang tindih dengan jadwal lain pada sarpras & tanggal yang sama.
                        </div>
                        <div>
                            <InputLabel for="kegiatan" value="Kegiatan / Keperluan" />
                            <TextInput id="kegiatan" v-model="form.kegiatan" class="mt-1 block w-full" :disabled="isShow" placeholder="Latihan, pertandingan, dll" />
                            <InputError :message="form.errors.kegiatan" class="mt-2" />
                            <InputError :message="form.errors.keperluan" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="hari" value="Hari (opsional, untuk jadwal rutin)" />
                            <select id="hari" v-model="form.hari" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                <option value="">— Tanpa hari —</option>
                                <option value="senin">Senin</option>
                                <option value="selasa">Selasa</option>
                                <option value="rabu">Rabu</option>
                                <option value="kamis">Kamis</option>
                                <option value="jumat">Jumat</option>
                                <option value="sabtu">Sabtu</option>
                                <option value="minggu">Minggu</option>
                            </select>
                            <InputError :message="form.errors.hari" class="mt-2" />
                        </div>
                        <div v-if="!isShow" class="flex gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('sarpras-jadwals.index')" class="self-center text-sm text-gray-600 dark:text-gray-400">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
