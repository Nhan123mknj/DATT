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
      <Button color="primary" @click="openCreateModal">
        <font-awesome-icon icon="plus" class="mr-2" />
        Thêm thiết bị
      </Button>
    </div>

    <div
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4"
    >
      <div class="grid gap-3 md:grid-cols-4">
        <input
          v-model="filters.search"
          type="text"
          class="px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
          placeholder="Tìm kiếm theo tên hoặc hãng..."
        />
        <select
          v-model="filters.category_id"
          class="px-3 py-2 rounded-lg border border-gray-200 text-sm text-gray-700"
        >
          <option value="">Tất cả danh mục</option>
          <option
            v-for="category in categories"
            :key="category.id"
            :value="category.id"
          >
            {{ category.name }}
          </option>
        </select>
        <select
          v-model="filters.is_active"
          class="px-3 py-2 rounded-lg border border-gray-200 text-sm text-gray-700"
        >
          <option value="">-- Trạng thái --</option>
          <option value="1">Kích hoạt</option>
          <option value="0">Tạm dừng</option>
        </select>
        <div class="flex gap-2">
          <button
            class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50"
            @click="resetFilters"
          >
            Đặt lại
          </button>
          <button
            class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-700 flex-1"
            @click="loadDevices()"
          >
            Lọc
          </button>
        </div>
      </div>

      <LoadingSkeleton v-if="isLoading" />
      <div v-else>
        <Table :data="devices" :headers="headers">
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
                @click="openEditModal(item)"
              >
                Sửa
              </button>
              <button
                class="px-3 py-1 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-sm"
                @click="deleteDevice(item)"
              >
                Xóa
              </button>
            </div>
          </template>
        </Table>
        <Pagination
          v-if="pagination.total > pagination.per_page"
          :links="pagination.links"
          @page-changed="loadDevices"
        />
      </div>
    </div>

    <Modal :show="showModal" :title="modalTitle" @close="closeModal">
      <form class="space-y-4" @submit.prevent="saveDevice">
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
            class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500"
            @click="saveDevice"
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
import { devicesService } from "../../../services/devices/devicesService";
import { deviceCategoriesService } from "../../../services/devices/deviceCategoriesService";

const toast = useToast();

const isLoading = ref(false);
const devices = ref([]);
const categories = ref([]);
const pagination = reactive({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1,
  links: [],
});

const headers = {
  name: "Tên thiết bị",
  category: "Danh mục",
  manufacturer: "Hãng",
  model: "Model",
  total_units: "Đơn vị",
  is_active: "Trạng thái",
};

const filters = reactive({
  search: "",
  category_id: "",
  is_active: "",
});

const showModal = ref(false);
const modalMode = ref("create");
const form = reactive({
  id: null,
  name: "",
  category_id: "",
  manufacturer: "",
  model: "",
  specifications: "",
  is_active: 1,
});
const errors = reactive({});

const modalTitle = computed(() =>
  modalMode.value === "create" ? "Thêm thiết bị" : "Cập nhật thiết bị"
);

const loadCategories = async () => {
  try {
    const { data } = await deviceCategoriesService.list({ page: 1 });
    categories.value = data.categories?.data || [];
  } catch {
    categories.value = [];
  }
};

const loadDevices = async (page = 1) => {
  isLoading.value = true;
  try {
    const params = {
      page,
      search: filters.search || undefined,
      category_id: filters.category_id || undefined,
      is_active: filters.is_active !== "" ? filters.is_active : undefined,
    };
    const { data } = await devicesService.list(params);
    const payload = data.devices;
    devices.value = payload?.data || [];
    pagination.current_page = payload?.current_page || 1;
    pagination.per_page = payload?.per_page || 10;
    pagination.total = payload?.total || 0;
    pagination.last_page = payload?.last_page || 1;
    pagination.links = payload?.links || [];
  } catch (error) {
    if (error.response?.status === 404) {
      devices.value = [];
      pagination.total = 0;
    } else {
      toast.error("Không thể tải danh sách thiết bị");
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

const openEditModal = (device) => {
  modalMode.value = "edit";
  form.id = device.id;
  form.name = device.name;
  form.category_id = device.category_id;
  form.manufacturer = device.manufacturer;
  form.model = device.model;
  form.specifications = device.specifications;
  form.is_active = device.is_active ? 1 : 0;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const resetForm = () => {
  form.id = null;
  form.name = "";
  form.category_id = "";
  form.manufacturer = "";
  form.model = "";
  form.specifications = "";
  form.is_active = 1;
  Object.keys(errors).forEach((key) => delete errors[key]);
};

const saveDevice = async () => {
  Object.keys(errors).forEach((key) => delete errors[key]);
  const payload = {
    name: form.name,
    category_id: form.category_id || null,
    manufacturer: form.manufacturer,
    model: form.model,
    specifications: form.specifications,
    is_active: form.is_active ? 1 : 0,
  };
  try {
    if (modalMode.value === "create") {
      await devicesService.create(payload);
      toast.success("Đã thêm thiết bị");
    } else {
      await devicesService.update(form.id, payload);
      toast.success("Đã cập nhật thiết bị");
    }
    closeModal();
    loadDevices(pagination.current_page);
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(errors, error.response.data.errors);
    } else {
      toast.error("Không thể lưu thiết bị");
    }
  }
};

const deleteDevice = async (device) => {
  if (!confirm(`Bạn chắc chắn muốn xóa thiết bị "${device.name}"?`)) return;
  try {
    await devicesService.remove(device.id);
    toast.success("Đã xóa thiết bị");
    loadDevices(pagination.current_page);
  } catch (error) {
    toast.error(error.response?.data?.error || "Không thể xóa thiết bị");
  }
};

const resetFilters = () => {
  filters.search = "";
  filters.category_id = "";
  filters.is_active = "";
  loadDevices();
};

onMounted(() => {
  loadCategories();
  loadDevices();
});
</script>
