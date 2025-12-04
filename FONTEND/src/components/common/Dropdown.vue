<template>
  <select
    v-bind:value="modelValue"
    v-on:change="handleChange"
    class="px-3 py-2 rounded-lg border border-gray-200 text-sm"
  >
    <option value="">Chọn {{ label }}</option>
    <option v-for="item in options" :key="item[idKey]" :value="item[idKey]">
      {{ item[nameKey] }}
    </option>
  </select>
</template>
<script>
export default {
  name: "Dropdown",
  props: {
    modelValue: {
      type: String,
      default: "",
    },
    options: {
      type: Array,
      default: () => [],
    },
    label: {
      type: String,
      default: "",
    },
    nameKey: {
      type: String,
      default: "name",
    },
    idKey: {
      type: String,
      default: "id",
    },
  },
  methods: {
    handleChange(event) {
      this.$emit("update:modelValue", event.target.value);
    },
  },
};
</script>
