import { computed } from 'vue';
import { useToast } from 'vue-toastification';
import authService, { user } from '../services/auth/authService';

export function useUserAccount() {
  const toast = useToast();
  
  const currentUser = user;
  
  const isLoading = computed(() => !currentUser.value);

  const lastLoginDate = computed(() => {
    return currentUser.value?.last_login_at
      ? new Date(currentUser.value.last_login_at).toLocaleDateString('vi-VN')
      : 'Chưa cập nhật';
  });

  const loadUserData = async () => {
    try {
      await authService.verifyToken();
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
