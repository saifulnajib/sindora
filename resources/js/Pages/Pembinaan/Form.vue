<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    pembinaan: Object,
    cabors: Array,
    organisasis: Array,
    atlets: Array,
    klubs: Array,
    sdms: Array,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    nama_program: props.pembinaan?.nama_program ?? '',
    deskripsi: props.pembinaan?.deskripsi ?? '',
    target: props.pembinaan?.target ?? '',
    anggaran: props.pembinaan?.anggaran ?? props.pembinaan?.anggaran_raw ?? '',
    sumber_anggaran: props.pembinaan?.sumber_anggaran ?? '',
    tahun_anggaran: props.pembinaan?.tahun_anggaran ?? '',
    periode_mulai: props.pembinaan?.periode_mulai ?? '',
    periode_selesai: props.pembinaan?.periode_selesai ?? '',
    evaluasi: props.pembinaan?.evaluasi ?? '',
    status: props.pembinaan?.status ?? 'draft',
    organisasi_id: props.pembinaan?.organisasi_id ?? '',
    cabor_id: props.pembinaan?.cabor_id ?? '',
    peserta_atlet_ids: props.pembinaan?.peserta_atlet_ids ?? [],
    peserta_klub_ids: props.pembinaan?.peserta_klub_ids ?? [],
    peserta_sdm_ids: props.pembinaan?.peserta_sdm_ids ?? [],
});

function submit() {
    if (props.isShow) return;
    if (props.isEdit) {
        form.put(route('pembinaans.update', props.pembinaan.id));
    } else {
        form.post(route('pembinaans.store'));
    }
}

const sumberOptions = ['APBD', 'APBN', 'Hibah', 'Sponsor'];
const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'aktif', label: 'Aktif' },
    { value: 'selesai', label: 'Selesai' },
    { value: 'ditunda', label: 'Ditunda' },
];
</script>

<template>
    <Head :title="isShow ? 'Detail Pembinaan' : (isEdit ? 'Edit Pembinaan' : 'Tambah Pembinaan')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('pembinaans.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail Pembinaan' : (isEdit ? 'Edit Pembinaan' : 'Tambah Pembinaan') }}</h2>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="nama_program" value="Nama Program *" />
                            <TextInput id="nama_program" v-model="form.nama_program" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.nama_program" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="deskripsi" value="Deskripsi" />
                            <textarea id="deskripsi" v-model="form.deskripsi" :disabled="isShow" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                            <InputError :message="form.errors.deskripsi" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="target" value="Target" />
                            <textarea id="target" v-model="form.target" :disabled="isShow" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" placeholder="Target pembinaan..."></textarea>
                            <InputError :message="form.errors.target" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <InputLabel for="anggaran" value="Anggaran (Rp)" />
                                <TextInput id="anggaran" type="number" min="0" step="0.01" v-model="form.anggaran" class="mt-1 block w-full" :disabled="isShow" placeholder="0" />
                                <InputError :message="form.errors.anggaran" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="sumber_anggaran" value="Sumber Anggaran" />
                                <select id="sumber_anggaran" v-model="form.sumber_anggaran" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih —</option>
                                    <option v-for="s in sumberOptions" :key="s" :value="s">{{ s }}</option>
                                </select>
                                <InputError :message="form.errors.sumber_anggaran" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="tahun_anggaran" value="Tahun Anggaran" />
                                <TextInput id="tahun_anggaran" type="number" min="2000" max="2030" v-model="form.tahun_anggaran" class="mt-1 block w-full" :disabled="isShow" placeholder="2026" />
                                <InputError :message="form.errors.tahun_anggaran" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="periode_mulai" value="Periode Mulai" />
                                <TextInput id="periode_mulai" type="date" v-model="form.periode_mulai" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.periode_mulai" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="periode_selesai" value="Periode Selesai" />
                                <TextInput id="periode_selesai" type="date" v-model="form.periode_selesai" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.periode_selesai" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="evaluasi" value="Evaluasi" />
                            <textarea id="evaluasi" v-model="form.evaluasi" :disabled="isShow" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                            <InputError :message="form.errors.evaluasi" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <InputLabel for="status" value="Status" />
                                <select id="status" v-model="form.status" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                                </select>
                                <InputError :message="form.errors.status" class="mt-2" />
                            </div>
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
                        <!-- Peserta sections -->
                        <div class="rounded-lg border p-4 dark:border-gray-600">
                            <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Peserta Pembinaan</h3>
                            <div class="space-y-4">
                                <div>
                                    <InputLabel value="Atlet (multi-select)" />
                                    <select v-if="!isShow" v-model="form.peserta_atlet_ids" multiple class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" size="6">
                                        <option v-for="a in atlets" :key="a.id" :value="a.id">{{ a.nama }}</option>
                                    </select>
                                    <div v-else class="mt-1 flex flex-wrap gap-1.5">
                                        <span v-for="id in form.peserta_atlet_ids" :key="id" class="rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-700">{{ atlets.find(x=>x.id===id)?.nama ?? id }}</span>
                                        <span v-if="!form.peserta_atlet_ids.length" class="text-xs text-gray-500 dark:text-gray-400">— Tidak ada atlet —</span>
                                    </div>
                                    <div v-if="!isShow" class="mt-1 flex flex-wrap gap-1">
                                        <span v-for="id in form.peserta_atlet_ids" :key="id" class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800">
                                            {{ atlets.find(x=>x.id===id)?.nama ?? id }}
                                            <button type="button" @click="form.peserta_atlet_ids = form.peserta_atlet_ids.filter(x=>x!==id)" class="ml-1 text-blue-600 hover:text-blue-800">×</button>
                                        </span>
                                    </div>
                                    <InputError :message="form.errors.peserta_atlet_ids" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel value="Klub (multi-select)" />
                                    <select v-if="!isShow" v-model="form.peserta_klub_ids" multiple class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" size="6">
                                        <option v-for="k in klubs" :key="k.id" :value="k.id">{{ k.nama }}</option>
                                    </select>
                                    <div v-else class="mt-1 flex flex-wrap gap-1.5">
                                        <span v-for="id in form.peserta_klub_ids" :key="id" class="rounded-full bg-green-50 px-2 py-0.5 text-xs text-green-700">{{ klubs.find(x=>x.id===id)?.nama ?? id }}</span>
                                        <span v-if="!form.peserta_klub_ids.length" class="text-xs text-gray-500 dark:text-gray-400">— Tidak ada klub —</span>
                                    </div>
                                    <div v-if="!isShow" class="mt-1 flex flex-wrap gap-1">
                                        <span v-for="id in form.peserta_klub_ids" :key="id" class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800">
                                            {{ klubs.find(x=>x.id===id)?.nama ?? id }}
                                            <button type="button" @click="form.peserta_klub_ids = form.peserta_klub_ids.filter(x=>x!==id)" class="ml-1 text-green-600 hover:text-green-800">×</button>
                                        </span>
                                    </div>
                                    <InputError :message="form.errors.peserta_klub_ids" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel value="SDM (multi-select)" />
                                    <select v-if="!isShow" v-model="form.peserta_sdm_ids" multiple class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" size="6">
                                        <option v-for="s in sdms" :key="s.id" :value="s.id">{{ s.nama }}<span v-if="s.tipe"> — {{ s.tipe }}</span></option>
                                    </select>
                                    <div v-else class="mt-1 flex flex-wrap gap-1.5">
                                        <span v-for="id in form.peserta_sdm_ids" :key="id" class="rounded-full bg-purple-50 px-2 py-0.5 text-xs text-purple-700">{{ sdms.find(x=>x.id===id)?.nama ?? id }}</span>
                                        <span v-if="!form.peserta_sdm_ids.length" class="text-xs text-gray-500 dark:text-gray-400">— Tidak ada SDM —</span>
                                    </div>
                                    <div v-if="!isShow" class="mt-1 flex flex-wrap gap-1">
                                        <span v-for="id in form.peserta_sdm_ids" :key="id" class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-2 py-0.5 text-xs text-purple-800">
                                            {{ sdms.find(x=>x.id===id)?.nama ?? id }}
                                            <button type="button" @click="form.peserta_sdm_ids = form.peserta_sdm_ids.filter(x=>x!==id)" class="ml-1 text-purple-600 hover:text-purple-800">×</button>
                                        </span>
                                    </div>
                                    <InputError :message="form.errors.peserta_sdm_ids" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        <div v-if="isShow && pembinaan?.verification_status" class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 p-4 text-sm dark:text-gray-200">
                            <div class="flex flex-wrap gap-4 text-xs">
                                <span>Verifikasi: <strong>{{ pembinaan.verification_label ?? pembinaan.verification_status }}</strong></span>
                                <span v-if="pembinaan.catatan_verifikator">Catatan: {{ pembinaan.catatan_verifikator }}</span>
                            </div>
                        </div>
                        <div v-if="!isShow" class="flex gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('pembinaans.index')" class="self-center text-sm text-gray-600 dark:text-gray-400">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
