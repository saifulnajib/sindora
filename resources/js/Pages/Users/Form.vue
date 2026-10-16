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
    cabor_ids: props.user?.cabor_ids ?? (props.user?.cabor_id ? [props.user.cabor_id] : []),
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

function toggleCabor(cid) {
    const num = Number(cid);
    const idx = form.cabor_ids.indexOf(num);
    if (idx >= 0) {
        form.cabor_ids.splice(idx, 1);
    } else {
        form.cabor_ids.push(num);
    }
    // Update primary cabor_id
    form.cabor_id = form.cabor_ids.length > 0 ? form.cabor_ids[0] : '';
}

function getRoleLabel(role) {
    const name = typeof role === 'object' ? role.name : role;
    if (name === 'super_admin') return 'Super Admin';
    if (name === 'verifikator') return 'Verifikator Dispora';
    if (name === 'pimpinan') return 'Pimpinan';
    if (name === 'pengurus_cabor' || name === 'pengkot') return 'Pengurus Cabor (Pengkot)';
    return name;
}

function getRoleName(role) {
    return typeof role === 'object' ? role.name : role;
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
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="name" value="Nama Lengkap" />
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

                        <!-- Roles selection -->
                        <div>
                            <InputLabel value="Hak Akses (Role)" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih role sesuai tanggung jawab pengguna dalam sistem SINDORA.</p>
                            <div class="mt-3 grid gap-2.5 sm:grid-cols-2">
                                <label
                                    v-for="r in roles"
                                    :key="getRoleName(r)"
                                    :class="[
                                        'relative flex cursor-pointer rounded-xl border p-3 shadow-sm transition hover:bg-gray-50 dark:hover:bg-gray-700/50',
                                        form.roles.includes(getRoleName(r))
                                            ? 'border-red-600 bg-red-50/50 dark:border-red-500 dark:bg-red-950/30 ring-1 ring-red-500'
                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800',
                                        isShow ? 'opacity-60 pointer-events-none' : ''
                                    ]"
                                >
                                    <input
                                        type="checkbox"
                                        :value="getRoleName(r)"
                                        :checked="form.roles.includes(getRoleName(r))"
                                        @change="toggleRole(getRoleName(r))"
                                        :disabled="isShow"
                                        class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500 mt-0.5"
                                    />
                                    <div class="ml-3">
                                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ getRoleLabel(r) }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ typeof r === 'object' && r.desc ? r.desc : 'Role ' + getRoleName(r) }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                            <InputError :message="form.errors.roles" class="mt-2" />
                            <InputError :message="form.errors['roles.0']" class="mt-1" />
                        </div>

                        <!-- Cabang Olahraga Assignment for Pengkot (Pengurus Cabor) -->
                        <div
                            v-if="form.roles.includes('pengurus_cabor') || form.roles.includes('pengkot') || form.roles.includes('operator_organisasi')"
                            class="rounded-xl border border-red-200 bg-red-50/40 p-4 dark:border-red-900/50 dark:bg-red-950/20 space-y-3"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            Penentuan Cabang Olahraga Dikelola (Pengkot)
                                        </h4>
                                        <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-bold text-red-800 dark:bg-red-900/50 dark:text-red-200">
                                            Wajib untuk Pengkot
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                                        Pilih cabor yang dikelola oleh user Pengkot ini. Pengkot <strong>hanya dapat melihat dan mengelola data cabor yang dipilih</strong> di sini.
                                    </p>
                                </div>
                                <span class="text-xs font-semibold text-red-700 dark:text-red-300">
                                    {{ form.cabor_ids.length }} cabor terpilih
                                </span>
                            </div>

                            <div v-if="cabors.length === 0" class="text-xs text-gray-500 p-2">
                                Belum ada data cabang olahraga di sistem.
                            </div>
                            <div v-else class="grid gap-2 sm:grid-cols-2 max-h-60 overflow-y-auto pr-1">
                                <label
                                    v-for="c in cabors"
                                    :key="c.id"
                                    :class="[
                                        'flex items-center justify-between rounded-lg border p-2.5 text-xs font-medium transition cursor-pointer',
                                        form.cabor_ids.includes(c.id)
                                            ? 'border-red-600 bg-white dark:bg-gray-800 text-red-900 dark:text-red-200 ring-1 ring-red-500 font-bold'
                                            : 'border-gray-200 bg-white/70 dark:border-gray-700 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300 hover:bg-white',
                                        isShow ? 'pointer-events-none opacity-60' : ''
                                    ]"
                                >
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            :value="c.id"
                                            :checked="form.cabor_ids.includes(c.id)"
                                            @change="toggleCabor(c.id)"
                                            :disabled="isShow"
                                            class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                                        />
                                        <span>{{ c.nama }}</span>
                                    </div>
                                    <span v-if="c.kategori" class="inline-flex rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-[10px] font-bold uppercase text-gray-600 dark:text-gray-300">
                                        {{ c.kategori }}
                                    </span>
                                </label>
                            </div>
                            <InputError :message="form.errors.cabor_ids" class="mt-2" />
                            <InputError :message="form.errors.cabor_id" class="mt-1" />
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
