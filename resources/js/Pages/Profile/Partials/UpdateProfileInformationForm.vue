<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const roles = computed(() => user.value?.roles ?? []);
const permissions = computed(() => user.value?.permissions ?? []);

const form = useForm({
    name: user.value.name,
    email: user.value.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium dark:text-gray-100 text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Sprint 2 1.6: show roles & permissions read-only -->
            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-700/40">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Roles & Permissions</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <span v-for="r in roles" :key="r" class="inline-flex rounded-full bg-[#1e3a8a] dark:bg-blue-700 px-2.5 py-1 text-xs font-semibold text-white">{{ r }}</span>
                    <span v-if="roles.length===0" class="text-xs text-gray-500 dark:text-gray-400">no role</span>
                </div>
                <p v-if="permissions.length" class="mt-2 text-xs text-gray-500 dark:text-gray-400">Permissions: {{ permissions.join(', ') }}</p>
                <p class="mt-1 text-xs text-gray-400">Klub: {{ user.klub_id ?? '—' }} · Organisasi: {{ user.organisasi_id ?? '—' }} · Cabor: {{ user.cabor_id ?? '—' }}</p>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 dark:text-gray-400 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium dark:text-gray-100 text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0" leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"> <p v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
