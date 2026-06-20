<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

type Payment = {
    id: string;
    amount: string;
    payment_method: string | null;
    proof_path: string | null;
    status: string;
    rejection_reason: string | null;
    expense: {
        title: string;
        group: {
            name: string;
        };
    };
};

defineProps<{
    payments: Payment[];
}>();

const form = useForm<{
    payment_method: 'transfer' | 'cash' | 'qris';
    proof: File | null;
}>({
    payment_method: 'transfer',
    proof: null,
});

function setProof(event: Event) {
    const input = event.target as HTMLInputElement;
    form.proof = input.files?.[0] ?? null;
}

function submit(payment: Payment) {
    form.post(`/payments/${payment.id}/pay`, {
        forceFormData: true,
        onSuccess: () => form.reset('proof'),
    });
}
</script>

<template>
    <Head title="Payments" />

    <main class="flex flex-col gap-6 p-4 md:p-8">
        <section class="rounded-[2rem] bg-black p-8 text-white md:p-10">
            <p class="text-sm font-semibold text-white/60">Tagihan saya</p>
            <h1
                class="mt-3 text-4xl font-semibold tracking-[-0.04em] md:text-6xl"
            >
                Pembayaran patungan
            </h1>
            <p class="mt-4 max-w-2xl text-white/70">
                Lihat tagihan, pilih metode bayar, dan unggah bukti pembayaran.
            </p>
        </section>

        <section class="grid gap-4">
            <article
                v-for="payment in payments"
                :key="payment.id"
                class="rounded-3xl border border-black/10 p-6"
            >
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                >
                    <div>
                        <p class="text-sm font-semibold text-black/50">
                            {{ payment.expense.group.name }}
                        </p>
                        <h2 class="mt-1 text-2xl font-semibold text-black">
                            {{ payment.expense.title }}
                        </h2>
                        <p class="mt-2 text-black/60">
                            Rp {{ payment.amount }} · {{ payment.status }}
                        </p>
                        <p
                            v-if="payment.rejection_reason"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ payment.rejection_reason }}
                        </p>
                    </div>

                    <form
                        v-if="['pending', 'rejected'].includes(payment.status)"
                        class="grid gap-3 md:w-80"
                        @submit.prevent="submit(payment)"
                    >
                        <select
                            v-model="form.payment_method"
                            class="h-12 rounded-xl border border-black/10 px-4"
                            name="payment_method"
                        >
                            <option value="transfer">Transfer</option>
                            <option value="cash">Tunai</option>
                            <option value="qris">QRIS</option>
                        </select>
                        <input
                            class="h-12 rounded-xl border border-black/10 px-4 py-3"
                            name="proof"
                            type="file"
                            @change="setProof"
                        />
                        <button
                            class="h-12 rounded-full bg-black px-5 font-semibold text-white disabled:opacity-50"
                            :disabled="form.processing"
                            type="submit"
                        >
                            Kirim pembayaran
                        </button>
                    </form>
                </div>
            </article>

            <p
                v-if="payments.length === 0"
                class="rounded-3xl border border-dashed border-black/20 p-8 text-black/60"
            >
                Belum ada tagihan.
            </p>
        </section>
    </main>
</template>
