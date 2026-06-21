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
    notifications: Notification[] | { data: Notification[] };
}>();

function getNotifications(notifications: Notification[] | { data: Notification[] }): Notification[] {
    if (Array.isArray(notifications)) {
        return notifications;
    }
    return notifications.data;
}
</script>

<template>
    <div class="grid gap-3">
        <div
            v-for="notification in getNotifications(notifications)"
            :key="notification.id"
            class="vh-muted-card"
            :class="{ 'opacity-60': notification.read_at }"
        >
            <p class="font-semibold text-foreground">
                {{ notification.message }}
            </p>
            <div class="mt-3 flex gap-3 text-sm">
                <Link
                    v-if="notification.link"
                    :href="notification.link"
                    class="vh-link"
                >
                    Buka
                </Link>
                <Link
                    v-if="!notification.read_at"
                    :href="`/notifications/${notification.id}/read`"
                    as="button"
                    class="font-semibold text-primary"
                    method="patch"
                >
                    Tandai dibaca
                </Link>
            </div>
        </div>

        <p v-if="getNotifications(notifications).length === 0" class="text-muted-foreground">
            Tidak ada notifikasi.
        </p>
    </div>
</template>
