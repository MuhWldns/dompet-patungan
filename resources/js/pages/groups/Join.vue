<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

type Group = {
    id: string;
    name: string;
    description: string | null;
    status: string;
    members_count: number;
};

defineProps<{
    group: Group;
    groupUrl: string;
    isMember: boolean;
    joinUrl: string;
}>();
</script>

<template>
    <Head :title="`Join ${group.name}`" />

    <main
        class="flex min-h-[calc(100vh-4rem)] items-center justify-center bg-background p-4 md:p-6"
    >
        <section
            class="w-full max-w-3xl overflow-hidden rounded-lg border border-border bg-card"
        >
            <div class="bg-primary p-6 text-primary-foreground md:p-8">
                <p
                    class="text-xs font-medium tracking-[0.5px] text-primary-foreground/70 uppercase"
                >
                    Group invite
                </p>
                <h1
                    class="mt-3 text-4xl leading-tight font-bold tracking-[-0.03em] md:text-5xl"
                >
                    Join {{ group.name }}
                </h1>
                <p class="mt-3 max-w-2xl text-primary-foreground/75">
                    {{
                        group.description ??
                        'You have been invited to join this shared expense group.'
                    }}
                </p>
            </div>

            <div class="grid gap-4 p-5 md:p-6">
                <div
                    class="grid gap-3 text-sm text-muted-foreground md:grid-cols-2"
                >
                    <div class="vh-muted-card">
                        <p class="font-semibold text-foreground">Status</p>
                        <p class="mt-1 capitalize">{{ group.status }}</p>
                    </div>
                    <div class="vh-muted-card">
                        <p class="font-semibold text-foreground">Members</p>
                        <p class="mt-1">{{ group.members_count }} joined</p>
                    </div>
                </div>

                <div v-if="isMember" class="vh-muted-card">
                    <h2 class="text-xl font-semibold text-foreground">
                        You are already a member
                    </h2>
                    <p class="mt-2 text-muted-foreground">
                        Open the group to view members, expenses, and payments.
                    </p>
                    <Link :href="groupUrl" class="vh-primary-action mt-4">
                        Open group
                    </Link>
                </div>

                <div v-else class="vh-muted-card">
                    <h2 class="text-xl font-semibold text-foreground">
                        Confirm before joining
                    </h2>
                    <p class="mt-2 text-muted-foreground">
                        Joining lets other members see your name and include you
                        in shared expenses.
                    </p>
                    <Link
                        :href="joinUrl"
                        as="button"
                        class="vh-sage-action mt-4"
                        method="post"
                    >
                        Join group
                    </Link>
                </div>
            </div>
        </section>
    </main>
</template>
