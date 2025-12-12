<template>
  <ModalForm
    :show="show"
    title="Lý do từ chối"
    @close="$emit('close')"
    @submit="handleSubmit"
  >
    <div class="space-y-3">
      <textarea
        v-model="reason"
        rows="4"
        class="w-full px-3 py-2 rounded-lg border border-gray-200"
        placeholder="Nhập lý do..."
      ></textarea>
      <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
    </div>
    <template #footer>
      <button
        type="button"
        class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600"
        @click="$emit('close')"
      >
        Hủy
      </button>
      <button
        type="submit"
        class="px-4 py-2 rounded-lg bg-red-600 text-white"
        :disabled="loading"
      >
        {{ loading ? "Đang xử lý..." : "Từ chối" }}
      </button>
    </template>
  </ModalForm>
</template>

<script setup>
import { ref, watch } from "vue";
import ModalForm from "../../../components/ModalForm.vue";

const props = defineProps({
  show: Boolean,
  loading: Boolean,
  error: String,
});

const emit = defineEmits(["close", "submit"]);

const reason = ref("");

watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      reason.value = "";
    }
  }
);

const handleSubmit = () => {
  emit("submit", reason.value);
};
</script>
