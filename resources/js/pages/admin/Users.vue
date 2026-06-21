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

    <main class="vh-page">
        <section class="vh-hero">
            <p class="vh-eyebrow">System admin</p>
            <h1 class="vh-title">Users</h1>
        </section>

        <section
            class="overflow-hidden rounded-lg border border-border bg-card"
        >
            <div
                v-for="user in users.data"
                :key="user.id"
                class="flex min-h-12 flex-col gap-3 border-b border-muted px-4 py-3 transition-colors last:border-b-0 hover:bg-background md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <p class="font-semibold text-foreground">{{ user.name }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ user.email }} · {{ user.role }} ·
                        <span
                            :class="[
                                'vh-chip ml-1',
                                user.is_active
                                    ? 'vh-chip-success'
                                    : 'vh-chip-error',
                            ]"
                        >
                            {{ user.is_active ? 'active' : 'inactive' }}
                        </span>
                    </p>
                </div>
                <Link
                    :href="`/admin/users/${user.id}/status`"
                    :data="{ is_active: !user.is_active }"
                    as="button"
                    class="vh-secondary-action h-10 px-4"
                    method="patch"
                >
                    {{ user.is_active ? 'Deactivate' : 'Activate' }}
                </Link>
            </div>
        </section>
    </main>
</template>
