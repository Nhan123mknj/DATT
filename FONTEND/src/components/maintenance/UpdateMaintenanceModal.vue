<template>
  <ModalForm
    :show="visible"
    title="Cập nhật bảo trì"
    @close="handleCancel"
    @submit="handleSubmit"
  >
    <div class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Trạng thái</label
          >
          <select
            v-model="form.status"
            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="pending">Chờ xử lý</option>
            <option value="in_progress">Đang xử lý</option>
            <option value="completed">Hoàn thành</option>
            <option value="cancelled">Hủy bỏ</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Chi phí (VNĐ)</label
          >
          <input
            type="number"
            v-model="form.cost"
            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Ngày bắt đầu</label
          >
          <input
            type="datetime-local"
            v-model="form.start_date"
            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Ngày kết thúc</label
          >
          <input
            type="datetime-local"
            v-model="form.end_date"
            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"
          >Ghi chú kỹ thuật</label
        >
        <textarea
          v-model="form.notes"
          rows="4"
          placeholder="Ghi chú về quá trình sửa chữa, linh kiện thay thế..."
          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        ></textarea>
      </div>
    </div>

    <template #footer>
      <button
        type="button"
        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        @click="handleCancel"
      >
        Hủy bỏ
      </button>
      <button
        type="submit"
        :disabled="loading"
        class="inline-flex justify-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg
          v-if="loading"
          class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
        {{ loading ? "Đang cập nhật..." : "Cập nhật" }}
      </button>
    </template>
  </ModalForm>
</template>

<script setup>
import { ref, reactive, watch } from "vue";
import { useToast } from "vue-toastification";
import ModalForm from "../ModalForm.vue";
import maintenanceService from "../../services/maintenanceService";

const props = defineProps({
  visible: Boolean,
  maintenance: Object,
});

const emit = defineEmits(["update:visible", "success"]);
const toast = useToast();

const visible = ref(props.visible);
const loading = ref(false);

const form = reactive({
  status: "pending",
  cost: 0,
  notes: "",
  start_date: null,
  end_date: null,
});

watch(
  () => props.visible,
  (val) => {
    visible.value = val;
    if (val && props.maintenance) {
      form.status = props.maintenance.status;
      form.cost = props.maintenance.cost;
      form.notes = props.maintenance.notes;
      form.start_date = props.maintenance.start_date;
      form.end_date = props.maintenance.end_date;
    }
  }
);

watch(visible, (val) => {
  emit("update:visible", val);
});

const handleCancel = () => {
  visible.value = false;
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    await maintenanceService.update(props.maintenance.id, form);

    toast.success("Cập nhật thành công");
    visible.value = false;
    emit("success");
  } catch (error) {
    console.error(error);
    toast.error("Cập nhật thất bại");
  } finally {
    loading.value = false;
  }
};
</script>
