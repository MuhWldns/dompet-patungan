<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
</script>

<template>
    <Head title="Dompet Patungan" />

    <main class="min-h-screen bg-white text-black">
        <section class="bg-black px-6 py-6 text-white md:px-10">
            <nav class="mx-auto flex max-w-7xl items-center justify-between">
                <div class="text-lg font-semibold tracking-[-0.02em]">
                    Dompet Patungan
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-full bg-white px-5 py-2 text-sm font-semibold text-black"
                    >
                        Dashboard
                    </Link>
                    <Link
                        v-else
                        :href="login()"
                        class="rounded-full bg-white px-5 py-2 text-sm font-semibold text-black"
                    >
                        Masuk
                    </Link>
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        class="hidden rounded-full border border-white px-5 py-2 text-sm font-semibold text-white md:inline-flex"
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
                        class="text-sm font-semibold tracking-[0.24px] text-white/60"
                    >
                        Split tagihan tanpa ribet
                    </p>
                    <h1
                        class="mt-4 max-w-4xl text-5xl leading-none font-semibold tracking-[-0.06em] md:text-7xl"
                    >
                        Patungan jelas. Utang selesai.
                    </h1>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-white/70">
                        Buat grup, catat pengeluaran, bagi tagihan rata atau
                        kustom, kumpulkan bukti bayar, lalu generate settlement
                        yang minim transaksi.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <Link
                            :href="
                                $page.props.auth.user ? dashboard() : register()
                            "
                            class="rounded-full bg-white px-7 py-4 font-semibold text-black"
                        >
                            Mulai kelola patungan
                        </Link>
                        <a
                            href="#features"
                            class="rounded-full border border-white px-7 py-4 font-semibold text-white"
                        >
                            Lihat fitur
                        </a>
                    </div>
                </div>

                <div class="rounded-[2rem] bg-[#16181a] p-5">
                    <div class="rounded-3xl bg-[#494fdf] p-5 text-white">
                        <p class="text-sm font-semibold text-white/70">
                            Trip Bandung
                        </p>
                        <p
                            class="mt-5 text-4xl font-semibold tracking-[-0.05em]"
                        >
                            Rp 1.240.000
                        </p>
                        <p class="mt-2 text-white/70">Total pengeluaran grup</p>
                    </div>
                    <div class="mt-4 grid gap-3">
                        <div class="rounded-3xl bg-white p-4 text-black">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">Makan malam</span>
                                <span>Rp 360.000</span>
                            </div>
                            <p class="mt-2 text-sm text-black/50">
                                3 anggota · split rata
                            </p>
                        </div>
                        <div class="rounded-3xl bg-white p-4 text-black">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">Settlement</span>
                                <span class="text-[#006400]">2 transfer</span>
                            </div>
                            <p class="mt-2 text-sm text-black/50">
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
                    class="max-w-3xl text-4xl leading-tight font-semibold tracking-[-0.04em] md:text-5xl"
                >
                    Semua alur patungan dalam satu tempat.
                </h2>
                <div class="mt-7 grid gap-4 md:grid-cols-3">
                    <div class="rounded-3xl border border-black/10 p-5">
                        <h3 class="text-xl font-semibold">Grup & undangan</h3>
                        <p class="mt-3 text-black/60">
                            Admin membuat grup, mengundang anggota, dan
                            mengelola status patungan.
                        </p>
                    </div>
                    <div class="rounded-3xl border border-black/10 p-5">
                        <h3 class="text-xl font-semibold">
                            Expense & bukti bayar
                        </h3>
                        <p class="mt-3 text-black/60">
                            Catat struk, split rata atau kustom, lalu validasi
                            pembayaran anggota.
                        </p>
                    </div>
                    <div class="rounded-3xl border border-black/10 p-5">
                        <h3 class="text-xl font-semibold">
                            Settlement otomatis
                        </h3>
                        <p class="mt-3 text-black/60">
                            Hitung utang bersih agar semua orang tahu harus
                            bayar ke siapa.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>
