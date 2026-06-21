<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

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

function buildInitialSplits(): Record<number, string> {
    return Object.fromEntries(
        props.group.members.map((member) => [member.id, '']),
    );
}

const expenseForm = useForm<{
    title: string;
    amount: string;
    category: string;
    date: string;
    split_method: 'equal' | 'custom';
    splits: Record<number, string>;
    receipt: File | null;
}>({
    title: '',
    amount: '',
    category: '',
    date: new Date().toISOString().slice(0, 10),
    split_method: 'equal',
    splits: buildInitialSplits(),
    receipt: null,
});

const amountCents = computed(() => toCents(expenseForm.amount));
const customSplitTotalCents = computed(() =>
    Object.values(expenseForm.splits).reduce(
        (total, amount) => total + toCents(amount),
        0,
    ),
);
const customSplitDifferenceCents = computed(
    () => amountCents.value - customSplitTotalCents.value,
);

function toCents(value: string | number): number {
    const numeric = Number(value || 0);

    if (!Number.isFinite(numeric)) {
        return 0;
    }

    return Math.round(numeric * 100);
}

function formatCents(cents: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
    }).format(cents / 100);
}

function getSplitError(memberId: number): string | undefined {
    return (expenseForm.errors as Record<string, string | undefined>)[
        `splits.${memberId}`
    ];
}

function setReceipt(event: Event) {
    const input = event.target as HTMLInputElement;
    expenseForm.receipt = input.files?.[0] ?? null;
}

function submitExpense() {
    expenseForm.post(`/groups/${props.group.id}/expenses`, {
        forceFormData: true,
        onSuccess: () => {
            expenseForm.reset('title', 'amount', 'category', 'receipt');
            expenseForm.split_method = 'equal';
            expenseForm.splits = buildInitialSplits();
        },
    });
}

const statusForm = useForm<{ status: string }>({ status: '' });

function updateStatus(newStatus: string) {
    statusForm.status = newStatus;
    statusForm.patch(`/groups/${props.group.id}/status`, {
        onSuccess: () => {
            statusForm.reset();
        },
    });
}
</script>

<template>
    <Head :title="group.name" />

    <main class="vh-page">
        <Link class="vh-link text-sm" href="/groups"> Kembali ke grup </Link>

        <section class="vh-hero">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
            >
                <div>
                    <p class="vh-eyebrow">
                        {{ group.status }}
                    </p>
                    <h1 class="vh-title">
                        {{ group.name }}
                    </h1>
                    <p class="vh-description">
                        {{ group.description ?? 'Grup patungan aktif.' }}
                    </p>
                </div>
                <div
                    v-if="isAdmin"
                    class="rounded-lg bg-card p-4 text-card-foreground"
                >
                    <p
                        class="text-xs font-medium tracking-[0.5px] text-muted-foreground uppercase"
                    >
                        Invite link
                    </p>
                    <p class="mt-2 text-sm break-all">{{ inviteUrl }}</p>
                </div>
                <div v-if="isAdmin" class="flex gap-2">
                    <select
                        :value="group.status"
                        class="vh-input text-sm"
                        @change="updateStatus(($event.target as HTMLSelectElement).value)"
                    >
                        <option value="active">Active</option>
                        <option value="settled">Settled</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="vh-card">
                <h2 class="text-2xl font-semibold text-foreground">Anggota</h2>
                <ul class="mt-4 grid gap-3">
                    <li
                        v-for="member in group.members"
                        :key="member.id"
                        class="vh-muted-card flex items-center justify-between"
                    >
                        <div>
                            <p class="font-semibold text-foreground">
                                {{ member.name }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ member.email }}
                            </p>
                        </div>
                        <span class="vh-chip vh-chip-navy">
                            {{ member.pivot?.role ?? 'member' }}
                        </span>
                        <Link
                            v-if="isAdmin || member.id === $page.props.auth.user.id"
                            :href="`/groups/${group.id}/members/${member.id}`"
                            as="button"
                            class="rounded-lg p-1 text-muted-foreground hover:text-destructive"
                            method="delete"
                            :confirm="member.id === $page.props.auth.user.id ? 'Yakin ingin keluar dari grup?' : 'Yakin ingin mengeluarkan anggota ini?'"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </Link>
                    </li>
                </ul>
            </div>

            <div class="vh-card">
                <h2 class="text-2xl font-semibold text-foreground">
                    Pengeluaran
                </h2>
                <ul v-if="group.expenses.length > 0" class="mt-4 grid gap-3">
                    <li
                        v-for="expense in group.expenses"
                        :key="expense.id"
                        class="vh-muted-card"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <p class="font-semibold text-foreground">
                                {{ expense.title }}
                            </p>
                            <p class="font-mono font-semibold text-foreground">
                                Rp {{ expense.amount }}
                            </p>
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">
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
                                class="rounded-lg border border-border bg-card p-3 text-sm"
                            >
                                <div
                                    class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
                                >
                                    <p class="text-muted-foreground">
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
                                            class="rounded-lg bg-primary px-3 py-1 font-semibold text-primary-foreground"
                                            method="patch"
                                        >
                                            Confirm
                                        </Link>
                                        <Link
                                            :href="`/payments/${payment.id}/reject`"
                                            as="button"
                                            class="rounded-lg border border-primary px-3 py-1 font-semibold text-primary"
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
                <p v-else class="mt-4 text-muted-foreground">
                    Belum ada pengeluaran di grup ini.
                </p>
            </div>
        </section>

        <section v-if="isAdmin" class="vh-card">
            <h2 class="text-2xl font-semibold text-foreground">
                Tambah pengeluaran
            </h2>
            <form
                class="mt-5 grid gap-4 md:grid-cols-2"
                @submit.prevent="submitExpense"
            >
                <label class="block text-sm font-medium text-foreground">
                    Judul
                    <input
                        v-model="expenseForm.title"
                        class="vh-input"
                        name="title"
                    />
                    <span
                        v-if="expenseForm.errors.title"
                        class="mt-1 block text-sm text-destructive"
                    >
                        {{ expenseForm.errors.title }}
                    </span>
                </label>

                <label class="block text-sm font-medium text-foreground">
                    Jumlah
                    <input
                        v-model="expenseForm.amount"
                        class="vh-input"
                        min="0"
                        name="amount"
                        step="0.01"
                        type="number"
                    />
                    <span
                        v-if="expenseForm.errors.amount"
                        class="mt-1 block text-sm text-destructive"
                    >
                        {{ expenseForm.errors.amount }}
                    </span>
                </label>

                <label class="block text-sm font-medium text-foreground">
                    Kategori
                    <input
                        v-model="expenseForm.category"
                        class="vh-input"
                        name="category"
                    />
                </label>

                <label class="block text-sm font-medium text-foreground">
                    Tanggal
                    <input
                        v-model="expenseForm.date"
                        class="vh-input"
                        name="date"
                        type="date"
                    />
                </label>

                <label class="block text-sm font-medium text-foreground">
                    Metode split
                    <select
                        v-model="expenseForm.split_method"
                        class="vh-input"
                        name="split_method"
                    >
                        <option value="equal">Rata semua anggota</option>
                        <option value="custom">Kustom per anggota</option>
                    </select>
                </label>

                <section
                    v-if="expenseForm.split_method === 'custom'"
                    class="md:col-span-2"
                >
                    <div
                        class="rounded-lg border border-border bg-background p-4"
                    >
                        <div
                            class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between"
                        >
                            <div>
                                <h3 class="font-semibold text-foreground">
                                    Nominal custom per anggota
                                </h3>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Isi nominal tagihan untuk tiap anggota.
                                    Total custom harus sama dengan jumlah
                                    pengeluaran.
                                </p>
                            </div>
                            <div class="text-sm md:text-right">
                                <p class="text-muted-foreground">
                                    Total custom
                                </p>
                                <p
                                    class="font-mono font-semibold text-foreground"
                                >
                                    {{ formatCents(customSplitTotalCents) }}
                                </p>
                                <p
                                    :class="[
                                        'mt-1 font-mono text-xs font-semibold',
                                        customSplitDifferenceCents === 0
                                            ? 'text-green-600'
                                            : 'text-destructive',
                                    ]"
                                >
                                    Selisih
                                    {{
                                        formatCents(customSplitDifferenceCents)
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3">
                            <label
                                v-for="member in group.members"
                                :key="member.id"
                                class="grid gap-3 rounded-lg border border-border bg-card p-3 md:grid-cols-[1fr_220px] md:items-center"
                            >
                                <span>
                                    <span
                                        class="block font-semibold text-foreground"
                                    >
                                        {{ member.name }}
                                    </span>
                                    <span
                                        class="block text-sm text-muted-foreground"
                                    >
                                        {{ member.email }} ·
                                        {{ member.pivot?.role ?? 'member' }}
                                    </span>
                                </span>
                                <span>
                                    <input
                                        v-model="expenseForm.splits[member.id]"
                                        class="vh-input font-mono"
                                        min="0"
                                        :name="`splits[${member.id}]`"
                                        placeholder="0.00"
                                        step="0.01"
                                        type="number"
                                    />
                                    <span
                                        v-if="getSplitError(member.id)"
                                        class="mt-1 block text-sm text-destructive"
                                    >
                                        {{ getSplitError(member.id) }}
                                    </span>
                                </span>
                            </label>
                        </div>

                        <p
                            v-if="expenseForm.errors.splits"
                            class="mt-3 text-sm text-destructive"
                        >
                            {{ expenseForm.errors.splits }}
                        </p>
                    </div>
                </section>

                <label class="block text-sm font-medium text-foreground">
                    Struk
                    <input
                        class="vh-input py-2.5"
                        name="receipt"
                        type="file"
                        @change="setReceipt"
                    />
                </label>

                <div class="md:col-span-2">
                    <button
                        class="vh-primary-action"
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
