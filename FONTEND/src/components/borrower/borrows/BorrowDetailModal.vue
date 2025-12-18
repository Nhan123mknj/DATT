<template>
  <Modal
    :show="show"
    title="Chi tiết phiếu mượn"
    @close="$emit('close')"
    size="2xl"
  >
    <div v-if="borrow" class="text-sm text-gray-700">
      <div
        class="grid grid-cols-1 gap-6"
        :class="{ 'lg:grid-cols-2': borrow.reservation?.commitment_file }"
      >
        <div class="space-y-5">
          <div
            class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100"
          >
            <div>
              <p
                class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
              >
                Mã phiếu
              </p>
              <p class="font-bold text-gray-900 text-lg">#{{ borrow.id }}</p>
            </div>
            <div class="text-right">
              <p
                class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
              >
                Trạng thái
              </p>
              <span
                class="px-3 py-1 rounded-full text-xs font-semibold inline-block"
                :class="statusClasses(borrow.status)"
              >
                {{ statusReverseLabel(borrow.status) }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="p-3 border border-gray-100 rounded-lg">
              <p class="text-gray-500 text-xs mb-1">Ngày mượn</p>
              <p class="font-medium flex items-center gap-2">
                <font-awesome-icon
                  icon="calendar-check"
                  class="text-indigo-500"
                />
                {{ formatDate(borrow.borrowed_date) }}
              </p>
            </div>
            <div class="p-3 border border-gray-100 rounded-lg">
              <p class="text-gray-500 text-xs mb-1">Trả dự kiến</p>
              <p class="font-medium flex items-center gap-2">
                <font-awesome-icon
                  icon="hourglass-end"
                  class="text-amber-500"
                />
                {{ formatDate(borrow.expected_return_date) }}
              </p>
            </div>
          </div>

          <div>
            <p class="font-bold text-gray-900 mb-3 flex items-center gap-2">
              <font-awesome-icon icon="boxes" class="text-gray-400" />
              Danh sách thiết bị
            </p>
            <div
              class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden"
            >
              <ul class="divide-y divide-gray-200 max-h-60 overflow-y-auto">
                <li
                  v-for="(detail, index) in borrow.details || []"
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
                          v-if="detail.device_unit?.serial_number"
                          class="ml-2 bg-gray-200 px-1.5 py-0.5 rounded text-gray-600"
                          >{{ detail.device_unit.serial_number }}</span
                        >
                      </span>
                    </div>
                  </div>
                  <button
                    v-if="borrow.status === 'borrowed'"
                    @click="$emit('open-report', detail.device_unit)"
                    class="text-xs px-2 py-1 rounded bg-red-50 text-red-600 hover:bg-red-100 font-medium transition-colors"
                  >
                    Báo hỏng
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <div
            v-if="borrow.notes"
            class="bg-amber-50 border border-amber-100 rounded-xl p-4"
          >
            <p class="font-bold text-amber-800 mb-1 flex items-center gap-2">
              <font-awesome-icon icon="sticky-note" />
              Ghi chú
            </p>
            <p class="text-amber-900">{{ borrow.notes }}</p>
          </div>
        </div>

        <!-- Commitment File Column -->
        <div v-if="borrow.commitment_file" class="lg:border-l lg:pl-6">
          <div class="sticky top-0">
            <p class="font-bold text-purple-800 mb-3 flex items-center gap-2">
              <font-awesome-icon icon="file-contract" />
              File cam kết
            </p>

            <div class="space-y-3">
              <div
                v-if="isPdfFile(borrow.commitment_file)"
                class="rounded-lg overflow-hidden border border-purple-200 bg-white h-[600px]"
              >
                <embed
                  :src="getFileUrl(borrow.commitment_file)"
                  type="application/pdf"
                  class="w-full h-full"
                />
              </div>
              <div
                v-else-if="isImageFile(borrow.commitment_file)"
                class="rounded-lg overflow-hidden border border-purple-200 bg-white"
              >
                <img
                  :src="getFileUrl(borrow.commitment_file)"
                  alt="File cam kết"
                  class="w-full object-contain"
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
                    {{ getFileName(borrow.commitment_file) }}
                  </p>
                </div>
                <a
                  :href="getFileUrl(borrow.commitment_file)"
                  target="_blank"
                  class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm hover:bg-purple-700 transition-colors font-medium"
                >
                  Tải xuống
                </a>
              </div>
            </div>
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
import Modal from "../../../components/Modal.vue";
import useStatusLabel from "../../../composables/utils/statusLabel";
import useFormatDate from "../../../composables/utils/formatDate";

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  borrow: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close", "open-report"]);

const { statusReverseLabel, statusClasses } = useStatusLabel();
const { formatDate } = useFormatDate();

const getFileUrl = (path) => {
  if (!path) return "";
  if (path.startsWith("http")) return path;

  const cleanPath = path.startsWith("/") ? path.substring(1) : path;

  const apiBase =
    import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api";
  const rootUrl = apiBase.replace(/\/api\/?$/, "");

  return `${rootUrl}/storage/${cleanPath}`;
};

const getExtension = (path) => {
  if (!path) return "";
  const cleanPath = path.split("?")[0];
  const parts = cleanPath.split(".");
  if (parts.length < 2) return "";
  return parts.pop().toLowerCase().trim();
};

const isPdfFile = (filePath) => {
  return getExtension(filePath) === "pdf";
};

const isImageFile = (filePath) => {
  const imageExtensions = ["jpg", "jpeg", "png", "gif", "webp"];
  return imageExtensions.includes(getExtension(filePath));
};

const getFileName = (filePath) => {
  if (!filePath) return "";
  return filePath.split("/").pop();
};
</script>
