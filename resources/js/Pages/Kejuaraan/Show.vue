<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    kejuaraan: Object,
    can: Object,
});

const k = computed(() => props.kejuaraan ?? {});

const tingkatBadge = computed(() => {
    const map = {
        kabupaten: 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300',
        kota: 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300',
        kabupaten_kota: 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300',
        kecamatan: 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300',
        provinsi: 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300',
        nasional: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
        internasional: 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
    };
    return map[k.value.tingkat] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
});

const jenisBadge = computed(() => {
    const map = {
        turnamen: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        liga: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        festival: 'bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300',
        kejuaraan: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
        kegiatan: 'bg-slate-50 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    };
    return map[k.value.jenis] ?? 'bg-gray-50 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
});

const verificationBadge = computed(() => k.value.verification_badge ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300');

function destroyRow() {
    if (!confirm('Hapus kejuaraan ini? Data prestasi terkait tetap ada tapi tidak terhapus.')) return;
    router.delete(route('kejuaraans.destroy', k.value.id));
}
const showLightbox = ref(false);
function openLightbox() { if (k.value.poster_url) showLightbox.value = true; }
function closeLightbox() { showLightbox.value = false; }
function onKeydown(e) { if (e.key === 'Escape' && showLightbox.value) closeLightbox(); }
onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));

function submitVerification() {
    if (!confirm('Ajukan kejuaraan ini untuk verifikasi?')) return;
    router.post(route('verifikasi.submit'), { entity_type: 'kejuaraan', ids: [k.value.id] }, { preserveScroll: true });
}
</script>

<template>
    <Head :title="`Detail — ${k.nama ?? 'Kejuaraan'}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <Link :href="route('kejuaraans.index')" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                    <span class="hidden sm:inline text-gray-300 dark:text-gray-600">/</span>
                    <h2 class="truncate text-lg font-extrabold tracking-tight text-gray-900 dark:text-white">{{ k.nama }}</h2>
                    <span :class="['hidden sm:inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold ring-1', verificationBadge]">{{ k.verification_label ?? k.verification_status }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button v-if="k.verification_status==='draft' || k.verification_status==='perlu_perbaikan'" @click="submitVerification" class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-600">Ajukan Verifikasi</button>
                    <Link v-if="can?.manage" :href="route('kejuaraans.edit', k.id)" class="rounded-lg bg-[#1e3a8a] px-4 py-1.5 text-sm font-semibold text-white hover:bg-blue-800 dark:bg-blue-700 dark:hover:bg-blue-600">Edit</Link>
                    <button v-if="can?.manage" @click="destroyRow" class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-900/20">Hapus</button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Hero -->
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                    <div class="grid lg:grid-cols-[360px_1fr]">
                        <div class="relative bg-gray-50 dark:bg-gray-900/50 flex items-center justify-center p-4 min-h-[240px]">
                            <button v-if="k.poster_url" @click="openLightbox" class="group relative block rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" title="Klik untuk memperbesar">
                                <img :src="k.poster_url" :alt="k.nama" class="h-40 w-40 sm:h-44 sm:w-44 rounded-xl object-cover shadow ring-1 ring-black/5 group-hover:opacity-90 transition" />
                                <span class="absolute inset-0 flex items-center justify-center rounded-xl bg-black/0 group-hover:bg-black/20 transition">
                                    <span class="opacity-0 group-hover:opacity-100 transition inline-flex items-center gap-1 rounded-full bg-black/60 px-2 py-1 text-[11px] font-medium text-white"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15zM10.5 7.5v6m3-3h-6"/></svg> Perbesar</span>
                                </span>
                            </button>
                            <div v-else class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500">
                                <svg class="h-12 w-12 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="text-xs">Tidak ada poster</span>
                            </div>
                            <div class="absolute left-3 top-3 flex gap-1.5">
                                <span :class="['rounded-full px-2.5 py-1 text-[11px] font-bold ring-1', tingkatBadge]">{{ k.tingkat ?? '—' }}</span>
                                <span v-if="k.jenis" :class="['rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-black/5', jenisBadge]">{{ k.jenis }}</span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-tight">{{ k.nama }}</h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1.5"><svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg> {{ k.lokasi ?? '—' }}</p>

                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-700/40 p-3">
                                    <div class="text-[11px] font-bold tracking-widest text-gray-500 dark:text-gray-400">PENYELENGGARA</div>
                                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ k.penyelenggara ?? '—' }}</div>
                                    <div v-if="k.organisasi?.nama" class="text-xs text-gray-500 dark:text-gray-400">{{ k.organisasi.nama }}</div>
                                </div>
                                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-700/40 p-3">
                                    <div class="text-[11px] font-bold tracking-widest text-gray-500 dark:text-gray-400">CABOR</div>
                                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ k.cabor?.nama ?? 'Umum' }}</div>
                                    <div v-if="k.cabor?.kode" class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ k.cabor.kode }}</div>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                    {{ k.tanggal_range ?? (k.tanggal_mulai ? k.tanggal_mulai + ' — ' + k.tanggal_selesai : '—') }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700 ring-1 ring-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Prestasi: {{ typeof k.prestasis_count === 'number' ? k.prestasis_count : (k.prestasis_count ?? 0) }} entri
                                    <Link :href="route('prestasis.index', { kejuaraan_id: k.id })" class="ml-1 underline decoration-dotted hover:text-gray-900 dark:hover:text-white">Lihat →</Link>
                                </span>
                                <span :class="['inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1', verificationBadge]">Status: {{ k.verification_label }}</span>
                            </div>

                            <div v-if="k.catatan_verifikator" class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300">
                                <div class="text-xs font-bold tracking-widest opacity-70">CATATAN VERIFIKATOR</div>
                                <div class="mt-1">{{ k.catatan_verifikator }}</div>
                                <div v-if="k.verified_at" class="mt-1 text-xs opacity-60">{{ k.verified_at }} <span v-if="k.verified_by">• by #{{ k.verified_by }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details grid -->
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                            <h3 class="text-sm font-bold tracking-widest text-gray-500 dark:text-gray-400">DESKRIPSI</h3>
                            <div v-if="k.deskripsi" class="prose prose-sm dark:prose-invert mt-2 max-w-none whitespace-pre-wrap text-sm leading-6 text-gray-700 dark:text-gray-300">{{ k.deskripsi }}</div>
                            <div v-else class="mt-2 text-sm text-gray-400 dark:text-gray-500 italic">Tidak ada deskripsi.</div>
                        </div>
                        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                            <h3 class="text-sm font-bold tracking-widest text-gray-500 dark:text-gray-400">WAKTU & LOKASI</h3>
                            <dl class="mt-3 grid gap-4 sm:grid-cols-2 text-sm">
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">Tanggal Mulai</dt>
                                    <dd class="mt-0.5 font-semibold text-gray-900 dark:text-white">{{ k.tanggal_mulai ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">Tanggal Selesai</dt>
                                    <dd class="mt-0.5 font-semibold text-gray-900 dark:text-white">{{ k.tanggal_selesai ?? '—' }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">Lokasi</dt>
                                    <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">{{ k.lokasi ?? '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                            <h3 class="text-sm font-bold tracking-widest text-gray-500 dark:text-gray-400">RINGKASAN</h3>
                            <dl class="mt-3 space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Jenis</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white capitalize">{{ k.jenis ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Tingkat</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white capitalize">{{ k.tingkat ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Organisasi</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white">{{ k.organisasi?.nama ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Cabor</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white">{{ k.cabor?.nama ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Dibuat</dt>
                                    <dd class="text-xs text-gray-700 dark:text-gray-300">{{ k.created_at ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Diperbarui</dt>
                                    <dd class="text-xs text-gray-700 dark:text-gray-300">{{ k.updated_at ?? '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div class="rounded-xl bg-gradient-to-br from-[#1e3a8a] to-blue-700 p-5 text-white shadow">
                            <h3 class="text-sm font-bold tracking-widest text-blue-200">TINDAKAN</h3>
                            <div class="mt-3 grid gap-2">
                                <Link :href="route('prestasis.index', { kejuaraan_id: k.id })" class="rounded-lg bg-white px-4 py-2 text-center text-sm font-semibold text-[#1e3a8a] hover:bg-blue-50">Lihat Prestasi ({{ k.prestasis_count ?? 0 }})</Link>
                                <Link :href="route('kalender.index', { month: (k.tanggal_mulai||'').slice(0,7) })" class="rounded-lg bg-white/10 px-4 py-2 text-center text-sm font-medium ring-1 ring-white/20 hover:bg-white/15">Lihat di Kalender</Link>
                                <Link v-if="can?.manage" :href="route('kejuaraans.edit', k.id)" class="rounded-lg bg-white/10 px-4 py-2 text-center text-sm font-medium ring-1 ring-white/20 hover:bg-white/15">Edit Kejuaraan</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lightbox -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showLightbox" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeLightbox">
                    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="closeLightbox"></div>
                    <div class="relative max-h-[90vh] max-w-[90vw] overflow-auto rounded-2xl bg-white dark:bg-gray-900 p-2 shadow-2xl ring-1 ring-white/10">
                        <button @click="closeLightbox" class="absolute right-2 top-2 z-10 rounded-full bg-black/60 p-1.5 text-white hover:bg-black/80" aria-label="Tutup">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <img :src="k.poster_url" :alt="k.nama" class="max-h-[85vh] max-w-[85vw] rounded-xl object-contain" />
                        <div class="px-2 pb-1 pt-2 text-center text-xs text-gray-500 dark:text-gray-400">{{ k.nama }} — {{ k.tingkat }}</div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
