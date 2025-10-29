import { useToast } from 'vue-toastification';

/**
 * Utility function để hiển thị confirm dialog với toast
 * @param {string} title - Tiêu đề dialog
 * @param {string} message - Nội dung thông báo
 * @param {string} type - Loại toast (success, error, warning, info)
 * @returns {Promise<boolean>}
 */
export const showConfirmDialog = (title, message, type = 'warning') => {
  return new Promise((resolve) => {

    const cleanMessage = message.replace(/<[^>]*>/g, '');
    

    const result = confirm(`${title}\n\n${cleanMessage}`);
    resolve(result);
  });
};

/**
 * Enhanced confirm với toast notification
 */
export const useConfirmDialog = () => {
  const toast = useToast();

  const confirm = async (title, message, options = {}) => {
    const {
      type = 'warning',
      confirmText = 'Đồng ý',
      cancelText = 'Hủy'
    } = options;

    return new Promise((resolve) => {
      const result = window.confirm(`${title}\n\n${message}`);
      resolve(result);
    });
  };

  return { confirm };
};
