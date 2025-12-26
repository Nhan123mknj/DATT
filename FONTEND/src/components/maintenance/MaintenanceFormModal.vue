<template>
  <ModalForm
    :show="show"
    :title="mode === 'create' ? 'Tạo phiếu bảo trì' : 'Cập nhật phiếu bảo trì'"
    @close="$emit('close')"
    @submit="handleSubmit"
    size="large"
  >
    <div class="space-y-4">
      <!-- Device Unit -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Thiết bị <span class="text-red-500">*</span>
        </label>
        <select
          v-model="form.device_unit_id"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
          :disabled="mode === 'edit'"
        >
          <option value="">-- Chọn thiết bị --</option>
          <option v-for="unit in deviceUnits" :key="unit?.id" :value="unit?.id">
            {{ unit?.device?.name }} - SN: {{ unit?.serial_number }}
          </option>
        </select>
        <p v-if="errors.device_unit_id" class="text-xs text-red-500 mt-1">
          {{ errors.device_unit_id[0] }}
        </p>
      </div>

      <!-- Type & Priority -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Loại <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.type"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
          >
            <option value="routine">Bảo trì định kỳ</option>
            <option value="repair">Sửa chữa</option>
            <option value="inspection">Kiểm tra</option>
            <option value="damage_report">Báo hỏng</option>
          </select>
          <p v-if="errors.type" class="text-xs text-red-500 mt-1">
            {{ errors.type[0] }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Mức độ <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.priority"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
          >
            <option value="low">Thấp</option>
            <option value="normal">Bình thường</option>
            <option value="high">Cao</option>
            <option value="urgent">Khẩn cấp</option>
          </select>
          <p v-if="errors.priority" class="text-xs text-red-500 mt-1">
            {{ errors.priority[0] }}
          </p>
        </div>
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Mô tả <span class="text-red-500">*</span>
        </label>
        <textarea
          v-model="form.description"
          rows="3"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
          placeholder="Mô tả chi tiết vấn đề..."
        ></textarea>
        <p v-if="errors.description" class="text-xs text-red-500 mt-1">
          {{ errors.description[0] }}
        </p>
      </div>

      <!-- Status & Assigned To (Edit mode only) -->
      <div v-if="mode === 'edit'" class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Trạng thái
          </label>
          <select
            v-model="form.status"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
          >
            <option value="pending">Chờ xử lý</option>
            <option value="in_progress">Đang xử lý</option>
            <option value="completed">Hoàn thành</option>
            <option value="cancelled">Đã hủy</option>
          </select>
        </div>
      </div>

      <!-- Dates -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Ngày bắt đầu
          </label>
          <input
            v-model="form.start_date"
            type="datetime-local"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Ngày kết thúc
          </label>
          <input
            v-model="form.end_date"
            type="datetime-local"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
          />
        </div>
      </div>

      <!-- Cost & Next Maintenance Date -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Chi phí (VNĐ)
          </label>
          <input
            v-model.number="form.cost"
            type="number"
            min="0"
            step="1000"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
            placeholder="0"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Bảo trì tiếp theo
          </label>
          <input
            v-model="form.next_maintenance_date"
            type="date"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
          />
        </div>
      </div>

      <!-- Notes -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Ghi chú
        </label>
        <textarea
          v-model="form.notes"
          rows="2"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500"
          placeholder="Ghi chú thêm..."
        ></textarea>
      </div>
    </div>

    <template #footer>
      <div class="flex gap-3">
        <button
          type="button"
          @click="$emit('close')"
          class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium"
        >
          Hủy
        </button>
        <button
          type="submit"
          :disabled="saving"
          class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium disabled:opacity-50"
        >
          {{
            saving ? "Đang lưu..." : mode === "create" ? "Tạo mới" : "Cập nhật"
          }}
        </button>
      </div>
    </template>
  </ModalForm>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from "vue";
import { useToast } from "vue-toastification";
import maintenanceService from "../../services/maintenanceService";
import { deviceUnitService } from "../../services/admin/deviceUnitService";
import { usersService } from "../../services/admin/usersService";
import ModalForm from "../ModalForm.vue";

const props = defineProps({
  show: Boolean,
  mode: {
    type: String,
    default: "create",
  },
  maintenance: Object,
});

const emit = defineEmits(["close", "refresh"]);
const toast = useToast();

const saving = ref(false);
const errors = ref({});
const deviceUnits = ref([]);
const staffUsers = ref([]);

const form = reactive({
  device_unit_id: "",
  type: "routine",
  priority: "normal",
  description: "",
  status: "pending",
  start_date: "",
  end_date: "",
  cost: 0,
  next_maintenance_date: "",
  notes: "",
});

const loadDeviceUnits = async () => {
  try {
    const { data } = await deviceUnitService.list({ status: "available" });

    const units = data?.data?.data || [];
    deviceUnits.value = units.filter((unit) => unit && unit.id);
  } catch (error) {
    console.error("Failed to load device units:", error);
    deviceUnits.value = [];
  }
};

const loadStaffUsers = async () => {
  try {
    const { data } = await usersService.getAllUser({ role: "staff" });
    staffUsers.value = data.data || [];
  } catch (error) {
    console.error("Failed to load staff users:", error);
  }
};

const handleSubmit = async () => {
  saving.value = true;
  errors.value = {};

  try {
    if (props.mode === "create") {
      await maintenanceService.create(form);
      toast.success("Tạo phiếu bảo trì thành công");
    } else {
      await maintenanceService.update(props.maintenance.id, form);
      toast.success("Cập nhật phiếu bảo trì thành công");
    }
    emit("refresh");
    emit("close");
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      toast.error(
        error.response?.data?.message || "Không thể lưu phiếu bảo trì"
      );
    }
  } finally {
    saving.value = false;
  }
};

watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      errors.value = {};
      if (props.mode === "create") {
        Object.assign(form, {
          device_unit_id: "",
          type: "routine",
          priority: "normal",
          description: "",
          status: "pending",
          start_date: "",
          end_date: "",
          cost: 0,
          next_maintenance_date: "",
          notes: "",
        });
      } else if (props.maintenance) {
        Object.assign(form, {
          device_unit_id: props.maintenance.device_unit_id,
          type: props.maintenance.type,
          priority: props.maintenance.priority,
          description: props.maintenance.description,
          status: props.maintenance.status,
          start_date: props.maintenance.start_date
            ? props.maintenance.start_date.substring(0, 16)
            : "",
          end_date: props.maintenance.end_date
            ? props.maintenance.end_date.substring(0, 16)
            : "",
          cost: props.maintenance.cost || 0,
          next_maintenance_date: props.maintenance.next_maintenance_date || "",
          notes: props.maintenance.notes || "",
        });
      }
    }
  }
);

onMounted(() => {
  loadDeviceUnits();
  loadStaffUsers();
});
</script>
