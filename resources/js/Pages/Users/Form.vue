<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    roles: Array,
    klubs: Array,
    organisasis: Array,
    cabors: Array,
    isEdit: Boolean,
    isShow: Boolean,
});

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    password_confirmation: '',
    roles: props.user?.roles ?? [],
    klub_id: props.user?.klub_id ?? '',
    organisasi_id: props.user?.organisasi_id ?? '',
    cabor_id: props.user?.cabor_id ?? '',
});

function submit() {
    if (props.isShow) return;
    if (props.isEdit) {
        form.put(route('users.update', props.user.id));
    } else {
        form.post(route('users.store'));
    }
}

function toggleRole(r) {
    const idx = form.roles.indexOf(r);
    if (idx >= 0) form.roles.splice(idx, 1);
    else form.roles.push(r);
}
</script>

<template>
    <Head :title="isShow ? 'Detail User' : (isEdit ? 'Edit User' : 'Tambah User')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('users.index')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Kembali</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ isShow ? 'Detail User' : (isEdit ? 'Edit User' : 'Tambah User') }}</h2>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="sindora-card p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="name" value="Nama" />
                            <TextInput id="name" v-model="form.name" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" type="email" v-model="form.email" class="mt-1 block w-full" :disabled="isShow" required />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>
                        <div v-if="!isShow" class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="password" :value="isEdit ? 'Password (kosongkan jika tidak ganti)' : 'Password'" />
                                <TextInput id="password" type="password" v-model="form.password" class="mt-1 block w-full" :required="!isEdit" />
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="password_confirmation" value="Konfirmasi Password" />
                                <TextInput id="password_confirmation" type="password" v-model="form.password_confirmation" class="mt-1 block w-full" :required="!isEdit" />
                                <InputError :message="form.errors.password_confirmation" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Roles" />
                            <div class="mt-2 flex flex-wrap gap-2">
                                <label v-for="r in roles" :key="r" class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium" :class="[form.roles.includes(r) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 dark:text-gray-300 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50', isShow ? 'opacity-60 pointer-events-none' : 'cursor-pointer']">
                                    <input type="checkbox" :value="r" :checked="form.roles.includes(r)" @change="toggleRole(r)" :disabled="isShow" class="sr-only" /> {{ r }}
                                </label>
                            </div>
                            <InputError :message="form.errors.roles" class="mt-2" />
                            <InputError :message="form.errors['roles.0']" class="mt-1" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <InputLabel for="klub_id" value="Klub (opsional)" />
                                <select id="klub_id" v-model="form.klub_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Tidak ada —</option>
                                    <option v-for="k in klubs" :key="k.id" :value="k.id">{{ k.nama }}</option>
                                </select>
                                <InputError :message="form.errors.klub_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="organisasi_id" value="Organisasi (opsional)" />
                                <select id="organisasi_id" v-model="form.organisasi_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Tidak ada —</option>
                                    <option v-for="o in organisasis" :key="o.id" :value="o.id">{{ o.nama }}</option>
                                </select>
                                <InputError :message="form.errors.organisasi_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="cabor_id" value="Cabor (opsional)" />
                                <select id="cabor_id" v-model="form.cabor_id" :disabled="isShow" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 text-sm">
                                    <option value="">— Tidak ada —</option>
                                    <option v-for="c in cabors" :key="c.id" :value="c.id">{{ c.nama }}</option>
                                </select>
                                <InputError :message="form.errors.cabor_id" class="mt-2" />
                            </div>
                        </div>
                        <div v-if="!isShow" class="flex items-center gap-3">
                            <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Update' : 'Simpan' }}</PrimaryButton>
                            <Link :href="route('users.index')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
