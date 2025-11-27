<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Đơn vị thiết bị</h1>
        <p class="text-gray-500 text-sm">
          Quản lý từng thiết bị cụ thể, số serial và trạng thái sử dụng.
        </p>
      </div>
      <Button color="primary" @click="openCreateModal">
        <font-awesome-icon icon="plus" class="mr-2" />
        Thêm đơn vị
      </Button>
    </div>

    <div
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4"
    >
      <div class="grid gap-3 md:grid-cols-4">
        <input
          v-model="filters.search"
          type="text"
          class="px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
          placeholder="Tìm theo serial..."
        />
        <select
          v-model="filters.device_id"
          class="px-3 py-2 rounded-lg border border-gray-200 text-sm"
        >
          <option value="">Tất cả thiết bị</option>
          <option v-for="device in devices" :key="device.id" :value="device.id">
            {{ device.name }}
          </option>
        </select>
        <select
          v-model="filters.status"
          class="px-3 py-2 rounded-lg border border-gray-200 text-sm"
        >
          <option value="">Tất cả trạng thái</option>
          <option
            v-for="status in statusOptions"
            :key="status.value"
            :value="status.value"
          >
            {{ status.label }}
          </option>
        </select>
        <div class="flex gap-2">
          <button
            class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600"
            @click="resetFilters"
          >
            Đặt lại
          </button>
          <button
            class="px-4 py-2 rounded-lg bg-gray-900 text-white flex-1"
            @click="loadUnits()"
          >
            Lọc
          </button>
        </div>
      </div>

      <LoadingSkeleton v-if="isLoading" />
      <div v-else>
        <Table :data="units" :headers="headers">
          <template #device="{ item }">
            {{ item.device?.name || "Chưa gán" }}
          </template>
          <template #status="{ item }">
            <span
              class="px-3 py-1 rounded-full text-xs font-medium"
              :class="statusClass(item.status)"
            >
              {{ statusLabel(item.status) }}
            </span>
          </template>

          <template #actions="{ item }">
            <div class="flex gap-2">
              <button
                class="px-3 py-1 rounded-lg border border-gray-200 hover:bg-gray-50 text-sm"
                @click="openEditModal(item)"
              >
                Sửa
              </button>
              <button
                class="px-3 py-1 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-sm"
                @click="deleteUnit(item)"
              >
                Xóa
              </button>
            </div>
          </template>
        </Table>
        <Pagination
          v-if="pagination.total && pagination.last_page > 1"
          :links="pagination.links"
          @page-changed="loadUnits"
        />
      </div>
    </div>

    <Modal :show="showModal" :title="modalTitle" @close="closeModal">
      <form class="space-y-4" @submit.prevent="saveUnit">
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="text-sm font-medium text-gray-700">Thiết bị</label>
            <select
              v-model="form.device_id"
              class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
            >
              <option value="">-- Chọn thiết bị --</option>
              <option
                v-for="device in devices"
                :key="device.id"
                :value="device.id"
              >
                {{ device.name }}
              </option>
            </select>
            <p v-if="errors.device_id" class="text-xs text-red-500 mt-1">
              {{ errors.device_id?.[0] || errors.device_id }}
            </p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Serial</label>
            <input
              v-model="form.serial_number"
              type="text"
              class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
            />
            <p v-if="errors.serial_number" class="text-xs text-red-500 mt-1">
              {{ errors.serial_number?.[0] || errors.serial_number }}
            </p>
          </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="text-sm font-medium text-gray-700">Trạng thái</label>
            <select
              v-model="form.status"
              class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
            >
              <option
                v-for="status in statusOptions"
                :key="status.value"
                :value="status.value"
              >
                {{ status.label }}
              </option>
            </select>
            <p v-if="errors.status" class="text-xs text-red-500 mt-1">
              {{ errors.status?.[0] || errors.status }}
            </p>
          </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="text-sm font-medium text-gray-700">Ngày mua</label>
            <input
              v-model="form.purchase_date"
              type="date"
              class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
            />
            <p v-if="errors.purchase_date" class="text-xs text-red-500 mt-1">
              {{ errors.purchase_date?.[0] || errors.purchase_date }}
            </p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700"
              >Hạn bảo hành</label
            >
            <input
              v-model="form.warranty_end"
              type="date"
              class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
            />
          </div>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Ghi chú</label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
            placeholder="Tình trạng khi nhập kho..."
          ></textarea>
        </div>
        <button type="submit" class="hidden" aria-hidden="true"></button>
      </form>
      <template #footer>
        <div class="flex gap-3">
          <button
            type="button"
            class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600"
            @click="closeModal"
          >
            Hủy
          </button>
          <button
            type="button"
            class="px-4 py-2 rounded-lg bg-indigo-600 text-white"
            @click="saveUnit"
          >
            {{ modalMode === "create" ? "Thêm mới" : "Cập nhật" }}
          </button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useToast } from "vue-toastification";
import Table from "../../../components/Table.vue";
import Button from "../../../components/Button.vue";
import LoadingSkeleton from "../../../components/LoadingSkeleton.vue";
import Pagination from "../../../components/Pagination.vue";
import Modal from "../../../components/Modal.vue";
import { deviceUnitsService } from "../../../services/devices/deviceUnitsService";
import { devicesService } from "../../../services/devices/devicesService";

const toast = useToast();

const isLoading = ref(false);
const units = ref([]);
const devices = ref([]);
const pagination = reactive({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1,
  links: [],
});

const headers = {
  serial_number: "Serial",
  device: "Thiết bị",
  status: "Trạng thái",
  purchase_date: "Ngày mua",
  warranty_end: "Hạn bảo hành",
  // is_active: "Kích hoạt",
};

const statusOptions = [
  { value: "available", label: "Sẵn sàng" },
  { value: "reserved", label: "Đặt trước" },
  // { value: "in_use", label: "Đang sử dụng" },
  { value: "under_maintenance", label: "Bảo trì" },
  { value: "retired", label: "Ngưng sử dụng" },
];

const filters = reactive({
  search: "",
  device_id: "",
  status: "",
});

const showModal = ref(false);
const modalMode = ref("create");
const form = reactive({
  id: null,
  device_id: "",
  serial_number: "",
  status: "available",
  purchase_date: "",
  warranty_end: "",
  notes: "",
  // is_active: 1,
});
const errors = reactive({});

const modalTitle = computed(() =>
  modalMode.value === "create"
    ? "Thêm đơn vị thiết bị"
    : "Cập nhật đơn vị thiết bị"
);

const statusLabel = (status) =>
  statusOptions.find((item) => item.value === status)?.label || status;
const statusClass = (status) => {
  switch (status) {
    case "available":
      return "bg-green-100 text-green-700";
    case "in_use":
      return "bg-blue-100 text-blue-700";
    case "under_maintenance":
      return "bg-amber-100 text-amber-700";
    case "retired":
      return "bg-gray-100 text-gray-600";
    default:
      return "bg-gray-100 text-gray-600";
  }
};

const loadDevices = async () => {
  try {
    const { data } = await devicesService.list({ page: 1 });
    devices.value = data.devices?.data || [];
  } catch {
    devices.value = [];
  }
};

const loadUnits = async (page = 1) => {
  isLoading.value = true;
  try {
    const params = {
      page,
      search: filters.search || undefined,
      device_id: filters.device_id || undefined,
      status: filters.status || undefined,
    };
    const { data } = await deviceUnitsService.list(params);
    const payload = data;
    units.value = payload?.data || [];
    pagination.current_page = payload?.current_page || 1;
    pagination.per_page = payload?.per_page || 10;
    pagination.total = payload?.total || 0;
    pagination.last_page = payload?.last_page || 1;
    pagination.links = payload?.links || [];
  } catch (error) {
    if (error.response?.status === 404) {
      units.value = [];
      pagination.total = 0;
    } else {
      toast.error("Không thể tải đơn vị thiết bị");
    }
  } finally {
    isLoading.value = false;
  }
};

const openCreateModal = () => {
  modalMode.value = "create";
  resetForm();
  showModal.value = true;
};

const openEditModal = (unit) => {
  modalMode.value = "edit";
  form.id = unit.id;
  form.device_id = unit.device_id;
  form.serial_number = unit.serial_number;
  form.status = unit.status;
  form.purchase_date = unit.purchase_date;
  form.warranty_end = unit.warranty_end;
  form.notes = unit.notes;
  // form.is_active = unit.is_active ? 1 : 0;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const resetForm = () => {
  form.id = null;
  form.device_id = "";
  form.serial_number = "";
  form.status = "available";
  form.purchase_date = "";
  form.warranty_end = "";
  form.notes = "";
  // form.is_active = 1;
  Object.keys(errors).forEach((key) => delete errors[key]);
};

const saveUnit = async () => {
  Object.keys(errors).forEach((key) => delete errors[key]);
  const payload = {
    device_id: form.device_id || null,
    serial_number: form.serial_number,
    status: form.status,
    purchase_date: form.purchase_date || null,
    warranty_end: form.warranty_end || null,
    notes: form.notes,
    // is_active: form.is_active ? 1 : 0,
  };
  try {
    if (modalMode.value === "create") {
      await deviceUnitsService.create(payload);
      toast.success("Đã tạo đơn vị thiết bị");
    } else {
      await deviceUnitsService.update(form.id, payload);
      toast.success("Đã cập nhật đơn vị thiết bị");
    }
    closeModal();
    loadUnits(pagination.current_page);
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(errors, error.response.data.errors);
    } else {
      toast.error("Không thể lưu đơn vị thiết bị");
    }
  }
};

const deleteUnit = async (unit) => {
  if (!confirm(`Bạn chắc chắn muốn xóa đơn vị ${unit.serial_number}?`)) return;
  try {
    await deviceUnitsService.remove(unit.id);
    toast.success("Đã xóa đơn vị thiết bị");
    loadUnits(pagination.current_page);
  } catch (error) {
    toast.error(error.response?.data?.error || "Không thể xóa đơn vị thiết bị");
  }
};

const resetFilters = () => {
  filters.search = "";
  filters.device_id = "";
  filters.status = "";
  loadUnits();
};

onMounted(() => {
  loadDevices();
  loadUnits();
});
</script>
