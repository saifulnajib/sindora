<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    sebaranKategori: {
        type: Object,
        default: () => ({
            total_atlet: 0,
            items: [],
        }),
    },
    title: {
        type: String,
        default: 'Distribusi Atlet Berdasarkan Kategori',
    },
    subtitle: {
        type: String,
        default: 'Proporsi pembinaan atlet Kota Tanjungpinang dalam Tiga Pilar Olahraga',
    },
    showToggle: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['select-category']);

// Chart type: 'donut' | 'pie'
const chartType = ref('donut');

// Hovered or clicked category key ('koni' | 'kormi' | 'disabilitas' | null)
const hoveredKey = ref(null);
const selectedKey = ref(null);

const activeKey = computed(() => hoveredKey.value || selectedKey.value || null);

// Color configuration per category
const categoryTheme = {
    koni: {
        fill: '#dc2626',
        hover: '#b91c1c',
        gradientStart: '#ef4444',
        gradientEnd: '#b91c1c',
        badgeBg: 'bg-red-100 text-red-800 dark:bg-red-950/70 dark:text-red-300 border-red-200 dark:border-red-800',
        dotClass: 'bg-red-600',
        textColor: 'text-red-600 dark:text-red-400',
        ringClass: 'ring-red-500/40 border-red-300 dark:border-red-700',
        glowColor: 'rgba(220, 38, 38, 0.35)',
    },
    kormi: {
        fill: '#f59e0b',
        hover: '#d97706',
        gradientStart: '#fbbf24',
        gradientEnd: '#d97706',
        badgeBg: 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-200 dark:border-amber-800',
        dotClass: 'bg-amber-500',
        textColor: 'text-amber-600 dark:text-amber-400',
        ringClass: 'ring-amber-500/40 border-amber-300 dark:border-amber-700',
        glowColor: 'rgba(245, 158, 11, 0.35)',
    },
    disabilitas: {
        fill: '#2563eb',
        hover: '#1d4ed8',
        gradientStart: '#60a5fa',
        gradientEnd: '#1d4ed8',
        badgeBg: 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-200 dark:border-blue-800',
        dotClass: 'bg-blue-600',
        textColor: 'text-blue-600 dark:text-blue-400',
        ringClass: 'ring-blue-500/40 border-blue-300 dark:border-blue-700',
        glowColor: 'rgba(37, 99, 235, 0.35)',
    },
};

// Safe access to theme
function getTheme(key) {
    return categoryTheme[key] || {
        fill: '#64748b',
        hover: '#475569',
        gradientStart: '#94a3b8',
        gradientEnd: '#475569',
        badgeBg: 'bg-slate-100 text-slate-800 border-slate-200',
        dotClass: 'bg-slate-600',
        textColor: 'text-slate-600',
        ringClass: 'ring-slate-500/30',
        glowColor: 'rgba(100, 116, 139, 0.35)',
    };
}

// Compute total atlet count
const totalAtlet = computed(() => {
    if (props.sebaranKategori?.total_atlet > 0) {
        return props.sebaranKategori.total_atlet;
    }
    const items = props.sebaranKategori?.items || [];
    return items.reduce((sum, item) => sum + (item.atlet_count || 0), 0) || 0;
});

// Currently active item metadata for center text
const activeItem = computed(() => {
    if (!activeKey.value) return null;
    return (props.sebaranKategori?.items || []).find((i) => i.key === activeKey.value) || null;
});

// Trigonometry helper for SVG arc coordinates (clock-wise starting from 12 o'clock)
function getPoint(cx, cy, r, angleDeg) {
    const rad = (angleDeg * Math.PI) / 180;
    return {
        x: cx + r * Math.sin(rad),
        y: cy - r * Math.cos(rad),
    };
}

// Compute SVG slices data
const cx = 160;
const cy = 160;
const outerR = 125;

const slices = computed(() => {
    const items = props.sebaranKategori?.items || [];
    const total = totalAtlet.value;
    if (!items.length || total === 0) return [];

    const innerR = chartType.value === 'donut' ? 76 : 0;
    let currentAngle = 0;

    return items.map((item) => {
        const count = item.atlet_count || 0;
        const angleDelta = (count / total) * 360;
        const startAngle = currentAngle;
        const endAngle = currentAngle + angleDelta;
        const midAngle = startAngle + angleDelta / 2;
        currentAngle = endAngle;

        // Path generation
        let pathD = '';
        const isFullCircle = angleDelta >= 359.99;

        if (isFullCircle) {
            if (innerR > 0) {
                // Two concentric circles with cutout
                pathD = `
                    M ${cx} ${cy - outerR}
                    A ${outerR} ${outerR} 0 1 1 ${cx} ${cy + outerR}
                    A ${outerR} ${outerR} 0 1 1 ${cx} ${cy - outerR}
                    M ${cx} ${cy - innerR}
                    A ${innerR} ${innerR} 0 1 0 ${cx} ${cy + innerR}
                    A ${innerR} ${innerR} 0 1 0 ${cx} ${cy - innerR}
                    Z
                `;
            } else {
                pathD = `
                    M ${cx} ${cy - outerR}
                    A ${outerR} ${outerR} 0 1 1 ${cx} ${cy + outerR}
                    A ${outerR} ${outerR} 0 1 1 ${cx} ${cy - outerR}
                    Z
                `;
            }
        } else {
            const p1 = getPoint(cx, cy, outerR, startAngle);
            const p2 = getPoint(cx, cy, outerR, endAngle);
            const largeArcFlag = angleDelta > 180 ? 1 : 0;

            if (innerR > 0) {
                const p3 = getPoint(cx, cy, innerR, endAngle);
                const p4 = getPoint(cx, cy, innerR, startAngle);

                pathD = `
                    M ${p1.x.toFixed(2)} ${p1.y.toFixed(2)}
                    A ${outerR} ${outerR} 0 ${largeArcFlag} 1 ${p2.x.toFixed(2)} ${p2.y.toFixed(2)}
                    L ${p3.x.toFixed(2)} ${p3.y.toFixed(2)}
                    A ${innerR} ${innerR} 0 ${largeArcFlag} 0 ${p4.x.toFixed(2)} ${p4.y.toFixed(2)}
                    Z
                `;
            } else {
                pathD = `
                    M ${cx} ${cy}
                    L ${p1.x.toFixed(2)} ${p1.y.toFixed(2)}
                    A ${outerR} ${outerR} 0 ${largeArcFlag} 1 ${p2.x.toFixed(2)} ${p2.y.toFixed(2)}
                    Z
                `;
            }
        }

        // Hover offset translation (pops out along the midAngle vector)
        const offset = 7;
        const radMid = (midAngle * Math.PI) / 180;
        const hoverDx = (offset * Math.sin(radMid)).toFixed(2);
        const hoverDy = (-offset * Math.cos(radMid)).toFixed(2);

        // Mid point for slice percentage label in Pie mode
        const labelR = innerR > 0 ? (outerR + innerR) / 2 : outerR * 0.68;
        const labelPoint = getPoint(cx, cy, labelR, midAngle);

        return {
            ...item,
            startAngle,
            endAngle,
            midAngle,
            angleDelta,
            pathD,
            hoverDx,
            hoverDy,
            labelPoint,
            theme: getTheme(item.key),
        };
    });
});

function handleSliceClick(item) {
    if (selectedKey.value === item.key) {
        selectedKey.value = null;
    } else {
        selectedKey.value = item.key;
        emit('select-category', item);
    }
}
</script>

<template>
    <div
        class="relative overflow-hidden rounded-3xl border border-slate-200/90 bg-gradient-to-b from-white to-slate-50/60 p-6 sm:p-7 shadow-xs dark:border-slate-800 dark:from-slate-900/90 dark:to-slate-950/60"
    >
        <!-- CARD HEADER -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-5 dark:border-slate-800/80">
            <div>
                <div class="inline-flex items-center gap-1.5 rounded-md bg-red-100 px-2 py-0.5 text-[11px] font-extrabold text-red-800 dark:bg-red-950/70 dark:text-red-300">
                    <span>🥧</span>
                    <span>Visualisasi Komposisi Atlet</span>
                </div>
                <h3 class="mt-1.5 text-lg sm:text-xl font-black text-slate-900 dark:text-white">
                    {{ title }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ subtitle }}
                </p>
            </div>

            <!-- Chart Mode Toggle Switch -->
            <div v-if="showToggle" class="inline-flex items-center rounded-xl bg-slate-100 p-1 dark:bg-slate-800/80 self-start sm:self-center">
                <button
                    type="button"
                    @click="chartType = 'donut'"
                    :class="[
                        'rounded-lg px-2.5 py-1 text-xs font-bold transition flex items-center gap-1.5',
                        chartType === 'donut'
                            ? 'bg-white text-slate-900 shadow-xs dark:bg-slate-700 dark:text-white'
                            : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white'
                    ]"
                    title="Tampilan grafik Donut modern dengan statistik pusat"
                >
                    <span>🍩</span>
                    <span>Donut</span>
                </button>
                <button
                    type="button"
                    @click="chartType = 'pie'"
                    :class="[
                        'rounded-lg px-2.5 py-1 text-xs font-bold transition flex items-center gap-1.5',
                        chartType === 'pie'
                            ? 'bg-white text-slate-900 shadow-xs dark:bg-slate-700 dark:text-white'
                            : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white'
                    ]"
                    title="Tampilan grafik Pie klasik"
                >
                    <span>🥧</span>
                    <span>Pie</span>
                </button>
            </div>
        </div>

        <!-- MAIN CONTENT: CHART & LEGEND -->
        <div class="mt-6 grid grid-cols-1 items-center gap-6 lg:grid-cols-12">
            <!-- SVG PIE/DONUT CHART -->
            <div class="relative flex items-center justify-center lg:col-span-6">
                <div class="relative w-64 h-64 sm:w-72 sm:h-72">
                    <svg
                        viewBox="0 0 320 320"
                        class="w-full h-full select-none overflow-visible drop-shadow-sm"
                        role="graphics-document"
                        aria-label="Grafik Pie Komposisi Atlet Berdasarkan Kategori"
                    >
                        <defs>
                            <!-- Linear Gradients for Slices -->
                            <linearGradient
                                v-for="slice in slices"
                                :key="'grad-' + slice.key"
                                :id="'pie-grad-' + slice.key"
                                x1="0%"
                                y1="0%"
                                x2="100%"
                                y2="100%"
                            >
                                <stop offset="0%" :stop-color="slice.theme.gradientStart" />
                                <stop offset="100%" :stop-color="slice.theme.gradientEnd" />
                            </linearGradient>

                            <!-- Drop Shadow Filter for Hovered Slice -->
                            <filter id="slice-hover-glow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="4" stdDeviation="6" flood-opacity="0.3" />
                            </filter>
                        </defs>

                        <!-- Slices Group -->
                        <g>
                            <g
                                v-for="slice in slices"
                                :key="slice.key"
                                class="cursor-pointer transition-all duration-300"
                                :style="{
                                    transform: activeKey === slice.key ? `translate(${slice.hoverDx}px, ${slice.hoverDy}px)` : 'translate(0, 0)',
                                }"
                                @mouseenter="hoveredKey = slice.key"
                                @mouseleave="hoveredKey = null"
                                @click="handleSliceClick(slice)"
                            >
                                <!-- Slice Path -->
                                <path
                                    :d="slice.pathD"
                                    :fill="`url(#pie-grad-${slice.key})`"
                                    class="stroke-white dark:stroke-slate-900 transition-all duration-300"
                                    :stroke-width="slices.length > 1 ? 3 : 0"
                                    stroke-linejoin="round"
                                    :class="[
                                        activeKey === slice.key
                                            ? 'brightness-110 filter drop-shadow-md'
                                            : (activeKey && activeKey !== slice.key ? 'opacity-40 saturate-50' : 'opacity-100 hover:brightness-105')
                                    ]"
                                />

                                <!-- Percentage Label (Visible in Pie Mode if slice is large enough) -->
                                <text
                                    v-if="chartType === 'pie' && slice.angleDelta > 28"
                                    :x="slice.labelPoint.x"
                                    :y="slice.labelPoint.y"
                                    text-anchor="middle"
                                    dominant-baseline="central"
                                    class="fill-white text-[11px] font-black pointer-events-none drop-shadow"
                                >
                                    {{ slice.persentase }}%
                                </text>
                            </g>
                        </g>
                    </svg>

                    <!-- HTML CENTER METRIC OVERLAY (DONUT MODE) -->
                    <div
                        v-if="chartType === 'donut'"
                        class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center px-4"
                    >
                        <transition mode="out-in" name="fade-fast">
                            <!-- Category Hovered State -->
                            <div v-if="activeItem" :key="'active-' + activeItem.key" class="flex flex-col items-center">
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider border shadow-2xs"
                                    :class="getTheme(activeItem.key).badgeBg"
                                >
                                    {{ activeItem.nama }}
                                </span>
                                <span class="mt-1 text-3xl font-black tracking-tight" :class="getTheme(activeItem.key).textColor">
                                    {{ (activeItem.atlet_count || 0).toLocaleString() }}
                                </span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                    {{ activeItem.persentase }}% dari total
                                </span>
                                <span class="mt-0.5 text-[10.5px] font-medium text-slate-500 dark:text-slate-400">
                                    {{ activeItem.atlet_putra || 0 }} ♂ · {{ activeItem.atlet_putri || 0 }} ♀
                                </span>
                            </div>

                            <!-- Default Total State -->
                            <div v-else :key="'default-total'" class="flex flex-col items-center">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    Total Atlet
                                </span>
                                <span class="mt-1 text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                                    {{ totalAtlet.toLocaleString() }}
                                </span>
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    Orang Terbina
                                </span>
                                <span class="mt-0.5 text-[10.5px] font-bold text-red-700 dark:text-red-400">
                                    3 Pilar Olahraga
                                </span>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>

            <!-- INTERACTIVE LEGEND & PILLAR BREAKDOWN -->
            <div class="lg:col-span-6 space-y-3">
                <div
                    v-for="item in slices"
                    :key="item.key"
                    @mouseenter="hoveredKey = item.key"
                    @mouseleave="hoveredKey = null"
                    @click="handleSliceClick(item)"
                    :class="[
                        'group relative flex flex-col justify-between rounded-2xl border p-4 transition-all duration-200 cursor-pointer',
                        activeKey === item.key
                            ? 'bg-white shadow-md ring-2 ring-offset-1 dark:bg-slate-800/90 dark:ring-offset-slate-900 ' + item.theme.ringClass
                            : 'bg-white/80 hover:bg-white hover:border-slate-300 dark:bg-slate-900/60 dark:hover:bg-slate-900 border-slate-200/80 dark:border-slate-800'
                    ]"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-3 w-3 shrink-0 rounded-full" :class="item.theme.dotClass"></span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-black text-slate-900 dark:text-white">
                                        {{ item.nama }}
                                    </h4>
                                    <span class="rounded-md px-1.5 py-0.2 text-[10px] font-bold border" :class="item.theme.badgeBg">
                                        {{ item.badge }}
                                    </span>
                                </div>
                                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                    {{ item.subnama }}
                                </p>
                            </div>
                        </div>

                        <!-- Count & Percentage Badges -->
                        <div class="text-right">
                            <span class="text-base font-black text-slate-900 dark:text-white">
                                {{ (item.atlet_count || 0).toLocaleString() }}
                            </span>
                            <span class="ml-1 text-xs font-semibold text-slate-500 dark:text-slate-400">atlet</span>
                            <div class="text-[11px] font-extrabold" :class="item.theme.textColor">
                                {{ item.persentase }}%
                            </div>
                        </div>
                    </div>

                    <!-- Gender Ratio & Cabor Quick Metrics -->
                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2 text-[11px] text-slate-500 dark:border-slate-800 dark:text-slate-400">
                        <div class="flex items-center gap-2 font-medium">
                            <span class="inline-flex items-center gap-1">
                                <span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>
                                {{ item.atlet_putra || 0 }} Putra
                            </span>
                            <span>•</span>
                            <span class="inline-flex items-center gap-1">
                                <span class="h-1.5 w-1.5 rounded-full bg-pink-500"></span>
                                {{ item.atlet_putri || 0 }} Putri
                            </span>
                        </div>
                        <div class="font-bold text-slate-700 dark:text-slate-300">
                            {{ item.cabor_count || 0 }} Cabor
                        </div>
                    </div>

                    <!-- Mini proportion progress bar inside legend card -->
                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div
                            class="h-full rounded-full transition-all duration-500"
                            :class="item.theme.dotClass"
                            :style="{ width: (item.persentase || 0) + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Footer Summary Note -->
                <div class="pt-1 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <span class="text-xs">💡</span>
                        <span>Klik kategori untuk fokus grafik</span>
                    </span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">
                        100% Terverifikasi DISPORA
                    </span>
                </div>
            </div>
        </div>

        <!-- ACCESSIBLE SCREEN-READER TABLE (WCAG / A11Y COMPLIANT) -->
        <table class="sr-only">
            <caption>Ringkasan Distribusi Atlet Berdasarkan Tiga Pilar Kategori Keolahragaan Kota Tanjungpinang</caption>
            <thead>
                <tr>
                    <th scope="col">Kategori</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col">Jumlah Atlet</th>
                    <th scope="col">Persentase</th>
                    <th scope="col">Putra</th>
                    <th scope="col">Putri</th>
                    <th scope="col">Jumlah Cabang Olahraga</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in sebaranKategori?.items || []" :key="'sr-' + item.key">
                    <td>{{ item.nama }}</td>
                    <td>{{ item.subnama }}</td>
                    <td>{{ item.atlet_count }}</td>
                    <td>{{ item.persentase }}%</td>
                    <td>{{ item.atlet_putra }}</td>
                    <td>{{ item.atlet_putri }}</td>
                    <td>{{ item.cabor_count }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.fade-fast-enter-active,
.fade-fast-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-fast-enter-from,
.fade-fast-leave-to {
    opacity: 0;
    transform: scale(0.96);
}
</style>
