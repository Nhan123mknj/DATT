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
          <h2 class="text-xl font-semibold text-gray-800">
            Cập nhật người dùng
          </h2>
          <button
            @click="$emit('close')"
            class="text-gray-500 hover:text-gray-700 text-xl"
          >
            ✕
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-900">
              Tên tài khoản <span class="text-red-500">*</span>
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              :class="[
                'mt-2 block w-full rounded-md border px-3 py-2 text-sm',
                errors.name
                  ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                  : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500',
              ]"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-500">
              {{ errors.name }}
            </p>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-900">
              Email <span class="text-red-500">*</span>
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              :class="[
                'mt-2 block w-full rounded-md border px-3 py-2 text-sm',
                errors.email
                  ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                  : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500',
              ]"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-500">
              {{ errors.email }}
            </p>
          </div>

          <div>
            <label for="role" class="block text-sm/6 font-medium text-gray-900">
              Role <span class="text-red-500">*</span>
            </label>
            <div class="mt-2 grid grid-cols-1">
              <select
                id="role"
                v-model="form.role"
                :class="[
                  'col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900',
                  errors.role
                    ? 'outline-red-500'
                    : 'outline-gray-300 focus:outline-indigo-600',
                ]"
              >
                <option disabled value="">-- Chọn role --</option>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="borrower">Người mượn</option>
              </select>
              <p v-if="errors.role" class="mt-1 text-sm text-red-500">
                {{ errors.role }}
              </p>
            </div>
          </div>

          <div>
            <label class="block text-sm/6 font-medium text-gray-900">
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

          <!-- Footer buttons -->
          <div class="flex justify-end gap-3 border-t pt-4">
            <button
              type="button"
              @click="$emit('close')"
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
              <span v-else>Cập nhật</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: "EditUserModal",

  props: {
    visible: { type: Boolean, required: true },
    isSubmitting: { type: Boolean, default: false },
    serverErrors: { type: Object, default: () => ({}) },
    userData: { type: Object, required: true },
  },

  emits: ["close", "submit"],

  data() {
    return {
      form: {
        name: "",
        email: "",
        role: "",
        is_active: 1,
      },
      errors: {},
    };
  },

  watch: {
    userData: {
      handler(newUserData) {
        if (newUserData) {
          this.form = {
            name: newUserData.name || "",
            email: newUserData.email || "",
            role: newUserData.role || "",
            is_active: newUserData.is_active ?? 1,
          };
        }
      },
      immediate: true,
    },
    serverErrors: {
      handler(newErrors) {
        this.errors = { ...newErrors };
      },
      deep: true,
    },
    form: {
      handler() {
        this.errors = {};
      },
      deep: true,
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

    handleSubmit() {
      if (!this.validateForm()) return;
      this.$emit("submit", { ...this.form });
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
