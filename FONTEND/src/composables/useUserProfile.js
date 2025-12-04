import { ref, watch } from 'vue';
import { useToast } from 'vue-toastification';
import { profileService } from '../services/auth/profileService';
import authService from '../services/auth/authService';

export function useUserProfile(user) {
  const toast = useToast();

  const profileForm = ref({
    name: '',
    email: '',
    phone: '',
  });

  const profileErrors = ref({});
  const profileLoading = ref(false);

  const resetProfileForm = () => {
    if (!user.value) return;
    profileForm.value.name = user.value.name || '';
    profileForm.value.email = user.value.email || '';
    profileForm.value.phone = user.value.phone || '';
    profileErrors.value = {};
  };

  watch(user, (newUser) => {
    if (newUser) {
      resetProfileForm();
    }
  }, { immediate: true, deep: true });

  const updateProfile = async () => {
    if (profileLoading.value) return;

    profileLoading.value = true;
    profileErrors.value = {};

    try {
      await profileService.updateProfile({
        name: profileForm.value.name,
        email: profileForm.value.email,
        phone: profileForm.value.phone,
      });


      if (user.value) {
        user.value.name = profileForm.value.name;
        user.value.email = profileForm.value.email;
        user.value.phone = profileForm.value.phone;
      }


      if (authService.updateUser) {
        authService.updateUser({
          name: profileForm.value.name,
          email: profileForm.value.email,
          phone: profileForm.value.phone,
        });
      }

      toast.success('Đã cập nhật thông tin thành công');
      return true;
    } catch (error) {
      if (error.response?.status === 422) {
        profileErrors.value = error.response.data.errors;
      } else {
        toast.error('Cập nhật thất bại');
      }
      return false;
    } finally {
      profileLoading.value = false;
    }
  };

  return {
    profileForm,
    profileErrors,
    profileLoading,
    resetProfileForm,
    updateProfile,
  };
}