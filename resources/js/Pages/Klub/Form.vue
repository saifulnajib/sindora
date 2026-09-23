<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { LMap, LTileLayer, LMarker } from '@vue-leaflet/vue-leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    klub: Object,
    cabors: Array,
    kelurahans: Array,
    kecamatans: Array,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    nama: props.klub?.nama ?? '',
    cabor_id: props.klub?.cabor_id ?? '',
    kelurahan_id: props.klub?.kelurahan_id ?? '',
    kecamatan_id: props.klub?.kecamatan_id ?? '',
    alamat: props.klub?.alamat ?? '',
    ketua: props.klub?.ketua ?? '',
    kontak_hp: props.klub?.kontak_hp ?? (props.klub?.kontak?.hp ?? ''),
    kontak_email: props.klub?.kontak_email ?? (props.klub?.kontak?.email ?? ''),
    deskripsi: props.klub?.deskripsi ?? '',
    nomor_sk: props.klub?.nomor_sk ?? '',
    tanggal_sk: props.klub?.tanggal_sk ?? '',
    jadwal_latihan: props.klub?.jadwal_latihan ?? [],
    latitude: props.klub?.latitude ?? '',
    longitude: props.klub?.longitude ?? '',
    logo: null,
    dokumen_legalitas: null,
});

const logoPreview = ref(props.klub?.logo_url ?? null);
const dokumenUrl = ref(props.klub?.dokumen_url ?? null);

const bbox = { center: { lat: 0.917, lng: 104.45 }, zoom: 12 };
const mapZoom = ref(bbox.zoom);
const mapCenter = computed(() => {
    const lat = parseFloat(form.latitude);
    const lng = parseFloat(form.longitude);
    if (!isNaN(lat) && !isNaN(lng) && lat >= 0.85 && lat <= 1.05 && lng >= 104.30 && lng <= 104.55) return [lat, lng];
    return [bbox.center.lat, bbox.center.lng];
});
function onMapClick(e) {
    const { lat, lng } = e.latlng;
    form.latitude = Math.min(1.05, Math.max(0.85, lat)).toFixed(8);
    form.longitude = Math.min(104.55, Math.max(104.30, lng)).toFixed(8);
}

function onLogoChange(e) {
    const f = e.target.files[0];
    form.logo = f || null;
    if (f) logoPreview.value = URL.createObjectURL(f);
}
function onDokumenChange(e) {
    const f = e.target.files[0];
    form.dokumen_legalitas = f || null;
}

// Auto-sync kecamatan from kelurahan
watch(() => form.kelurahan_id, (val) => {
    if (!val) return;
    const kel = props.kelurahans.find(k => String(k.id) === String(val));
    if (kel && kel.kecamatan_id) form.kecamatan_id = kel.kecamatan_id;
});

function addJadwal() {
    form.jadwal_latihan.push({ hari: '', jam_mulai: '', jam_selesai: '', lokasi: '' });
}
function removeJadwal(idx) {
    form.jadwal_latihan.splice(idx, 1);
}

function submit() {
    if (props.isShow) return;
    if (props.isEdit) {
        form.post(route('klubs.update', props.klub.id), {
            _method: 'put',
            forceFormData: true,
        });
    } else {
        form.post(route('klubs.store'), { forceFormData: true });
    }
}
</script>

<template>
    <Head :title="isShow ? 'Detail Klub' : (isEdit ? 'Edit Klub' : 'Tambah Klub')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('klubs.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail Klub' : (isEdit ? 'Edit Klub' : 'Tambah Klub') }}</h2>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="nama" value="Nama Klub *" />
                            <TextInput id="nama" v-model="form.nama" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="cabor_id" value="Cabor *" />
                                <select id="cabor_id" v-model="form.cabor_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih Cabor —</option>
                                    <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                                </select>
                                <InputError :message="form.errors.cabor_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="kelurahan_id" value="Kelurahan" />
                                <select id="kelurahan_id" v-model="form.kelurahan_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih Kelurahan —</option>
                                    <option v-for="k in kelurahans" :key="k.id" :value="k.id">{{ k.nama }}<span v-if="k.kecamatan"> — {{ k.kecamatan.nama }}</span></option>
                                </select>
                                <InputError :message="form.errors.kelurahan_id" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="kecamatan_id" value="Kecamatan" />
                            <select id="kecamatan_id" v-model="form.kecamatan_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                <option value="">— Pilih Kecamatan —</option>
                                <option v-for="k in kecamatans" :key="k.id" :value="k.id">{{ k.nama }}</option>
                            </select>
                            <InputError :message="form.errors.kecamatan_id" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="alamat" value="Alamat" />
                            <textarea id="alamat" v-model="form.alamat" :disabled="isShow" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                            <InputError :message="form.errors.alamat" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
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
                        </div>
                        <div>
                            <InputLabel for="kontak_email" value="Kontak Email" />
                            <TextInput id="kontak_email" type="email" v-model="form.kontak_email" class="mt-1 block w-full" :disabled="isShow" />
                            <InputError :message="form.errors.kontak_email" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="latitude" value="Latitude (bbox 0.85–1.05)" />
                                <TextInput id="latitude" v-model="form.latitude" type="number" step="0.00000001" class="mt-1 block w-full" :disabled="isShow" placeholder="0.917" />
                                <InputError :message="form.errors.latitude" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="longitude" value="Longitude (bbox 104.30–104.55)" />
                                <TextInput id="longitude" v-model="form.longitude" type="number" step="0.00000001" class="mt-1 block w-full" :disabled="isShow" placeholder="104.45" />
                                <InputError :message="form.errors.longitude" class="mt-2" />
                            </div>
                        </div>
                        <div v-if="!isShow" class="rounded-xl overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700">
                            <div class="bg-gray-50 dark:bg-gray-700/50 px-3 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 flex items-center justify-between">
                                <span>Picker Peta — klik peta untuk isi Lat/Long</span>
                                <span class="font-mono text-[11px]">{{ form.latitude || '—' }}, {{ form.longitude || '—' }}</span>
                            </div>
                            <div style="height: 280px">
                                <LMap :zoom="mapZoom" :center="mapCenter" style="height: 100%; width: 100%" @click="onMapClick" :use-global-leaflet="false">
                                    <LTileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" attribution="&copy; OpenStreetMap" />
                                    <LMarker v-if="form.latitude && form.longitude" :lat-lng="[parseFloat(form.latitude), parseFloat(form.longitude)]" />
                                </LMap>
                            </div>
                        </div>
                        <p v-else class="text-xs text-gray-500 dark:text-gray-400">Koordinat: {{ form.latitude ?? '—' }}, {{ form.longitude ?? '—' }}</p>
                        <!-- File uploads -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="logo" value="Logo (jpg/png max 2MB)" />
                                <div v-if="logoPreview" class="mt-2">
                                    <img :src="logoPreview" alt="logo preview" class="h-16 w-16 rounded object-cover ring-1 ring-gray-200" />
                                </div>
                                <input v-if="!isShow" id="logo" type="file" accept="image/jpeg,image/png,image/jpg" class="mt-2 block w-full text-sm" @change="onLogoChange" />
                                <div v-if="klub?.logo_url && isShow" class="mt-2 text-xs"><a :href="klub.logo_url" target="_blank" class="text-blue-600">Lihat logo</a></div>
                                <InputError :message="form.errors.logo" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="dokumen_legalitas" value="Dokumen Legalitas (pdf/jpg/png max 5MB)" />
                                <input v-if="!isShow" id="dokumen_legalitas" type="file" accept=".pdf,.jpg,.jpeg,.png" class="mt-2 block w-full text-sm" @change="onDokumenChange" />
                                <div v-if="klub?.dokumen_url" class="mt-2 text-xs"><a :href="klub.dokumen_url" target="_blank" class="text-blue-600">Lihat dokumen</a></div>
                                <InputError :message="form.errors.dokumen_legalitas" class="mt-2" />
                            </div>
                        </div>
                        <!-- Jadwal latihan repeatable -->
                        <div>
                            <div class="flex items-center justify-between">
                                <InputLabel value="Jadwal Latihan" />
                                <button v-if="!isShow" type="button" @click="addJadwal" class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-2 py-1 hover:bg-gray-50 dark:hover:bg-gray-600 text-xs">+ Tambah Jadwal</button>
                            </div>
                            <div v-if="form.jadwal_latihan && form.jadwal_latihan.length>0" class="mt-2 space-y-3">
                                <div v-for="(j, idx) in form.jadwal_latihan" :key="idx" class="grid gap-2 rounded border p-3 sm:grid-cols-4 dark:border-gray-600">
                                    <input v-model="j.hari" :disabled="isShow" placeholder="Hari" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                                    <input v-model="j.jam_mulai" :disabled="isShow" placeholder="Mulai (08:00)" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                                    <input v-model="j.jam_selesai" :disabled="isShow" placeholder="Selesai (10:00)" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                                    <div class="flex gap-2">
                                        <input v-model="j.lokasi" :disabled="isShow" placeholder="Lokasi" class="flex-1 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                                        <button v-if="!isShow" type="button" @click="removeJadwal(idx)" class="text-xs text-red-600">Hapus</button>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="mt-2 text-xs text-gray-500 dark:text-gray-400">Belum ada jadwal. Klik Tambah Jadwal.</p>
                            <InputError :message="form.errors.jadwal_latihan" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="nomor_sk" value="Nomor SK" />
                                <TextInput id="nomor_sk" v-model="form.nomor_sk" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.nomor_sk" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="tanggal_sk" value="Tanggal SK" />
                                <TextInput id="tanggal_sk" type="date" v-model="form.tanggal_sk" class="mt-1 block w-full" :disabled="isShow" />
                                <InputError :message="form.errors.tanggal_sk" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="deskripsi" value="Deskripsi" />
                            <textarea id="deskripsi" v-model="form.deskripsi" :disabled="isShow" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                            <InputError :message="form.errors.deskripsi" class="mt-2" />
                        </div>
                        <div v-if="!isShow" class="flex gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('klubs.index')" class="self-center text-sm text-gray-600 dark:text-gray-400">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
