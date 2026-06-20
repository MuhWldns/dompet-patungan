<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

type User = {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
};

defineProps<{
    users: {
        data: User[];
    };
}>();
</script>

<template>
    <Head title="Admin Users" />

    <main class="flex flex-col gap-6 p-4 md:p-8">
        <section class="rounded-[2rem] bg-black p-8 text-white md:p-10">
            <p class="text-sm font-semibold text-white/60">System admin</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-[-0.04em] md:text-6xl">
                Users
            </h1>
        </section>

        <section class="overflow-hidden rounded-3xl border border-black/10">
            <div
                v-for="user in users.data"
                :key="user.id"
                class="flex flex-col gap-3 border-b border-black/10 p-5 last:border-b-0 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <p class="font-semibold text-black">{{ user.name }}</p>
                    <p class="text-sm text-black/60">
                        {{ user.email }} · {{ user.role }} ·
                        {{ user.is_active ? 'active' : 'inactive' }}
                    </p>
                </div>
                <Link
                    :href="`/admin/users/${user.id}/status`"
                    :data="{ is_active: !user.is_active }"
                    as="button"
                    class="h-10 rounded-full bg-black px-4 text-sm font-semibold text-white"
                    method="patch"
                >
                    {{ user.is_active ? 'Deactivate' : 'Activate' }}
                </Link>
            </div>
        </section>
    </main>
</template>
