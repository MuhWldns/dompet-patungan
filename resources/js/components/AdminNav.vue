<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BarChart3, Shield, Users } from '@lucide/vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';

const { isCurrentUrl } = useCurrentUrl();

const items = [
    {
        title: 'User Control',
        description: 'Manage access, roles, and account activation',
        href: '/admin/users',
        icon: Shield,
    },
    {
        title: 'Group Monitoring',
        description: 'Review group health and activity aggregates',
        href: '/admin/groups',
        icon: Users,
    },
    {
        title: 'Platform Metrics',
        description: 'Monitor aggregate operational signals',
        href: '/admin/stats',
        icon: BarChart3,
    },
];
</script>

<template>
    <nav class="grid gap-3 md:grid-cols-3" aria-label="Admin navigation">
        <Link
            v-for="item in items"
            :key="item.href"
            :href="item.href"
            :class="[
                'vh-card-sm flex items-center gap-3 transition-colors hover:border-tertiary hover:bg-accent',
                isCurrentUrl(item.href) ? 'border-tertiary bg-accent' : '',
            ]"
        >
            <span
                class="flex size-10 items-center justify-center rounded-lg bg-primary text-primary-foreground"
            >
                <component :is="item.icon" class="size-5" />
            </span>
            <span>
                <span class="block font-semibold text-foreground">
                    {{ item.title }}
                </span>
                <span class="block text-sm text-muted-foreground">
                    {{ item.description }}
                </span>
            </span>
        </Link>
    </nav>
</template>
