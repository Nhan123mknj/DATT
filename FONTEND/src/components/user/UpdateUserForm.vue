<template>
  <ModalForm
    :show="visible"
    title="Cập nhật người dùng"
    size="medium"
    @close="$emit('close')"
    @submit="handleSubmit"
  >
    <!-- Hiển thị lỗi chung -->
    <ul
      v-if="Object.keys(errors).length"
      class="mb-4 list-disc list-inside text-red-600 bg-red-100 p-3 rounded-md"
    >
      <li v-for="(error, key) in errors" :key="key">
        {{ Array.isArray(error) ? error[0] : error }}
      </li>
    </ul>

    <!-- Form Fields -->
    <div class="space-y-6">
      <!-- Tên tài khoản -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-900">
          Tên tài khoản <span class="text-red-500">*</span>
        </label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          class="mt-2 block w-full rounded-md border px-3 py-2 text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
        />
        <p v-if="errors.name" class="text-sm text-red-600 mt-1">
          {{ errors.name }}
        </p>
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
          class="mt-2 block w-full rounded-md border px-3 py-2 text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
        />
        <p v-if="errors.email" class="text-sm text-red-600 mt-1">
          {{ errors.email }}
        </p>
      </div>

      <!-- Role -->
      <div>
        <label for="role" class="block text-sm font-medium text-gray-900">
          Role <span class="text-red-500">*</span>
        </label>
        <select
          id="role"
          v-model="form.role"
          class="mt-2 w-full appearance-none rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900 border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option disabled value="">-- Chọn role --</option>
          <option value="admin">Admin</option>
          <option value="staff">Staff</option>
          <option value="borrower">Người mượn</option>
        </select>
        <p v-if="errors.role" class="text-sm text-red-600 mt-1">
          {{ errors.role }}
        </p>
      </div>

      <!-- Trạng thái -->
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
        <span v-else>Cập nhật</span>
      </button>
      <button
        type="button"
        @click="$emit('close')"
        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
      >
        Hủy
      </button>
    </template>
  </ModalForm>
</template>

<script>
import ModalForm from "../ModalForm.vue";
import { usersService } from "../../services/users/usersService";
import { useToast } from "vue-toastification";

export default {
  name: "UpdateUserForm",
  components: {
    ModalForm,
  },
  props: {
    visible: { type: Boolean, required: true },
    userData: { type: Object, default: null },
  },
  emits: ["close", "refresh"],
  data() {
    return {
      form: {
        id: "",
        name: "",
        email: "",
        role: "",
        is_active: 1,
      },
      errors: {},
      isSubmitting: false,
    };
  },
  watch: {
    userData: {
      immediate: true,
      handler(newUserData) {
        if (newUserData) {
          Object.assign(this.form, {
            id: newUserData.id,
            name: newUserData.name || "",
            email: newUserData.email || "",
            role: newUserData.role || "",
            is_active: newUserData.is_active ?? 1,
          });
        }
      },
    },
    form: {
      deep: true,
      handler() {
        this.errors = {};
      },
    },
  },
  methods: {
    validateForm() {
      this.errors = {};

      if (!this.form.name) {
        this.errors.name = "Tên tài khoản không được để trống";
      } else if (this.form.name.length < 3) {
        this.errors.name = "Tên tài khoản phải có ít nhất 3 ký tự";
      }

      if (!this.form.email) {
        this.errors.email = "Email không được để trống";
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
        this.errors.email = "Email không hợp lệ";
      }

      if (!this.form.role) {
        this.errors.role = "Vui lòng chọn role";
      }

      return Object.keys(this.errors).length === 0;
    },
    async handleSubmit() {
      if (!this.validateForm()) return;
      this.isSubmitting = true;
      try {
        await usersService.updateUser(this.form.id, this.form);
        this.toast.success("Cập nhật thành công!");
        this.$emit("refresh");
        this.$emit("close");
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors;
        } else {
          this.toast.error("Không thể cập nhật!");
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
