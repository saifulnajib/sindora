<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    notifications: Object,
    unread_count: Number,
});

function markRead(id) {
    router.post(route('notifications.read', id), {}, { preserveScroll: true });
}
function markAllRead() {
    router.post(route('notifications.readAll'), {}, { preserveScroll: true });
}
function goNotif(n) {
    const url = n.data?.url;
    if (url) router.visit(url);
}
</script>

<template>
    <Head title="Notifikasi" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Notifikasi</h2>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ unread_count ?? 0 }} belum dibaca · total {{ notifications.meta?.total ?? 0 }}</span>
                    <button v-if="(unread_count ?? 0) > 0" @click="markAllRead" class="rounded-lg border bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50">Tandai semua dibaca</button>
                </div>
            </div>
        </template>
        <div class="py-6"> <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                    <ul v-if="notifications.data && notifications.data.length" class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="n in notifications.data" :key="n.id" class="flex gap-3 px-4 py-3" :class="!n.read_at ? 'bg-blue-50/60 dark:bg-blue-900/30' : 'bg-white dark:bg-gray-800'">
                            <div class="mt-0.5"> <span v-if="!n.read_at" class="inline-block h-2 w-2 rounded-full bg-blue-600"></span>
                                <span v-else class="inline-block h-2 w-2 rounded-full bg-gray-300"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm text-gray-800 dark:text-gray-100">{{ n.data?.message ?? n.data?.action ?? 'Notifikasi' }}</div>
                                <div v-if="n.data?.entity_name" class="text-xs text-gray-600 dark:text-gray-400">Entity: {{ n.data.entity_type }} #{{ n.data.entity_id }} — {{ n.data.entity_name }}</div>
                                <div v-if="n.data?.catatan" class="mt-1 rounded bg-yellow-50 px-2 py-1 text-xs text-yellow-800">Catatan: {{ n.data.catatan }}</div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ n.created_at_human ?? n.created_at }} <span v-if="n.data?.actor_name">· oleh {{ n.data.actor_name }}</span></div>
                            </div>
                            <div class="flex shrink-0 flex-col gap-1">
                                <button v-if="!n.read_at" @click="markRead(n.id)" class="rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-gray-200 px-2 py-1 text-xs hover:bg-gray-50 dark:hover:bg-gray-600">Tandai dibaca</button>
                                <button v-if="n.data?.url" @click="goNotif(n)" class="rounded bg-[#1e3a8a] dark:bg-blue-700 px-2 py-1 text-xs font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Lihat</button>
                            </div>
                        </li>
                    </ul>
                    <div v-else class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada notifikasi.</div>
                    <div v-if="notifications.meta && notifications.meta.last_page>1" class="flex justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 text-xs dark:text-gray-300">
                        <span>Hal {{ notifications.meta.current_page }} / {{ notifications.meta.last_page }}</span>
                        <div class="flex gap-2"> <Link v-if="notifications.links.prev" :href="notifications.links.prev" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Prev</Link>
                            <Link v-if="notifications.links.next" :href="notifications.links.next" preserve-state class="rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-600">Next</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
