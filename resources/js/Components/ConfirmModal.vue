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
        <div class="p-6">
            <div class="flex items-start gap-4">
                <!-- Icon based on type -->
                <div
                    v-if="type === 'danger'"
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 ring-4 ring-rose-50 dark:ring-rose-950/30"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div
                    v-else-if="type === 'success'"
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 ring-4 ring-emerald-50 dark:ring-emerald-950/30"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div
                    v-else-if="type === 'warning'"
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 ring-4 ring-amber-50 dark:ring-amber-950/30"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div
                    v-else
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 ring-4 ring-blue-50 dark:ring-blue-950/30"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                </div>

                <div class="flex-1">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h3>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <slot name="content">
                            <p>{{ message }}</p>
                        </slot>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton :disabled="processing" @click="handleClose">
                    {{ cancelText }}
                </SecondaryButton>

                <button
                    type="button"
                    :disabled="processing"
                    @click="handleConfirm"
                    :class="[
                        'inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-offset-2',
                        type === 'danger'
                            ? 'bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-500'
                            : type === 'success'
                            ? 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500'
                            : type === 'warning'
                            ? 'bg-amber-600 text-white hover:bg-amber-700 focus:ring-amber-500'
                            : 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500'
                    ]"
                >
                    <svg
                        v-if="processing"
                        class="h-4 w-4 animate-spin text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ confirmText }}</span>
                </button>
            </div>
        </div>
    </Modal>
</template>
