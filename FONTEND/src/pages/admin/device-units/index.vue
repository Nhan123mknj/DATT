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
      <Button @click="openCreate" label="Thêm đơn vị">
        <template #icon>
          <font-awesome-icon icon="plus" class="mr-2" />
        </template>
      </Button>
    </div>

    <div
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4"
    >
      <div class="grid gap-3 md:grid-cols-4">
        <SearchBar v-on:handleSearch="handleSearch" />
        <Dropdown
          v-model="filters.device_id"
          :options="devices"
          label="thiết bị"
        />
        <Dropdown
          v-model="filters.status"
          :options="statusOptions"
          label="trạng thái"
          nameKey="label"
          idKey="value"
        />
        <div class="flex gap-2">
          <Button @click="resetFilters" label="Đặt lại" color="gray" />
          <Button @click="loadData()" label="Lọc" />
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
                @click="openEdit(item)"
              >
                Sửa
              </button>
              <button
                class="px-3 py-1 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-sm"
                @click="deleteItem(item.id)"
              >
                Xóa
              </button>
            </div>
          </template>
        </Table>
        <Pagination
          v-if="pagination.total && pagination.last_page > 1"
          :links="pagination.links"
          @page-changed="loadData"
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

<script>
import { reactive, ref, computed, onMounted } from "vue";
import Table from "../../../components/common/Table.vue";
import Button from "../../../components/common/Button.vue";
import LoadingSkeleton from "../../../components/common/LoadingSkeleton.vue";
import Pagination from "../../../components/common/Pagination.vue";
import Modal from "../../../components/Modal.vue";
import SearchBar from "../../../components/common/SearchBar.vue";
import Dropdown from "../../../components/common/Dropdown.vue";
import { deviceUnitsService } from "../../../services/devices/deviceUnitsService";
import { devicesService } from "../../../services/devices/devicesService";
import { useDeviceUnits } from "../../../composables/fetchData/admin/useDeviceUnits";
import { useForm } from "../../../composables/useForm";
export default {
  name: "DeviceUnits",
  components: {
    Table,
    Button,
    LoadingSkeleton,
    Pagination,
    Modal,
    SearchBar,
    Dropdown,
  },
  setup() {
    const filters = reactive({
      search: "",
      device_id: "",
      status: "",
    });

    const devices = ref([]);

    const {
      deviceUnits,
      isLoading,
      pagination,
      loadDeviceUnits,
      deleteDeviceUnit,
    } = useDeviceUnits();

    const handleLoadUnits = (page = 1) => {
      loadDeviceUnits(page, filters);
    };

    const {
      form,
      errors,
      showModal,
      modalMode,
      openCreate,
      openEdit,
      closeModal,
      save,
    } = useForm({
      createData: (data) => deviceUnitsService.create(data),
      updateData: (id, data) => deviceUnitsService.update(id, data),
      initialForm: {
        id: null,
        device_id: "",
        serial_number: "",
        status: "available",
        purchase_date: "",
        warranty_end: "",
        notes: "",
      },
    });

    const headers = {
      serial_number: "Serial",
      device: "Thiết bị",
      status: "Trạng thái",
      purchase_date: "Ngày mua",
      warranty_end: "Hạn bảo hành",
    };

    const statusOptions = [
      { value: "available", label: "Sẵn sàng" },
      { value: "reserved", label: "Đặt trước" },
      { value: "under_maintenance", label: "Bảo trì" },
      { value: "retired", label: "Ngưng sử dụng" },
    ];

    const modalTitle = computed(() =>
      modalMode.value === "create"
        ? "Thêm đơn vị thiết bị"
        : "Cập nhật đơn vị thiết bị"
    );

    const loadDevices = async () => {
      try {
        const { data } = await devicesService.list({ page: 1 });
        devices.value = data.devices?.data || [];
      } catch {
        devices.value = [];
      }
    };

    const handleSearch = (data) => {
      filters.search = data;
      handleLoadUnits();
    };

    const resetFilters = () => {
      filters.search = "";
      filters.device_id = "";
      filters.status = "";
      loadDeviceUnits();
    };

    const saveUnit = () => {
      save(() => handleLoadUnits(pagination.current_page));
    };

    const statusLabel = (status) => {
      return (
        statusOptions.find((item) => item.value === status)?.label || status
      );
    };

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

    onMounted(() => {
      loadDevices();
      handleLoadUnits();
    });

    return {
      filters,
      units: deviceUnits,
      isLoading,
      pagination,
      loadData: handleLoadUnits,
      deleteItem: async (id) => {
        const success = await deleteDeviceUnit(id);
        if (success) {
          handleLoadUnits(pagination.current_page);
        }
      },
      form,
      errors,
      showModal,
      modalMode,
      openCreate,
      openEdit,
      closeModal,
      save,
      headers,
      modalTitle,
      handleSearch,
      resetFilters,
      saveUnit,
      statusLabel,
      statusClass,
      devices,
      statusOptions,
    };
  },
};
</script>
