<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

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
};

type Group = {
    id: string;
    name: string;
    description: string | null;
    status: string;
    members: User[];
    expenses: Expense[];
};

defineProps<{
    group: Group;
    inviteUrl: string;
    isAdmin: boolean;
}>();
</script>

<template>
    <Head :title="group.name" />

    <main class="flex flex-col gap-6 p-4 md:p-8">
        <Link class="text-sm font-semibold text-[#494fdf]" href="/groups">
            Kembali ke grup
        </Link>

        <section class="rounded-[2rem] bg-black p-8 text-white md:p-10">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-sm font-semibold text-white/60">
                        {{ group.status }}
                    </p>
                    <h1 class="mt-3 text-4xl font-semibold tracking-[-0.04em] md:text-6xl">
                        {{ group.name }}
                    </h1>
                    <p class="mt-4 max-w-2xl text-white/70">
                        {{ group.description ?? 'Grup patungan aktif.' }}
                    </p>
                </div>
                <div v-if="isAdmin" class="rounded-3xl bg-white p-4 text-black">
                    <p class="text-xs font-semibold uppercase tracking-[0.24px] text-black/50">
                        Invite link
                    </p>
                    <p class="mt-2 break-all text-sm">{{ inviteUrl }}</p>
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
                            <p class="font-semibold text-black">{{ member.name }}</p>
                            <p class="text-sm text-black/60">{{ member.email }}</p>
                        </div>
                        <span class="rounded-full bg-black px-3 py-1 text-xs font-semibold text-white">
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
                            <p class="font-semibold text-black">{{ expense.title }}</p>
                            <p class="font-semibold text-black">Rp {{ expense.amount }}</p>
                        </div>
                        <p class="mt-1 text-sm text-black/60">
                            {{ expense.status }} oleh {{ expense.payer?.name ?? 'admin' }}
                        </p>
                    </li>
                </ul>
                <p v-else class="mt-4 text-black/60">
                    Belum ada pengeluaran di grup ini.
                </p>
            </div>
        </section>
    </main>
</template>
