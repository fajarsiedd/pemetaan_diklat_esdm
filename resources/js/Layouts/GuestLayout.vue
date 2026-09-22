<script setup>
import { Activity } from "@lucide/vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const appName = page.props.appName;
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-muted p-6">
    <div class="mb-6 flex items-center gap-2 text-2xl font-bold tracking-tight">
      <span class="flex size-9 items-center justify-center rounded-lg bg-primary text-primary-foreground">
        <Activity class="size-5" />
      </span>
      {{ appName }}
    </div>

    <slot />
  </div>
</template>