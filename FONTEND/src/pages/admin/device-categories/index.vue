<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Danh mục thiết bị</h1>
        <p class="text-gray-500 text-sm">
          Quản lý nhóm thiết bị để phân loại và thống kê.
        </p>
      </div>
      <Button color="primary" @click="goToCreate">
        <font-awesome-icon icon="plus" class="mr-2" />
        Thêm danh mục
      </Button>
    </div>

    <div
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4"
    >
      <div
        class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between"
      >
        <input
          v-model="filters.search"
          type="text"
          class="w-full sm:max-w-md px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
          placeholder="Tìm kiếm theo tên hoặc mã..."
        />
        <div class="flex gap-2">
          <button
            class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50"
            @click="resetFilters"
          >
            Đặt lại
          </button>
          <button
            class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-700"
            @click="loadCategories()"
          >
            Tìm kiếm
          </button>
        </div>
      </div>

      <LoadingSkeleton v-if="isLoading" />
      <div v-else>
        <Table :data="categories" :headers="headers">
          <template #code="{ item }">
            <span class="font-mono uppercase text-gray-700">{{
              item.code
            }}</span>
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
          <template #devices_count="{ item }">
            {{ item.devices_count ?? item.devices?.length ?? 0 }}
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
                @click="deleteCategory(item)"
              >
                Xóa
              </button>
            </div>
          </template>
        </Table>
        <Pagination
          v-if="pagination.total > pagination.per_page"
          :links="pagination.links"
          @page-changed="loadCategories"
        />
      </div>
    </div>

    <Modal :show="showModal" :title="modalTitle" @close="closeModal">
      <form class="space-y-4" @submit.prevent="saveCategory">
        <div>
          <label class="text-sm font-medium text-gray-700">Tên danh mục</label>
          <input
            v-model="form.name"
            type="text"
            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
          />
          <p v-if="errors.name" class="text-xs text-red-500 mt-1">
            {{ errors.name?.[0] || errors.name }}
          </p>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Mã</label>
          <input
            v-model="form.code"
            type="text"
            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 uppercase tracking-wide"
          />
          <p v-if="errors.code" class="text-xs text-red-500 mt-1">
            {{ errors.code?.[0] || errors.code }}
          </p>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Mô tả</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
            placeholder="Ghi chú ngắn gọn..."
          ></textarea>
        </div>
        <div class="flex items-center gap-2">
          <input
            id="active"
            type="checkbox"
            v-model="form.is_active"
            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
          />
          <label for="active" class="text-sm text-gray-700"
            >Kích hoạt danh mục</label
          >
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
            @click="saveCategory"
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
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import Table from "../../../components/Table.vue";
import Button from "../../../components/Button.vue";
import LoadingSkeleton from "../../../components/LoadingSkeleton.vue";
import Pagination from "../../../components/Pagination.vue";
import Modal from "../../../components/Modal.vue";
import { deviceCategoriesService } from "../../../services/devices/deviceCategoriesService";

const toast = useToast();
const router = useRouter();

const isLoading = ref(false);
const categories = ref([]);
const pagination = reactive({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1,
  links: [],
});

const headers = {
  name: "Tên danh mục",
  code: "Mã",
  is_active: "Trạng thái",
  devices_count: "Số thiết bị",
};

const filters = reactive({
  search: "",
});

const showModal = ref(false);
const modalMode = ref("create");
const form = reactive({
  id: null,
  name: "",
  code: "",
  description: "",
  is_active: true,
});
const errors = reactive({});

const loadCategories = async (page = 1) => {
  isLoading.value = true;
  try {
    const params = {
      page,
      search: filters.search || undefined,
      with_devices: true,
    };
    const { data } = await deviceCategoriesService.list(params);
    const payload = data.categories;
    categories.value = payload?.data || [];
    pagination.current_page = payload?.current_page || 1;
    pagination.per_page = payload?.per_page || 10;
    pagination.total = payload?.total || 0;
    pagination.last_page = payload?.last_page || 1;
    pagination.links = payload?.links || [];
  } catch (error) {
    if (error.response?.status === 404) {
      categories.value = [];
      pagination.total = 0;
    } else {
      toast.error("Không thể tải danh mục");
    }
  } finally {
    isLoading.value = false;
  }
};

const goToCreate = () => {
  router.push({ name: "admin.deviceCategories.create" });
};

const deleteCategory = async (item) => {
  if (!confirm(`Bạn chắc chắn muốn xóa danh mục "${item.name}"?`)) {
    return;
  }
  try {
    await deviceCategoriesService.remove(item.id);
    toast.success("Đã xóa danh mục");
    loadCategories(pagination.current_page);
  } catch (error) {
    toast.error(error.response?.data?.error || "Không thể xóa danh mục");
  }
};

const resetFilters = () => {
  filters.search = "";
  loadCategories();
};

onMounted(() => {
  loadCategories();
});
</script>
