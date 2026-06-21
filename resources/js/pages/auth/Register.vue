<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineOptions({
    layout: {
        title: 'Daftar Dompet Patungan',
        description:
            'Buat akun untuk mulai mengelola grup patungan dan split tagihan.',
    },
});

defineProps<{
    passwordRules: string;
}>();
</script>

<template>
    <Head title="Daftar" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="rounded-lg border border-border bg-card p-6 shadow-none"
    >
        <div class="grid gap-4">
            <div>
                <p class="text-sm font-semibold text-tertiary">
                    Dompet Patungan
                </p>
                <h2
                    class="mt-2 text-xl font-semibold tracking-[-0.03em] text-foreground"
                >
                    Daftar Dompet Patungan
                </h2>
                <p class="mt-1.5 text-sm text-muted-foreground">
                    Buat akun, undang teman, lalu mulai catat patungan pertama.
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="name">Nama</Label>
                <Input
                    id="name"
                    autocomplete="name"
                    autofocus
                    name="name"
                    placeholder="Nama lengkap"
                    required
                    type="text"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    autocomplete="email"
                    name="email"
                    placeholder="nama@email.com"
                    required
                    type="email"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <PasswordInput
                    id="password"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Minimal 8 karakter"
                    required
                />
                <p class="text-xs text-muted-foreground">{{ passwordRules }}</p>
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Konfirmasi password</Label>
                <PasswordInput
                    id="password_confirmation"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Ulangi password"
                    required
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button class="mt-2 w-full" :disabled="processing" type="submit">
                <Spinner v-if="processing" />
                Daftar
            </Button>

            <p class="text-center text-sm text-muted-foreground">
                Sudah punya akun?
                <Link :href="login()" class="font-semibold text-tertiary">
                    Masuk
                </Link>
            </p>
        </div>
    </Form>
</template>
