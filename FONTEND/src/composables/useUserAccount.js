import { computed } from 'vue';
import { useToast } from 'vue-toastification';
import { useAuthStore } from '../stores/authStore';

export function useUserAccount() {
  const toast = useToast();
  const authStore = useAuthStore();
  
  const currentUser = computed(() => authStore.user);
  
  const isLoading = computed(() => !currentUser.value);

  const lastLoginDate = computed(() => {
    return currentUser.value?.last_login_at
      ? new Date(currentUser.value.last_login_at).toLocaleDateString('vi-VN')
      : 'Chưa cập nhật';
  });

  const loadUserData = async () => {
    try {
      await authStore.verifyToken();
    } catch (error) {
      toast.error('Không thể tải thông tin tài khoản');
      console.error(error);
    }
  };

  return {
    currentUser,
    isLoading,
    lastLoginDate,
    loadUserData,
  };
}
