<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="grid min-h-svh bg-primary text-primary-foreground lg:grid-cols-[1fr_1fr]"
    >
        <section class="hidden flex-col justify-between p-8 lg:flex">
            <Link
                :href="home()"
                class="font-heading text-xl font-bold tracking-[-0.03em]"
            >
                Dompet Patungan
            </Link>
            <div>
                <p
                    class="text-xs font-medium tracking-[0.5px] text-primary-foreground/60 uppercase"
                >
                    Split. Bayar. Selesai.
                </p>
                <h2
                    class="mt-4 max-w-xl text-5xl leading-[1.15] font-bold tracking-[-0.04em]"
                >
                    Semua tagihan grup tetap transparan.
                </h2>
                <p class="mt-5 max-w-lg text-primary-foreground/70">
                    Login untuk melihat grup, pembayaran tertunda, notifikasi,
                    dan settlement terbaru.
                </p>
            </div>
            <div
                class="grid grid-cols-3 gap-3 text-sm text-primary-foreground/75"
            >
                <div class="rounded-lg bg-primary-foreground/10 p-3">Grup</div>
                <div class="rounded-lg bg-primary-foreground/10 p-3">
                    Bukti bayar
                </div>
                <div
                    class="rounded-lg bg-tertiary p-3 text-tertiary-foreground"
                >
                    Settlement
                </div>
            </div>
        </section>

        <section
            class="flex items-center justify-center bg-background p-5 text-foreground md:p-8"
        >
            <div class="w-full max-w-md">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-3 text-left">
                        <Link :href="home()" class="vh-link text-sm lg:hidden">
                            Dompet Patungan
                        </Link>
                        <h1
                            class="text-2xl font-bold tracking-[-0.03em] text-foreground"
                        >
                            {{ title }}
                        </h1>
                        <p class="text-sm leading-6 text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                    <slot />
                </div>
            </div>
        </section>
    </div>
</template>
