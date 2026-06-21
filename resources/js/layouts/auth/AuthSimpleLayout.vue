<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="grid min-h-svh bg-black text-white lg:grid-cols-[1fr_1fr]">
        <section class="hidden flex-col justify-between p-8 lg:flex">
            <Link
                :href="home()"
                class="text-xl font-semibold tracking-[-0.03em]"
            >
                Dompet Patungan
            </Link>
            <div>
                <p class="text-sm font-semibold text-white/50">
                    Split. Bayar. Selesai.
                </p>
                <h2
                    class="mt-4 max-w-xl text-5xl leading-none font-semibold tracking-[-0.06em]"
                >
                    Semua tagihan grup tetap transparan.
                </h2>
                <p class="mt-5 max-w-lg text-white/65">
                    Login untuk melihat grup, pembayaran tertunda, notifikasi,
                    dan settlement terbaru.
                </p>
            </div>
            <div class="grid grid-cols-3 gap-3 text-sm text-white/70">
                <div class="rounded-3xl bg-white/10 p-3">Grup</div>
                <div class="rounded-3xl bg-white/10 p-3">Bukti bayar</div>
                <div class="rounded-3xl bg-[#494fdf] p-3 text-white">
                    Settlement
                </div>
            </div>
        </section>

        <section
            class="flex items-center justify-center bg-white p-5 text-black md:p-8"
        >
            <div class="w-full max-w-md">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-3 text-left">
                        <Link
                            :href="home()"
                            class="text-sm font-semibold text-[#494fdf] lg:hidden"
                        >
                            Dompet Patungan
                        </Link>
                        <h1
                            class="text-2xl font-semibold tracking-[-0.04em] text-black"
                        >
                            {{ title }}
                        </h1>
                        <p class="text-sm leading-6 text-black/60">
                            {{ description }}
                        </p>
                    </div>
                    <slot />
                </div>
            </div>
        </section>
    </div>
</template>
