<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Masuk ke Dompet Patungan',
        description:
            'Lanjutkan mengelola grup, tagihan, dan settlement patungan.',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Masuk" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="rounded-[2rem] border border-black/10 bg-white p-5 shadow-none"
    >
        <div class="grid gap-4">
            <div>
                <p class="text-sm font-semibold text-[#494fdf]">
                    Dompet Patungan
                </p>
                <h2
                    class="mt-2 text-xl font-semibold tracking-[-0.03em] text-black"
                >
                    Masuk ke Dompet Patungan
                </h2>
                <p class="mt-1.5 text-sm text-black/60">
                    Pantau tagihan, upload bukti bayar, dan selesaikan utang
                    grup.
                </p>
            </div>
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="nama@email.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Password</Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-sm"
                        :tabindex="5"
                    >
                        Lupa password?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Password"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Ingat saya</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-2 w-full rounded-full bg-black text-white hover:bg-black/90"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Masuk
            </Button>

            <p class="text-center text-sm text-black/60">
                Belum punya akun?
                <TextLink
                    :href="register()"
                    class="font-semibold text-[#494fdf]"
                >
                    Daftar Dompet Patungan
                </TextLink>
            </p>
        </div>
    </Form>
</template>
