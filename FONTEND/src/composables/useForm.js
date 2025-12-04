import { ref, reactive } from 'vue';
import { useToast } from 'vue-toastification';

export function useForm(options = {}) {
    const {
        createData,
        updateData,
        rejectData,
        initialForm = {},
    } = options;

    const toast = useToast();
    const showModal = ref(false);
    const modalMode = ref('create');
    const errors = ref({});
    const form = reactive({ ...initialForm });

    const resetForm = () => {
        for (const key in initialForm) {
            form[key] = initialForm[key];
        }
        errors.value = {};
    };

    const openCreate = () => {
        modalMode.value = 'create';
        resetForm();
        showModal.value = true;
    };

    const openEdit = (item) => {
        modalMode.value = 'edit';
        resetForm();

        Object.assign(form, item);
        errors.value = {};
        showModal.value = true;
    };

    const openDetail = (item) => {
        modalMode.value = 'detail';
        resetForm();

        Object.assign(form, item);
        errors.value = {};
        showModal.value = true;
    };

    const openReject = (item) => {
        modalMode.value = 'reject';
        resetForm();

        Object.assign(form, item);
        // Ensure reason field is initialized if not present in item
        if (!form.reason) form.reason = '';
        errors.value = {};
        showModal.value = true;
    };

    const closeModal = () => {
        showModal.value = false;
        errors.value = {};
    };

    const save = async (successCallback) => {
        errors.value = {};
        try {
            if (modalMode.value === 'create') {
                await createData(form);
                toast.success("Thêm mới thành công");
            } else if (modalMode.value === 'edit') {
                await updateData(form.id, form);
                toast.success("Cập nhật thành công");
            } else if (modalMode.value === 'reject') {
                await rejectData(form.id, form);
                toast.success("Đã từ chối thành công");
            }
            closeModal();
            if (successCallback) successCallback();
            return true;
        } catch (error) {
            if (error.response?.status === 422) {
                errors.value = error.response.data.errors;
            } else {
                toast.error(error.response?.data?.message || "Có lỗi xảy ra");
            }
            return false;
        }
    };

    return {
        form,
        errors,
        showModal,
        modalMode,
        openCreate,
        openEdit,
        openDetail,
        openReject,
        closeModal,
        save,
        resetForm
    };
}
