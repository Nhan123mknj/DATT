<template>
  <transition name="fade">
    <div
      v-if="visible"
      class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"
    >
      <div
        class="bg-white rounded-xl shadow-lg w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]"
      >
        <!-- Header -->
        <div class="flex justify-between items-center border-b pb-3 mb-4">
          <h2 class="text-xl font-semibold text-gray-800">Thêm người dùng</h2>
          <button
            @click="handleClose"
            class="text-gray-500 hover:text-gray-700 text-xl"
          >
            ✕
          </button>
        </div>

        <!-- Hiển thị lỗi chung -->
        <ul
          v-if="Object.keys(errors).length"
          class="mb-4 list-disc list-inside text-red-600 bg-red-100 p-3 rounded-md"
        >
          <li v-for="(error, key) in errors" :key="key">
            {{ Array.isArray(error) ? error[0] : error }}
          </li>
        </ul>

        <!-- Form -->
        <form @submit.prevent="handleAddUser" class="space-y-6">
          <!-- Tên tài khoản -->
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

          <!-- Email -->
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

          <!-- Role -->
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
              <option value="borrower">Người mượn</option>
            </select>
          </div>

          <!-- Trạng thái -->
          <div>
            <label class="block text-sm font-medium text-gray-900">
              Trạng thái
            </label>
            <div class="flex items-center gap-6">
              <label class="flex items-center gap-2">
                <input
                  type="radio"
                  name="is_active"
                  :value="1"
                  v-model="form.is_active"
                />
                <span>Kích hoạt</span>
              </label>
              <label class="flex items-center gap-2">
                <input
                  type="radio"
                  name="is_active"
                  :value="0"
                  v-model="form.is_active"
                />
                <span>Chưa kích hoạt</span>
              </label>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex justify-end gap-3 border-t pt-4">
            <button
              type="button"
              @click="handleClose"
              class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium"
            >
              Hủy
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-2 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium"
            >
              <span v-if="isSubmitting">Đang xử lý...</span>
              <span v-else>Lưu</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </transition>
</template>

<script>
import { usersService } from "../../../services/usersService";
import { useToast } from "vue-toastification";

export default {
  name: "AddUserForm",
  props: {
    visible: { type: Boolean, required: true },
  },
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
      toast: useToast(),
    };
  },
  methods: {
    removeError(field) {
      if (this.errors[field]) delete this.errors[field];
    },

    async handleAddUser() {
      this.isSubmitting = true;
      try {
        await usersService.createUser(this.form);
        this.toast.success("Thêm người dùng thành công!");
        this.handleClose();
        this.resetForm();
        this.$emit("refresh"); // emit để cha reload danh sách
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

    handleClose() {
      this.resetForm();
      this.$emit("close");
    },

    resetForm() {
      this.form = { name: "", email: "", role: "", is_active: 1 };
      this.errors = {};
    },
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
