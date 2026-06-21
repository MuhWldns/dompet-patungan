<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminNav from '@/components/AdminNav.vue';

type Stats = {
    users: number;
    activeUsers: number;
    groups: number;
    expenses: number;
    expenseTotal: string | number;
    confirmedPaymentTotal: string | number;
};

defineProps<{
    stats: Stats;
}>();
</script>

<template>
    <Head title="Admin Stats" />

    <main class="vh-page">
        <section class="vh-hero">
            <p class="vh-eyebrow">System admin</p>
            <h1 class="vh-title">Aggregate stats</h1>
            <p class="vh-description">
                Platform totals only. Private expense details are not exposed
                here.
            </p>
        </section>

        <AdminNav />

        <section class="grid gap-4 md:grid-cols-3">
            <div v-for="(value, key) in stats" :key="key" class="vh-stat-card">
                <p class="vh-stat-label">{{ key }}</p>
                <p class="vh-stat-value font-mono">
                    {{ value }}
                </p>
            </div>
        </section>
    </main>
</template>
