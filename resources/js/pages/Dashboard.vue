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

    <main class="flex flex-col gap-5 bg-white p-4 text-black md:p-6">
        <section class="rounded-[2rem] bg-black p-6 text-white md:p-8">
            <p class="text-sm font-semibold text-white/60">Dashboard</p>
            <h1
                class="mt-3 text-4xl font-semibold tracking-[-0.04em] md:text-5xl"
            >
                Dompet Patungan
            </h1>
            <p class="mt-3 max-w-2xl text-white/70">
                Pantau grup, tagihan, notifikasi, dan penyelesaian utang dari
                satu tempat.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl bg-[#f4f4f4] p-5">
                <p class="text-sm font-semibold text-black/50">Grup</p>
                <p class="mt-2 text-4xl font-semibold text-black">
                    {{ summary.groups }}
                </p>
            </div>
            <div class="rounded-3xl bg-[#f4f4f4] p-5">
                <p class="text-sm font-semibold text-black/50">Tagihan aktif</p>
                <p class="mt-2 text-4xl font-semibold text-black">
                    {{ summary.pendingPayments }}
                </p>
            </div>
            <div class="rounded-3xl bg-[#f4f4f4] p-5">
                <p class="text-sm font-semibold text-black/50">
                    Notifikasi baru
                </p>
                <p class="mt-2 text-4xl font-semibold text-black">
                    {{ summary.unreadNotifications }}
                </p>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-3xl border border-black/10 bg-white p-5">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-semibold text-black">
                        Grup terbaru
                    </h2>
                    <Link class="font-semibold text-[#494fdf]" href="/groups">
                        Lihat semua
                    </Link>
                </div>
                <div class="mt-4 grid gap-3">
                    <Link
                        v-for="group in recentGroups"
                        :key="group.id"
                        :href="`/groups/${group.id}`"
                        class="rounded-2xl bg-[#f4f4f4] p-4"
                    >
                        <p class="font-semibold text-black">{{ group.name }}</p>
                        <p class="text-sm text-black/60">{{ group.status }}</p>
                    </Link>
                    <p v-if="recentGroups.length === 0" class="text-black/60">
                        Belum ada grup.
                    </p>
                </div>
            </div>

            <div class="rounded-3xl border border-black/10 bg-white p-5">
                <h2 class="text-2xl font-semibold text-black">Notifikasi</h2>
                <div class="mt-4">
                    <NotificationList :notifications="notifications" />
                </div>
            </div>
        </section>
    </main>
</template>
