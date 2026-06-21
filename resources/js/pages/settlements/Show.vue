<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

type Transfer = {
    from_user_id: number;
    from_name: string;
    to_user_id: number;
    to_name: string;
    amount: string;
};

type Settlement = {
    id: string;
    debt_details: Transfer[];
    generated_at: string;
} | null;

const props = defineProps<{
    group: {
        id: string;
        name: string;
    };
    settlement: Settlement;
    isAdmin: boolean;
}>();
</script>

<template>
    <Head :title="`Settlement - ${group.name}`" />

    <main class="vh-page">
        <section class="vh-hero">
            <p class="vh-eyebrow">Settlement</p>
            <h1 class="vh-title">
                {{ group.name }}
            </h1>
            <p class="vh-description">
                Rekap utang bersih untuk meminimalkan transaksi antar anggota.
            </p>
            <Link
                v-if="isAdmin"
                :href="`/settlements/generate/${props.group.id}`"
                as="button"
                class="vh-sage-action mt-6"
                method="post"
            >
                Generate settlement
            </Link>
        </section>

        <section class="vh-card">
            <h2 class="text-2xl font-semibold text-foreground">Transfer</h2>
            <div v-if="settlement?.debt_details.length" class="mt-4 grid gap-3">
                <div
                    v-for="transfer in settlement.debt_details"
                    :key="`${transfer.from_user_id}-${transfer.to_user_id}`"
                    class="vh-muted-card"
                >
                    <p class="font-semibold text-foreground">
                        {{ transfer.from_name }} bayar Rp
                        <span class="font-mono">{{ transfer.amount }}</span> ke
                        {{ transfer.to_name }}
                    </p>
                </div>
            </div>
            <p v-else class="mt-4 text-muted-foreground">
                Belum ada settlement atau tidak ada utang tersisa.
            </p>
        </section>
    </main>
</template>
