<template>
  <button
    :type="type"
    :class="[
      'font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none',
      colorClasses,
    ]"
    @click="$emit('click')"
  >
    <slot>{{ label }}</slot>
  </button>
</template>

<script setup>
import { computed } from "vue";

// 🟢 Khai báo props
const props = defineProps({
  label: {
    type: String,
    default: "",
  },
  type: {
    type: String,
    default: "button",
  },
  color: {
    type: String,
    default: "primary",
  },
});

// 🟢 Khai báo emit
const emit = defineEmits(["click"]);

// 🟢 Tạo computed reactive tương tự
const colorClasses = computed(() => {
  switch (props.color) {
    case "primary":
      return "text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800";
    case "danger":
      return "text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800";
    case "success":
      return "text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-800";
    case "gray":
      return "text-gray-900 bg-gray-100 hover:bg-gray-200 focus:ring-4 focus:ring-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-800";
    default:
      return "";
  }
});
</script>
