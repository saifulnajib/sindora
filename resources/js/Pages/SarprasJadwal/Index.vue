<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    jadwals: Object,
    filters: Object,
    sarprasList: Array,
    klubs: Array,
    can: Object,
});

const sarpras_id = ref(props.filters?.sarpras_id ?? '');
const klub_id = ref(props.filters?.klub_id ?? '');
const tanggal = ref(props.filters?.tanggal ?? '');
const search = ref(props.filters?.search ?? '');

let t = null;
watch([sarpras_id, klub_id, tanggal, search], ([sid, kid, tg, s]) => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('sarpras-jadwals.index'), {
        sarpras_id: sid,
        klub_id: kid,
        tanggal: tg,
        search: s,
    }, { preserveState: true, replace: true }), 300);
});

function destroyRow(id) {
    if (!confirm('Hapus jadwal ini?')) return;
    router.delete(route('sarpras-jadwals.destroy', id));
}
</script>

<template>
    <Head title="Jadwal Sarpras" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Jadwal Pemanfaatan Sarpras</h2>
                <Link v-if="can?.manage" :href="route('sarpras-jadwals.create')" class="rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Tambah Jadwal</Link>
            </div>
        </template>
        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card overflow-hidden">
                    <div class="flex flex-wrap gap-3 p-4">
                        <select v-model="sarpras_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Sarpras</option>
                            <option v-for="s in sarprasList" :key="s.id" :value="s.id">{{ s.nama }} ({{ s.jenis }})</option>
                        </select>
                        <select v-model="klub_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Klub</option>
                            <option v-for="k in klubs" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                        <input v-model="tanggal" type="date" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <input v-model="search" placeholder="Cari kegiatan / sarpras..." class="w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <span class="ml-auto self-center text-xs text-gray-500 dark:text-gray-400">Total: {{ jadwals.meta?.total ?? 0 }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Sarpras</th> <th class="px-4 py-3">Klub</th> <th class="px-4 py-3">Tanggal</th> <th class="px-4 py-3">Jam</th> <th class="px-4 py-3">Kegiatan / Keperluan</th> <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="j in jadwals.data" :key="j.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">{{ j.sarpras?.nama ?? '—' }} <span class="text-xs text-gray-500 dark:text-gray-400">({{ j.sarpras?.jenis ?? '' }})</span></td>
                                    <td class="px-4 py-3">{{ j.klub?.nama ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ j.tanggal_formatted ?? j.tanggal ?? '—' }}</td>
                                    <td class="px-4 py-3"><span class="rounded bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs dark:text-gray-300">{{ j.jam_mulai ?? '—' }} — {{ j.jam_selesai ?? '—' }}</span></td>
                                    <td class="px-4 py-3">{{ j.kegiatan ?? j.keperluan ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <Link :href="route('sarpras-jadwals.show', j.id)" class="mr-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Lihat</Link>
                                        <Link v-if="can?.manage" :href="route('sarpras-jadwals.edit', j.id)" class="mr-2 text-sm font-medium dark:text-gray-100 text-blue-600 hover:text-blue-800">Edit</Link>
                                        <button v-if="can?.manage" @click="destroyRow(j.id)" class="text-sm font-medium dark:text-gray-100 text-red-600 hover:text-red-800">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="!jadwals.data || jadwals.data.length===0"><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada jadwal.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="jadwals.meta && jadwals.meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300">
                        <span>Hal {{ jadwals.meta.current_page }} / {{ jadwals.meta.last_page }}</span>
                        <div class="flex gap-2"> <Link v-if="jadwals.links.prev" :href="jadwals.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link>
                            <Link v-if="jadwals.links.next" :href="jadwals.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link>
                        </div>
                    </div>
                </div>

                <!-- Simple kalender hint -->
                <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    Bentrok deteksi: sistem mencegah 2 jadwal pada sarpras & tanggal yang sama dengan jam yang tumpang tindih (overlap).
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
