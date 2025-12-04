<template>
  <button
    :type="type"
    :class="[
      'font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none',
      colorClasses,
    ]"
  >
    <slot name="icon"></slot>
    {{ label }}
  </button>
</template>

<script>
export default {
  name: "Button",
  props: {
    label: String,
    type: {
      type: String,
      default: "button",
    },
    color: {
      type: String,
      default: "",
    },
  },
  computed: {
    colorClasses() {
      switch (this.color) {
        case "danger":
          return "inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 active:ring-4 active:ring-red-300 focus:outline-none transition-all duration-150 dark:bg-red-600 dark:hover:bg-red-700 dark:active:ring-red-900";
        case "success":
          return "inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 active:ring-4 active:ring-green-300 focus:outline-none transition-all duration-150 dark:bg-green-600 dark:hover:bg-green-700 dark:active:ring-green-900";
        case "gray":
          return "inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 active:ring-4 active:ring-gray-200 focus:outline-none transition-all duration-150 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:active:ring-gray-700";
        default:
          return "inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 active:ring-4 active:ring-blue-300 focus:outline-none transition-all duration-150 dark:bg-blue-600 dark:hover:bg-blue-700 dark:active:ring-blue-900";
      }
    },
  },
};
</script>
