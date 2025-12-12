<template>
  <Modal :show="show" @close="$emit('close')" size="2xl">
    <template #header>
      <h3 class="text-xl font-semibold text-gray-900">Xử Lý Trả Thiết Bị</h3>
      <p class="text-sm text-gray-500 mt-1">
        Phiếu mượn #{{ borrow?.id }} - {{ borrow?.borrower?.name }}
      </p>
    </template>

    <form @submit.prevent="submitReturn" class="space-y-6">
      <!-- Device List -->
      <div class="space-y-4">
        <h4 class="font-medium text-gray-900">Danh Sách Thiết Bị:</h4>

        <div
          v-for="(item, index) in returnItems"
          :key="item.device_unit_id"
          class="border border-gray-200 rounded-lg p-4 space-y-4 bg-gray-50"
        >
          <!-- Device Info -->
          <div class="flex justify-between items-start">
            <div>
              <p class="font-medium text-gray-900">{{ item.device_name }}</p>
              <p class="text-sm text-gray-500">
                Serial: {{ item.serial_number }}
              </p>
              <p class="text-xs text-gray-400">
                Tình trạng mượn:
                <span class="font-medium">{{ item.condition_at_borrow }}</span>
              </p>
            </div>
          </div>

          <!-- Condition Selector -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tình Trạng Trả: <span class="text-red-500">*</span>
            </label>
            <select
              v-model="item.condition_at_return"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              required
            >
              <option value="">-- Chọn tình trạng --</option>
              <option value="excellent">Xuất sắc</option>
              <option value="good">Tốt</option>
              <option value="fair">Khá</option>
              <option value="damaged">Hư hỏng nhẹ</option>
              <option value="broken">Hư hỏng nặng</option>
            </select>
          </div>

          <!-- Damage Notes (conditional) -->
          <div v-if="['damaged', 'broken'].includes(item.condition_at_return)">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Mô Tả Hư Hỏng: <span class="text-red-500">*</span>
            </label>
            
          </div>

          <!-- Photo Upload -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Hình Ảnh Bằng Chứng: (Tối đa 5 ảnh)
            </label>
            <input
              type="file"
              @change="handlePhotoUpload($event, index)"
              accept="image/*"
              multiple
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
            />
            <p class="text-xs text-gray-500 mt-1">
              Tải lên tối đa 5 ảnh, mỗi ảnh &lt; 5MB
            </p>

            <!-- Photo Previews -->
            <div v-if="item.photos?.length" class="flex gap-2 mt-3 flex-wrap">
              <div
                v-for="(photo, pIndex) in item.photos"
                :key="pIndex"
                class="relative group"
              >
                <img
                  :src="photo.preview"
                  class="w-20 h-20 object-cover rounded-lg border-2 border-gray-200"
                  alt="Preview"
                />
                <button
                  type="button"
                  @click="removePhoto(index, pIndex)"
                  class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  ×
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- General Notes -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Ghi Chú Chung:
        </label>
        <textarea
          v-model="notes"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          rows="3"
          placeholder="Nhập ghi chú chung về việc trả thiết bị..."
        ></textarea>
      </div>

      <!-- Signatures -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Chữ Ký Người Trả: <span class="text-red-500">*</span>
          </label>
          <SignaturePad ref="borrowerSignature" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Chữ Ký Nhân Viên: <span class="text-red-500">*</span>
          </label>
          <SignaturePad ref="staffSignature" />
        </div>
      </div>
    </form>

    <template #footer>
      <div class="flex justify-end gap-3">
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          :disabled="loading"
        >
          Hủy
        </button>
        <button
          type="submit"
          @click="submitReturn"
          :disabled="loading"
          class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        >
          <span
            v-if="loading"
            class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"
          ></span>
          <span>{{ loading ? "Đang xử lý..." : "Xác Nhận Trả" }}</span>
        </button>
      </div>
    </template>
  </Modal>
</template>

<script>
import Modal from "../Modal.vue";
import SignaturePad from "../common/SignaturePad.vue";
import { staffBorrowService } from "../../services/borrows/staffBorrowService";

export default {
  name: "ReturnModal",

  components: {
    Modal,
    SignaturePad,
  },

  props: {
    show: Boolean,
    borrow: Object,
  },

  emits: ["close", "success"],

  data() {
    return {
      returnItems: [],
      notes: "",
      loading: false,
    };
  },

  watch: {
    borrow: {
      immediate: true,
      handler(val) {
        if (val) {
          this.returnItems = val.details.map((detail) => ({
            device_unit_id: detail.device_unit_id,
            device_name: detail.device_unit?.device?.name || "N/A",
            serial_number: detail.device_unit?.serial_number || "N/A",
            condition_at_borrow: detail.condition_at_borrow || "good",
            condition_at_return: "",
            photos: [],
          }));
        }
      },
    },
  },

  methods: {
    handlePhotoUpload(event, index) {
      const files = Array.from(event.target.files).slice(0, 5);

      // Validate file size
      const maxSize = 5 * 1024 * 1024; // 5MB
      const validFiles = files.filter((file) => {
        if (file.size > maxSize) {
          this.$toast.warning(`File ${file.name} vượt quá 5MB`);
          return false;
        }
        return true;
      });

      this.returnItems[index].photos = validFiles.map((file) => ({
        file,
        preview: URL.createObjectURL(file),
      }));
    },

    removePhoto(itemIndex, photoIndex) {
      this.returnItems[itemIndex].photos.splice(photoIndex, 1);
    },

    async submitReturn() {
      // Validate signatures
      if (!this.$refs.borrowerSignature.hasData()) {
        this.$toast.error("Vui lòng lấy chữ ký người trả");
        return;
      }

      if (!this.$refs.staffSignature.hasData()) {
        this.$toast.error("Vui lòng ký xác nhận");
        return;
      }

      // Validate conditions
      const invalidItems = this.returnItems.filter(
        (item) => !item.condition_at_return
      );
      if (invalidItems.length > 0) {
        this.$toast.error("Vui lòng chọn tình trạng cho tất cả thiết bị");
        return;
      }


      this.loading = true;

      try {
        const formData = new FormData();

        // Add return items
        this.returnItems.forEach((item, index) => {
          formData.append(
            `return_items[${index}][device_unit_id]`,
            item.device_unit_id
          );
          formData.append(
            `return_items[${index}][condition_at_return]`,
            item.condition_at_return
          );

          // Add photos
          item.photos?.forEach((photo, pIndex) => {
            formData.append(
              `return_items[${index}][photos][${pIndex}]`,
              photo.file
            );
          });
        });

        // Add signatures
        const staffSignature = this.$refs.staffSignature.getData();
        const borrowerSignature = this.$refs.borrowerSignature.getData();

        formData.append("signatures[staff]", staffSignature);
        formData.append("signatures[borrower]", borrowerSignature);

        // Add notes
        if (this.notes) {
          formData.append("notes", this.notes);
        }

        // Submit
        const response = await staffBorrowService.processReturn(
          this.borrow.id,
          formData
        );

        this.$toast.success("Đã xử lý trả thiết bị thành công");

        // Show PDF URL if available
        if (response.data.pdf_url) {
          this.$toast.info("PDF phiếu trả đã được tạo", {
            duration: 5000,
          });
        }

        this.$emit("success", response.data);
        this.$emit("close");
      } catch (error) {
        console.error("Return error:", error);
        const message =
          error.response?.data?.message ||
          "Có lỗi xảy ra khi xử lý trả thiết bị";
        this.$toast.error(message);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
/* Additional styles if needed */
</style>
