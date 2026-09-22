<script setup>
import { reactiveOmit, useVModel } from "@vueuse/core";
import { CheckboxIndicator, CheckboxRoot } from "reka-ui";
import { CheckIcon } from "@lucide/vue";
import { cn } from "@/lib/utils";

const props = defineProps({
  class: { type: null, required: false },
  modelValue: { type: [Boolean, String], required: false },
  defaultValue: { type: [Boolean, String], required: false },
});

const emits = defineEmits(["update:modelValue"]);

const delegatedProps = reactiveOmit(props, "class", "modelValue");

const modelValue = useVModel(props, "modelValue", emits, {
  passive: true,
  defaultValue: props.defaultValue ?? false,
});
</script>

<template>
  <CheckboxRoot
    v-model="modelValue"
    :class="
      cn(
        'peer size-4 shrink-0 rounded-[4px] border border-input shadow-xs outline-none transition-[color,box-shadow] disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground focus-visible:ring-ring/50 focus-visible:ring-3',
        props.class,
      )
    "
    v-bind="delegatedProps"
  >
    <CheckboxIndicator class="flex items-center justify-center text-current">
      <CheckIcon class="size-3.5" />
    </CheckboxIndicator>
  </CheckboxRoot>
</template>