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
        class="flex min-h-[calc(100vh-4rem)] items-center justify-center p-4 md:p-6"
    >
        <section
            class="w-full max-w-3xl overflow-hidden rounded-[2rem] border border-black/10 bg-white"
        >
            <div class="bg-black p-6 text-white md:p-8">
                <p
                    class="text-sm font-semibold tracking-[0.24px] text-white/60 uppercase"
                >
                    Group invite
                </p>
                <h1
                    class="mt-3 text-4xl font-semibold tracking-[-0.04em] md:text-5xl"
                >
                    Join {{ group.name }}
                </h1>
                <p class="mt-3 max-w-2xl text-white/70">
                    {{
                        group.description ??
                        'You have been invited to join this shared expense group.'
                    }}
                </p>
            </div>

            <div class="grid gap-4 p-5 md:p-6">
                <div class="grid gap-3 text-sm text-black/60 md:grid-cols-2">
                    <div class="rounded-2xl bg-[#f4f4f4] p-4">
                        <p class="font-semibold text-black">Status</p>
                        <p class="mt-1 capitalize">{{ group.status }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f4f4f4] p-4">
                        <p class="font-semibold text-black">Members</p>
                        <p class="mt-1">{{ group.members_count }} joined</p>
                    </div>
                </div>

                <div v-if="isMember" class="rounded-2xl bg-[#f4f4f4] p-4">
                    <h2 class="text-xl font-semibold text-black">
                        You are already a member
                    </h2>
                    <p class="mt-2 text-black/60">
                        Open the group to view members, expenses, and payments.
                    </p>
                    <Link
                        :href="groupUrl"
                        class="mt-4 inline-flex h-11 items-center rounded-full bg-black px-6 font-semibold text-white"
                    >
                        Open group
                    </Link>
                </div>

                <div v-else class="rounded-2xl bg-[#f4f4f4] p-4">
                    <h2 class="text-xl font-semibold text-black">
                        Confirm before joining
                    </h2>
                    <p class="mt-2 text-black/60">
                        Joining lets other members see your name and include you
                        in shared expenses.
                    </p>
                    <Link
                        :href="joinUrl"
                        as="button"
                        class="mt-4 inline-flex h-11 items-center rounded-full bg-black px-6 font-semibold text-white"
                        method="post"
                    >
                        Join group
                    </Link>
                </div>
            </div>
        </section>
    </main>
</template>
