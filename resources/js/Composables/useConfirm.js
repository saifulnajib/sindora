import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const dialogState = ref({
    show: false,
    title: 'Konfirmasi Hapus Data',
    message: 'Apakah Anda yakin ingin menghapus data ini?',
    itemDetail: null,
    warningDetail: 'Tindakan ini bersifat permanen dan data yang dihapus tidak dapat dipulihkan.',
    confirmText: 'Ya, Hapus Data',
    cancelText: 'Batal',
    type: 'danger', // 'danger' | 'warning' | 'info' | 'success'
    processing: false,
    resolvePromise: null,
});

export function useConfirm() {
    function ask({
        title = 'Konfirmasi Tindakan',
        message = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        itemDetail = null,
        warningDetail = null,
        confirmText = 'Ya, Lanjutkan',
        cancelText = 'Batal',
        type = 'warning',
    } = {}) {
        return new Promise((resolve) => {
            dialogState.value = {
                show: true,
                title,
                message,
                itemDetail,
                warningDetail,
                confirmText,
                cancelText,
                type,
                processing: false,
                resolvePromise: resolve,
            };
        });
    }

    function confirmDelete({
        title = 'Konfirmasi Hapus Data',
        message = 'Apakah Anda yakin ingin menghapus data ini?',
        itemName = null,
        itemDetail = null,
        warningDetail = 'Tindakan ini bersifat permanen dan data yang dihapus tidak dapat dipulihkan.',
        confirmText = 'Ya, Hapus Data',
        cancelText = 'Batal',
    } = {}) {
        const item = itemName || itemDetail;
        return ask({
            title,
            message: item ? `Apakah Anda yakin ingin menghapus data "${item}"?` : message,
            itemDetail: item,
            warningDetail,
            confirmText,
            cancelText,
            type: 'danger',
        });
    }

    function deleteRecord(url, {
        title = 'Konfirmasi Hapus Data',
        message = 'Apakah Anda yakin ingin menghapus data ini?',
        itemName = null,
        itemDetail = null,
        warningDetail = 'Tindakan ini bersifat permanen dan data yang dihapus tidak dapat dipulihkan.',
        confirmText = 'Ya, Hapus Data',
        cancelText = 'Batal',
        inertiaOptions = {},
    } = {}) {
        const item = itemName || itemDetail;
        return new Promise((resolve) => {
            dialogState.value = {
                show: true,
                title,
                message: item ? `Apakah Anda yakin ingin menghapus data "${item}"?` : message,
                itemDetail: item,
                warningDetail,
                confirmText,
                cancelText,
                type: 'danger',
                processing: false,
                resolvePromise: (confirmed) => {
                    if (confirmed) {
                        dialogState.value.processing = true;
                        router.delete(url, {
                            preserveScroll: true,
                            ...inertiaOptions,
                            onSuccess: (page) => {
                                close();
                                inertiaOptions.onSuccess?.(page);
                                resolve(true);
                            },
                            onError: (errors) => {
                                close();
                                inertiaOptions.onError?.(errors);
                                resolve(false);
                            },
                            onFinish: (visit) => {
                                close();
                                inertiaOptions.onFinish?.(visit);
                            },
                        });
                    } else {
                        close();
                        resolve(false);
                    }
                },
            };
        });
    }

    function close() {
        dialogState.value.show = false;
        dialogState.value.processing = false;
        dialogState.value.resolvePromise = null;
    }

    function onConfirm() {
        if (dialogState.value.processing) return;
        if (dialogState.value.resolvePromise) {
            const resolver = dialogState.value.resolvePromise;
            resolver(true);
            // If it was standard ask() (not deleteRecord with inner router delete), close immediately
            if (!dialogState.value.processing) {
                close();
            }
        } else {
            close();
        }
    }

    function onCancel() {
        if (dialogState.value.processing) return;
        if (dialogState.value.resolvePromise) {
            dialogState.value.resolvePromise(false);
        }
        close();
    }

    return {
        dialogState,
        ask,
        confirmDelete,
        deleteRecord,
        close,
        onConfirm,
        onCancel,
    };
}
