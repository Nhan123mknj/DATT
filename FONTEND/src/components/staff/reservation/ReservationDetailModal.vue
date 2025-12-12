<template>
  <Modal
    :show="show"
    title="Chi tiết đặt trước"
    @close="$emit('close')"
    size="large"
  >
    <div v-if="reservation" class="space-y-5 text-sm text-gray-700">
      <!-- Header Info -->
      <div
        class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100"
      >
        <div>
          <p
            class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
          >
            Mã yêu cầu
          </p>
          <p class="font-bold text-gray-900 text-lg">#{{ reservation.id }}</p>
        </div>
        <div class="text-right">
          <p
            class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
          >
            Trạng thái
          </p>
          <span
            class="px-3 py-1 rounded-full text-xs font-semibold inline-block"
            :class="statusClasses(reservation.status)"
          >
            {{ statusLabel(reservation.status) }}
          </span>
        </div>
      </div>

      <!-- User Info -->
      <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
        <h3 class="font-bold text-indigo-900 mb-3 flex items-center">
          <span
            class="w-6 h-6 rounded-full bg-indigo-200 text-indigo-700 flex items-center justify-center text-xs mr-2"
          >
            <font-awesome-icon icon="user" />
          </span>
          Thông tin người mượn
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <p class="text-gray-500 text-xs mb-1">Họ tên</p>
            <p class="font-medium text-gray-900">
              {{ reservation.user?.name || "N/A" }}
            </p>
          </div>
          <div>
            <p class="text-gray-500 text-xs mb-1">Email</p>
            <p class="font-medium text-gray-900">
              {{ reservation.user?.email || "N/A" }}
            </p>
          </div>
          <div>
            <p class="text-gray-500 text-xs mb-1">Số điện thoại</p>
            <p class="font-medium text-gray-900">
              {{ reservation.user?.phone || "N/A" }}
            </p>
          </div>
        </div>
      </div>

      <!-- Time Info -->
      <div class="grid grid-cols-2 gap-4">
        <div class="p-3 border border-gray-100 rounded-lg">
          <p class="text-gray-500 text-xs mb-1">Từ ngày</p>
          <p class="font-medium flex items-center gap-2">
            <font-awesome-icon icon="calendar-alt" class="text-indigo-500" />
            {{ formatDate(reservation.reserved_from) }}
          </p>
        </div>
        <div class="p-3 border border-gray-100 rounded-lg">
          <p class="text-gray-500 text-xs mb-1">Đến ngày</p>
          <p class="font-medium flex items-center gap-2">
            <font-awesome-icon icon="calendar-check" class="text-indigo-500" />
            {{ formatDate(reservation.reserved_until) }}
          </p>
        </div>
      </div>

      <!-- Devices List -->
      <div>
        <p class="font-bold text-gray-900 mb-3 flex items-center gap-2">
          <font-awesome-icon icon="boxes" class="text-gray-400" />
          Danh sách thiết bị
        </p>
        <div
          class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden"
        >
          <ul class="divide-y divide-gray-200">
            <li
              v-for="(detail, index) in reservation.details || []"
              :key="detail.id"
              class="p-3 hover:bg-white transition-colors flex items-center justify-between"
            >
              <div class="flex items-center gap-3">
                <span
                  class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-xs font-bold"
                >
                  {{ index + 1 }}
                </span>
                <div>
                  <span class="font-medium text-gray-900 block">{{
                    detail.device_unit?.device?.name ||
                    "Thiết bị không xác định"
                  }}</span>
                  <span class="text-xs text-gray-500 font-mono">
                    Unit #{{ detail.device_unit_id }}
                    <span
                      v-if="detail.device_unit?.code"
                      class="ml-2 bg-gray-200 px-1.5 py-0.5 rounded text-gray-600"
                      >{{ detail.device_unit.code }}</span
                    >
                  </span>
                </div>
              </div>
            </li>
          </ul>
          <div
            v-if="!reservation.details?.length"
            class="p-4 text-center text-gray-500 italic"
          >
            Không có thiết bị nào
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div
        v-if="reservation.notes"
        class="bg-amber-50 border border-amber-100 rounded-xl p-4"
      >
        <p class="font-bold text-amber-800 mb-1 flex items-center gap-2">
          <font-awesome-icon icon="sticky-note" />
          Ghi chú
        </p>
        <p class="text-amber-900">{{ reservation.notes }}</p>
      </div>

      <!-- Commitment File -->
      <div
        v-if="reservation.commitment_file"
        class="bg-purple-50 border border-purple-100 rounded-xl p-4"
      >
        <p class="font-bold text-purple-800 mb-3 flex items-center gap-2">
          <font-awesome-icon icon="file-contract" />
          File cam kết
        </p>

        <div class="space-y-3">
          <div
            v-if="isPdfFile(reservation.commitment_file)"
            class="rounded-lg overflow-hidden border border-purple-200 bg-white"
          >
            <iframe
              :src="reservation.commitment_file"
              class="w-full h-96"
            ></iframe>
          </div>
          <div
            v-else-if="isImageFile(reservation.commitment_file)"
            class="rounded-lg overflow-hidden border border-purple-200 bg-white"
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
            <div
              class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600"
            >
              <font-awesome-icon icon="file-download" class="text-xl" />
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">Tệp đính kèm</p>
              <p class="text-xs text-gray-500">
                {{ getFileName(reservation.commitment_file) }}
              </p>
            </div>
            <a
              :href="reservation.commitment_file"
              target="_blank"
              class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700 transition-colors font-medium"
            >
              Tải xuống
            </a>
          </div>
        </div>
      </div>
    </div>
    <template #footer>
      <button
        type="button"
        class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors"
        @click="$emit('close')"
      >
        Đóng
      </button>
    </template>
  </Modal>
</template>

<script setup>
import Modal from "../../Modal.vue";
import useStatusLabel from "../../../composables/utils/statusLabel";
import useFormatDate from "../../../composables/utils/formatDate";

const props = defineProps({
  show: Boolean,
  reservation: Object,
});

defineEmits(["close"]);

const { statusReverseLabel: statusLabel, statusClasses } = useStatusLabel();
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
