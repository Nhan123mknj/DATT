<template>
  <ModalForm
    :show="visible"
    title="Thêm người dùng"
    size="medium"
    @close="handleClose"
    @submit="handleAddUser"
  >
    <ul
      v-if="Object.keys(errors).length"
      class="mb-4 list-disc list-inside text-red-600 bg-red-100 p-3 rounded-md"
    >
      <li v-for="(error, key) in errors" :key="key">
        {{ Array.isArray(error) ? error[0] : error }}
      </li>
    </ul>

    <div class="space-y-6">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-900">
          Tên tài khoản <span class="text-red-500">*</span>
        </label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          @input="removeError('name')"
          :class="[
            'mt-2 block w-full rounded-md border px-3 py-2 text-sm',
            errors.name
              ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
              : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500',
          ]"
        />
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-900">
          Email <span class="text-red-500">*</span>
        </label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          @input="removeError('email')"
          :class="[
            'mt-2 block w-full rounded-md border px-3 py-2 text-sm',
            errors.email
              ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
              : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500',
          ]"
        />
      </div>

      <div>
        <label for="role" class="block text-sm font-medium text-gray-900">
          Role <span class="text-red-500">*</span>
        </label>
        <select
          id="role"
          v-model="form.role"
          @change="removeError('role')"
          :class="[
            'mt-2 w-full appearance-none rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900',
            errors.role
              ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
              : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500',
          ]"
        >
          <option disabled value="">-- Chọn role --</option>
          <option value="admin">Admin</option>
          <option value="staff">Staff</option>
          <option value="student">Học sinh</option>
          <option value="teacher">Giảng viên</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-900">
          Trạng thái
        </label>
        <div class="flex items-center gap-6 mt-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="radio"
              name="is_active"
              :value="1"
              v-model="form.is_active"
              class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
            />
            <span>Kích hoạt</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="radio"
              name="is_active"
              :value="0"
              v-model="form.is_active"
              class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
            />
            <span>Chưa kích hoạt</span>
          </label>
        </div>
      </div>
    </div>

    <template #footer>
      <button
        type="submit"
        :disabled="isSubmitting"
        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="isSubmitting">Đang xử lý...</span>
        <span v-else>Lưu</span>
      </button>
      <button
        type="button"
        @click="handleClose"
        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
      >
        Hủy
      </button>
    </template>
  </ModalForm>
</template>

<script>
import { usersService } from "../../services/admin/usersService";
import { useToast } from "vue-toastification";
import ModalForm from "../ModalForm.vue";

export default {
  name: "AddUserForm",
  components: {
    ModalForm,
  },
  props: {
    visible: {
      type: Boolean,
      required: true,
    },
  },
  emits: ["close", "refresh"],
  data() {
    return {
      form: {
        name: "",
        email: "",
        role: "",
        is_active: 1,
      },
      errors: {},
      isSubmitting: false,
    };
  },
  methods: {
    removeError(field) {
      if (this.errors[field]) delete this.errors[field];
    },
    resetForm() {
      Object.assign(this.form, { name: "", email: "", role: "", is_active: 1 });
      this.errors = {};
    },
    handleClose() {
      this.resetForm();
      this.$emit("close");
    },
    async handleAddUser() {
      this.isSubmitting = true;
      try {
        await usersService.createUser(this.form);
        this.toast.success("Thêm người dùng thành công!");
        this.handleClose();
        this.$emit("refresh");
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors;
        } else {
          this.toast.error("Có lỗi xảy ra khi thêm người dùng!");
        }
      } finally {
        this.isSubmitting = false;
      }
    },
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
};
</script>

<style scoped>
/* Styles removed as they are handled by Modal component */
</style>
