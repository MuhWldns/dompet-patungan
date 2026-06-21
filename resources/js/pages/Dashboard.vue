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
    filter?: string;
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
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-semibold text-foreground">
                        Notifikasi
                    </h2>
                    <Link
                        href="/notifications/mark-all-read"
                        as="button"
                        class="vh-link text-sm"
                        method="patch"
                    >
                        Tandai semua dibaca
                    </Link>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <Link
                        href="/dashboard"
                        class="vh-chip"
                        :class="!filter ? 'vh-chip-primary' : 'vh-chip-muted'"
                    >
                        Semua
                    </Link>
                    <Link
                        href="/dashboard?filter=unread"
                        class="vh-chip"
                        :class="filter === 'unread' ? 'vh-chip-primary' : 'vh-chip-muted'"
                    >
                        Belum dibaca
                    </Link>
                    <Link
                        href="/dashboard?filter=bill.created"
                        class="vh-chip"
                        :class="filter === 'bill.created' ? 'vh-chip-primary' : 'vh-chip-muted'"
                    >
                        Tagihan
                    </Link>
                    <Link
                        href="/dashboard?filter=payment.submitted"
                        class="vh-chip"
                        :class="filter === 'payment.submitted' ? 'vh-chip-primary' : 'vh-chip-muted'"
                    >
                        Pembayaran
                    </Link>
                    <Link
                        href="/dashboard?filter=settlement.generated"
                        class="vh-chip"
                        :class="filter === 'settlement.generated' ? 'vh-chip-primary' : 'vh-chip-muted'"
                    >
                        Settlement
                    </Link>
                </div>
                <div class="mt-4">
                    <NotificationList :notifications="notifications" />
                </div>
            </div>
        </section>
    </main>
</template>
