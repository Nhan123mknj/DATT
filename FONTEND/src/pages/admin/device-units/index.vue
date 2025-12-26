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
      <div class="flex gap-2">
        <Button label=" Xuất Excel" color="success" @click="exportUnits">
          <template #icon>
            <font-awesome-icon icon="file-excel" class="mr-2" />
          </template>
        </Button>
        <Button
          label="Xuất danh sách người mượn"
          color="success"
          @click="exportBorrowers"
        >
          <template #icon>
            <font-awesome-icon icon="file-excel" class="mr-2" />
          </template>
        </Button>

        <Button label="Nhập Excel" color="primary" @click="triggerFileInput">
          <template #icon>
            <font-awesome-icon icon="file-upload" class="mr-2" />
          </template>
        </Button>

        <input
          ref="fileInput"
          type="file"
          accept=".xlsx,.xls,.csv"
          class="hidden"
          @change="handleFileImport"
        />

        <Button @click="openCreate" label="Thêm đơn vị">
          <template #icon>
            <font-awesome-icon icon="plus" class="mr-2" />
          </template>
        </Button>
      </div>
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

      <TableLoading v-if="isLoading" />
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
          <template #purchase_date="{ item }">
            {{ formatDates(item.purchase_date) }}
          </template>
          <template #warranty_end="{ item }">
            {{ formatDates(item.warranty_end) }}
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
                class="px-3 py-1 rounded-lg border border-orange-200 text-orange-600 hover:bg-orange-50 text-sm"
                @click="openRetireModal(item)"
              >
                Thanh lý
              </button>
            </div>
          </template>
        </Table>
        <Pagination :links="pagination.links" @page-changed="loadData" />
      </div>
    </div>

    <CreateDeviceUnitForm
      :show="showModal && modalMode === 'create'"
      :form="form"
      :errors="errors"
      :devices="devices"
      :status-options="statusOptions"
      @close="closeModal"
      @submit="saveUnit"
    />

    <UpdateDeviceUnitForm
      :show="showModal && modalMode === 'edit'"
      :form="form"
      :errors="errors"
      :devices="devices"
      :status-options="statusOptions"
      @close="closeModal"
      @submit="saveUnit"
    />

    <RetireModal
      :show="showRetireModal"
      :device-unit="selectedUnit"
      @close="showRetireModal = false"
      @retire="handleRetire"
    />
  </div>
</template>

<script>
import { reactive, ref, computed, onMounted } from "vue";
import Table from "../../../components/common/Table.vue";
import Button from "../../../components/common/Button.vue";
import TableLoading from "../../../components/common/TableLoading.vue";
import Pagination from "../../../components/common/Pagination.vue";
import SearchBar from "../../../components/common/SearchBar.vue";
import Dropdown from "../../../components/common/Dropdown.vue";
import CreateDeviceUnitForm from "../../../components/admin/device_unit/CreateDeviceUnitForm.vue";
import UpdateDeviceUnitForm from "../../../components/admin/device_unit/UpdateDeviceUnitForm.vue";
import RetireModal from "../../../components/admin/device_unit/RetireModal.vue";
import { devicesService } from "../../../services/devices/devicesService";
import { deviceUnitService } from "../../../services/admin/deviceUnitService";
import { useDeviceUnits } from "../../../composables/fetchData/admin/useDeviceUnits";
import { useForm } from "../../../composables/useForm";
import { useToast } from "vue-toastification";
import formatDates from "../../../composables/utils/formatDates";
import apiClient from "../../../services/api/apiClient";
export default {
  name: "DeviceUnits",
  components: {
    Table,
    Button,
    TableLoading,
    Pagination,
    SearchBar,
    Dropdown,
    CreateDeviceUnitForm,
    UpdateDeviceUnitForm,
    RetireModal,
  },
  setup() {
    const toast = useToast();
    const devices = ref([]);

    const {
      units,
      isLoading,
      pagination,
      loadDeviceUnits,
      retireDeviceUnit,
      bulkRetireDeviceUnits,
      filters,
      addUnit,
      updateUnit,
    } = useDeviceUnits();

    const handleLoadUnits = (page = 1) => {
      loadDeviceUnits(page);
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
      createData: async (data) => {
        const success = await addUnit(data);
        if (!success) throw new Error("Failed to create");
        return { success: true };
      },
      updateData: async (id, data) => {
        const success = await updateUnit(id, data);
        if (!success) throw new Error("Failed to update");
        return { success: true };
      },
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
      { value: "reserved", label: "Được mượn" },
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

    const fileInput = ref(null);

    const exportUnits = async () => {
      try {
        toast.info("Đang xuất file Excel...");
        const response = await deviceUnitService.exportExcel();

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute(
          "download",
          `device_units_${new Date().getTime()}.xlsx`
        );
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        toast.success("Xuất Excel thành công!");
      } catch (error) {
        console.error("Export error:", error);
        toast.error("Không thể xuất file Excel");
      }
    };

    const triggerFileInput = () => {
      fileInput.value.click();
    };

    const handleFileImport = async (event) => {
      const file = event.target.files[0];
      if (!file) return;

      try {
        toast.info("Đang nhập dữ liệu...");

        const formData = new FormData();
        formData.append("file", file);

        await deviceUnitService.importExcel(formData);

        toast.success("Nhập Excel thành công!");
        handleLoadUnits();

        event.target.value = "";
      } catch (error) {
        console.error("Import error:", error);

        if (error.response?.data?.errors) {
          const errors = error.response.data.errors;
          toast.error(`Import thất bại: ${errors.length} lỗi`);
        } else {
          toast.error(
            error.response?.data?.message || "Không thể nhập file Excel"
          );
        }

        event.target.value = "";
      }
    };

    onMounted(() => {
      loadDevices();
      handleLoadUnits();
    });

    const showRetireModal = ref(false);
    const selectedUnit = ref(null);

    const openRetireModal = (unit) => {
      selectedUnit.value = unit;
      showRetireModal.value = true;
    };

    const exportBorrowers = async () => {
      try {
        const response = await apiClient.get(
          "/admin/device-units/export-borrowers",
          {
            responseType: "blob",
          }
        );

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute(
          "download",
          `device_unit_borrowers_${new Date().getTime()}.xlsx`
        );
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        toast.success("Xuất danh sách người mượn thành công!");
      } catch (error) {
        console.error("Export borrowers error:", error);
        toast.error("Không thể xuất danh sách người mượn");
      }
    };
    const handleRetire = async (retirementData) => {
      if (selectedUnit.value) {
        try {
          const response = await deviceUnitService.retire(
            selectedUnit.value.id,
            retirementData
          );

          if (response.data) {
            toast.success("Thanh lý thiết bị thành công!");
            showRetireModal.value = false;
            selectedUnit.value = null;
            handleLoadUnits(pagination.current_page);
          }
        } catch (error) {
          console.error("Retire error:", error);
          toast.error(
            error.response?.data?.error || "Không thể thanh lý thiết bị"
          );
        }
      }
    };

    return {
      filters,
      units,
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
      fileInput,
      exportUnits,
      triggerFileInput,
      handleFileImport,
      showRetireModal,
      selectedUnit,
      openRetireModal,
      handleRetire,
      formatDates,
      exportBorrowers,
    };
  },
};
</script>
