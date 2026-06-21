<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import NotificationList from '@/components/NotificationList.vue';

type Summary = {
    groups: number;
    pendingPayments: number;
    unreadNotifications: number;
};

type Group = {
    id: string;
    name: string;
    status: string;
};

type Notification = {
    id: number;
    type: string;
    message: string;
    link: string | null;
    read_at: string | null;
};

defineProps<{
    summary: Summary;
    recentGroups: Group[];
    notifications: Notification[];
}>();
</script>

<template>
    <Head title="Dashboard" />

    <main class="vh-page">
        <section class="vh-hero">
            <p class="vh-eyebrow">Dashboard</p>
            <h1 class="vh-title">Dompet Patungan</h1>
            <p class="vh-description">
                Pantau grup, tagihan, notifikasi, dan penyelesaian utang dari
                satu tempat.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="vh-stat-card">
                <p class="vh-stat-label">Grup</p>
                <p class="vh-stat-value">
                    {{ summary.groups }}
                </p>
            </div>
            <div class="vh-stat-card">
                <p class="vh-stat-label">Tagihan aktif</p>
                <p class="vh-stat-value">
                    {{ summary.pendingPayments }}
                </p>
            </div>
            <div class="vh-stat-card">
                <p class="vh-stat-label">Notifikasi baru</p>
                <p class="vh-stat-value">
                    {{ summary.unreadNotifications }}
                </p>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <div class="vh-card">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-semibold text-foreground">
                        Grup terbaru
                    </h2>
                    <Link class="vh-link" href="/groups"> Lihat semua </Link>
                </div>
                <div class="mt-4 grid gap-3">
                    <Link
                        v-for="group in recentGroups"
                        :key="group.id"
                        :href="`/groups/${group.id}`"
                        class="vh-muted-card transition-colors hover:border-tertiary hover:bg-accent"
                    >
                        <p class="font-semibold text-foreground">
                            {{ group.name }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ group.status }}
                        </p>
                    </Link>
                    <p
                        v-if="recentGroups.length === 0"
                        class="text-muted-foreground"
                    >
                        Belum ada grup.
                    </p>
                </div>
            </div>

            <div class="vh-card">
                <h2 class="text-2xl font-semibold text-foreground">
                    Notifikasi
                </h2>
                <div class="mt-4">
                    <NotificationList :notifications="notifications" />
                </div>
            </div>
        </section>
    </main>
</template>
