<template>
  <ModalForm
    :show="visible"
    title="Báo cáo hỏng thiết bị"
    @close="handleCancel"
    @submit="handleSubmit"
  >
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"
          >Thiết bị</label
        >
        <input
          type="text"
          :value="deviceUnit?.device?.device_name"
          disabled
          class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"
          >Mã thiết bị (Serial)</label
        >
        <input
          type="text"
          :value="deviceUnit?.serial_number"
          disabled
          class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"
          >Mức độ ưu tiên <span class="text-red-500">*</span></label
        >
        <select
          v-model="form.priority"
          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        >
          <option value="low">Thấp</option>
          <option value="normal">Bình thường</option>
          <option value="high">Cao</option>
          <option value="urgent">Khẩn cấp</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"
          >Mô tả sự cố <span class="text-red-500">*</span></label
        >
        <textarea
          v-model="form.description"
          rows="4"
          placeholder="Mô tả chi tiết vấn đề gặp phải..."
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
        {{ loading ? "Đang gửi..." : "Gửi báo cáo" }}
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
  deviceUnit: Object,
});

const emit = defineEmits(["update:visible", "success"]);
const toast = useToast();

const visible = ref(props.visible);
const loading = ref(false);

const form = reactive({
  priority: "normal",
  description: "",
});

watch(
  () => props.visible,
  (val) => {
    visible.value = val;
    if (val) {
      form.priority = "normal";
      form.description = "";
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
  if (!form.description) {
    toast.error("Vui lòng nhập mô tả sự cố");
    return;
  }

  loading.value = true;
  try {
    await maintenanceService.create({
      device_unit_id: props.deviceUnit.id,
      type: "damage_report",
      priority: form.priority,
      description: form.description,
    });

    toast.success("Đã gửi báo cáo thành công");
    visible.value = false;
    emit("success");
  } catch (error) {
    console.error(error);
    toast.error("Gửi báo cáo thất bại");
  } finally {
    loading.value = false;
  }
};
</script>
