<template>
  <ModalForm
    :show="show"
    title="Thêm đơn vị thiết bị"
    @close="handleClose"
    @submit="handleSubmit"
  >
    <DeviceUnitFormFields
      :form="form"
      :errors="errors"
      :devices="devices"
      :status-options="statusOptions"
    />

    <template #footer>
      <button
        type="button"
        class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50"
        @click="handleClose"
      >
        Hủy
      </button>
      <button
        type="submit"
        class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700"
      >
        Thêm mới
      </button>
    </template>
  </ModalForm>
</template>

<script setup>
import ModalForm from "../../ModalForm.vue";
import DeviceUnitFormFields from "./DeviceUnitFormFields.vue";

const props = defineProps({
  show: Boolean,
  form: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  devices: { type: Array, default: () => [] },
  statusOptions: { type: Array, required: true },
});

const emit = defineEmits(["close", "submit"]);

const handleSubmit = () => {
  emit("submit");
};

const handleClose = () => {
  emit("close");
};
</script>
