<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    sdms: Object,
    filters: Object,
    cabors: Array,
    klubs: Array,
    can: Object,
});

const search = ref(props.filters?.search ?? '');
const tipe = ref(props.filters?.tipe ?? '');
const cabor_id = ref(props.filters?.cabor_id ?? '');
const expired = ref(props.filters?.expired ?? '');

let t = null;
watch([search, tipe, cabor_id, expired], ([s, tp, cid, ex]) => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('sdms.index'), { search: s, tipe: tp, cabor_id: cid, expired: ex }, { preserveState: true, replace: true }), 300);
});

function destroyRow(id) {
    if (!confirm('Hapus SDM ini?')) return;
    router.delete(route('sdms.destroy', id));
}

function tipeBadge(s) {
    if (s.tipe_badge) return s.tipe_badge;
    if (s.tipe === 'pelatih') return 'bg-green-100 text-green-800';
    if (s.tipe === 'wasit') return 'bg-yellow-100 text-yellow-800';
    return 'bg-blue-100 text-blue-800';
}
function lisensiBadge(s) {
    return s.lisensi_badge ?? 'bg-gray-100 text-gray-600 dark:text-gray-400';
}
</script>

<template>
    <Head title="SDM" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">SDM — Pelatih / Wasit / Tenaga</h2>
                <Link v-if="can?.manage" :href="route('sdms.create')" class="rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Tambah SDM</Link>
            </div>
        </template>
        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card overflow-hidden">
                    <div class="flex flex-wrap gap-3 p-4">
                        <input v-model="search" placeholder="Cari nama..." class="w-56 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm" />
                        <select v-model="tipe" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Tipe</option>
                            <option value="pelatih">Pelatih</option>
                            <option value="wasit">Wasit</option>
                            <option value="tenaga">Tenaga</option>
                        </select>
                        <select v-model="cabor_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Cabor</option>
                            <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                        </select>
                        <select v-model="expired" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                            <option value="">Semua Lisensi</option>
                            <option value="aman">Aman (&gt;90 hari)</option>
                            <option value="peringatan">Perlu Perpanjangan (H-90)</option>
                            <option value="kritis">Segera Perpanjang (H-30)</option>
                            <option value="expired">Kadaluarsa</option>
                        </select>
                        <span class="ml-auto self-center text-xs text-gray-500 dark:text-gray-400">Total: {{ sdms.meta?.total ?? 0 }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Foto</th> <th class="px-4 py-3">Nama</th> <th class="px-4 py-3">Tipe</th> <th class="px-4 py-3">Cabor</th> <th class="px-4 py-3">Klub</th> <th class="px-4 py-3">Lisensi</th> <th class="px-4 py-3">Expired</th> <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="s in sdms.data" :key="s.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3"> <img v-if="s.foto_url" :src="s.foto_url" :alt="s.nama" class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-200" />
                                        <span v-else class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400">{{ s.nama.charAt(0).toUpperCase() }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-medium dark:text-gray-100">
                                        <div>{{ s.nama }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ s.no_hp_masked ?? s.no_hp ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3"> <span class="rounded-full px-2 py-0.5 text-xs font-medium dark:text-gray-100" :class="tipeBadge(s)">{{ s.tipe_label ?? s.tipe }}</span> </td> <td class="px-4 py-3">{{ s.cabor?.nama ?? '—' }}</td> <td class="px-4 py-3">{{ s.klub?.nama ?? '—' }}</td> <td class="px-4 py-3"> <div class="text-xs"> <span v-if="s.nomor_lisensi" class="font-mono">{{ s.nomor_lisensi }}</span> <span v-else class="text-gray-400">—</span> <span v-if="s.level" class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[11px]">{{ s.level }}</span>
                                        </div>
                                        <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium dark:text-gray-100" :class="lisensiBadge(s)">{{ s.lisensi_label ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <div v-if="s.expired_at">
                                            <div>{{ s.expired_at }}</div>
                                            <div :class="s.days_until_expired <= 0 ? 'text-red-600 font-medium' : s.days_until_expired <= 30 ? 'text-orange-600' : s.days_until_expired <=90 ? 'text-yellow-600' : 'text-green-600'">
                                                <span v-if="s.days_until_expired <= 0">Kadaluarsa ({{ Math.abs(s.days_until_expired) }} hari lalu)</span>
                                                <span v-else>{{ s.days_until_expired }} hari lagi</span>
                                            </div>
                                        </div>
                                        <span v-else class="text-gray-400">—</span> </td> <td class="px-4 py-3 text-right">
                                        <Link :href="route('sdms.show', s.id)" class="mr-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Lihat</Link>
                                        <Link v-if="can?.manage" :href="route('sdms.edit', s.id)" class="mr-2 text-sm font-medium dark:text-gray-100 text-blue-600 hover:text-blue-800">Edit</Link>
                                        <button v-if="can?.manage" @click="destroyRow(s.id)" class="text-sm font-medium dark:text-gray-100 text-red-600 hover:text-red-800">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="!sdms.data || sdms.data.length===0"><td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="sdms.meta && sdms.meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300"><span>Hal {{ sdms.meta.current_page }} / {{ sdms.meta.last_page }}</span><div class="flex gap-2"><Link v-if="sdms.links.prev" :href="sdms.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link><Link v-if="sdms.links.next" :href="sdms.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link></div></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
