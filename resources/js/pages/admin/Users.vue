<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminNav from '@/components/AdminNav.vue';

type User = {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    created_at: string;
};

type Summary = {
    total: number;
    active: number;
    inactive: number;
    systemAdmins: number;
};

defineProps<{
    users: {
        data: User[];
    };
    summary: Summary;
}>();
</script>

<template>
    <Head title="Admin Users" />

    <main class="vh-page">
        <section class="vh-hero">
            <p class="vh-eyebrow">System admin</p>
            <h1 class="vh-title">User control center</h1>
            <p class="vh-description">
                Kelola akses akun, nonaktifkan user bermasalah, dan pantau
                operator system admin dari satu tampilan khusus admin.
            </p>
        </section>

        <AdminNav />

        <section class="grid gap-4 md:grid-cols-4">
            <div class="vh-stat-card">
                <p class="vh-stat-label">Total users</p>
                <p class="vh-stat-value font-mono">{{ summary.total }}</p>
            </div>
            <div class="vh-stat-card">
                <p class="vh-stat-label">Active</p>
                <p class="vh-stat-value font-mono">{{ summary.active }}</p>
            </div>
            <div class="vh-stat-card">
                <p class="vh-stat-label">Inactive</p>
                <p class="vh-stat-value font-mono">{{ summary.inactive }}</p>
            </div>
            <div class="vh-stat-card">
                <p class="vh-stat-label">System admins</p>
                <p class="vh-stat-value font-mono">
                    {{ summary.systemAdmins }}
                </p>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-lg border border-border bg-card"
        >
            <div
                class="grid gap-3 border-b border-muted bg-background px-4 py-3 text-xs font-semibold tracking-[0.5px] text-muted-foreground uppercase md:grid-cols-[1.3fr_0.8fr_0.7fr_0.8fr]"
            >
                <span>User</span>
                <span>Role</span>
                <span>Status</span>
                <span class="md:text-right">Control</span>
            </div>
            <div
                v-for="user in users.data"
                :key="user.id"
                class="grid min-h-12 gap-3 border-b border-muted px-4 py-4 transition-colors last:border-b-0 hover:bg-background md:grid-cols-[1.3fr_0.8fr_0.7fr_0.8fr] md:items-center"
            >
                <div>
                    <p class="font-semibold text-foreground">{{ user.name }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ user.email }}
                    </p>
                </div>
                <div>
                    <span class="vh-chip vh-chip-navy">
                        {{ user.role }}
                    </span>
                </div>
                <div>
                    <span
                        :class="[
                            'vh-chip',
                            user.is_active
                                ? 'vh-chip-success'
                                : 'vh-chip-error',
                        ]"
                    >
                        {{ user.is_active ? 'active' : 'inactive' }}
                    </span>
                </div>
                <Link
                    :href="`/admin/users/${user.id}/status`"
                    :data="{ is_active: !user.is_active }"
                    as="button"
                    class="vh-secondary-action h-10 px-4 md:justify-self-end"
                    method="patch"
                >
                    {{ user.is_active ? 'Deactivate account' : 'Reactivate' }}
                </Link>
            </div>
        </section>
    </main>
</template>
