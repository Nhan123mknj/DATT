<template>
  <ModalForm
    :show="show"
    title="Tạo phiếu mượn từ đặt trước"
    @close="$emit('close')"
    @submit="$emit('submit')"
    size="large"
  >
    <div v-if="reservation" class="space-y-4">
      <!-- Thông tin người mượn -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
          <span
            class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs mr-2"
            >👤</span
          >
          Thông tin người mượn
        </h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <p class="text-gray-600">Tên:</p>
            <p class="font-medium text-gray-900">
              {{ reservation.user?.name }}
            </p>
          </div>
          <div>
            <p class="text-gray-600">Email:</p>
            <p class="font-medium text-gray-900">
              {{ reservation.user?.email }}
            </p>
          </div>
          <div>
            <p class="text-gray-600">Điện thoại:</p>
            <p class="font-medium text-gray-900">
              {{ reservation.user?.phone || "—" }}
            </p>
          </div>
          <div>
            <p class="text-gray-600">Mã yêu cầu:</p>
            <p class="font-medium text-gray-900">#{{ reservation.id }}</p>
          </div>
        </div>
      </div>

      <!-- Thời gian mượn -->
      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
          <span
            class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs mr-2"
            >📅</span
          >
          Thời gian mượn
        </h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <p class="text-gray-600">Từ ngày:</p>
            <p class="font-medium text-gray-900">
              {{ formatDate(reservation.reserved_from) }}
            </p>
          </div>
          <div>
            <p class="text-gray-600">Đến ngày:</p>
            <p class="font-medium text-gray-900">
              {{ formatDate(reservation.reserved_until) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Danh sách thiết bị -->
      <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
          <span
            class="w-6 h-6 rounded-full bg-green-600 text-white flex items-center justify-center text-xs mr-2"
            >📦</span
          >
          Danh sách thiết bị ({{ reservation.details?.length || 0 }}
          items)
        </h3>
        <div class="space-y-2 max-h-64 overflow-y-auto">
          <div
            v-for="(detail, index) in reservation.details || []"
            :key="detail.id"
            class="flex items-start p-3 bg-white rounded-lg border border-green-200"
          >
            <span
              class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-600 text-white text-xs font-bold mr-3 flex-shrink-0"
            >
              {{ index + 1 }}
            </span>
            <div class="flex-1">
              <p class="font-medium text-gray-900">
                {{ detail.device_unit?.device?.name }}
              </p>
              <p class="text-xs text-gray-500 mt-1">
                <span class="inline-block bg-gray-100 px-2 py-1 rounded mr-2">
                  Unit #{{ detail.device_unit_id }}
                </span>
                <span
                  v-if="detail.device_unit?.code"
                  class="inline-block bg-gray-100 px-2 py-1 rounded"
                >
                  {{ detail.device_unit.code }}
                </span>
              </p>
            </div>
          </div>
          <div
            v-if="!reservation.details?.length"
            class="text-center py-4 text-gray-500"
          >
            Không có thiết bị
          </div>
        </div>
      </div>

      <!-- Ghi chú -->
      <div
        v-if="reservation.notes"
        class="bg-gray-50 border border-gray-200 rounded-lg p-4"
      >
        <h3 class="font-semibold text-gray-900 mb-2 flex items-center">
          <span
            class="w-6 h-6 rounded-full bg-gray-600 text-white flex items-center justify-center text-xs mr-2"
            >📝</span
          >
          Ghi chú
        </h3>
        <p class="text-sm text-gray-700 italic">
          {{ reservation.notes }}
        </p>
      </div>

      <!-- File cam kết -->
      <div
        v-if="reservation.commitment_file"
        class="bg-purple-50 border border-purple-200 rounded-lg p-4"
      >
        <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
          <span
            class="w-6 h-6 rounded-full bg-purple-600 text-white flex items-center justify-center text-xs mr-2"
            >📄</span
          >
          File cam kết trách nhiệm
        </h3>
        <div class="space-y-3">
          <div
            v-if="isPdfFile(reservation.commitment_file)"
            class="rounded-lg overflow-hidden border border-purple-200"
          >
            <iframe
              :src="reservation.commitment_file"
              class="w-full"
              style="height: 400px"
            ></iframe>
          </div>
          <div
            v-else-if="isImageFile(reservation.commitment_file)"
            class="rounded-lg overflow-hidden border border-purple-200"
          >
            <img
              :src="reservation.commitment_file"
              alt="File cam kết"
              class="w-full max-h-96 object-contain"
            />
          </div>
          <div
            v-else
            class="flex items-center gap-3 p-3 bg-white rounded-lg border border-purple-200"
          >
            <span class="text-2xl">📁</span>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">Tệp đính kèm</p>
              <p class="text-xs text-gray-500">
                {{ getFileName(reservation.commitment_file) }}
              </p>
            </div>
            <a
              :href="reservation.commitment_file"
              target="_blank"
              class="px-3 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700"
            >
              Tải
            </a>
          </div>
        </div>
      </div>

      <!-- Warning -->
      <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-sm text-red-700 flex items-start">
          <span class="mr-2 mt-0.5">⚠️</span>
          <span
            >Sau khi tạo phiếu mượn, yêu cầu đặt trước sẽ được đánh dấu là
            <strong>Đã hoàn thành</strong></span
          >
        </p>
      </div>
    </div>

    <template #footer>
      <button
        type="button"
        class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50"
        @click="$emit('close')"
      >
        Hủy
      </button>
      <button
        type="submit"
        class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50"
        :disabled="loading"
      >
        {{ loading ? "Đang tạo..." : "Xác nhận tạo phiếu mượn" }}
      </button>
    </template>
  </ModalForm>
</template>

<script setup>
import ModalForm from "../../ModalForm.vue";
import useFormatDate from "../../../composables/utils/formatDate";

const props = defineProps({
  show: Boolean,
  reservation: Object,
  loading: Boolean,
});

defineEmits(["close", "submit"]);

const { formatDate } = useFormatDate();

const isPdfFile = (filePath) => {
  return filePath?.toLowerCase().endsWith(".pdf");
};

const isImageFile = (filePath) => {
  if (!filePath) return false;
  const imageExtensions = [".jpg", ".jpeg", ".png", ".gif", ".webp"];
  return imageExtensions.some((ext) => filePath.toLowerCase().endsWith(ext));
};

const getFileName = (filePath) => {
  if (!filePath) return "";
  return filePath.split("/").pop();
};
</script>
