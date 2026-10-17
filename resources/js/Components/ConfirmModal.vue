<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Konfirmasi',
    },
    message: {
        type: String,
        default: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    },
    itemDetail: {
        type: String,
        default: null,
    },
    warningDetail: {
        type: String,
        default: null,
    },
    confirmText: {
        type: String,
        default: 'Ya, Lanjutkan',
    },
    cancelText: {
        type: String,
        default: 'Batal',
    },
    type: {
        type: String,
        default: 'warning', // 'info' | 'success' | 'warning' | 'danger'
    },
    processing: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: 'md',
    },
});

const emit = defineEmits(['close', 'confirm']);

const handleClose = () => {
    if (!props.processing) {
        emit('close');
    }
};

const handleConfirm = () => {
    emit('confirm');
};
</script>

<template>
    <Modal :show="show" :max-width="maxWidth" @close="handleClose">
        <!-- Top Accent Line -->
        <div
            class="h-1.5 w-full"
            :class="{
                'bg-gradient-to-r from-rose-500 via-red-500 to-amber-500': type === 'danger',
                'bg-gradient-to-r from-amber-400 via-orange-400 to-yellow-400': type === 'warning',
                'bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500': type === 'success',
                'bg-gradient-to-r from-blue-500 via-indigo-500 to-cyan-500': type === 'info',
            }"
        ></div>

        <div class="p-6">
            <div class="flex items-start gap-4">
                <!-- Icon based on type with ambient ring -->
                <div class="shrink-0">
                    <div
                        v-if="type === 'danger'"
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 ring-8 ring-rose-50 dark:bg-rose-950/50 dark:text-rose-400 dark:ring-rose-950/30"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </div>
                    <div
                        v-else-if="type === 'success'"
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 ring-8 ring-emerald-50 dark:bg-emerald-950/50 dark:text-emerald-400 dark:ring-emerald-950/30"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                    <div
                        v-else-if="type === 'warning'"
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 ring-8 ring-amber-50 dark:bg-amber-950/50 dark:text-amber-400 dark:ring-amber-950/30"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div
                        v-else
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 ring-8 ring-blue-50 dark:bg-blue-950/50 dark:text-blue-400 dark:ring-blue-950/30"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                    </div>
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 leading-snug">{{ title }}</h3>
                    <div class="mt-1 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        <slot name="content">
                            <p>{{ message }}</p>
                        </slot>
                    </div>
                </div>
            </div>

            <!-- Optional Item Detail -->
            <div
                v-if="itemDetail"
                class="mt-4 rounded-xl border border-slate-200/80 bg-slate-50 p-3.5 dark:border-slate-800 dark:bg-slate-850/70"
            >
                <div class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    <span class="h-1.5 w-1.5 rounded-full" :class="type === 'danger' ? 'bg-rose-500' : 'bg-blue-500'"></span>
                    <span>Data yang dipilih:</span>
                </div>
                <p class="mt-1 text-sm font-bold text-slate-900 dark:text-slate-100 break-words">
                    {{ itemDetail }}
                </p>
            </div>

            <!-- Optional Warning Detail -->
            <div
                v-if="warningDetail"
                class="mt-3 flex items-start gap-2 rounded-xl p-3 text-xs leading-relaxed"
                :class="{
                    'bg-rose-50/80 text-rose-700 dark:bg-rose-950/30 dark:text-rose-300 border border-rose-200/50 dark:border-rose-900/40': type === 'danger',
                    'bg-amber-50/80 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300 border border-amber-200/50 dark:border-amber-900/40': type === 'warning',
                    'bg-blue-50/80 text-blue-700 dark:bg-blue-950/30 dark:text-blue-300 border border-blue-200/50 dark:border-blue-900/40': type === 'info',
                    'bg-emerald-50/80 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300 border border-emerald-200/50 dark:border-emerald-900/40': type === 'success',
                }"
            >
                <svg class="h-4 w-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ warningDetail }}</span>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/50">
            <SecondaryButton :disabled="processing" @click="handleClose">
                {{ cancelText }}
            </SecondaryButton>

            <button
                type="button"
                :disabled="processing"
                @click="handleConfirm"
                :class="[
                    'inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-xs font-bold text-white shadow-md transition disabled:opacity-50 active:scale-95 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2',
                    type === 'danger'
                        ? 'bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 shadow-rose-600/25 focus:ring-rose-500'
                        : type === 'success'
                        ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-600/25 focus:ring-emerald-500'
                        : type === 'warning'
                        ? 'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-amber-500/25 focus:ring-amber-500'
                        : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-blue-600/25 focus:ring-blue-500'
                ]"
            >
                <svg
                    v-if="processing"
                    class="h-3.5 w-3.5 animate-spin text-white"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ processing ? 'Memproses...' : confirmText }}</span>
            </button>
        </div>
    </Modal>
</template>
