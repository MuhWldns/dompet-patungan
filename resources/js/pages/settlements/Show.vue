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

    <main class="flex flex-col gap-6 p-4 md:p-8">
        <section class="rounded-[2rem] bg-black p-8 text-white md:p-10">
            <p class="text-sm font-semibold text-white/60">Settlement</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-[-0.04em] md:text-6xl">
                {{ group.name }}
            </h1>
            <p class="mt-4 max-w-2xl text-white/70">
                Rekap utang bersih untuk meminimalkan transaksi antar anggota.
            </p>
            <Link
                v-if="isAdmin"
                :href="`/settlements/generate/${props.group.id}`"
                as="button"
                class="mt-6 h-12 rounded-full bg-white px-6 font-semibold text-black"
                method="post"
            >
                Generate settlement
            </Link>
        </section>

        <section class="rounded-3xl border border-black/10 p-6">
            <h2 class="text-2xl font-semibold text-black">Transfer</h2>
            <div v-if="settlement?.debt_details.length" class="mt-4 grid gap-3">
                <div
                    v-for="transfer in settlement.debt_details"
                    :key="`${transfer.from_user_id}-${transfer.to_user_id}`"
                    class="rounded-2xl bg-[#f4f4f4] p-4"
                >
                    <p class="font-semibold text-black">
                        {{ transfer.from_name }} bayar Rp {{ transfer.amount }} ke
                        {{ transfer.to_name }}
                    </p>
                </div>
            </div>
            <p v-else class="mt-4 text-black/60">
                Belum ada settlement atau tidak ada utang tersisa.
            </p>
        </section>
    </main>
</template>
