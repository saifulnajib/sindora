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
    sarpras: Object,
    kelurahans: Array,
    kecamatans: Array,
    klubs: Array,
    cabors: Array,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    nama: props.sarpras?.nama ?? '',
    jenis: props.sarpras?.jenis ?? '',
    alamat: props.sarpras?.alamat ?? '',
    kelurahan_id: props.sarpras?.kelurahan_id ?? '',
    kecamatan_id: props.sarpras?.kecamatan_id ?? '',
    klub_id: props.sarpras?.klub_id ?? '',
    cabor_id: props.sarpras?.cabor_id ?? '',
    latitude: props.sarpras?.latitude ?? '',
    longitude: props.sarpras?.longitude ?? '',
    kondisi: props.sarpras?.kondisi ?? 'baik',
    kapasitas: props.sarpras?.kapasitas ?? '',
    fasilitas: props.sarpras?.fasilitas ?? [],
    fasilitas_input: (props.sarpras?.fasilitas ?? []).join(', '),
    deskripsi: props.sarpras?.deskripsi ?? '',
    foto: null,
});

const fotoPreview = ref(props.sarpras?.foto_url ?? null);

// 8.6 Geocoding picker — bbox Tanjungpinang
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
    // clamp bbox
    const clampedLat = Math.min(1.05, Math.max(0.85, lat));
    const clampedLng = Math.min(104.55, Math.max(104.30, lng));
    form.latitude = clampedLat.toFixed(8);
    form.longitude = clampedLng.toFixed(8);
}

function onFotoChange(e) {
    const f = e.target.files[0];
    form.foto = f || null;
    if (f) fotoPreview.value = URL.createObjectURL(f);
}

// Auto-sync kecamatan from kelurahan
watch(() => form.kelurahan_id, (val) => {
    if (!val) return;
    const kel = props.kelurahans.find(k => String(k.id) === String(val));
    if (kel && kel.kecamatan_id) form.kecamatan_id = kel.kecamatan_id;
});

function submit() {
    if (props.isShow) return;
    // fasilitas_input comma separated -> array
    if (form.fasilitas_input !== undefined) {
        const raw = form.fasilitas_input ?? '';
        form.fasilitas = raw.split(',').map(s => s.trim()).filter(Boolean);
    }
    if (props.isEdit) {
        form.post(route('sarpras.update', props.sarpras.id), { _method: 'put', forceFormData: true });
    } else {
        form.post(route('sarpras.store'), { forceFormData: true });
    }
}
</script>

<template>
    <Head :title="isShow ? 'Detail Sarpras' : (isEdit ? 'Edit Sarpras' : 'Tambah Sarpras')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('sarpras.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail Sarpras' : (isEdit ? 'Edit Sarpras' : 'Tambah Sarpras') }}</h2>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="nama" value="Nama Sarpras *" />
                            <TextInput id="nama" v-model="form.nama" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="jenis" value="Jenis *" />
                                <select id="jenis" v-model="form.jenis" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih Jenis —</option>
                                    <option value="lapangan">Lapangan</option>
                                    <option value="gedung">Gedung</option>
                                    <option value="kolam">Kolam</option>
                                    <option value="stadion">Stadion</option>
                                    <option value="aula">Aula</option>
                                    <option value="gym">Gym</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                                <InputError :message="form.errors.jenis" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="kondisi" value="Kondisi" />
                                <select id="kondisi" v-model="form.kondisi" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="baik">Baik</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                </select>
                                <InputError :message="form.errors.kondisi" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="alamat" value="Alamat" />
                            <textarea id="alamat" v-model="form.alamat" :disabled="isShow" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                            <InputError :message="form.errors.alamat" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="kelurahan_id" value="Kelurahan" />
                                <select id="kelurahan_id" v-model="form.kelurahan_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih Kelurahan —</option>
                                    <option v-for="k in kelurahans" :key="k.id" :value="k.id">{{ k.nama }}<span v-if="k.kecamatan"> — {{ k.kecamatan.nama }}</span></option>
                                </select>
                                <InputError :message="form.errors.kelurahan_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="kecamatan_id" value="Kecamatan" />
                                <select id="kecamatan_id" v-model="form.kecamatan_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Pilih Kecamatan —</option>
                                    <option v-for="k in kecamatans" :key="k.id" :value="k.id">{{ k.nama }}</option>
                                </select>
                                <InputError :message="form.errors.kecamatan_id" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="klub_id" value="Klub (pengelola)" />
                                <select id="klub_id" v-model="form.klub_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Tanpa Klub —</option>
                                    <option v-for="k in klubs" :key="k.id" :value="k.id">{{ k.nama }}</option>
                                </select>
                                <InputError :message="form.errors.klub_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="cabor_id" value="Cabor" />
                                <select id="cabor_id" v-model="form.cabor_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Tanpa Cabor —</option>
                                    <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                                </select>
                                <InputError :message="form.errors.cabor_id" class="mt-2" />
                            </div>
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
                                <span>Picker Peta — klik peta untuk isi Lat/Long (bbox Tanjungpinang)</span>
                                <span class="font-mono text-[11px]">{{ form.latitude || '—' }}, {{ form.longitude || '—' }}</span>
                            </div>
                            <div style="height: 280px">
                                <LMap :zoom="mapZoom" :center="mapCenter" style="height: 100%; width: 100%" @click="onMapClick" :use-global-leaflet="false">
                                    <LTileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" attribution="&copy; OpenStreetMap" />
                                    <LMarker v-if="form.latitude && form.longitude" :lat-lng="[parseFloat(form.latitude), parseFloat(form.longitude)]" />
                                </LMap>
                            </div>
                            <p class="px-3 py-2 text-[11px] text-gray-500 dark:text-gray-400">Klik peta untuk set koordinat. Nilai otomatis clamp ke bbox [104.30,0.85,104.55,1.05].</p>
                        </div>
                        <p v-else class="text-xs text-gray-500 dark:text-gray-400">Koordinat: {{ form.latitude ?? '—' }}, {{ form.longitude ?? '—' }} — <a :href="`https://www.openstreetmap.org/?mlat=${form.latitude}&mlon=${form.longitude}#map=${mapZoom}/${form.latitude}/${form.longitude}`" target="_blank" class="text-blue-600 underline">Lihat di OSM</a></p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="kapasitas" value="Kapasitas" />
                                <TextInput id="kapasitas" type="number" v-model="form.kapasitas" class="mt-1 block w-full" :disabled="isShow" placeholder="mis. 500" />
                                <InputError :message="form.errors.kapasitas" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="fasilitas_input" value="Fasilitas (pisah koma)" />
                                <TextInput id="fasilitas_input" v-model="form.fasilitas_input" class="mt-1 block w-full" :disabled="isShow" placeholder="toilet, parkir, tribun" />
                                <InputError :message="form.errors.fasilitas" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="deskripsi" value="Deskripsi" />
                            <textarea id="deskripsi" v-model="form.deskripsi" :disabled="isShow" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm"></textarea>
                            <InputError :message="form.errors.deskripsi" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="foto" value="Foto (jpg/png max 2MB)" />
                            <div v-if="fotoPreview" class="mt-2">
                                <img :src="fotoPreview" alt="foto preview" class="h-32 w-48 rounded object-cover ring-1 ring-gray-200" />
                            </div>
                            <input v-if="!isShow" id="foto" type="file" accept="image/jpeg,image/png,image/jpg" class="mt-2 block w-full text-sm" @change="onFotoChange" />
                            <div v-if="sarpras?.foto_url && isShow" class="mt-2 text-xs"><a :href="sarpras.foto_url" target="_blank" class="text-blue-600">Lihat foto</a></div>
                            <InputError :message="form.errors.foto" class="mt-2" />
                        </div>
                        <div v-if="!isShow" class="flex gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('sarpras.index')" class="self-center text-sm text-gray-600 dark:text-gray-400">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
