<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Danh sách người dùng</h1>

      <Button label="Thêm mới" @click="openCreate">
        <template #icon>
          <font-awesome-icon icon="plus" class="mr-2" />
        </template>
      </Button>
    </div>
    <div
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4 space-y-4"
    >
      <div class="grid gap-3 md:grid-cols-4">
        <SearchBar v-on:handleSearch="handleSearch" />
        <Dropdown
          v-model="filters.role"
          label="Vai trò"
          :options="[
            { label: 'Quản trị viên', value: 'admin' },
            { label: 'Nhân viên', value: 'staff' },
            { label: 'Người dùng', value: 'borrower' },
          ]"
          nameKey="label"
          idKey="value"
        />
        <Dropdown
          v-model="filters.is_active"
          label="Trạng thái"
          :options="[
            { label: 'Kích hoạt', value: '1' },
            { label: 'Vô hiệu hóa', value: '0' },
          ]"
          nameKey="label"
          idKey="value"
        />

        <div class="flex gap-2">
          <Button label="Đặt lại" @click="resetFilters" color="gray" />
          <Button label="Lọc" @click="loadData()" />
        </div>
      </div>
    </div>

    <div>
      <LoadingSkeleton v-if="isLoading" />
      <Table
        v-else
        :data="users"
        :headers="headers"
        @edit="openEdit"
        @delete="handleDelete"
      >
        <template #STT="{ index }">
          {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}
        </template>
        <template #role="{ item }">{{ getRoleLabel(item.role) }}</template>
        <template #is_active="{ item }">{{
          statusActive(item.is_active)
        }}</template>
        <template #actions="{ item }">
          <button
            @click="viewDetail(item)"
            class="px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition"
            title="Xem chi tiết"
          >
            <font-awesome-icon icon="fa-solid fa-eye" />
          </button>

          <button
            @click="openEdit(item)"
            class="px-2 py-1 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition"
            title="Chỉnh sửa"
          >
            <font-awesome-icon icon="fa-solid fa-pen" />
          </button>

          <button
            @click="handleDelete(item)"
            class="px-2 py-1 rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition"
            title="Xóa người dùng"
          >
            <font-awesome-icon icon="fa-solid fa-trash" />
          </button>
        </template>
      </Table>
      <Pagination
        v-if="pagination.total && pagination.last_page > 1"
        :links="pagination.links"
        @page-changed="loadData"
      />
    </div>
  </div>
  <AddUserForm
    :visible="showModal && modalMode === 'create'"
    @close="closeModal"
    @refresh="loadData"
  />
  <UpdateUserForm
    :visible="showModal && modalMode === 'edit'"
    :user-data="form"
    @close="closeModal"
    @refresh="loadData"
  />
</template>

<script>
import { ref, onMounted } from "vue";
import { usersService } from "../../../services/users/usersService";
import { useToast } from "vue-toastification";
import Table from "../../../components/common/Table.vue";
import Button from "../../../components/common/Button.vue";
import LoadingSkeleton from "../../../components/common/LoadingSkeleton.vue";
import AddUserForm from "../../../components/user/AddUserForm.vue";
import UpdateUserForm from "../../../components/user/UpdateUserForm.vue";
import Pagination from "../../../components/common/Pagination.vue";
import Dropdown from "../../../components/common/Dropdown.vue";
import SearchBar from "../../../components/common/SearchBar.vue";
import { useDataTable } from "../../../composables/fetchData/useDataTable";
import { useUserFilter } from "../../../composables/filter/useUserFilter";
import { useStatusLabel } from "../../../composables/utils/statusLabel";
import { useForm } from "../../../composables/useForm";

export default {
  name: "UsersIndex",
  components: {
    Table,
    Button,
    LoadingSkeleton,
    AddUserForm,
    UpdateUserForm,
    Pagination,
    Dropdown,
    SearchBar,
  },
  setup() {
    const { filters, resetFilters } = useUserFilter();
    const { statusActive, getRoleLabel } = useStatusLabel();
    const toast = useToast();

    const selectedUser = ref(null);
    const showDetailModal = ref(false);

    const {
      items: users,
      isLoading,
      pagination,
      loadData,
      deleteItem,
    } = useDataTable({
      fetchData: (params) =>
        usersService.getAllUser({
          ...params,
          search: filters.search || undefined,
          role: filters.role || undefined,
          is_active: filters.is_active || undefined,
        }),
      deleteData: (id) => usersService.deleteUser(id),
      dataKey: null,
      confirmDeleteMsg: "Bạn có chắc muốn xóa người dùng này?",
    });

    const headers = {
      name: "Tên",
      email: "Email",
      role: "Vai trò",
      is_active: "Trạng thái",
    };

    const { form, showModal, modalMode, openCreate, openEdit, closeModal } =
      useForm({
        initialForm: {
          id: null,
          name: "",
          email: "",
          role: "",
          is_active: 1,
        },
      });

    const handleSearch = (data) => {
      filters.search = data;
      loadData();
    };

    const viewDetail = async (user) => {
      try {
        const res = await usersService.getUserById(user.id);
        selectedUser.value = res.data.data || res.data;
        showDetailModal.value = true;
      } catch {
        toast.error("Không thể tải chi tiết người dùng");
      }
    };

    const handleDelete = (user) => {
      deleteItem(user.id);
    };

    onMounted(() => {
      loadData();
    });

    return {
      filters,
      resetFilters,
      statusActive,
      getRoleLabel,
      users,
      isLoading,
      pagination,
      loadData,
      handleDelete,
      headers,
      form,
      showModal,
      modalMode,
      openCreate,
      openEdit,
      closeModal,
      handleSearch,
      viewDetail,
      selectedUser,
      showDetailModal,
    };
  },
};
</script>
