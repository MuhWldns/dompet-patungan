<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

type User = {
    id: number;
    name: string;
    email: string;
    pivot?: {
        role: string;
    };
};

type Expense = {
    id: string;
    title: string;
    amount: string;
    status: string;
    payer?: User;
    payments?: Array<{
        id: string;
        amount: string;
        status: string;
        payment_method: string | null;
        rejection_reason: string | null;
        user: User;
    }>;
};

type Group = {
    id: string;
    name: string;
    description: string | null;
    status: string;
    members: User[];
    expenses: Expense[];
};

const props = defineProps<{
    group: Group;
    inviteUrl: string;
    isAdmin: boolean;
}>();

const expenseForm = useForm<{
    title: string;
    amount: string;
    category: string;
    date: string;
    split_method: 'equal' | 'custom';
    receipt: File | null;
}>({
    title: '',
    amount: '',
    category: '',
    date: new Date().toISOString().slice(0, 10),
    split_method: 'equal',
    receipt: null,
});

function setReceipt(event: Event) {
    const input = event.target as HTMLInputElement;
    expenseForm.receipt = input.files?.[0] ?? null;
}

function submitExpense() {
    expenseForm.post(`/groups/${props.group.id}/expenses`, {
        forceFormData: true,
        onSuccess: () =>
            expenseForm.reset('title', 'amount', 'category', 'receipt'),
    });
}
</script>

<template>
    <Head :title="group.name" />

    <main class="flex flex-col gap-6 p-4 md:p-8">
        <Link class="text-sm font-semibold text-[#494fdf]" href="/groups">
            Kembali ke grup
        </Link>

        <section class="rounded-[2rem] bg-black p-8 text-white md:p-10">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
            >
                <div>
                    <p class="text-sm font-semibold text-white/60">
                        {{ group.status }}
                    </p>
                    <h1
                        class="mt-3 text-4xl font-semibold tracking-[-0.04em] md:text-6xl"
                    >
                        {{ group.name }}
                    </h1>
                    <p class="mt-4 max-w-2xl text-white/70">
                        {{ group.description ?? 'Grup patungan aktif.' }}
                    </p>
                </div>
                <div v-if="isAdmin" class="rounded-3xl bg-white p-4 text-black">
                    <p
                        class="text-xs font-semibold tracking-[0.24px] text-black/50 uppercase"
                    >
                        Invite link
                    </p>
                    <p class="mt-2 text-sm break-all">{{ inviteUrl }}</p>
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-3xl border border-black/10 p-6">
                <h2 class="text-2xl font-semibold text-black">Anggota</h2>
                <ul class="mt-4 grid gap-3">
                    <li
                        v-for="member in group.members"
                        :key="member.id"
                        class="flex items-center justify-between rounded-2xl bg-[#f4f4f4] p-4"
                    >
                        <div>
                            <p class="font-semibold text-black">
                                {{ member.name }}
                            </p>
                            <p class="text-sm text-black/60">
                                {{ member.email }}
                            </p>
                        </div>
                        <span
                            class="rounded-full bg-black px-3 py-1 text-xs font-semibold text-white"
                        >
                            {{ member.pivot?.role ?? 'member' }}
                        </span>
                    </li>
                </ul>
            </div>

            <div class="rounded-3xl border border-black/10 p-6">
                <h2 class="text-2xl font-semibold text-black">Pengeluaran</h2>
                <ul v-if="group.expenses.length > 0" class="mt-4 grid gap-3">
                    <li
                        v-for="expense in group.expenses"
                        :key="expense.id"
                        class="rounded-2xl bg-[#f4f4f4] p-4"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <p class="font-semibold text-black">
                                {{ expense.title }}
                            </p>
                            <p class="font-semibold text-black">
                                Rp {{ expense.amount }}
                            </p>
                        </div>
                        <p class="mt-1 text-sm text-black/60">
                            {{ expense.status }} oleh
                            {{ expense.payer?.name ?? 'admin' }}
                        </p>
                        <div
                            v-if="isAdmin && expense.payments?.length"
                            class="mt-4 grid gap-2"
                        >
                            <div
                                v-for="payment in expense.payments"
                                :key="payment.id"
                                class="rounded-xl bg-white p-3 text-sm"
                            >
                                <div
                                    class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
                                >
                                    <p class="text-black/70">
                                        {{ payment.user.name }} · Rp
                                        {{ payment.amount }} ·
                                        {{ payment.status }}
                                    </p>
                                    <div
                                        v-if="payment.status === 'submitted'"
                                        class="flex gap-2"
                                    >
                                        <Link
                                            :href="`/payments/${payment.id}/confirm`"
                                            as="button"
                                            class="rounded-full bg-black px-3 py-1 font-semibold text-white"
                                            method="patch"
                                        >
                                            Confirm
                                        </Link>
                                        <Link
                                            :href="`/payments/${payment.id}/reject`"
                                            as="button"
                                            class="rounded-full border border-black px-3 py-1 font-semibold text-black"
                                            method="patch"
                                        >
                                            Reject
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                <p v-else class="mt-4 text-black/60">
                    Belum ada pengeluaran di grup ini.
                </p>
            </div>
        </section>

        <section v-if="isAdmin" class="rounded-3xl bg-[#f4f4f4] p-6">
            <h2 class="text-2xl font-semibold text-black">
                Tambah pengeluaran
            </h2>
            <form
                class="mt-5 grid gap-4 md:grid-cols-2"
                @submit.prevent="submitExpense"
            >
                <label class="block text-sm font-semibold text-black">
                    Judul
                    <input
                        v-model="expenseForm.title"
                        class="mt-2 h-12 w-full rounded-xl border border-black/10 bg-white px-4 text-black"
                        name="title"
                    />
                    <span
                        v-if="expenseForm.errors.title"
                        class="mt-1 block text-sm text-red-600"
                    >
                        {{ expenseForm.errors.title }}
                    </span>
                </label>

                <label class="block text-sm font-semibold text-black">
                    Jumlah
                    <input
                        v-model="expenseForm.amount"
                        class="mt-2 h-12 w-full rounded-xl border border-black/10 bg-white px-4 text-black"
                        min="0"
                        name="amount"
                        step="0.01"
                        type="number"
                    />
                    <span
                        v-if="expenseForm.errors.amount"
                        class="mt-1 block text-sm text-red-600"
                    >
                        {{ expenseForm.errors.amount }}
                    </span>
                </label>

                <label class="block text-sm font-semibold text-black">
                    Kategori
                    <input
                        v-model="expenseForm.category"
                        class="mt-2 h-12 w-full rounded-xl border border-black/10 bg-white px-4 text-black"
                        name="category"
                    />
                </label>

                <label class="block text-sm font-semibold text-black">
                    Tanggal
                    <input
                        v-model="expenseForm.date"
                        class="mt-2 h-12 w-full rounded-xl border border-black/10 bg-white px-4 text-black"
                        name="date"
                        type="date"
                    />
                </label>

                <label class="block text-sm font-semibold text-black">
                    Metode split
                    <select
                        v-model="expenseForm.split_method"
                        class="mt-2 h-12 w-full rounded-xl border border-black/10 bg-white px-4 text-black"
                        name="split_method"
                    >
                        <option value="equal">Rata semua anggota</option>
                        <option value="custom">Kustom via backend</option>
                    </select>
                </label>

                <label class="block text-sm font-semibold text-black">
                    Struk
                    <input
                        class="mt-2 h-12 w-full rounded-xl border border-black/10 bg-white px-4 py-3 text-black"
                        name="receipt"
                        type="file"
                        @change="setReceipt"
                    />
                </label>

                <div class="md:col-span-2">
                    <button
                        class="h-12 rounded-full bg-black px-6 font-semibold text-white disabled:opacity-50"
                        :disabled="expenseForm.processing"
                        type="submit"
                    >
                        Simpan pengeluaran
                    </button>
                </div>
            </form>
        </section>
    </main>
</template>
