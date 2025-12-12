<template>
  <div v-if="activeTab === 'password'">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Đổi mật khẩu</h2>

      <form @submit.prevent="$emit('change-password')">
        <div class="space-y-5">
          <!-- Current Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Mật khẩu hiện tại <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                v-model="passwordForm.old_password"
                :type="showCurrentPassword ? 'text' : 'password'"
                class="w-full px-4 py-2.5 pr-10 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                placeholder="Nhập mật khẩu hiện tại"
              />
              <button
                type="button"
                @click="showCurrentPassword = !showCurrentPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <span v-if="showCurrentPassword">👁️</span>
                <span v-else>🔒</span>
              </button>
            </div>
            <p
              v-if="passwordErrors.old_password"
              class="text-xs text-red-500 mt-1"
            >
              {{
                passwordErrors.old_password?.[0] || passwordErrors.old_password
              }}
            </p>
          </div>

          <!-- New Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Mật khẩu mới <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                v-model="passwordForm.new_password"
                :type="showNewPassword ? 'text' : 'password'"
                class="w-full px-4 py-2.5 pr-10 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                placeholder="Nhập mật khẩu mới (ít nhất 8 ký tự)"
              />
              <button
                type="button"
                @click="showNewPassword = !showNewPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <span v-if="showNewPassword">👁️</span>
                <span v-else>🔒</span>
              </button>
            </div>
            <p
              v-if="passwordErrors.new_password"
              class="text-xs text-red-500 mt-1"
            >
              {{
                passwordErrors.new_password?.[0] || passwordErrors.new_password
              }}
            </p>
            <div class="mt-2 text-xs text-gray-500 space-y-1">
              <p>✓ Ít nhất 8 ký tự</p>
              <p>✓ Gồm chữ hoa, chữ thường, số</p>
            </div>
          </div>

          <!-- Confirm Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Xác nhận mật khẩu <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                v-model="passwordForm.new_password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                class="w-full px-4 py-2.5 pr-10 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                placeholder="Nhập lại mật khẩu mới"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <span v-if="showConfirmPassword">👁️</span>
                <span v-else>🔒</span>
              </button>
            </div>
            <p
              v-if="passwordErrors.confirm_password"
              class="text-xs text-red-500 mt-1"
            >
              {{
                passwordErrors.confirm_password?.[0] ||
                passwordErrors.confirm_password
              }}
            </p>
          </div>

          <!-- Info Box -->
          <div
            class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-3"
          >
            <span class="text-xl">💡</span>
            <p class="text-sm text-blue-700">
              Sau khi đổi mật khẩu, bạn sẽ cần đăng nhập lại.
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
          <button
            type="submit"
            class="px-6 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="passwordLoading"
          >
            <span v-if="!passwordLoading">Đổi mật khẩu</span>
            <span v-else class="flex items-center gap-2">
              <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              Đang xử lý...
            </span>
          </button>
          <button
            type="button"
            @click="$emit('reset-form')"
            class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition"
          >
            Hủy
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: "PasswordTab",
  props: {
    activeTab: String,
    passwordForm: Object,
    passwordErrors: Object,
    passwordLoading: Boolean,
  },
  emits: ["change-password", "reset-form"],
  data() {
    return {
      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
    };
  },
};
</script>
