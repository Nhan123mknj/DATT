import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { profileService } from '../services/auth/profileService';

export function useUserPassword() {
  const router = useRouter();
  const toast = useToast();

  const passwordForm = ref({
    old_password: '',
    new_password: '',
    new_password_confirmation: '',
  });

  const passwordErrors = ref({});
  const passwordLoading = ref(false);

  const showCurrentPassword = ref(false);
  const showNewPassword = ref(false);
  const showConfirmPassword = ref(false);

  const resetPasswordForm = () => {
    passwordForm.value.old_password = '';
    passwordForm.value.new_password = '';
    passwordForm.value.new_password_confirmation = '';
    showCurrentPassword.value = false;
    showNewPassword.value = false;
    showConfirmPassword.value = false;
    passwordErrors.value = {};
  };

  const changePassword = async () => {
    if (passwordLoading.value) return;

    passwordLoading.value = true;
    passwordErrors.value = {};

    try {
      await profileService.changePassword({
        old_password: passwordForm.value.old_password,
        new_password: passwordForm.value.new_password,
        confirm_password: passwordForm.value.new_password_confirmation,
      });

      toast.success('Đã đổi mật khẩu thành công. Vui lòng đăng nhập lại.');
      resetPasswordForm();

      // Redirect to login after 2 seconds
      setTimeout(() => {
        router.push({ name: 'login' });
      }, 2000);

      return true;
    } catch (error) {
      if (error.response?.status === 422) {
        passwordErrors.value = error.response.data.errors;
      } else {
        toast.error(
          error.response?.data?.message || 'Đổi mật khẩu thất bại'
        );
      }
      return false;
    } finally {
      passwordLoading.value = false;
    }
  };

  return {
    passwordForm,
    passwordErrors,
    passwordLoading,
    showCurrentPassword,
    showNewPassword,
    showConfirmPassword,
    resetPasswordForm,
    changePassword,
  };
}
