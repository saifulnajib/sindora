<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, null],
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
    valueKey: {
        type: String,
        default: 'id',
    },
    labelKey: {
        type: String,
        default: 'nama',
    },
    subLabelKey: {
        type: String,
        default: 'kode',
    },
    badgeKey: {
        type: String,
        default: 'kategori',
    },
    placeholder: {
        type: String,
        default: '— Pilih Cabang Olahraga —',
    },
    searchPlaceholder: {
        type: String,
        default: 'Cari cabang olahraga (nama, singkatan, kategori)...',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    clearable: {
        type: Boolean,
        default: true,
    },
    disabledValues: {
        type: Array,
        default: () => [],
    },
    id: {
        type: String,
        default: () => 'select_' + Math.random().toString(36).slice(2, 9),
    },
    name: {
        type: String,
        default: '',
    },
    error: {
        type: [Boolean, String],
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const containerRef = ref(null);
const searchInputRef = ref(null);
const listboxRef = ref(null);

const isOpen = ref(false);
const searchQuery = ref('');
const highlightedIndex = ref(-1);

function getVal(option) {
    if (option === null || option === undefined) return '';
    if (typeof option === 'object') {
        return option[props.valueKey] !== undefined ? option[props.valueKey] : (option.value ?? option.id ?? '');
    }
    return option;
}

function getLabel(option) {
    if (option === null || option === undefined) return '';
    if (typeof option === 'object') {
        return option[props.labelKey] ?? option.nama ?? option.label ?? option.name ?? '';
    }
    return String(option);
}

function getSubLabel(option) {
    if (typeof option === 'object' && option !== null) {
        return option[props.subLabelKey] ?? option.kode ?? option.singkatan ?? '';
    }
    return '';
}

function getBadge(option) {
    if (typeof option === 'object' && option !== null) {
        return option[props.badgeKey] ?? option.kategori ?? '';
    }
    return '';
}

function isOptionDisabled(option) {
    if (!option) return false;
    const val = getVal(option);
    if (option.disabled === true) return true;
    if (props.disabledValues && props.disabledValues.some(v => String(v) === String(val))) {
        return true;
    }
    return false;
}

const selectedOption = computed(() => {
    if (props.modelValue === '' || props.modelValue === null || props.modelValue === undefined) {
        return null;
    }
    return props.options.find(opt => String(getVal(opt)) === String(props.modelValue)) || null;
});

const filteredOptions = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return props.options;

    return props.options.filter(opt => {
        const label = String(getLabel(opt)).toLowerCase();
        const sub = String(getSubLabel(opt)).toLowerCase();
        const badge = String(getBadge(opt)).toLowerCase();
        return label.includes(q) || sub.includes(q) || badge.includes(q);
    });
});

function toggleDropdown() {
    if (props.disabled) return;
    if (isOpen.value) {
        closeDropdown();
    } else {
        openDropdown();
    }
}

function openDropdown() {
    if (props.disabled) return;
    isOpen.value = true;
    searchQuery.value = '';

    // Set initial highlighted index to currently selected or first enabled option
    nextTick(() => {
        if (selectedOption.value) {
            const idx = filteredOptions.value.findIndex(o => String(getVal(o)) === String(props.modelValue));
            highlightedIndex.value = idx >= 0 ? idx : 0;
        } else {
            highlightedIndex.value = filteredOptions.value.findIndex(o => !isOptionDisabled(o));
            if (highlightedIndex.value === -1) highlightedIndex.value = 0;
        }

        if (searchInputRef.value) {
            searchInputRef.value.focus();
        }
        scrollToHighlighted();
    });
}

function closeDropdown() {
    isOpen.value = false;
    highlightedIndex.value = -1;
    searchQuery.value = '';
}

function selectOption(option) {
    if (isOptionDisabled(option)) return;
    const val = getVal(option);
    emit('update:modelValue', val);
    emit('change', val, option);
    closeDropdown();
}

function clearSelection(e) {
    e.stopPropagation();
    if (props.disabled) return;
    emit('update:modelValue', '');
    emit('change', '', null);
}

function scrollToHighlighted() {
    nextTick(() => {
        if (!listboxRef.value) return;
        const items = listboxRef.value.querySelectorAll('[role="option"]');
        const activeItem = items[highlightedIndex.value];
        if (activeItem) {
            activeItem.scrollIntoView({ block: 'nearest' });
        }
    });
}

function onTriggerKeyDown(e) {
    if (props.disabled) return;
    if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(e.key)) {
        e.preventDefault();
        openDropdown();
    }
}

function onSearchKeyDown(e) {
    if (e.isComposing) return;

    if (e.key === 'Escape') {
        e.preventDefault();
        closeDropdown();
        return;
    }

    if (e.key === 'Tab') {
        closeDropdown();
        return;
    }

    const total = filteredOptions.value.length;
    if (total === 0) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        let next = highlightedIndex.value + 1;
        if (next >= total) next = 0;
        highlightedIndex.value = next;
        scrollToHighlighted();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        let prev = highlightedIndex.value - 1;
        if (prev < 0) prev = total - 1;
        highlightedIndex.value = prev;
        scrollToHighlighted();
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (highlightedIndex.value >= 0 && highlightedIndex.value < total) {
            const opt = filteredOptions.value[highlightedIndex.value];
            if (!isOptionDisabled(opt)) {
                selectOption(opt);
            }
        }
    }
}

function handleClickOutside(e) {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        closeDropdown();
    }
}

watch(searchQuery, () => {
    // When search query changes, highlight first non-disabled result
    const firstEnabled = filteredOptions.value.findIndex(o => !isOptionDisabled(o));
    highlightedIndex.value = firstEnabled >= 0 ? firstEnabled : 0;
});

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="containerRef" class="relative w-full">
        <!-- Hidden Native Input for HTML5 Form Validation -->
        <input
            v-if="required"
            type="text"
            :id="id"
            :name="name"
            :value="modelValue || ''"
            :required="required"
            tabindex="-1"
            class="sr-only"
            aria-hidden="true"
        />

        <!-- Trigger Button -->
        <button
            type="button"
            :id="!required ? id : undefined"
            :disabled="disabled"
            @click="toggleDropdown"
            @keydown="onTriggerKeyDown"
            role="combobox"
            :aria-expanded="isOpen"
            :aria-haspopup="'listbox'"
            :aria-controls="id + '_listbox'"
            :class="[
                'group relative flex w-full items-center justify-between gap-2 rounded-lg border text-left transition-all duration-150',
                'px-3 py-2 text-xs sm:text-sm shadow-sm focus:outline-none',
                error
                    ? 'border-red-500 ring-1 ring-red-500 focus:ring-2 focus:ring-red-500'
                    : 'border-gray-300 dark:border-gray-600 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:focus:border-red-400',
                disabled
                    ? 'cursor-not-allowed bg-gray-50 text-gray-400 dark:bg-gray-800/80 dark:text-gray-500 opacity-70'
                    : 'bg-white text-gray-900 hover:border-gray-400 dark:bg-gray-700 dark:text-gray-100 dark:hover:border-gray-500',
                isOpen ? 'ring-2 ring-red-500/20 border-red-500 dark:border-red-400' : ''
            ]"
        >
            <div class="flex items-center gap-2 overflow-hidden truncate">
                <template v-if="selectedOption">
                    <!-- Icon Cabang Olahraga -->
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-red-100 text-red-700 dark:bg-red-950/70 dark:text-red-300">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </span>

                    <span class="truncate font-medium text-gray-900 dark:text-gray-100">
                        {{ getLabel(selectedOption) }}
                    </span>

                    <span
                        v-if="getSubLabel(selectedOption)"
                        class="shrink-0 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-gray-600 dark:bg-gray-600 dark:text-gray-200"
                    >
                        {{ getSubLabel(selectedOption) }}
                    </span>

                    <span
                        v-if="getBadge(selectedOption)"
                        :class="[
                            'shrink-0 rounded-full px-2 py-0.2 text-[10px] font-bold uppercase',
                            String(getBadge(selectedOption)).toLowerCase() === 'koni'
                                ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300'
                                : String(getBadge(selectedOption)).toLowerCase() === 'kormi'
                                ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'
                                : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300'
                        ]"
                    >
                        {{ getBadge(selectedOption) }}
                    </span>
                </template>

                <template v-else>
                    <span class="truncate text-gray-400 dark:text-gray-400">
                        {{ placeholder }}
                    </span>
                </template>
            </div>

            <!-- Action Controls (Clear + Chevron) -->
            <div class="flex shrink-0 items-center gap-1.5 text-gray-400 dark:text-gray-400">
                <button
                    v-if="selectedOption && clearable && !disabled"
                    type="button"
                    @click="clearSelection"
                    title="Kosongkan pilihan"
                    class="rounded p-0.5 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-600 dark:hover:text-gray-200"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <svg
                    :class="['h-4 w-4 transition-transform duration-200', isOpen ? 'rotate-180 text-red-600 dark:text-red-400' : '']"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </button>

        <!-- Dropdown Menu / Popover -->
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                :id="id + '_listbox'"
                role="listbox"
                class="absolute left-0 right-0 z-50 mt-1.5 rounded-xl border border-gray-200 bg-white shadow-2xl ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10 overflow-hidden"
            >
                <!-- Search Box Header -->
                <div class="border-b border-gray-100 p-2 dark:border-gray-700/80 bg-gray-50/70 dark:bg-gray-800/80">
                    <div class="relative flex items-center">
                        <svg
                            class="pointer-events-none absolute left-3 h-4 w-4 text-gray-400 dark:text-gray-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>

                        <input
                            ref="searchInputRef"
                            type="text"
                            v-model="searchQuery"
                            @keydown="onSearchKeyDown"
                            :placeholder="searchPlaceholder"
                            autocomplete="off"
                            class="w-full rounded-lg border border-gray-200 bg-white py-1.5 pl-9 pr-8 text-xs text-gray-900 placeholder-gray-400 transition focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                        />

                        <button
                            v-if="searchQuery"
                            type="button"
                            @click="searchQuery = ''; searchInputRef?.focus();"
                            class="absolute right-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Meta bar info -->
                    <div class="mt-1.5 flex items-center justify-between px-1 text-[11px] text-gray-500 dark:text-gray-400">
                        <span>Pilih cabang olahraga</span>
                        <span class="font-medium text-gray-600 dark:text-gray-300">
                            {{ filteredOptions.length }} pilihan
                        </span>
                    </div>
                </div>

                <!-- Options List -->
                <div
                    ref="listboxRef"
                    class="max-h-64 overflow-y-auto p-1.5 space-y-0.5 text-xs focus:outline-none"
                    tabindex="-1"
                >
                    <template v-if="filteredOptions.length > 0">
                        <button
                            v-for="(option, idx) in filteredOptions"
                            :key="getVal(option) || idx"
                            type="button"
                            role="option"
                            :aria-selected="String(getVal(option)) === String(modelValue)"
                            :disabled="isOptionDisabled(option)"
                            @click="selectOption(option)"
                            @mouseenter="highlightedIndex = idx"
                            :class="[
                                'w-full flex items-center justify-between gap-2 rounded-lg px-2.5 py-2 text-left transition',
                                isOptionDisabled(option)
                                    ? 'opacity-40 cursor-not-allowed bg-transparent text-gray-400 dark:text-gray-500'
                                    : String(getVal(option)) === String(modelValue)
                                    ? 'bg-red-50 text-red-900 font-semibold ring-1 ring-red-200 dark:bg-red-950/60 dark:text-red-200 dark:ring-red-900'
                                    : highlightedIndex === idx
                                    ? 'bg-gray-100 text-gray-900 dark:bg-gray-700/80 dark:text-gray-100'
                                    : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50'
                            ]"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <!-- Checkmark or Bullet -->
                                <span
                                    v-if="String(getVal(option)) === String(modelValue)"
                                    class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-red-600 text-white dark:bg-red-500"
                                >
                                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <span
                                    v-else
                                    class="h-1.5 w-1.5 shrink-0 rounded-full bg-gray-300 dark:bg-gray-600 ml-1.5 mr-1"
                                ></span>

                                <div class="truncate">
                                    <span class="font-medium">
                                        {{ getLabel(option) }}
                                    </span>
                                    <span
                                        v-if="getSubLabel(option)"
                                        class="ml-1.5 text-gray-500 dark:text-gray-400 text-[11px]"
                                    >
                                        ({{ getSubLabel(option) }})
                                    </span>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-1.5">
                                <span
                                    v-if="isOptionDisabled(option)"
                                    class="text-[10px] italic text-amber-700 dark:text-amber-400"
                                >
                                    (Sudah dipilih)
                                </span>

                                <span
                                    v-if="getBadge(option)"
                                    :class="[
                                        'rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide',
                                        String(getBadge(option)).toLowerCase() === 'koni'
                                            ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'
                                            : String(getBadge(option)).toLowerCase() === 'kormi'
                                            ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'
                                            : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                                    ]"
                                >
                                    {{ getBadge(option) }}
                                </span>
                            </div>
                        </button>
                    </template>

                    <!-- Empty state -->
                    <div v-else class="py-6 px-4 text-center">
                        <svg
                            class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Cabang olahraga tidak ditemukan
                        </p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
                            Tidak ada hasil yang cocok dengan "{{ searchQuery }}"
                        </p>
                    </div>
                </div>

                <!-- Footer tip -->
                <div class="border-t border-gray-100 bg-gray-50/50 px-3 py-1.5 text-[10px] text-gray-400 dark:border-gray-700/80 dark:bg-gray-800/80 flex items-center justify-between">
                    <span>Gunakan panah ↑↓ dan Enter</span>
                    <button
                        type="button"
                        @click="closeDropdown"
                        class="hover:text-gray-700 dark:hover:text-gray-200"
                    >
                        Tutup (Esc)
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
