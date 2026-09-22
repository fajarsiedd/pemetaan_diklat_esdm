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
  <td data-slot="table-cell" :class="cn('p-2 align-middle whitespace-nowrap', props.class)">
    <slot />
  </td>
</template>