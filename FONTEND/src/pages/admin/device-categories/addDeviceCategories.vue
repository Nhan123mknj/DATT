<template>
  <div
    class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
  >
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">
        Thêm mới danh mục thiết bị
      </h1>
    </div>
    <div class="flex gap-3">
      <button
        class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 flex items-center"
      >
        ← Trở lại danh sách
      </button>
    </div>
  </div>

  <!-- Form -->
  <div
    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6 m-6"
  >
    <form class="space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2"
          >Tên danh mục thiết bị *</label
        >
        <input
          type="text"
          class="w-full px-3 py-2 rounded-lg border border-gray-200"
          placeholder="Nhập tên danh mục thiết bị..."
        />
      </div>
      <div>
        <div class="space-y-4">
          <label class="block text-sm font-medium text-gray-700 mb-2"
            >Ghi chú</label
          >
          <textarea
            rows="3"
            class="w-full px-4 py-2.5 rounded-lg border border-gray-300"
            placeholder="Nhập ghi chú..."
          ></textarea>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >File cam kết</label
            >
            <input
              type="file"
              class="block w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
        </div>
      </div>
      <!-- Nút gửi -->
      <div
        class="flex items-center justify-between pt-4 border-t border-gray-100"
      >
        <div class="flex gap-3">
          <button
            type="button"
            class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium"
          >
            Hủy
          </button>
          <button
            type="submit"
            class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium"
          >
            Gửi yêu cầu
          </button>
        </div>
      </div>
    </form>
  </div>
</template>
<script>
import { deviceCategoriesService } from "../../../services/devices/deviceCategoriesService";
import { useToast } from "vue-toastification";
import { useRouter } from "vue-router";

export default {
  name: "AddDeviceCategory",
  data() {
    return {
      isSubmitting: false,
      form: {
        name: "",
        description: "",
        is_active: true,
      },
    };
  },
  methods: {
    goBack() {
      this.router.push({ name: "admin.deviceCategories" });
    },
    async handleSubmit() {
      if (!this.form.name) {
        this.toast.error("Vui lòng nhập tên danh mục");
        return;
      }

      this.isSubmitting = true;
      try {
        await deviceCategoriesService.create(this.form);
        this.toast.success("Thêm danh mục thành công");
        this.goBack();
      } catch (error) {
        this.toast.error(error.response?.data?.message || "Có lỗi xảy ra");
      } finally {
        this.isSubmitting = false;
      }
    },
  },
  setup() {
    const toast = useToast();
    const router = useRouter();
    return { toast, router };
  },
};
</script>
