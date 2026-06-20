<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

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

    <main class="flex flex-col gap-6 p-4 md:p-8">
        <section class="rounded-[2rem] bg-black p-8 text-white md:p-10">
            <p class="text-sm font-semibold text-white/60">System admin</p>
            <h1
                class="mt-3 text-4xl font-semibold tracking-[-0.04em] md:text-6xl"
            >
                Aggregate stats
            </h1>
            <p class="mt-4 max-w-2xl text-white/70">
                Platform totals only. Private expense details are not exposed
                here.
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div
                v-for="(value, key) in stats"
                :key="key"
                class="rounded-3xl bg-[#f4f4f4] p-6"
            >
                <p class="text-sm font-semibold text-black/50">{{ key }}</p>
                <p class="mt-3 text-3xl font-semibold text-black">
                    {{ value }}
                </p>
            </div>
        </section>
    </main>
</template>
