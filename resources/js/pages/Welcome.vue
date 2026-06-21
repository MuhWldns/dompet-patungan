<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
</script>

<template>
    <Head title="Dompet Patungan" />

    <main class="min-h-screen bg-background text-foreground">
        <section class="bg-primary px-6 py-6 text-primary-foreground md:px-10">
            <nav class="mx-auto flex max-w-7xl items-center justify-between">
                <div class="font-heading text-lg font-bold tracking-[-0.02em]">
                    Dompet Patungan
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-lg bg-tertiary px-5 py-2 text-sm font-semibold text-tertiary-foreground transition-colors hover:bg-emerald-700"
                    >
                        Dashboard
                    </Link>
                    <Link
                        v-else
                        :href="login()"
                        class="rounded-lg bg-tertiary px-5 py-2 text-sm font-semibold text-tertiary-foreground transition-colors hover:bg-emerald-700"
                    >
                        Masuk
                    </Link>
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        class="hidden rounded-lg border border-primary-foreground px-5 py-2 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary-foreground/10 md:inline-flex"
                    >
                        Daftar
                    </Link>
                </div>
            </nav>

            <div
                class="mx-auto grid max-w-7xl gap-8 py-12 md:grid-cols-[1.2fr_0.8fr] md:py-16"
            >
                <div>
                    <p
                        class="text-xs font-medium tracking-[0.5px] text-primary-foreground/70 uppercase"
                    >
                        Split tagihan tanpa ribet
                    </p>
                    <h1
                        class="mt-4 max-w-4xl text-5xl leading-[1.15] font-bold tracking-[-0.04em] md:text-7xl"
                    >
                        Patungan jelas. Utang selesai.
                    </h1>
                    <p
                        class="mt-5 max-w-2xl text-lg leading-8 text-primary-foreground/75"
                    >
                        Buat grup, catat pengeluaran, bagi tagihan rata atau
                        kustom, kumpulkan bukti bayar, lalu generate settlement
                        yang minim transaksi.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <Link
                            :href="
                                $page.props.auth.user ? dashboard() : register()
                            "
                            class="rounded-lg bg-tertiary px-7 py-3 font-semibold text-tertiary-foreground transition-colors hover:bg-emerald-700"
                        >
                            Mulai kelola patungan
                        </Link>
                        <a
                            href="#features"
                            class="rounded-lg border border-primary-foreground px-7 py-3 font-semibold text-primary-foreground transition-colors hover:bg-primary-foreground/10"
                        >
                            Lihat fitur
                        </a>
                    </div>
                </div>

                <div
                    class="rounded-lg border border-primary-foreground/10 bg-primary-foreground/5 p-5"
                >
                    <div
                        class="rounded-lg bg-tertiary p-5 text-tertiary-foreground"
                    >
                        <p
                            class="text-sm font-semibold text-tertiary-foreground/75"
                        >
                            Trip Bandung
                        </p>
                        <p
                            class="mt-5 font-mono text-4xl font-semibold tracking-[-0.03em]"
                        >
                            Rp 1.240.000
                        </p>
                        <p class="mt-2 text-tertiary-foreground/75">
                            Total pengeluaran grup
                        </p>
                    </div>
                    <div class="mt-4 grid gap-3">
                        <div
                            class="rounded-lg bg-card p-4 text-card-foreground"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">Makan malam</span>
                                <span>Rp 360.000</span>
                            </div>
                            <p class="mt-2 text-sm text-muted-foreground">
                                3 anggota · split rata
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-card p-4 text-card-foreground"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">Settlement</span>
                                <span class="text-tertiary">2 transfer</span>
                            </div>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Lebih sedikit transaksi, lebih cepat selesai.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="px-6 py-12 md:px-10 md:py-14">
            <div class="mx-auto max-w-7xl">
                <h2
                    class="max-w-3xl text-4xl leading-tight font-bold tracking-[-0.03em] md:text-5xl"
                >
                    Semua alur patungan dalam satu tempat.
                </h2>
                <div class="mt-7 grid gap-4 md:grid-cols-3">
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h3 class="text-xl font-semibold">Grup & undangan</h3>
                        <p class="mt-3 text-muted-foreground">
                            Admin membuat grup, mengundang anggota, dan
                            mengelola status patungan.
                        </p>
                    </div>
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h3 class="text-xl font-semibold">
                            Expense & bukti bayar
                        </h3>
                        <p class="mt-3 text-muted-foreground">
                            Catat struk, split rata atau kustom, lalu validasi
                            pembayaran anggota.
                        </p>
                    </div>
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h3 class="text-xl font-semibold">
                            Settlement otomatis
                        </h3>
                        <p class="mt-3 text-muted-foreground">
                            Hitung utang bersih agar semua orang tahu harus
                            bayar ke siapa.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>
