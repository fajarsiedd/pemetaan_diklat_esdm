<script setup>
import { cva } from "class-variance-authority";
import { cn } from "@/lib/utils";

const props = defineProps({
  variant: { type: null, required: false },
  class: {
    type: [Boolean, null, String, Object, Array],
    required: false,
    skipCheck: true,
  },
});

const badgeVariants = cva(
  "inline-flex w-fit items-center gap-1 rounded-md border px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap transition-colors [&_svg]:size-3 [&_svg]:shrink-0",
  {
    variants: {
      variant: {
        default: "border-transparent bg-primary text-primary-foreground",
        secondary: "border-transparent bg-secondary text-secondary-foreground",
        outline: "text-foreground",
        success: "border-transparent bg-emerald-500/15 text-emerald-700 dark:text-emerald-400",
        warning: "border-transparent bg-amber-500/15 text-amber-700 dark:text-amber-400",
        destructive: "border-transparent bg-destructive/15 text-destructive",
        muted: "border-transparent bg-muted text-muted-foreground",
      },
    },
    defaultVariants: { variant: "default" },
  },
);
</script>

<template>
  <div data-slot="badge" :class="cn(badgeVariants({ variant }), props.class)">
    <slot />
  </div>
</template>