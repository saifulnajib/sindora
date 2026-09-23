<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
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
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900 relative"
    >
        <button
            type="button"
            @click="toggleTheme"
            :title="isDark ? 'Light mode' : 'Dark mode'"
            class="absolute right-4 top-4 inline-flex items-center justify-center rounded-full p-2 text-gray-500 hover:bg-white hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200 ring-1 ring-gray-200 dark:ring-gray-700 bg-white/80 dark:bg-gray-800/80 backdrop-blur"
            aria-label="Toggle theme"
        >
            <svg v-if="!isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0112 21.75 9.75 9.75 0 1115.002 2.248 7.5 7.5 0 0021.752 15.002z"/></svg>
            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
        </button>
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800"
        >
            <slot />
        </div>
    </div>
</template>
