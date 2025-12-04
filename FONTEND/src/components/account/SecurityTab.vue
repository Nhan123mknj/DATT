<template>
  <div v-if="activeTab === 'security'">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">
        Bảo mật tài khoản
      </h2>

      <div class="space-y-4">
        <!-- Last Login -->
        <div
          class="flex items-center justify-between p-5 bg-gray-50 rounded-lg border border-gray-200"
        >
          <div class="flex items-center gap-4">
            <span class="text-3xl">🕐</span>
            <div>
              <p class="text-sm font-semibold text-gray-900">
                Lần đăng nhập cuối cùng
              </p>
              <p class="text-sm text-gray-600 mt-1">
                {{ lastLoginDate }}
              </p>
            </div>
          </div>
        </div>

        <!-- Account Status -->
        <div
          class="flex items-center justify-between p-5 bg-green-50 rounded-lg border border-green-200"
        >
          <div class="flex items-center gap-4">
            <span class="text-3xl">✅</span>
            <div>
              <p class="text-sm font-semibold text-gray-900">
                Trạng thái tài khoản
              </p>
              <p class="text-sm text-green-600 mt-1">
                ✓ Tài khoản đang hoạt động
              </p>
            </div>
          </div>
        </div>

        <!-- Two Factor Auth -->
        <div
          class="flex items-center justify-between p-5 bg-gray-50 rounded-lg border border-gray-200"
        >
          <div class="flex items-center gap-4">
            <span class="text-3xl">🔐</span>
            <div>
              <p class="text-sm font-semibold text-gray-900">
                Xác thực hai yếu tố
              </p>
              <p class="text-sm text-gray-600 mt-1">
                Bảo vệ tài khoản bằng 2FA
              </p>
            </div>
          </div>
          <button
            class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
            disabled
          >
            Sắp có
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { user } from "../../services/auth/authService";
const props = defineProps({
  lastLoginDate: String,
  activeTab: String,
});
</script>
