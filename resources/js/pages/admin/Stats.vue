<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminNav from '@/components/AdminNav.vue';

type Stats = {
    users: number;
    activeUsers: number;
    inactiveUsers: number;
    systemAdmins: number;
    groups: number;
    activeGroups: number;
    settledGroups: number;
    closedGroups: number;
    expenses: number;
    expenseTotal: string | number;
    pendingPayments: number;
    submittedPayments: number;
    rejectedPayments: number;
    confirmedPaymentTotal: string | number;
};

const props = defineProps<{
    stats: Stats;
}>();

const sections = computed(() => [
    {
        title: 'User access control',
        description: 'Pantau akun aktif, nonaktif, dan operator system admin.',
        items: [
            { label: 'Total users', value: props.stats.users },
            { label: 'Active users', value: props.stats.activeUsers },
            { label: 'Inactive users', value: props.stats.inactiveUsers },
            { label: 'System admins', value: props.stats.systemAdmins },
        ],
    },
    {
        title: 'Group health monitoring',
        description:
            'Ikhtisar status grup tanpa membuka detail transaksi privat.',
        items: [
            { label: 'Total groups', value: props.stats.groups },
            { label: 'Active groups', value: props.stats.activeGroups },
            { label: 'Settled groups', value: props.stats.settledGroups },
            { label: 'Closed groups', value: props.stats.closedGroups },
        ],
    },
    {
        title: 'Payment operations',
        description: 'Sinyal antrean pembayaran untuk monitoring operasional.',
        items: [
            { label: 'Expenses', value: props.stats.expenses },
            { label: 'Pending payments', value: props.stats.pendingPayments },
            {
                label: 'Submitted payments',
                value: props.stats.submittedPayments,
            },
            { label: 'Rejected payments', value: props.stats.rejectedPayments },
        ],
    },
]);
</script>

<template>
    <Head title="Admin Stats" />

    <main class="vh-page">
        <section class="vh-hero">
            <p class="vh-eyebrow">System admin</p>
            <h1 class="vh-title">Admin monitoring center</h1>
            <p class="vh-description">
                Tampilan khusus system admin untuk monitoring platform, kontrol
                akses user, status grup, dan sinyal operasional tanpa membuka
                detail privat pengguna.
            </p>
        </section>

        <AdminNav />

        <section class="grid gap-4 md:grid-cols-2">
            <div class="vh-stat-card">
                <p class="vh-stat-label">Expense volume</p>
                <p class="vh-stat-value font-mono">{{ stats.expenseTotal }}</p>
                <p class="mt-2 text-sm text-muted-foreground">
                    Total nominal expense agregat seluruh platform.
                </p>
            </div>
            <div class="vh-stat-card">
                <p class="vh-stat-label">Confirmed payments</p>
                <p class="vh-stat-value font-mono">
                    {{ stats.confirmedPaymentTotal }}
                </p>
                <p class="mt-2 text-sm text-muted-foreground">
                    Total pembayaran confirmed sebagai sinyal settlement sehat.
                </p>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            <article
                v-for="section in sections"
                :key="section.title"
                class="vh-card"
            >
                <h2 class="text-xl font-semibold text-foreground">
                    {{ section.title }}
                </h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ section.description }}
                </p>
                <div class="mt-5 grid gap-3">
                    <div
                        v-for="item in section.items"
                        :key="item.label"
                        class="flex items-center justify-between gap-4 rounded-lg border border-border bg-background px-4 py-3"
                    >
                        <span class="text-sm text-muted-foreground">
                            {{ item.label }}
                        </span>
                        <span class="font-mono font-semibold text-foreground">
                            {{ item.value }}
                        </span>
                    </div>
                </div>
            </article>
        </section>
    </main>
</template>
