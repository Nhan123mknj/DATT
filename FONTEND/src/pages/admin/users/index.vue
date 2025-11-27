<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Danh sách người dùng</h1>

      <Button
        class="px-4 py-2"
        label="Thêm mới"
        color="primary"
        @click="showAddModal = true"
      />
    </div>

    <div
      v-if="error"
      class="bg-red-100 border border-red-400 text-red-700 p-4 mb-4 rounded"
    >
      {{ error }}
    </div>

    <div>
      <LoadingSkeleton v-if="isLoading" />
      <Table
        v-else
        :data="users"
        :headers="headers"
        @edit="handleEdit"
        @delete="handleDelete"
      >
        <template #STT="{ index }">
          {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}
        </template>
        <template #role="{ item }">{{ getRoleLabel(item.role) }}</template>
        <template #is_active="{ item }">{{
          getActiveLabel(item.is_active)
        }}</template>
        <template #actions="{ item }">
          <!-- Xem chi tiết -->
          <button
            @click="viewDetail(item)"
            class="px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition"
            title="Xem chi tiết"
          >
            <font-awesome-icon icon="fa-solid fa-eye" />
          </button>

          <!-- Sửa -->
          <button
            @click="handleEdit(item)"
            class="px-2 py-1 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition"
            title="Chỉnh sửa"
          >
            <font-awesome-icon icon="fa-solid fa-pen" />
          </button>

          <!-- Xóa -->
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
        @page-changed="loadUsers"
      />
    </div>
  </div>

  <!-- Form thêm -->
  <AddUserForm
    :visible="showAddModal"
    @close="closeModals"
    @refresh="loadUsers"
  />

  <!-- Form sửa -->
  <UpdateUserForm
    v-if="selectedUser"
    :visible="showEditModal"
    :user-data="selectedUser"
    :is-submitting="isSubmitting"
    :server-errors="serverErrors"
    @close="closeModals"
    @submit="handleUpdateUser"
  />
</template>

<script>
import { usersService } from "../../../services/users/usersService";
import { useToast } from "vue-toastification";
import Table from "../../../components/Table.vue";
import Button from "../../../components/Button.vue";
import LoadingSkeleton from "../../../components/LoadingSkeleton.vue";
import AddUserForm from "./AddUserForm.vue";
import UpdateUserForm from "./UpdateUserForm.vue";
import Pagination from "../../../components/Pagination.vue";

export default {
  name: "UserListPage",
  components: {
    Table,
    Button,
    LoadingSkeleton,
    AddUserForm,
    UpdateUserForm,
    Pagination,
  },

  data() {
    return {
      users: [],
      selectedUser: null,
      serverErrors: {},
      isLoading: false,
      isSubmitting: false,
      error: null,
      pagination: {
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
        links: [],
      },
      showAddModal: false,
      showEditModal: false,
      showDetailModal: false,

      headers: {
        name: "Tên",
        email: "Email",
        role: "Vai trò",
        is_active: "Trạng thái",
      },
    };
  },

  mounted() {
    this.loadUsers();
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
  methods: {
    async loadUsers(page = 1) {
      this.isLoading = true;
      this.error = null;
      try {
        const res = await usersService.getAllUser(
          page,
          this.pagination.per_page || 10
        );
        const payload = res.data;

        this.users = payload.data || [];
        this.pagination = {
          current_page: payload.current_page,
          per_page: payload.per_page,
          total: payload.total,
          last_page: payload.last_page,
          links: payload.links || [],
        };
      } catch (err) {
        if (err.response?.status === 404) {
          this.users = [];
          this.pagination = {
            current_page: 1,
            per_page: 10,
            total: 0,
            last_page: 1,
            links: [],
          };
          this.error = err.response.data.message;
        } else {
          this.error = "Không thể tải dữ liệu người dùng";
        }
        this.toast.error(this.error);
      } finally {
        this.isLoading = false;
      }
    },

    getRoleLabel(role) {
      const map = {
        admin: "Quản trị viên",
        staff: "Nhân viên",
        borrower: "Người dùng",
      };
      return map[role] || role;
    },

    getActiveLabel(is_active) {
      return is_active ? "Kích hoạt" : "Vô hiệu hóa";
    },

    async handleEdit(user) {
      try {
        const res = await usersService.getUserById(user.id);
        this.selectedUser = res.data.data || res.data;
        this.showEditModal = true;
      } catch {
        this.toast.error("Không thể tải dữ liệu người dùng");
      }
    },

    async handleUpdateUser(userData) {
      this.isSubmitting = true;
      try {
        await usersService.updateUser(userData.id, userData);
        this.toast.success("Cập nhật thành công!");
        this.showEditModal = false;
        await this.loadUsers();
      } catch {
        this.toast.error("Không thể cập nhật!");
      } finally {
        this.isSubmitting = false;
      }
    },

    async viewDetail(user) {
      try {
        const res = await usersService.getUserById(user.id);
        this.selectedUser = res.data.data || res.data;
        this.showDetailModal = true;
      } catch {
        this.toast.error("Không thể tải chi tiết người dùng");
      }
    },

    async handleDelete(user) {
      if (!confirm(`Bạn có chắc muốn xóa ${user.name}?`)) return;
      try {
        await usersService.deleteUser(user.id);
        this.toast.success("Đã xóa thành công");
        await this.loadUsers();
      } catch {
        this.toast.error("Không thể xóa người dùng");
      }
    },

    closeModals() {
      this.showAddModal = false;
      this.showEditModal = false;
      this.showDetailModal = false;
      this.serverErrors = {};
      this.selectedUser = null;
    },
  },
};
</script>
