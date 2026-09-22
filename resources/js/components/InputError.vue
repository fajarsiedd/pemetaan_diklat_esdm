<script setup>
defineProps({
  message: { type: String, required: false },
});

const emits = defineEmits(["close"]);
</script>

<template>
  <p v-if="message" class="text-sm font-medium text-destructive">
    {{ message }}
  </p>
</template>