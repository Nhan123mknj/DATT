<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Thiết bị</h1>
        <p class="text-gray-500 text-sm">
          Danh sách thiết bị cùng thông tin nhà sản xuất và danh mục.
        </p>
      </div>
      <div class="flex gap-2">
        <Button
          v-if="selectedDevices.length > 0"
          label="Xóa đã chọn"
          color="danger"
          @click="deleteSelected"
        >
          <template #icon>
            <font-awesome-icon icon="trash" class="mr-2" />
          </template>
        </Button>

        <Button label="Xuất Excel" color="success" @click="exportAllDevices">
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

        <Button label="Thêm thiết bị" @click="openCreate">
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
          v-model="filters.category_id"
          :options="categories"
          label="Danh mục"
        />
        <Dropdown
          v-model="filters.is_active"
          :options="statusOptions"
          label="Trạng thái"
          nameKey="label"
          idKey="value"
        />
        <div class="flex gap-2">
          <Button
            label="Đặt lại"
            @click="resetFilters"
            color="gray"
            variant="outlined"
          />
          <Button label="Lọc" @click="loadData()" />
        </div>
      </div>

      <TableLoading v-if="isLoading" />
      <div v-else>
        <Table
          :data="devices"
          :headers="headers"
          selectable
          v-model:selectedItems="selectedDevices"
        >
          <template #category="{ item }">
            {{ item.category?.name || "Chưa phân loại" }}
          </template>
          <template #is_active="{ item }">
            <span
              class="px-3 py-1 rounded-full text-xs font-medium"
              :class="
                item.is_active
                  ? 'bg-green-100 text-green-700'
                  : 'bg-gray-100 text-gray-600'
              "
            >
              {{ item.is_active ? "Kích hoạt" : "Tạm dừng" }}
            </span>
          </template>
          <template #stats="{ item }">
            <div class="flex flex-wrap gap-2">
              <span
                class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200"
                title="Tổng số lượng"
              >
                Tổng: {{ item.total_units || 0 }}
              </span>
              <span
                class="px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100"
                title="Đang mượn"
              >
                Mượn: {{ item.units_in_use_count || 0 }}
              </span>
              <span
                class="px-2 py-1 rounded text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-100"
                title="Đang đặt trước"
              >
                Đặt: {{ item.units_reserved_count || 0 }}
              </span>
              <span
                class="px-2 py-1 rounded text-xs font-medium bg-red-50 text-red-700 border border-red-100"
                title="Đang bảo trì"
              >
                Bảo trì: {{ item.units_maintenance_count || 0 }}
              </span>
            </div>
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
          v-if="pagination.total > pagination.per_page"
          :links="pagination.links"
          @page-changed="loadData"
        />
      </div>
    </div>

    <DeviceFormModal
      :show="showModal"
      :device="editingDevice"
      :categories="categories"
      @close="closeModal"
      @saved="onSaved"
    />
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from "vue";

import Table from "../../../components/common/Table.vue";
import Button from "../../../components/common/Button.vue";
import TableLoading from "../../../components/common/TableLoading.vue";
import Pagination from "../../../components/common/Pagination.vue";
import SearchBar from "../../../components/common/SearchBar.vue";
import Dropdown from "../../../components/common/Dropdown.vue";
import DeviceFormModal from "../../../components/admin/device/DeviceFormModal.vue";

import { deviceService as devicesService } from "../../../services/admin/deviceService";
import { deviceCategoryService as deviceCategoriesService } from "../../../services/admin/deviceCategoryService";

import { useToast } from "vue-toastification";
import { useDevices } from "../../../composables/fetchData/admin/useDevices";

export default {
  name: "Devices",

  components: {
    Table,
    Button,
    TableLoading,
    Pagination,
    SearchBar,
    Dropdown,
    DeviceFormModal,
  },

  setup() {
    const toast = useToast();

    const {
      devices,
      isLoading,
      pagination,
      loadDevices,
      deleteDevice,
      filters,
    } = useDevices();

    const categories = ref([]);

    const loadCategories = async () => {
      try {
        const { data } = await deviceCategoriesService.list({ page: 1 });
        categories.value = data.categories?.data || [];
      } catch {
        categories.value = [];
      }
    };

    const handleLoadDevices = (page = 1) => {
      loadDevices(page);
    };

    const showModal = ref(false);
    const editingDevice = ref(null);

    const openCreate = () => {
      editingDevice.value = null;
      showModal.value = true;
    };

    const openEdit = (item) => {
      editingDevice.value = item;
      showModal.value = true;
    };

    const closeModal = () => {
      showModal.value = false;
      editingDevice.value = null;
    };

    const onSaved = () => {
      handleLoadDevices(pagination.current_page);
    };

    const selectedDevices = ref([]);

    watch(devices, () => {
      selectedDevices.value = [];
    });

    const deleteSelected = async () => {
      if (
        !confirm(
          `Bạn chắc chắn muốn xóa ${selectedDevices.value.length} thiết bị đã chọn?`
        )
      )
        return;
      try {
        await Promise.all(
          selectedDevices.value.map((device) =>
            devicesService.remove(device.id)
          )
        );

        toast.success("Đã xóa các thiết bị đã chọn");
        handleLoadDevices(pagination.current_page);
        selectedDevices.value = [];
      } catch {
        toast.error("Có lỗi khi xóa thiết bị");
      }
    };

    const handleSearch = (value) => {
      filters.search = value;
      handleLoadDevices();
    };

    const resetFilters = () => {
      filters.search = "";
      filters.category_id = "";
      filters.is_active = undefined;
      handleLoadDevices();
    };

    const statusOptions = [
      { value: true, label: "Kích hoạt" },
      { value: false, label: "Tạm dừng" },
    ];

    const headers = {
      name: "Tên thiết bị",
      category: "Danh mục",
      manufacturer: "Nhà sản xuất",
      is_active: "Trạng thái",
      stats: "Thống kê (Tổng/Mượn/Đặt/Bảo trì)",
    };

    // Export/Import Excel
    const fileInput = ref(null);

    const exportAllDevices = async () => {
      try {
        toast.info("Đang xuất file Excel...");
        const response = await devicesService.exportExcel();

        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", `devices_${new Date().getTime()}.xlsx`);
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

        await devicesService.importExcel(formData);

        toast.success("Nhập Excel thành công!");
        handleLoadDevices(); // Reload data

        // Reset file input
        event.target.value = "";
      } catch (error) {
        console.error("Import error:", error);

        if (error.response?.data?.errors) {
          // Validation errors
          const errors = error.response.data.errors;
          toast.error(`Import thất bại: ${errors.length} lỗi`);
        } else {
          toast.error(
            error.response?.data?.message || "Không thể nhập file Excel"
          );
        }

        // Reset file input
        event.target.value = "";
      }
    };

    onMounted(() => {
      loadCategories();
      handleLoadDevices();
    });

    return {
      toast,
      filters,
      resetFilters,
      categories,
      devices,
      isLoading,
      pagination,
      loadData: handleLoadDevices,
      deleteItem: async (id) => {
        const success = await deleteDevice(id);
        if (success) {
          handleLoadDevices(pagination.current_page);
        }
      },
      showModal,
      editingDevice,
      openCreate,
      openEdit,
      closeModal,
      onSaved,
      selectedDevices,
      deleteSelected,
      handleSearch,
      headers,
      statusOptions,
      fileInput,
      exportAllDevices,
      triggerFileInput,
      handleFileImport,
    };
  },
};
</script>
