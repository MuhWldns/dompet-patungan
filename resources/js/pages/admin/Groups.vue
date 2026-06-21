<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminNav from '@/components/AdminNav.vue';

type Group = {
    id: string;
    name: string;
    description: string | null;
    status: string;
    target_amount: string | null;
    members_count: number;
    expenses_count: number;
    settlements_count: number;
    creator?: {
        id: number;
        name: string;
        email: string;
    };
};

type Summary = {
    total: number;
    active: number;
    settled: number;
    closed: number;
};

defineProps<{
    groups: {
        data: Group[];
    };
    summary: Summary;
}>();
</script>

<template>
    <Head title="Admin Group Monitoring" />

    <main class="vh-page">
        <section class="vh-hero">
            <p class="vh-eyebrow">System admin</p>
            <h1 class="vh-title">Group monitoring</h1>
            <p class="vh-description">
                Pantau status grup, jumlah anggota, pengeluaran, dan settlement
                secara agregat tanpa membuka detail privat transaksi member.
            </p>
        </section>

        <AdminNav />

        <section class="grid gap-4 md:grid-cols-4">
            <div class="vh-stat-card">
                <p class="vh-stat-label">Total groups</p>
                <p class="vh-stat-value font-mono">{{ summary.total }}</p>
            </div>
            <div class="vh-stat-card">
                <p class="vh-stat-label">Active</p>
                <p class="vh-stat-value font-mono">{{ summary.active }}</p>
            </div>
            <div class="vh-stat-card">
                <p class="vh-stat-label">Settled</p>
                <p class="vh-stat-value font-mono">{{ summary.settled }}</p>
            </div>
            <div class="vh-stat-card">
                <p class="vh-stat-label">Closed</p>
                <p class="vh-stat-value font-mono">{{ summary.closed }}</p>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-lg border border-border bg-card"
        >
            <div
                class="grid gap-3 border-b border-muted bg-background px-4 py-3 text-xs font-semibold tracking-[0.5px] text-muted-foreground uppercase md:grid-cols-[1.4fr_1fr_0.7fr_0.9fr]"
            >
                <span>Group</span>
                <span>Owner</span>
                <span>Status</span>
                <span class="md:text-right">Activity</span>
            </div>

            <div
                v-for="group in groups.data"
                :key="group.id"
                class="grid gap-3 border-b border-muted px-4 py-4 last:border-b-0 hover:bg-background md:grid-cols-[1.4fr_1fr_0.7fr_0.9fr] md:items-center"
            >
                <div>
                    <p class="font-semibold text-foreground">
                        {{ group.name }}
                    </p>
                    <p class="mt-1 line-clamp-2 text-sm text-muted-foreground">
                        {{ group.description ?? 'No description provided.' }}
                    </p>
                </div>

                <div class="text-sm text-muted-foreground">
                    <p class="font-semibold text-foreground">
                        {{ group.creator?.name ?? 'Unknown' }}
                    </p>
                    <p>{{ group.creator?.email ?? '-' }}</p>
                </div>

                <div>
                    <span class="vh-chip vh-chip-navy">
                        {{ group.status }}
                    </span>
                </div>

                <div
                    class="grid gap-1 text-sm text-muted-foreground md:text-right"
                >
                    <p>{{ group.members_count }} members</p>
                    <p>{{ group.expenses_count }} expenses</p>
                    <p>{{ group.settlements_count }} settlements</p>
                </div>
            </div>

            <p
                v-if="groups.data.length === 0"
                class="px-4 py-6 text-muted-foreground"
            >
                Belum ada grup untuk dimonitor.
            </p>
        </section>

        <section class="vh-muted-card">
            <h2 class="font-semibold text-foreground">Control boundary</h2>
            <p class="mt-2 text-sm text-muted-foreground">
                System admin hanya memonitor agregat dan kesehatan platform.
                Detail pengeluaran privat tetap berada di area grup
                masing-masing. Gunakan user control untuk menonaktifkan akun
                yang bermasalah.
            </p>
            <Link class="vh-link mt-3 inline-flex" href="/admin/users">
                Buka user control
            </Link>
        </section>
    </main>
</template>
