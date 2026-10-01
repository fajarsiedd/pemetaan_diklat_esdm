<script setup>
import { Activity } from "@lucide/vue";
import { Head, usePage } from "@inertiajs/vue3";

const page = usePage();
const appName = page.props.appName;
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-muted p-6">
    <Head>
      <title>{{ appName }}</title>
    </Head>

    <div class="mb-6 flex items-center gap-2 text-2xl font-bold tracking-tight text-esdm-charcoal">
      <img src="/images/logo-esdm.png" class="w-8 h-8" alt="" srcset="">
      {{ appName }}
    </div>

    <slot />
  </div>
</template>