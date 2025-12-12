<template>
  <ModalForm
    :show="show"
    title="Lý do từ chối"
    @close="$emit('close')"
    @submit="$emit('submit')"
  >
    <div class="space-y-3">
      <textarea
        :value="reason"
        @input="$emit('update:reason', $event.target.value)"
        rows="4"
        class="w-full px-3 py-2 rounded-lg border border-gray-200"
        placeholder="Nhập lý do..."
      ></textarea>
      <p v-if="error" class="text-xs text-red-500">
        {{ error }}
      </p>
    </div>
    <template #footer>
      <button
        type="button"
        class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600"
        @click="$emit('close')"
      >
        Hủy
      </button>
      <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white">
        Từ chối
      </button>
    </template>
  </ModalForm>
</template>

<script setup>
import ModalForm from "../../ModalForm.vue";

const props = defineProps({
  show: Boolean,
  reason: String,
  error: String,
});

defineEmits(["close", "submit", "update:reason"]);
</script>
