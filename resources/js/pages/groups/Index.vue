<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

type Group = {
    id: string;
    name: string;
    description: string | null;
    target_amount: string | null;
    status: string;
    members_count: number;
    expenses_count: number;
};

defineProps<{
    groups: Group[];
}>();

const form = useForm({
    name: '',
    description: '',
    target_amount: '',
});

function submit() {
    form.post('/groups', {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Groups" />

    <main class="flex flex-col gap-8 p-4 md:p-8">
        <section class="rounded-[2rem] bg-black p-8 text-white md:p-10">
            <p class="text-sm font-semibold tracking-[0.24px] text-white/70">
                Dompet Patungan
            </p>
            <div
                class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
            >
                <div>
                    <h1
                        class="text-4xl font-semibold tracking-[-0.04em] md:text-6xl"
                    >
                        Kelola grup patungan
                    </h1>
                    <p class="mt-4 max-w-2xl text-white/70">
                        Buat grup, undang anggota, dan lacak pengeluaran
                        bersama.
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="grid gap-4">
                <Link
                    v-for="group in groups"
                    :key="group.id"
                    :href="`/groups/${group.id}`"
                    class="rounded-3xl border border-black/10 bg-white p-6 transition hover:border-black"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-semibold text-black">
                                {{ group.name }}
                            </h2>
                            <p class="mt-2 text-sm text-black/60">
                                {{ group.description ?? 'Tidak ada deskripsi' }}
                            </p>
                        </div>
                        <span
                            class="rounded-full bg-black px-3 py-1 text-xs font-semibold text-white"
                        >
                            {{ group.status }}
                        </span>
                    </div>
                    <div class="mt-6 flex gap-3 text-sm text-black/60">
                        <span>{{ group.members_count }} anggota</span>
                        <span>{{ group.expenses_count }} pengeluaran</span>
                    </div>
                </Link>

                <div
                    v-if="groups.length === 0"
                    class="rounded-3xl border border-dashed border-black/20 p-8 text-black/60"
                >
                    Belum ada grup. Buat grup pertama di form sebelah kanan.
                </div>
            </div>

            <form class="rounded-3xl bg-[#f4f4f4] p-6" @submit.prevent="submit">
                <h2 class="text-xl font-semibold text-black">Buat grup</h2>
                <label class="mt-5 block text-sm font-semibold text-black">
                    Nama grup
                    <input
                        v-model="form.name"
                        class="mt-2 h-12 w-full rounded-xl border border-black/10 bg-white px-4 text-black"
                        name="name"
                    />
                </label>
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                    {{ form.errors.name }}
                </p>

                <label class="mt-4 block text-sm font-semibold text-black">
                    Deskripsi
                    <textarea
                        v-model="form.description"
                        class="mt-2 min-h-24 w-full rounded-xl border border-black/10 bg-white px-4 py-3 text-black"
                        name="description"
                    />
                </label>

                <label class="mt-4 block text-sm font-semibold text-black">
                    Target dana
                    <input
                        v-model="form.target_amount"
                        class="mt-2 h-12 w-full rounded-xl border border-black/10 bg-white px-4 text-black"
                        name="target_amount"
                        type="number"
                        min="0"
                    />
                </label>

                <button
                    class="mt-6 h-12 rounded-full bg-black px-6 font-semibold text-white disabled:opacity-50"
                    :disabled="form.processing"
                    type="submit"
                >
                    Simpan grup
                </button>
            </form>
        </section>
    </main>
</template>
