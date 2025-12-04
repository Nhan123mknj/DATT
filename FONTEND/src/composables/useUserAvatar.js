import { ref, computed, onBeforeUnmount } from 'vue';
import { useToast } from 'vue-toastification';
import { profileService } from '../services/auth/profileService';
import authService from '../services/auth/authService';

export function useUserAvatar(user) {
  const toast = useToast();

  const avatarPreview = ref('');
  const avatarUploading = ref(false);
  const avatarError = ref('');
  const avatarInputRef = ref(null);

  const currentAvatar = computed(() => {
    return avatarPreview.value || user.value.avatar_url || user.value.avatar;
  });


  const revokeAvatarPreview = () => {
    if (avatarPreview.value) {
      URL.revokeObjectURL(avatarPreview.value);
      avatarPreview.value = '';
    }
  };

  const triggerAvatarPicker = () => {
    if (avatarUploading.value) return;
    avatarError.value = '';
    avatarInputRef.value?.click();
  };

  const uploadAvatarFile = async (file) => {
    avatarUploading.value = true;
    try {
      const { data } = await profileService.uploadAvatar(file);

      const responseData = data.data || data;

      if (responseData.avatar_url) {
        user.value.avatar_url = responseData.avatar_url;
      }
      if (responseData.user) {
        user.value = { ...user.value, ...responseData.user };
      }

      toast.success('Ảnh đại diện đã được cập nhật');
      return true;
    } catch (error) {
      avatarError.value =
        error.response?.data?.message ||
        'Upload ảnh thất bại. Vui lòng thử lại.';
      return false;
    } finally {
      avatarUploading.value = false;
      revokeAvatarPreview();
    }
  };

  const handleAvatarChange = async (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    avatarError.value = '';

    if (!file.type.startsWith('image/')) {
      avatarError.value = 'Chỉ hỗ trợ định dạng ảnh.';
      event.target.value = '';
      return;
    }

    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
      avatarError.value = 'Ảnh không được vượt quá 5MB.';
      event.target.value = '';
      return;
    }

    revokeAvatarPreview();
    avatarPreview.value = URL.createObjectURL(file);

    try {
      await uploadAvatarFile(file);
    } catch (error) {
      avatarError.value =
        error.response?.data?.message ||
        'Upload ảnh thất bại. Vui lòng thử lại.';
    } finally {
      event.target.value = '';
    }
  };

  onBeforeUnmount(() => {
    revokeAvatarPreview();
  });

  return {
    avatarPreview,
    avatarUploading,
    avatarError,
    avatarInputRef,
    currentAvatar,
    triggerAvatarPicker,
    handleAvatarChange,
    revokeAvatarPreview,

  };
}
