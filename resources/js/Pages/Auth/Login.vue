<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const isDark = ref(false);
function applyTheme(dark) {
    isDark.value = dark;
    const root = document.documentElement;
    if (dark) root.classList.add('dark');
    else root.classList.remove('dark');
    try { localStorage.setItem('sindora-theme', dark ? 'dark' : 'light'); } catch(e) {}
}
function toggleTheme() { applyTheme(!isDark.value); }
onMounted(() => {
    try {
        const stored = localStorage.getItem('sindora-theme');
        if (stored) isDark.value = stored === 'dark';
        else isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(isDark.value);
    } catch(e) {}
});

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const quickLoading = ref('');

const quickAccounts = [
    {
        key: 'super_admin',
        label: 'Super Admin',
        sublabel: 'Admin Dispora',
        email: 'superadmin@sindora.test',
        password: 'password',
        color: 'from-violet-600 to-indigo-600',
        ring: 'ring-violet-200',
        icon: '👑',
        desc: 'Akses penuh • Master data • User',
    },
    {
        key: 'verifikator',
        label: 'Verifikator',
        sublabel: 'Dispora',
        email: 'verifikator@sindora.test',
        password: 'password',
        color: 'from-emerald-600 to-teal-600',
        ring: 'ring-emerald-200',
        icon: '✅',
        desc: 'Setujui • Tolak • Revisi',
    },
    {
        key: 'operator_organisasi',
        label: 'Operator Organisasi',
        sublabel: 'KONI / KORMI',
        email: 'org@sindora.test',
        password: 'password',
        color: 'from-blue-600 to-cyan-600',
        ring: 'ring-blue-200',
        icon: '🏢',
        desc: 'Kelola Cabor naungan',
    },
    {
        key: 'operator_klub',
        label: 'Operator Klub',
        sublabel: 'Perkumpulan',
        email: 'klub@sindora.test',
        password: 'password',
        color: 'from-orange-500 to-amber-500',
        ring: 'ring-orange-200',
        icon: '🏟️',
        desc: 'Input Atlet • Pelatih • Sarpras',
    },
    {
        key: 'viewer',
        label: 'Pimpinan',
        sublabel: 'Eksekutif Viewer',
        email: 'pimpinan@sindora.test',
        password: 'password',
        color: 'from-slate-600 to-gray-700',
        ring: 'ring-slate-200',
        icon: '👁️',
        desc: 'Read-only • Dashboard • GIS',
    },
];

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const quickLogin = (acc) => {
    // Use quick-login endpoint (passwordless in local) for 1-click
    quickLoading.value = acc.email;
    router.post(
        route('quick.login'),
        { email: acc.email },
        {
            onFinish: () => (quickLoading.value = ''),
            onError: () => {
                // fallback: fill form and submit via normal login
                form.email = acc.email;
                form.password = acc.password;
                submit();
            },
        },
    );
};

const fillForm = (acc) => {
    form.email = acc.email;
    form.password = acc.password;
    document.getElementById('email')?.focus();
};
</script>

<template>
    <Head title="Masuk — SINDORA" />
    <div class="min-h-screen bg-[#f1f5f9] dark:bg-gray-900 flex relative">
        <button type="button" @click="toggleTheme" :title="isDark ? 'Light mode' : 'Dark mode'" class="absolute right-4 top-4 z-20 inline-flex items-center justify-center rounded-full p-2.5 bg-white/90 dark:bg-gray-800/90 text-gray-600 dark:text-gray-300 ring-1 ring-slate-200 dark:ring-gray-700 shadow hover:bg-white dark:hover:bg-gray-700">
            <svg v-if="!isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0112 21.75 9.75 9.75 0 1115.002 2.248 7.5 7.5 0 0021.752 15.002z"/></svg>
            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
        </button>
        <!-- Left Branding — hidden on mobile -->
        <div class="hidden lg:flex lg:w-[52%] xl:w-[56%] relative overflow-hidden bg-gradient-to-br from-[#0f2a6b] via-[#1e3a8a] to-[#1e40af] text-white">
            <!-- decorative blobs -->
            <div class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-white/10 blur-3xl" />
            <div class="absolute top-1/2 -right-32 h-[480px] w-[480px] rounded-full bg-cyan-400/10 blur-3xl" />
            <div class="absolute -bottom-32 left-1/4 h-80 w-80 rounded-full bg-violet-500/10 blur-3xl" />

            <div class="relative z-10 flex w-full flex-col justify-between p-10 xl:p-12">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-[#1e3a8a] font-black text-xl shadow-lg">S</div>
                        <div>
                            <div class="text-[11px] tracking-[0.18em] text-white/70 font-semibold">KOTA TANJUNGPINANG</div>
                            <div class="text-xl font-extrabold tracking-tight -mt-1">SINDORA</div>
                        </div>
                        <span class="ml-2 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-bold tracking-widest">Sprint 1</span>
                    </div>
                    <div class="mt-10 max-w-[520px]">
                        <h1 class="text-4xl xl:text-[42px] font-extrabold leading-[1.05] tracking-tight">
                            Sistem Informasi<br />
                            <span class="text-cyan-200">Data Olahraga Daerah</span> </h1> <p class="mt-4 text-[15px] leading-6 text-white/80 max-w-[480px]">
                            DATA → INFORMASI → ANALISIS → KEBIJAKAN → PRESTASI.<br />
                            Kelola Atlet, Klub, SDM, Sarpras & Prestasi terintegrasi untuk Dispora Tanjungpinang.
                        </p>
                    </div>
                    <div class="mt-8 grid grid-cols-3 gap-3 max-w-[520px]">
                        <div class="rounded-2xl bg-white/10 backdrop-blur p-4 ring-1 ring-white/15">
                            <div class="text-2xl font-black">16</div>
                            <div class="text-[11px] tracking-widest text-white/70">TABEL DB</div>
                            <div class="text-xs text-white/60">MySQL 8</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 backdrop-blur p-4 ring-1 ring-white/15">
                            <div class="text-2xl font-black">5</div>
                            <div class="text-[11px] tracking-widest text-white/70">ROLE RBAC</div>
                            <div class="text-xs text-white/60">Spatie Permission</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 backdrop-blur p-4 ring-1 ring-white/15">
                            <div class="text-2xl font-black">18</div>
                            <div class="text-[11px] tracking-widest text-white/70">WILAYAH</div>
                            <div class="text-xs text-white/60">4 Kec • 18 Kel</div>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-2 max-w-[520px]">
                        <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs ring-1 ring-white/15">Laravel 12</span>
                        <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs ring-1 ring-white/15">Inertia Vue 3</span>
                        <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs ring-1 ring-white/15">Tailwind</span>
                        <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs ring-1 ring-white/15">Leaflet GIS</span>
                    </div>
                </div>
                <div class="space-y-3 max-w-[520px]">
                    <div class="flex items-center gap-2 text-[11px] tracking-[0.14em] text-white/60 font-semibold">
                        <span class="h-px w-6 bg-white/30" /> SPRINT 1 — Fondasi & RBAC
                    </div>
                    <div class="rounded-xl bg-black/20 p-3 text-xs leading-5 text-white/70 ring-1 ring-white/10">
                        Semua akun demo password: <span class="font-mono font-bold text-white">password</span> • Quick login aktif di env local
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Form -->
        <div class="flex w-full lg:w-[48%] xl:w-[44%] items-center justify-center bg-[#f1f5f9] dark:bg-gray-900 px-4 py-8 sm:px-6 lg:px-8">
            <div class="w-full max-w-[520px]">
                <!-- Mobile header -->
                <div class="lg:hidden mb-6 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#1e3a8a] dark:bg-blue-700 text-white font-black">S</div>
                    <div>
                        <div class="text-xs tracking-[0.14em] text-slate-500 font-semibold">KOTA TANJUNGPINANG</div>
                        <div class="font-extrabold tracking-tight text-slate-900 -mt-1">SINDORA</div>
                    </div>
                    <span class="ml-auto rounded-full bg-[#1e3a8a] dark:bg-blue-700 px-2.5 py-1 text-[10px] font-bold tracking-widest text-white">Sprint 1</span>
                </div>

                <div class="rounded-[20px] bg-white dark:bg-gray-800 shadow-[0_8px_40px_rgba(15,42,107,0.08)] ring-1 ring-slate-200 dark:ring-gray-700 overflow-hidden">
                    <div class="px-6 sm:px-8 pt-7 pb-2">
                        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Masuk ke SINDORA</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Gunakan akun demo atau login manual. Data → Prestasi.</p>
                    </div>

                    <div v-if="status" class="mx-6 sm:mx-8 mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium dark:text-gray-100 text-emerald-700 ring-1 ring-emerald-200">
                        {{ status }}
                    </div>

                    <!-- Quick Login -->
                    <div class="px-6 sm:px-8 pt-5">
                        <div class="flex items-center justify-between">
                            <div class="text-[11px] font-bold tracking-[0.14em] text-slate-500">QUICK LOGIN — 1 KLIK (DEMO)</div>
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold tracking-widest text-amber-700 ring-1 ring-amber-200">LOCAL ONLY</span>
                        </div>
                        <div class="mt-3 grid grid-cols-1 gap-2.5">
                            <button
                                v-for="acc in quickAccounts"
                                :key="acc.email"
                                type="button"
                                @click="quickLogin(acc)"
                                :disabled="!!quickLoading"
                                class="group flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-3 text-left shadow-sm transition hover:border-slate-300 dark:hover:border-gray-600 hover:shadow-md hover:-translate-y-[1px] disabled:opacity-60"
                            >
                                <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow', acc.color]">{{ acc.icon }}</div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[13px] font-extrabold leading-none text-slate-900 dark:text-white">{{ acc.label }}</span>
                                        <span class="rounded-full bg-slate-100 dark:bg-gray-700 px-1.5 py-0.5 text-[10px] font-bold tracking-widest text-slate-600 dark:text-gray-300">{{ acc.sublabel }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-gray-400 truncate">{{ acc.desc }} • {{ acc.email }}</div>
                                </div>
                                <div class="ml-2 flex items-center gap-1.5">
                                    <span v-if="quickLoading === acc.email" class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-slate-700" />
                                    <span v-else class="rounded-full bg-slate-900 px-3 py-1.5 text-[11px] font-bold tracking-widest text-white group-hover:bg-black">MASUK →</span>
                                </div>
                            </button>
                        </div>
                        <div class="mt-2 flex gap-2">
                            <button
                                v-for="acc in quickAccounts"
                                :key="'fill-'+acc.email"
                                type="button"
                                @click="fillForm(acc)"
                                class="rounded-full bg-slate-50 px-2.5 py-1 text-[10px] font-bold tracking-widest text-slate-600 ring-1 ring-slate-200 hover:bg-white"
                            >
                                Isi {{ acc.key }}
                            </button>
                        </div>
                        <div class="mt-3 hidden"> <!-- fallback fill triggers for keyboard users handled below --> </div>
                    </div>
                    <div class="px-6 sm:px-8 py-4">
                        <div class="flex items-center gap-3">
                            <span class="h-px flex-1 bg-slate-200" />
                            <span class="text-[11px] font-bold tracking-[0.14em] text-slate-400">ATAU LOGIN MANUAL</span>
                            <span class="h-px flex-1 bg-slate-200" />
                        </div>
                    </div>

                    <!-- Manual Form -->
                    <form @submit.prevent="submit" class="px-6 sm:px-8 pb-6 space-y-4">
                        <div>
                            <InputLabel for="email" value="Email" class="text-slate-700" />
                            <TextInput id="email" type="email" class="mt-1.5 block w-full rounded-xl border-slate-200 focus:border-[#1e3a8a] focus:ring-[#1e3a8a]" v-model="form.email" required autofocus autocomplete="username" placeholder="nama@sindora.test" />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div>
                            <div class="flex items-center justify-between">
                                <InputLabel for="password" value="Password" class="text-slate-700" />
                                <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs font-semibold text-slate-500 hover:text-slate-700 underline">Lupa password?</Link>
                            </div>
                            <TextInput id="password" type="password" class="mt-1.5 block w-full rounded-xl border-slate-200 focus:border-[#1e3a8a] focus:ring-[#1e3a8a]" v-model="form.password" required autocomplete="current-password" placeholder="••••••••" />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>
                        <label class="flex items-center gap-2">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="text-sm text-slate-600">Ingat saya</span>
                            <span class="ml-auto text-xs text-slate-400">Password demo: <span class="font-mono font-bold text-slate-700">password</span></span>
                        </label>

                        <PrimaryButton class="w-full justify-center rounded-xl bg-[#1e3a8a] dark:bg-blue-700 py-3 text-[13px] font-extrabold tracking-widest hover:bg-[#162f6b] focus:bg-[#162f6b]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            <span v-if="form.processing" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white" />
                            MASUK → DASHBOARD
                        </PrimaryButton>

                        <div class="flex items-center justify-between text-xs">
                            <Link href="/" class="font-semibold text-slate-500 hover:text-slate-700">← Kembali ke Beranda</Link>
                            <span class="text-slate-400">Butuh bantuan? Hubungi Admin Dispora</span>
                        </div>
                    </form>

                    <div class="bg-slate-50 px-6 sm:px-8 py-3 flex items-center justify-between text-[11px] leading-4 text-slate-500 ring-1 ring-slate-100">
                        <span>© 2026 Dispora Kota Tanjungpinang • SINDORA v1 — Sprint 1</span>
                        <span class="hidden sm:inline">DATA → INFORMASI → ANALISIS → KEBIJAKAN → PRESTASI</span>
                    </div>
                </div>
                <div class="mt-3 text-center text-[11px] text-slate-400">Quick login hanya untuk akun demo di environment local. Di production gunakan password.</div>
            </div>
        </div>
    </div>
</template>
