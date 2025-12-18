<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Danh sách người dùng</h1>

      <Button label="Thêm mới" @click="userStore.openCreate()">
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
          v-model="userStore.filters.role"
          label="Vai trò"
          :options="[
            { label: 'Quản trị viên', value: 'admin' },
            { label: 'Nhân viên', value: 'staff' },
            { label: 'Học sinh', value: 'student' },
            { label: 'Giảng viên', value: 'teacher' },
          ]"
          nameKey="label"
          idKey="value"
        />
        <Dropdown
          v-model="userStore.filters.is_active"
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
          <Button label="Lọc" @click="userStore.loadUsers()" />
        </div>
      </div>
    </div>

    <div>
      <TableLoading v-if="userStore.loading" />
      <Table
        v-else
        :data="userStore.users"
        :headers="headers"
        @edit="userStore.openEdit"
        @delete="handleDelete"
      >
        <template #STT="{ index }">
          {{
            (userStore.pagination.current_page - 1) *
              userStore.pagination.per_page +
            index +
            1
          }}
        </template>
        <template #role="{ item }">{{ getRoleLabel(item.role) }}</template>
        <template #is_active="{ item }">
          <span
            class="px-3 py-1 rounded-full text-xs font-medium"
            :class="statusActiveClass(item.is_active)"
          >
            {{ statusActive(item.is_active) }}
          </span>
        </template>
        <template #actions="{ item }">
          <button
            @click="viewDetail(item)"
            class="px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition"
            title="Xem chi tiết"
          >
            <font-awesome-icon icon="fa-solid fa-eye" />
          </button>

          <button
            @click="userStore.openEdit(item)"
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
        v-if="userStore.pagination.total && userStore.pagination.last_page > 1"
        :links="userStore.pagination.links"
        @page-changed="userStore.loadUsers"
      />
    </div>
  </div>

  <AddUserForm
    :visible="userStore.showModal && userStore.modalMode === 'create'"
    @close="userStore.closeModal()"
    @refresh="userStore.loadUsers(userStore.pagination.current_page)"
  />
  <UpdateUserForm
    :visible="userStore.showModal && userStore.modalMode === 'edit'"
    :user-data="userStore.userForm"
    @close="userStore.closeModal()"
    @refresh="userStore.loadUsers(userStore.pagination.current_page)"
  />
</template>

<script setup>
import { ref, onMounted } from "vue";
import { usersService } from "../../../services/admin/usersService";
import { useToast } from "vue-toastification";
import { useUserStore } from "../../../stores/userStore";
import Table from "../../../components/common/Table.vue";
import Button from "../../../components/common/Button.vue";
import TableLoading from "../../../components/common/TableLoading.vue";
import AddUserForm from "../../../components/user/AddUserForm.vue";
import UpdateUserForm from "../../../components/user/UpdateUserForm.vue";
import Pagination from "../../../components/common/Pagination.vue";
import Dropdown from "../../../components/common/Dropdown.vue";
import SearchBar from "../../../components/common/SearchBar.vue";
import useStatusLabel from "../../../composables/utils/statusLabel";

const toast = useToast();
const userStore = useUserStore();
const { statusActive, statusActiveClass, getRoleLabel } = useStatusLabel();

const selectedUser = ref(null);
const showDetailModal = ref(false);

const headers = {
  name: "Tên",
  email: "Email",
  role: "Vai trò",
  is_active: "Trạng thái",
};

const handleSearch = (data) => {
  userStore.filters.search = data;
  userStore.loadUsers();
};

const resetFilters = () => {
  userStore.resetFilters();
  userStore.loadUsers();
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

const handleDelete = async (user) => {
  const deleted = await userStore.deleteUser(user.id);
  if (deleted) {
    userStore.loadUsers(userStore.pagination.current_page);
  }
};

onMounted(() => {
  userStore.loadUsers();
});
</script>
