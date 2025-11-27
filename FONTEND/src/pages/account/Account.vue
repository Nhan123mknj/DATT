<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Tài khoản của tôi</h1>
        <p class="mt-2 text-sm text-gray-600">
          Quản lý thông tin cá nhân và bảo mật
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1">
          <div
            class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
          >
            <!-- User Info Card -->
            <div class="p-6 bg-gradient-to-br from-indigo-500 to-blue-600">
              <div class="flex flex-col items-center text-center">
                <div class="relative mb-3">
                  <div
                    v-if="currentAvatar"
                    class="w-24 h-24 rounded-full ring-4 ring-white/40 overflow-hidden bg-white/20"
                  >
                    <img
                      :src="currentAvatar"
                      alt="Ảnh đại diện"
                      class="w-full h-full object-cover"
                    />
                  </div>
                  <div
                    v-else
                    class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white text-2xl font-bold ring-4 ring-white/30"
                  >
                    {{ getInitials(user.name) }}
                  </div>
                  <button
                    type="button"
                    class="absolute bottom-1 right-1 w-9 h-9 rounded-full bg-white text-indigo-600 shadow-lg flex items-center justify-center text-lg hover:bg-indigo-50 transition disabled:opacity-50"
                    @click="triggerAvatarPicker"
                    :disabled="avatarUploading"
                    title="Cập nhật ảnh đại diện"
                  >
                    <svg
                      v-if="avatarUploading"
                      class="animate-spin h-4 w-4"
                      viewBox="0 0 24 24"
                    >
                      <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                      />
                      <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                      />
                    </svg>
                    <span v-else
                      ><font-awesome-icon icon="fa-solid fa-plus" />
                    </span>
                  </button>
                  <input
                    ref="avatarInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleAvatarChange"
                  />
                </div>
                <p v-if="avatarError" class="text-xs text-red-100 mb-2">
                  {{ avatarError }}
                </p>
                <p class="text-[11px] text-indigo-100 mb-2">
                  PNG, JPG, WEBP • tối đa 5MB
                </p>
                <h3 class="text-lg font-semibold text-white">
                  {{ user.name }}
                </h3>
                <p class="text-sm text-indigo-100 mt-1">
                  {{ roleLabel(user.role) }}
                </p>
              </div>
            </div>

            <!-- Navigation Tabs -->
            <nav class="p-2" aria-label="Tabs">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  activeTab === tab.id
                    ? 'bg-indigo-50 text-indigo-600 font-semibold'
                    : 'text-gray-700 hover:bg-gray-50',
                  'w-full flex items-center gap-3 px-4 py-3 rounded-lg transition text-left text-sm',
                ]"
              >
                <span class="text-xl">{{ tab.icon }}</span>
                <span>{{ tab.label }}</span>
              </button>
            </nav>
          </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-3">
          <!-- Profile Tab -->
          <div v-if="activeTab === 'profile'">
            <div
              class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
            >
              <h2 class="text-xl font-semibold text-gray-900 mb-6">
                Thông tin cá nhân
              </h2>

              <form @submit.prevent="updateProfile">
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
                    <p
                      v-if="profileErrors.name"
                      class="text-xs text-red-500 mt-1"
                    >
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
                    <p
                      v-if="profileErrors.email"
                      class="text-xs text-red-500 mt-1"
                    >
                      {{ profileErrors.email?.[0] || profileErrors.email }}
                    </p>
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
                    <p
                      v-if="profileErrors.phone"
                      class="text-xs text-red-500 mt-1"
                    >
                      {{ profileErrors.phone?.[0] || profileErrors.phone }}
                    </p>
                  </div>

                  <!-- Department -->
                  <div v-if="user.department">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Phòng ban
                    </label>
                    <input
                      type="text"
                      :value="user.department"
                      disabled
                      class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-500"
                    />
                    <p class="text-xs text-gray-500 mt-1">
                      Liên hệ admin để thay đổi
                    </p>
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
                      <svg
                        class="animate-spin h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
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
                    @click="resetProfileForm"
                    class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition"
                  >
                    Hủy
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Password Tab -->
          <div v-if="activeTab === 'password'">
            <div
              class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
            >
              <h2 class="text-xl font-semibold text-gray-900 mb-6">
                Đổi mật khẩu
              </h2>

              <form @submit.prevent="changePassword">
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
                        passwordErrors.old_password?.[0] ||
                        passwordErrors.old_password
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
                        passwordErrors.new_password?.[0] ||
                        passwordErrors.new_password
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
                      v-if="passwordErrors.new_password_confirmation"
                      class="text-xs text-red-500 mt-1"
                    >
                      {{
                        passwordErrors.new_password_confirmation?.[0] ||
                        passwordErrors.new_password_confirmation
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
                      <svg
                        class="animate-spin h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
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
                    @click="resetPasswordForm"
                    class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition"
                  >
                    Hủy
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Security Tab -->
          <div v-if="activeTab === 'security'">
            <div
              class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
            >
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
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { userService } from "../../services/user/userService";

const router = useRouter();
const toast = useToast();

const tabs = [
  { id: "profile", label: "Thông tin cá nhân", icon: "👤" },
  { id: "password", label: "Đổi mật khẩu", icon: "🔑" },
  { id: "security", label: "Bảo mật", icon: "🛡️" },
];

const activeTab = ref("profile");

// Loading state
const isLoading = ref(true);

// User data
const user = ref({
  name: "",
  email: "",
  phone: "",
  role: "borrower",
  department: "",
  avatar: "",
  avatar_url: "",
});

// Profile form
const profileForm = reactive({
  name: "",
  email: "",
  phone: "",
});
const profileErrors = reactive({});
const profileLoading = ref(false);

// Password form
const passwordForm = reactive({
  old_password: "",
  new_password: "",
  new_password_confirmation: "",
});
const passwordErrors = reactive({});
const passwordLoading = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Avatar upload
const avatarInput = ref(null);
const avatarPreview = ref("");
const avatarUploading = ref(false);
const avatarError = ref("");
const currentAvatar = computed(
  () => avatarPreview.value || user.value.avatar_url || user.value.avatar
);

const revokeAvatarPreview = () => {
  if (avatarPreview.value) {
    URL.revokeObjectURL(avatarPreview.value);
    avatarPreview.value = "";
  }
};

const triggerAvatarPicker = () => {
  if (avatarUploading.value) return;
  avatarError.value = "";
  avatarInput.value?.click();
};

const uploadAvatarFile = async (file) => {
  avatarUploading.value = true;
  try {
    const { data } = await userService.uploadAvatar(file);

    if (data.avatar_url) {
      user.value.avatar_url = data.avatar_url;
    }
    if (data.user) {
      user.value = { ...user.value, ...data.user };
    }

    toast.success("Ảnh đại diện đã được cập nhật");
  } catch (error) {
    avatarError.value =
      error.response?.data?.message || "Upload ảnh thất bại. Vui lòng thử lại.";
  } finally {
    avatarUploading.value = false;
    revokeAvatarPreview();
  }
};

const handleAvatarChange = async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  avatarError.value = "";

  if (!file.type.startsWith("image/")) {
    avatarError.value = "Chỉ hỗ trợ định dạng ảnh.";
    event.target.value = "";
    return;
  }

  const maxSize = 5 * 1024 * 1024; // 5MB
  if (file.size > maxSize) {
    avatarError.value = "Ảnh không được vượt quá 5MB.";
    event.target.value = "";
    return;
  }

  revokeAvatarPreview();
  avatarPreview.value = URL.createObjectURL(file);

  try {
    await uploadAvatarFile(file);
  } catch (error) {
    // handled in uploadAvatarFile
  } finally {
    event.target.value = "";
  }
};

// Security
const lastLoginDate = ref("Chưa cập nhật");

// Methods
const getInitials = (name) => {
  if (!name) return "U";
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
};

const roleLabel = (role) => {
  const roleMap = {
    admin: "Quản trị viên",
    staff: "Nhân viên",
    borrower: "Người mượn",
  };
  return roleMap[role] || role;
};

const loadUserData = async () => {
  try {
    const { data } = await userService.getProfile();
    user.value = data.data;
    revokeAvatarPreview();
    resetProfileForm();
    lastLoginDate.value = user.value.last_login_at
      ? new Date(user.value.last_login_at).toLocaleDateString("vi-VN")
      : "Chưa cập nhật";
  } catch (error) {
    toast.error("Không thể tải thông tin tài khoản");
  } finally {
    isLoading.value = false;
  }
};

const resetProfileForm = () => {
  profileForm.name = user.value.name;
  profileForm.email = user.value.email;
  profileForm.phone = user.value.phone;
  Object.keys(profileErrors).forEach((key) => delete profileErrors[key]);
};

const updateProfile = async () => {
  if (profileLoading.value) return;

  profileLoading.value = true;
  Object.keys(profileErrors).forEach((key) => delete profileErrors[key]);

  try {
    await userService.updateProfile({
      name: profileForm.name,
      email: profileForm.email,
      phone: profileForm.phone,
    });

    user.value.name = profileForm.name;
    user.value.email = profileForm.email;
    user.value.phone = profileForm.phone;

    toast.success("Đã cập nhật thông tin thành công");
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(profileErrors, error.response.data.errors);
    } else {
      toast.error("Cập nhật thất bại");
    }
  } finally {
    profileLoading.value = false;
  }
};

const resetPasswordForm = () => {
  passwordForm.old_password = "";
  passwordForm.new_password = "";
  passwordForm.new_password_confirmation = "";
  showCurrentPassword.value = false;
  showNewPassword.value = false;
  showConfirmPassword.value = false;
  Object.keys(passwordErrors).forEach((key) => delete passwordErrors[key]);
};

const changePassword = async () => {
  if (passwordLoading.value) return;

  passwordLoading.value = true;
  Object.keys(passwordErrors).forEach((key) => delete passwordErrors[key]);

  try {
    await userService.changePassword({
      old_password: passwordForm.old_password,
      new_password: passwordForm.new_password,
      new_password_confirmation: passwordForm.new_password_confirmation,
    });

    toast.success("Đã đổi mật khẩu thành công. Vui lòng đăng nhập lại.");
    resetPasswordForm();

    // Redirect to login after 2 seconds
    setTimeout(() => {
      router.push({ name: "login" });
    }, 2000);
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(passwordErrors, error.response.data.errors);
    } else {
      toast.error(error.response?.data?.message || "Đổi mật khẩu thất bại");
    }
  } finally {
    passwordLoading.value = false;
  }
};

onMounted(() => {
  loadUserData();
});

onBeforeUnmount(() => {
  revokeAvatarPreview();
});
</script>
