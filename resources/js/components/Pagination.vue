<script setup>
import { Link } from "@inertiajs/vue3";
import { ChevronLeft, ChevronRight } from "@lucide/vue";

const props = defineProps({
  meta: { type: Object, required: true },
});

const prevNextBase =
  "inline-flex h-9 min-w-9 items-center justify-center gap-1 rounded-md px-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-ring/50";
const pageBase =
  "inline-flex h-9 min-w-9 items-center justify-center rounded-md px-2 text-sm font-medium transition-colors hover:bg-accent hover:text-foreground focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-ring/50";
const dotsClass =
  "inline-flex h-9 min-w-9 items-center justify-center rounded-md px-2 text-sm text-muted-foreground";

const itemClass = (link, index, lastIndex) => {
  let base = index === 0 || index === lastIndex ? prevNextBase : pageBase;

  if (!link.url) base += " pointer-events-none opacity-50";
  if (link.active) {
    base += " bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground";
  }

  return base;
};
</script>

<template>
  <div
    v-if="meta.total > 0"
    class="flex flex-col items-center justify-between gap-3 border-t px-4 py-4 sm:flex-row sm:px-6"
  >
    <p class="text-sm text-muted-foreground">
      Menampilkan
      <span class="font-medium text-foreground">{{ meta.from }}</span>–
      <span class="font-medium text-foreground">{{ meta.to }}</span> dari
      <span class="font-medium text-foreground">{{ meta.total }}</span> data
    </p>

    <nav
      v-if="meta.last_page > 1"
      class="flex flex-wrap items-center justify-center gap-1"
      aria-label="Pagination"
    >
      <template v-for="(link, index) in meta.links" :key="index">
        <Link
          v-if="link.url"
          :href="link.url"
          :class="itemClass(link, index, meta.links.length - 1)"
          :aria-current="link.active ? 'page' : undefined"
        >
          <ChevronLeft v-if="index === 0" class="size-4" />
          <ChevronRight v-else-if="index === meta.links.length - 1" class="size-4" />
          <span v-else>{{ link.label }}</span>
        </Link>
        <span v-else :class="itemClass(link, index, meta.links.length - 1)">
          <ChevronLeft v-if="index === 0" class="size-4" />
          <ChevronRight v-else-if="index === meta.links.length - 1" class="size-4" />
          <span v-else>{{ link.label }}</span>
        </span>
      </template>
    </nav>
  </div>
</template>