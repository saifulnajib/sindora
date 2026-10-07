<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-lg border-gray-300 shadow-sm focus:border-red-600 focus:ring-red-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-red-500 dark:focus:ring-red-500"
        v-model="model"
        ref="input"
    />
</template>
