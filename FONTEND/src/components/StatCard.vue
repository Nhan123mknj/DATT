<template>
  <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
          {{ label }}
        </p>
        <p class="text-3xl font-semibold text-gray-900 mt-2">
          {{ formattedValue }}
        </p>
      </div>
      <div
        class="w-12 h-12 rounded-full flex items-center justify-center bg-indigo-50 text-indigo-600"
      >
        <slot name="icon">
          <font-awesome-icon icon="layer-group" />
        </slot>
      </div>
    </div>
    <p v-if="description" class="text-sm text-gray-400 mt-3">
      {{ description }}
    </p>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    default: "--",
  },
  description: {
    type: String,
    default: "",
  },
});

const formattedValue = computed(() => props.value ?? "--");
</script>
