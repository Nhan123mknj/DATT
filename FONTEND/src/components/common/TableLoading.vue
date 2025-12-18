<template>
  <div class="text-center py-8">
    <div class="inline-flex items-center gap-2 text-indigo-600">
      <font-awesome-icon icon="spinner" class="animate-spin" />
      <span>{{ message }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  message: {
    type: String,
    default: "Đang tải...",
  },
});
</script>
