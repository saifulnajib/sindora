<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    events: Array,
    filters: Object,
    cabors: Array,
    organisasis: Array,
});

const cabor_id = ref(props.filters?.cabor_id ?? '');
const organisasi_id = ref(props.filters?.organisasi_id ?? '');
const month = ref(props.filters?.month ?? new Date().toISOString().slice(0, 7));
const viewMode = ref('bulan'); // bulan vs list

function applyFilters() {
    router.get(route('kalender.index'), {
        cabor_id: cabor_id.value,
        organisasi_id: organisasi_id.value,
        month: month.value,
    }, { preserveState: true, replace: true });
}

watch([cabor_id, organisasi_id], () => applyFilters());

function prevMonth() {
    const d = new Date(month.value + '-01');
    d.setMonth(d.getMonth() - 1);
    month.value = d.toISOString().slice(0, 7);
    applyFilters();
}
function nextMonth() {
    const d = new Date(month.value + '-01');
    d.setMonth(d.getMonth() + 1);
    month.value = d.toISOString().slice(0, 7);
    applyFilters();
}
function onMonthChange(e) {
    month.value = e.target.value;
    applyFilters();
}

const monthLabel = computed(() => {
    try {
        const d = new Date(month.value + '-01');
        return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
    } catch { return month.value; }
});

const daysInMonth = computed(() => {
    const d = new Date(month.value + '-01');
    return new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
});
const firstWeekday = computed(() => {
    const d = new Date(month.value + '-01');
    let wd = d.getDay(); // 0 Sun
    // Convert to Monday=0 -> Sunday=6
    return (wd + 6) % 7;
});
const gridDays = computed(() => {
    const total = daysInMonth.value;
    const lead = firstWeekday.value;
    const cells = [];
    for (let i = 0; i < lead; i++) cells.push(null);
    for (let d = 1; d <= total; d++) cells.push(d);
    // pad to complete weeks
    while (cells.length % 7 !== 0) cells.push(null);
    return cells;
});

function eventsForDay(day) {
    if (!day) return [];
    const mm = month.value;
    const dateStr = `${mm}-${String(day).padStart(2, '0')}`;
    return props.events.filter(e => {
        // event spans start..end inclusive
        return dateStr >= e.start && dateStr <= e.end;
    });
}

function badgeClass(type) {
    if (type === 'kejuaraan') return 'bg-blue-600 text-white';
    if (type === 'pembinaan') return 'bg-emerald-600 text-white';
    return 'bg-gray-500 text-white';
}

function goEvent(ev) {
    if (ev.url) router.visit(ev.url);
    else if (ev.type === 'kejuaraan') router.visit(route('kejuaraans.show', ev.entity_id));
    else if (ev.type === 'pembinaan') router.visit(route('pembinaans.show', ev.entity_id));
}

const groupedByDate = computed(() => {
    // for list view: sort events by start
    return [...props.events].sort((a,b) => a.start.localeCompare(b.start));
});
</script>

<template>
    <Head title="Kalender Kegiatan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Kalender Kegiatan</h2>
                <div class="flex items-center gap-2">
                    <button @click="viewMode = viewMode === 'bulan' ? 'list' : 'bulan'" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-gray-200 px-3 py-1.5 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600">
                        {{ viewMode === 'bulan' ? 'Tampilan List' : 'Tampilan Bulan' }}
                    </button>
                    <Link :href="route('kejuaraans.index')" class="hidden sm:inline rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Kejuaraan</Link>
                    <Link :href="route('pembinaans.index')" class="hidden sm:inline rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-gray-200 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">Pembinaan</Link>
                </div>
            </div>
        </template>

        <div class="py-6"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                    <div class="flex items-center gap-2">
                        <button @click="prevMonth" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-gray-200 p-2 hover:bg-gray-50 dark:hover:bg-gray-600" aria-label="Prev">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <div class="min-w-[160px] text-center">
                            <div class="text-sm font-semibold capitalize text-gray-800 dark:text-gray-100">{{ monthLabel }}</div>
                            <input type="month" :value="month" @change="onMonthChange" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-xs" />
                        </div>
                        <button @click="nextMonth" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-gray-200 p-2 hover:bg-gray-50 dark:hover:bg-gray-600" aria-label="Next">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <div class="h-8 w-px bg-gray-200 hidden sm:block" />
                    <select v-model="cabor_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                        <option value="">Semua Cabor</option>
                        <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                    </select>
                    <select v-model="organisasi_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                        <option value="">Semua Organisasi</option>
                        <option v-for="o in organisasis" :key="o.id" :value="o.id">{{ o.nama }}</option>
                    </select>
                    <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">{{ events.length }} kegiatan</span>
                </div>

                <!-- Bulan grid -->
                <div v-if="viewMode === 'bulan'" class="overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                    <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-700 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                        <div class="px-2 py-3">Sen</div> <div class="px-2 py-3">Sel</div> <div class="px-2 py-3">Rab</div> <div class="px-2 py-3">Kam</div> <div class="px-2 py-3">Jum</div> <div class="px-2 py-3">Sab</div> <div class="px-2 py-3">Min</div> </div> <div class="grid grid-cols-7 divide-x divide-y divide-gray-200 dark:divide-gray-700">
                        <div v-for="(day, idx) in gridDays" :key="idx" class="min-h-[110px] bg-white dark:bg-gray-800 p-1.5">
                            <div v-if="day" class="flex flex-col"> <div class="mb-1 flex items-center justify-between">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold" :class="eventsForDay(day).length ? 'bg-[#1e3a8a] dark:bg-blue-700 text-white' : 'text-gray-700 dark:text-gray-300'">{{ day }}</span>
                                    <span v-if="eventsForDay(day).length" class="text-[10px] text-gray-500 dark:text-gray-400">{{ eventsForDay(day).length }} event</span>
                                </div>
                                <div class="space-y-1"> <template v-for="ev in eventsForDay(day).slice(0,3)" :key="ev.id + '-' + day">
                                        <button @click="goEvent(ev)" class="flex w-full items-center gap-1 truncate rounded px-1.5 py-1 text-left text-[11px] font-medium dark:text-gray-100 hover:opacity-90" :class="badgeClass(ev.type)" :title="ev.title + ' (' + ev.start + ' - ' + ev.end + ')'">
                                            <span class="truncate">{{ ev.title }}</span> </button> </template> <div v-if="eventsForDay(day).length > 3" class="text-center text-[10px] text-gray-500 dark:text-gray-400">+{{ eventsForDay(day).length - 3 }} lagi</div>
                                </div>
                            </div>
                            <div v-else class="h-full bg-gray-50/50 dark:bg-gray-700/30"></div>
                        </div>
                    </div>
                </div>

                <!-- List view -->
                <div v-else class="overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                    <div v-if="groupedByDate.length === 0" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada kegiatan pada bulan ini.</div>
                    <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="ev in groupedByDate" :key="ev.id" class="flex flex-col gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="ev.type === 'kejuaraan' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800'">{{ ev.type }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ ev.start }} — {{ ev.end }}</span>
                                    <span v-if="ev.tingkat" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700 dark:text-gray-300">{{ ev.tingkat }}</span>
                                    <span v-if="ev.status" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700 dark:text-gray-300">{{ ev.status }}</span>
                                </div>
                                <div class="mt-1 truncate text-sm font-semibold text-gray-800 dark:text-gray-100">{{ ev.title }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <span v-if="ev.location">{{ ev.location }} · </span>
                                    <span v-if="ev.cabor">{{ ev.cabor.nama }}</span>
                                    <span v-if="ev.organisasi"> · {{ ev.organisasi.nama }}</span>
                                </div>
                            </div>
                            <button @click="goEvent(ev)" class="shrink-0 rounded-lg bg-[#1e3a8a] dark:bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-800 dark:hover:bg-blue-600">Lihat</button>
                        </li>
                    </ul>
                </div>

                <!-- Legend -->
                <div class="mt-4 flex flex-wrap gap-3 text-xs text-gray-600 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-blue-600"></span> Kejuaraan</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-emerald-600"></span> Pembinaan</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
