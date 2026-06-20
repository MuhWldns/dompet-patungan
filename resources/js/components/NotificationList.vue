<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

type Notification = {
    id: number;
    type: string;
    message: string;
    link: string | null;
    read_at: string | null;
};

defineProps<{
    notifications: Notification[];
}>();
</script>

<template>
    <div class="grid gap-3">
        <div
            v-for="notification in notifications"
            :key="notification.id"
            class="rounded-2xl bg-[#f4f4f4] p-4"
        >
            <p class="font-semibold text-black">{{ notification.message }}</p>
            <div class="mt-3 flex gap-3 text-sm">
                <Link
                    v-if="notification.link"
                    :href="notification.link"
                    class="font-semibold text-[#494fdf]"
                >
                    Buka
                </Link>
                <Link
                    v-if="!notification.read_at"
                    :href="`/notifications/${notification.id}/read`"
                    as="button"
                    class="font-semibold text-black"
                    method="patch"
                >
                    Tandai dibaca
                </Link>
            </div>
        </div>

        <p v-if="notifications.length === 0" class="text-black/60">
            Belum ada notifikasi.
        </p>
    </div>
</template>
