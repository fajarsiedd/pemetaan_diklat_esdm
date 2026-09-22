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
  <div class="relative w-full overflow-x-auto">
    <table
      data-slot="table"
      :class="
        cn(
          'w-full caption-bottom text-sm',
          props.class,
        )
      "
    >
      <slot />
    </table>
  </div>
</template>