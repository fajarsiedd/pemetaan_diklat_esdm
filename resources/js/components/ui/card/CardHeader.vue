<script setup>
import { cn } from "@/lib/utils";

const props = defineProps({
  class: {
    type: [Boolean, null, String, Object, Array],
    required: false,
    skipCheck: true,
  },
});
</script>

<template>
  <div
    data-slot="card-header"
    :class="cn('grid auto-rows-min items-start gap-1.5 px-6', props.class)"
  >
    <slot />
  </div>
</template>