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
        <Button
          v-if="selectedDevices.length > 0"
          label="Xuất Excel"
          color="success"
          @click="exportExcel"
        >
          <template #icon>
            <font-awesome-icon icon="file-excel" class="mr-2" />
          </template>
        </Button>
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

      <LoadingSkeleton v-if="isLoading" />
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
          <template #total_units="{ item }">
            {{ item.units?.length ?? 0 }}
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

    <ModalForm
      :show="showModal"
      :title="modalTitle"
      @close="closeModal"
      @submit="saveDevice"
    >
      <div class="space-y-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Tên thiết bị</label>
          <input
            v-model="form.name"
            type="text"
            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
          />
          <p v-if="errors.name" class="text-xs text-red-500 mt-1">
            {{ errors.name?.[0] || errors.name }}
          </p>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="text-sm font-medium text-gray-700">Danh mục</label>
            <select
              v-model="form.category_id"
              class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
            >
              <option value="">-- Chọn danh mục --</option>
              <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
            <p v-if="errors.category_id" class="text-xs text-red-500 mt-1">
              {{ errors.category_id?.[0] || errors.category_id }}
            </p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700"
              >Nhà sản xuất</label
            >
            <input
              v-model="form.manufacturer"
              type="text"
              class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
            />
            <p v-if="errors.manufacturer" class="text-xs text-red-500 mt-1">
              {{ errors.manufacturer?.[0] || errors.manufacturer }}
            </p>
          </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="text-sm font-medium text-gray-700">Model</label>
            <input
              v-model="form.model"
              type="text"
              class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
            />
            <p v-if="errors.model" class="text-xs text-red-500 mt-1">
              {{ errors.model?.[0] || errors.model }}
            </p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Trạng thái</label>
            <select
              v-model="form.is_active"
              class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
            >
              <option :value="1">Kích hoạt</option>
              <option :value="0">Tạm dừng</option>
            </select>
          </div>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Thông số</label>
          <textarea
            v-model="form.specifications"
            rows="3"
            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
            placeholder="CPU, RAM, Bộ nhớ..."
          ></textarea>
        </div>
      </div>
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
            type="submit"
            class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500"
          >
            {{ modalMode === "create" ? "Thêm mới" : "Cập nhật" }}
          </button>
        </div>
      </template>
    </ModalForm>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from "vue";

import Table from "../../../components/common/Table.vue";
import Button from "../../../components/common/Button.vue";
import LoadingSkeleton from "../../../components/common/LoadingSkeleton.vue";
import Pagination from "../../../components/common/Pagination.vue";
import ModalForm from "../../../components/ModalForm.vue";
import SearchBar from "../../../components/common/SearchBar.vue";
import Dropdown from "../../../components/common/Dropdown.vue";

import { devicesService } from "../../../services/devices/devicesService";
import { deviceCategoriesService } from "../../../services/devices/deviceCategoriesService";

import { useToast } from "vue-toastification";
import { useDevices } from "../../../composables/fetchData/admin/useDevices";
import { useForm } from "../../../composables/useForm";
import { useDeviceFilters } from "../../../composables/filter/useDeviceFilter";

export default {
  name: "Devices",

  components: {
    Table,
    Button,
    LoadingSkeleton,
    Pagination,
    ModalForm,
    SearchBar,
    Dropdown,
  },

  setup() {
    const toast = useToast();

    const { filters, resetFilters, buildParams } = useDeviceFilters();

    const categories = ref([]);

    const loadCategories = async () => {
      try {
        const { data } = await deviceCategoriesService.list({ page: 1 });
        categories.value = data.categories?.data || [];
      } catch {
        categories.value = [];
      }
    };

    const { devices, isLoading, pagination, loadDevices, deleteDevice } =
      useDevices();

    const handleLoadDevices = (page = 1) => {
      loadDevices(page, filters);
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
      createData: (data) => devicesService.create(data),
      updateData: (id, data) => devicesService.update(id, data),
      initialForm: {
        id: null,
        name: "",
        category_id: "",
        manufacturer: "",
        model: "",
        specifications: "",
        is_active: 1,
      },
    });

    const modalTitle = computed(() =>
      modalMode.value === "create" ? "Thêm thiết bị" : "Cập nhật thiết bị"
    );

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

    const saveDevice = () => {
      save(() => handleLoadDevices(pagination.current_page));
    };

    const statusOptions = [
      { value: 1, label: "Kích hoạt" },
      { value: 0, label: "Tạm dừng" },
    ];

    const headers = {
      name: "Tên thiết bị",
      category: "Danh mục",
      manufacturer: "Nhà sản xuất",
      model: "Model",
      is_active: "Trạng thái",
      total_units: "Số lượng",
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
      form,
      errors,
      showModal,
      modalMode,
      openCreate,
      openEdit,
      closeModal,
      saveDevice,
      modalTitle,
      selectedDevices,
      deleteSelected,
      handleSearch,
      headers,
      statusOptions,
      exportExcel: () => toast.info("Tính năng đang phát triển"),
    };
  },
};
</script>
