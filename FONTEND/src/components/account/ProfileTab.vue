<template>
  <div v-if="activeTab === 'profile'">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">
        Thông tin cá nhân
      </h2>

      <form @submit.prevent="$emit('update-profile')">
        <div class="space-y-5">
          <!-- Full Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tên đầy đủ <span class="text-red-500">*</span>
            </label>
            <input
              v-model="profileForm.name"
              type="text"
              class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
              placeholder="Nhập tên đầy đủ"
            />
            <p v-if="profileErrors.name" class="text-xs text-red-500 mt-1">
              {{ profileErrors.name?.[0] || profileErrors.name }}
            </p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Email <span class="text-red-500">*</span>
            </label>
            <input
              v-model="profileForm.email"
              type="email"
              class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
              placeholder="Nhập email"
            />
            <p v-if="profileErrors.email" class="text-xs text-red-500 mt-1">
              {{ profileErrors.email?.[0] || profileErrors.email }}
            </p>
          </div>

          <!-- Student Code or Teacher Code -->
          <div
            v-if="
              currentUser.student?.student_code ||
              currentUser.teacher?.teacher_code
            "
          >
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ currentUser.student ? "Mã học sinh" : "Mã giáo viên" }}
            </label>
            <input
              type="text"
              :value="
                currentUser.student?.student_code ||
                currentUser.teacher?.teacher_code
              "
              disabled
              class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-500"
            />
            <p class="text-xs text-gray-500 mt-1">Liên hệ admin để thay đổi</p>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Số điện thoại
            </label>
            <input
              v-model="profileForm.phone"
              type="tel"
              class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
              placeholder="Nhập số điện thoại"
            />
            <p v-if="profileErrors.phone" class="text-xs text-red-500 mt-1">
              {{ profileErrors.phone?.[0] || profileErrors.phone }}
            </p>
          </div>

          <!-- Department -->
          <div v-if="currentUser.department">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Phòng ban
            </label>
            <input
              type="text"
              :value="currentUser.department"
              disabled
              class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-500"
            />
            <p class="text-xs text-gray-500 mt-1">Liên hệ admin để thay đổi</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
          <button
            type="submit"
            class="px-6 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="profileLoading"
          >
            <span v-if="!profileLoading">Lưu thay đổi</span>
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
              Đang lưu...
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
  name: "ProfileTab",
  props: {
    activeTab: String,
    profileForm: Object,
    profileErrors: Object,
    profileLoading: Boolean,
    currentUser: {
      type: Object,
      default: () => ({}),
    },
  },
  emits: ["update-profile", "reset-form"],
};
</script>
