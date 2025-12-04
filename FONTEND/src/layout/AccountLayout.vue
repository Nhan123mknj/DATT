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
        <SidebarNavigation
          :active-tab="activeTab"
          :tabs="tabs"
          @update:activeTab="handleTabChange"
        />

        <div class="lg:col-span-3">
          <ProfileTab
            :current-user="currentUser"
            :active-tab="activeTab"
            :profile-form="profileForm"
            :profile-errors="profileErrors"
            :profile-loading="profileLoading"
            @update-profile="updateProfile"
            @reset-form="resetProfileForm"
          />

          <PasswordTab
            :active-tab="activeTab"
            :password-form="passwordForm"
            :password-errors="passwordErrors"
            :password-loading="passwordLoading"
            @change-password="changePassword"
            @reset-form="resetPasswordForm"
          />

          <SecurityTab
            :active-tab="activeTab"
            :last-login-date="lastLoginDate"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import SidebarNavigation from "../components/account/SidebarNavigation.vue";
import ProfileTab from "../components/account/ProfileTab.vue";
import PasswordTab from "../components/account/PasswordTab.vue";
import SecurityTab from "../components/account/SecurityTab.vue";

import { useUserAccount } from "../composables/useUserAccount";
import { useUserProfile } from "../composables/useUserProfile";
import { useUserPassword } from "../composables/useUserPassword";

export default {
  name: "AccountLayout",
  components: {
    SidebarNavigation,
    ProfileTab,
    PasswordTab,
    SecurityTab,
  },
  setup() {
    const tabs = [
      { id: "profile", label: "Thông tin cá nhân", icon: "👤" },
      { id: "password", label: "Đổi mật khẩu", icon: "🔑" },
      { id: "security", label: "Bảo mật", icon: "🛡️" },
    ];
    const activeTab = ref("profile");

    const handleTabChange = (tabId) => {
      activeTab.value = tabId;
    };

    const { currentUser, lastLoginDate, loadUserData } = useUserAccount();

    const {
      profileForm,
      profileErrors,
      profileLoading,
      updateProfile,
      resetProfileForm,
    } = useUserProfile(currentUser);

    const {
      passwordForm,
      passwordErrors,
      passwordLoading,
      changePassword,
      resetPasswordForm,
    } = useUserPassword();

    onMounted(() => {
      loadUserData();
    });

    return {
      tabs,
      activeTab,
      handleTabChange,
      currentUser,
      lastLoginDate,
      profileForm,
      profileErrors,
      profileLoading,
      updateProfile,
      resetProfileForm,
      passwordForm,
      passwordErrors,
      passwordLoading,
      changePassword,
      resetPasswordForm,
    };
  },
};
</script>
