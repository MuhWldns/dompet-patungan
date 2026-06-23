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

    <main class="vh-page">
        <section class="vh-hero">
            <p class="vh-eyebrow">Dompet Patungan</p>
            <div
                class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
            >
                <div>
                    <h1 class="vh-title">Kelola grup patungan</h1>
                    <p class="vh-description">
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
                    class="group vh-card cursor-pointer transition duration-200 hover:border-tertiary hover:bg-accent"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-semibold text-foreground">
                                {{ group.name }}
                            </h2>
                            <p class="mt-2 text-sm text-muted-foreground">
                                {{ group.description ?? 'Tidak ada deskripsi' }}
                            </p>
                            <p
                                class="mt-3 text-sm font-medium text-muted-foreground"
                            >
                                Klik untuk melihat anggota, pengeluaran, dan
                                settlement.
                            </p>
                        </div>
                        <span class="vh-chip vh-chip-navy">
                            {{ group.status }}
                        </span>
                    </div>
                    <div
                        class="mt-5 flex flex-col gap-3 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex gap-3">
                            <span>{{ group.members_count }} anggota</span>
                            <span>{{ group.expenses_count }} pengeluaran</span>
                        </div>
                        <span
                            class="vh-link transition group-hover:translate-x-1"
                        >
                            Buka grup ->
                        </span>
                    </div>
                </Link>

                <div
                    v-if="groups.length === 0"
                    class="rounded-lg border border-dashed border-border bg-card p-8 text-muted-foreground"
                >
                    Belum ada grup. Buat grup pertama di form sebelah kanan.
                </div>
            </div>

            <form class="vh-card" @submit.prevent="submit">
                <h2 class="text-xl font-semibold text-foreground">Buat grup</h2>
                <label class="mt-5 block text-sm font-medium text-foreground">
                    Nama grup
                    <input v-model="form.name" class="vh-input" name="name" />
                </label>
                <p
                    v-if="form.errors.name"
                    class="mt-1 text-sm text-destructive"
                >
                    {{ form.errors.name }}
                </p>

                <label class="mt-4 block text-sm font-medium text-foreground">
                    Deskripsi
                    <textarea
                        v-model="form.description"
                        class="vh-textarea"
                        name="description"
                    />
                </label>

                <!-- Target dana disabled — not used in any business logic -->

                <button
                    class="vh-primary-action mt-6"
                    :disabled="form.processing"
                    type="submit"
                >
                    Simpan grup
                </button>
            </form>
        </section>
    </main>
</template>
