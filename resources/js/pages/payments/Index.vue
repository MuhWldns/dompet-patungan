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

    <main class="vh-page">
        <section class="vh-hero">
            <p class="vh-eyebrow">Tagihan saya</p>
            <h1 class="vh-title">Pembayaran patungan</h1>
            <p class="vh-description">
                Lihat tagihan, pilih metode bayar, dan unggah bukti pembayaran.
            </p>
        </section>

        <section class="grid gap-4">
            <article
                v-for="payment in payments"
                :key="payment.id"
                class="vh-card"
            >
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                >
                    <div>
                        <p class="vh-stat-label">
                            {{ payment.expense.group.name }}
                        </p>
                        <h2 class="mt-1 text-2xl font-semibold text-foreground">
                            {{ payment.expense.title }}
                        </h2>
                        <p class="mt-2 text-muted-foreground">
                            Rp {{ payment.amount }} · {{ payment.status }}
                        </p>
                        <p
                            v-if="payment.rejection_reason"
                            class="mt-2 text-sm text-destructive"
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
                            class="vh-input mt-0"
                            name="payment_method"
                        >
                            <option value="transfer">Transfer</option>
                            <option value="cash">Tunai</option>
                            <option value="qris">QRIS</option>
                        </select>
                        <input
                            class="vh-input mt-0 py-2.5"
                            name="proof"
                            type="file"
                            @change="setProof"
                        />
                        <button
                            class="vh-primary-action"
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
                class="rounded-lg border border-dashed border-border bg-card p-8 text-muted-foreground"
            >
                Belum ada tagihan.
            </p>
        </section>
    </main>
</template>
