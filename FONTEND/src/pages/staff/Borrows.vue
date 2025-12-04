<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Quản lý phiếu mượn</h1>
        <p class="text-sm text-gray-500">
          Xem và xử lý các phiếu mượn thiết bị.
        </p>
      </div>
      <div class="flex gap-2">
        <select
          v-model="filters.status"
          class="px-3 py-2 rounded-lg border border-gray-200 text-sm"
        >
          <option value="">Tất cả trạng thái</option>
          <option
            v-for="(label, value) in statusMap"
            :key="value"
            :value="value"
          >
            {{ label }}
          </option>
        </select>
        <button
          class="px-4 py-2 rounded-lg bg-gray-900 text-white"
          @click="loadBorrows()"
        >
          Lọc
        </button>
        <button
          class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition flex items-center gap-2"
          @click="openCreateModal"
        >
          <span>➕</span>
          Tạo phiếu mượn
        </button>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
      <LoadingSkeleton v-if="isLoading" />
      <div v-else>
        <Table :data="borrows" :headers="headers">
          <template #borrower="{ item }">
            {{ item.borrower?.name || "Không rõ" }}
            <p class="text-xs text-gray-500">{{ item.borrower?.email }}</p>
          </template>
          <template #status="{ item }">
            <span
              class="px-3 py-1 rounded-full text-xs font-medium"
              :class="statusClasses(item.status)"
            >
              {{ statusLabel(item.status) }}
            </span>
          </template>
          <template #borrowed_date="{ item }">
            {{ formatDate(item.borrowed_date) }}
          </template>
          <template #expected_return_date="{ item }">
            {{ formatDate(item.expected_return_date) }}
          </template>
          <template #actions="{ item }">
            <div class="flex flex-wrap gap-2">
              <button
                class="px-3 py-1 rounded-lg border border-gray-200 text-sm"
                @click="openDetail(item)"
              >
                Xem
              </button>
              <button
                v-if="item.status === 'pending'"
                class="px-3 py-1 rounded-lg border border-green-200 text-green-700 text-sm"
                @click="approveBorrow(item)"
              >
                Duyệt
              </button>
              <button
                v-if="item.status === 'pending'"
                class="px-3 py-1 rounded-lg border border-red-200 text-red-600 text-sm"
                @click="openReject(item)"
              >
                Từ chối
              </button>
              <button
                v-if="item.status === 'borrowed' || item.status === 'overdue'"
                class="px-3 py-1 rounded-lg border border-blue-200 text-blue-600 text-sm"
                @click="openReturnModal(item)"
              >
                Xử lý trả
              </button>
            </div>
          </template>
        </Table>
        <Pagination
          v-if="pagination.total > pagination.per_page"
          :links="pagination.links"
          @page-changed="loadBorrows"
        />
      </div>
    </div>

    <!-- Detail Modal -->
    <Modal
      :show="showModal && modalMode === 'detail'"
      title="Chi tiết phiếu mượn"
      @close="closeModal"
      size="large"
    >
      <div v-if="form" class="space-y-5 text-sm text-gray-700">
        <!-- Header Info -->
        <div
          class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100"
        >
          <div>
            <p
              class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
            >
              Mã phiếu
            </p>
            <p class="font-bold text-gray-900 text-lg">#{{ form.id }}</p>
          </div>
          <div class="text-right">
            <p
              class="text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1"
            >
              Trạng thái
            </p>
            <span
              class="px-3 py-1 rounded-full text-xs font-semibold inline-block"
              :class="statusClasses(form.status)"
            >
              {{ statusLabel(form.status) }}
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
                {{ form.user?.name || "N/A" }}
              </p>
            </div>
            <div>
              <p class="text-gray-500 text-xs mb-1">Email</p>
              <p class="font-medium text-gray-900">
                {{ form.user?.email || "N/A" }}
              </p>
            </div>
            <div>
              <p class="text-gray-500 text-xs mb-1">Số điện thoại</p>
              <p class="font-medium text-gray-900">
                {{ form.user?.phone || "N/A" }}
              </p>
            </div>
          </div>
        </div>

        <!-- Time Info -->
        <div class="grid grid-cols-2 gap-4">
          <div class="p-3 border border-gray-100 rounded-lg">
            <p class="text-gray-500 text-xs mb-1">Ngày mượn</p>
            <p class="font-medium flex items-center gap-2">
              <font-awesome-icon icon="calendar-alt" class="text-indigo-500" />
              {{ formatDate(form.borrowed_date) }}
            </p>
          </div>
          <div class="p-3 border border-gray-100 rounded-lg">
            <p class="text-gray-500 text-xs mb-1">Trả dự kiến</p>
            <p class="font-medium flex items-center gap-2">
              <font-awesome-icon
                icon="calendar-check"
                class="text-indigo-500"
              />
              {{ formatDate(form.expected_return_date) }}
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
                v-for="(detail, index) in form.details || []"
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
              v-if="!form.details?.length"
              class="p-4 text-center text-gray-500 italic"
            >
              Không có thiết bị nào
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div
          v-if="form.notes"
          class="bg-amber-50 border border-amber-100 rounded-xl p-4"
        >
          <p class="font-bold text-amber-800 mb-1 flex items-center gap-2">
            <font-awesome-icon icon="sticky-note" />
            Ghi chú
          </p>
          <p class="text-amber-900">{{ form.notes }}</p>
        </div>
      </div>
      <template #footer>
        <button
          type="button"
          class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors"
          @click="closeModal"
        >
          Đóng
        </button>
      </template>
    </Modal>

    <!-- Reject Modal -->
    <ModalForm
      :show="showModal && modalMode === 'reject'"
      title="Lý do từ chối"
      @close="closeModal"
      @submit="save(() => loadBorrows(pagination.current_page))"
    >
      <div class="space-y-3">
        <textarea
          v-model="form.reason"
          rows="4"
          class="w-full px-3 py-2 rounded-lg border border-gray-200"
          placeholder="Nhập lý do..."
        ></textarea>
        <p v-if="errors.reason" class="text-xs text-red-500">
          {{ errors.reason }}
        </p>
      </div>
      <template #footer>
        <button
          type="button"
          class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600"
          @click="closeModal"
        >
          Hủy
        </button>
        <button
          type="submit"
          class="px-4 py-2 rounded-lg bg-red-600 text-white"
        >
          Từ chối
        </button>
      </template>
    </ModalForm>

    <!-- Return Modal -->
    <ModalForm
      :show="showReturnModal"
      title="Xử lý trả thiết bị"
      @close="closeReturnModal"
      @submit="submitReturn"
    >
      <div v-if="returnTarget" class="space-y-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <p class="text-sm text-gray-700">
            <strong>Phiếu mượn:</strong> #{{ returnTarget.id }}
          </p>
          <p class="text-sm text-gray-700">
            <strong>Người mượn:</strong> {{ returnTarget.user?.name }}
          </p>
          <p class="text-sm text-gray-700">
            <strong>Ngày mượn:</strong>
            {{ formatDate(returnTarget.borrowed_date) }}
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2"
            >Ghi chú khi trả</label
          >
          <textarea
            v-model="returnNotes"
            rows="3"
            class="w-full px-3 py-2 rounded-lg border border-gray-200"
            placeholder="Ghi chú về tình trạng thiết bị khi trả..."
          ></textarea>
        </div>
        <p v-if="returnError" class="text-xs text-red-500">{{ returnError }}</p>
      </div>
      <template #footer>
        <button
          type="button"
          class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600"
          @click="closeReturnModal"
        >
          Hủy
        </button>
        <button
          type="submit"
          class="px-4 py-2 rounded-lg bg-blue-600 text-white"
          :disabled="returnLoading"
        >
          {{ returnLoading ? "Đang xử lý..." : "Xác nhận trả" }}
        </button>
      </template>
    </ModalForm>

    <!-- Create Borrow Modal -->
    <ModalForm
      :show="showCreateModal"
      title="Tạo phiếu mượn nhanh"
      @close="closeCreateModal"
      @submit="submitCreate"
      size="large"
    >
      <div class="space-y-4">
        <!-- Borrower Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Người mượn <span class="text-red-500">*</span>
          </label>
          <input
            v-model="createForm.borrower_search"
            type="text"
            class="w-full px-3 py-2 rounded-lg border border-gray-200"
            placeholder="Nhập tên hoặc email người mượn..."
            @input="searchBorrowers"
          />
          <div
            v-if="borrowerResults.length > 0"
            class="mt-2 border border-gray-200 rounded-lg max-h-40 overflow-y-auto"
          >
            <button
              v-for="user in borrowerResults"
              :key="user.id"
              type="button"
              class="w-full text-left px-3 py-2 hover:bg-gray-50 border-b last:border-b-0"
              @click="selectBorrower(user)"
            >
              <p class="font-medium">{{ user.name }}</p>
              <p class="text-xs text-gray-500">{{ user.email }}</p>
            </button>
          </div>
          <div
            v-if="createForm.borrower_id"
            class="mt-2 bg-indigo-50 border border-indigo-200 rounded-lg p-3"
          >
            <p class="text-sm font-medium text-indigo-900">
              ✓ {{ createForm.borrower_name }}
            </p>
            <p class="text-xs text-indigo-600">
              {{ createForm.borrower_email }}
            </p>
          </div>
          <p v-if="createErrors.borrower_id" class="text-xs text-red-500 mt-1">
            {{ createErrors.borrower_id }}
          </p>
        </div>

        <!-- Device Selection (Category-based like Reservation) -->
        <div>
          <div class="flex items-center justify-between mb-4">
            <label class="block text-sm font-medium text-gray-700">
              Thiết bị <span class="text-red-500">*</span>
            </label>
            <button
              type="button"
              class="px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100"
              @click="addDeviceGroup"
            >
              ➕ Thêm nhóm
            </button>
          </div>

          <div class="space-y-4">
            <div
              v-for="(group, groupIndex) in createForm.deviceGroups"
              :key="groupIndex"
              class="border border-gray-200 rounded-lg p-4 bg-gray-50 relative"
            >
              <button
                v-if="createForm.deviceGroups.length > 1"
                type="button"
                class="absolute top-2 right-2 text-gray-400 hover:text-red-500"
                @click="removeDeviceGroup(groupIndex)"
              >
                ✕
              </button>

              <div class="space-y-3">
                <!-- Category Selection -->
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">
                    Loại thiết bị
                  </label>
                  <select
                    v-model="group.category_id"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"
                    @change="onCategoryChange(groupIndex)"
                  >
                    <option value="">Chọn loại...</option>
                    <option
                      v-for="cat in categories"
                      :key="cat.id"
                      :value="cat.id"
                    >
                      {{ cat.name }}
                    </option>
                  </select>
                </div>

                <!-- Device Selection -->
                <div v-if="group.category_id && group.devices.length > 0">
                  <label class="block text-xs font-medium text-gray-600 mb-1">
                    Thiết bị
                  </label>
                  <select
                    v-model="group.device_id"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"
                    @change="onDeviceChange(groupIndex)"
                  >
                    <option value="">Chọn thiết bị...</option>
                    <option
                      v-for="device in group.devices"
                      :key="device.id"
                      :value="device.id"
                    >
                      {{ device.name }} ({{ device.model }})
                    </option>
                  </select>
                </div>

                <!-- Device Units Selection -->
                <div v-if="group.device_id && group.deviceUnits.length > 0">
                  <!-- Consumable: Number input -->
                  <div v-if="group.category_type === 'consumable'">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                      Số lượng
                    </label>
                    <input
                      type="number"
                      min="0"
                      :max="group.deviceUnits.length"
                      v-model.number="group.quantity"
                      class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"
                      @input="handleConsumableQuantity(groupIndex)"
                    />
                    <p class="text-xs text-gray-500 mt-1">
                      Khả dụng: {{ group.deviceUnits.length }}
                    </p>
                  </div>

                  <!-- Normal/Expensive: Checkboxes -->
                  <div v-else>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                      Đơn vị thiết bị (chọn nhiều)
                    </label>
                    <div
                      class="max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-2 space-y-1"
                    >
                      <label
                        v-for="unit in group.deviceUnits"
                        :key="unit.id"
                        class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-white cursor-pointer"
                        :class="{
                          'bg-indigo-50': group.device_unit_ids.includes(
                            unit.id
                          ),
                        }"
                      >
                        <input
                          type="checkbox"
                          :value="unit.id"
                          v-model="group.device_unit_ids"
                          :disabled="unit.status !== 'available'"
                          class="w-4 h-4 text-indigo-600 border-gray-300 rounded"
                        />
                        <span
                          class="text-sm"
                          :class="
                            unit.status !== 'available'
                              ? 'text-gray-400 line-through'
                              : ''
                          "
                        >
                          {{ unit.serial_number || `Unit #${unit.id}` }}
                        </span>
                        <span
                          v-if="unit.status !== 'available'"
                          class="ml-auto text-xs text-red-600"
                        >
                          {{
                            unit.status === "reserved"
                              ? "Đã đặt"
                              : "Không khả dụng"
                          }}
                        </span>
                      </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                      Đã chọn: {{ group.device_unit_ids.length }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <p v-if="createErrors.devices" class="text-xs text-red-500 mt-2">
            {{ createErrors.devices }}
          </p>
        </div>

        <!-- Expected Return Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Ngày trả dự kiến <span class="text-red-500">*</span>
          </label>
          <input
            v-model="createForm.expected_return_date"
            type="date"
            class="w-full px-3 py-2 rounded-lg border border-gray-200"
            :min="tomorrow"
          />
          <p
            v-if="createErrors.expected_return_date"
            class="text-xs text-red-500 mt-1"
          >
            {{ createErrors.expected_return_date }}
          </p>
        </div>

        <!-- Notes -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2"
            >Ghi chú</label
          >
          <textarea
            v-model="createForm.notes"
            rows="3"
            class="w-full px-3 py-2 rounded-lg border border-gray-200"
            placeholder="Ghi chú về phiếu mượn..."
          ></textarea>
        </div>

        <!-- Commitment File (for expensive devices) -->
        <div
          v-if="hasExpensiveDevice"
          class="bg-amber-50 border border-amber-200 rounded-lg p-4"
        >
          <label class="block text-sm font-medium text-amber-900 mb-2">
            File cam kết <span class="text-red-500">*</span>
          </label>
          <p class="text-xs text-amber-700 mb-2">
            Thiết bị đắt tiền yêu cầu file cam kết trách nhiệm
          </p>
          <input
            v-model="createForm.commitment_file"
            type="text"
            class="w-full px-3 py-2 rounded-lg border border-amber-300"
            placeholder="URL file cam kết..."
          />
          <p
            v-if="createErrors.commitment_file"
            class="text-xs text-red-500 mt-1"
          >
            {{ createErrors.commitment_file }}
          </p>
        </div>

        <p v-if="createErrors.general" class="text-sm text-red-500">
          {{ createErrors.general }}
        </p>
      </div>
      <template #footer>
        <button
          type="button"
          class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600"
          @click="closeCreateModal"
        >
          Hủy
        </button>
        <button
          type="submit"
          class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700"
          :disabled="createLoading"
        >
          {{ createLoading ? "Đang tạo..." : "Tạo phiếu mượn" }}
        </button>
      </template>
    </ModalForm>
  </div>
</template>

<script>
import { onMounted, reactive, ref, computed } from "vue";
import { useToast } from "vue-toastification";
import Table from "../../components/common/Table.vue";
import LoadingSkeleton from "../../components/common/LoadingSkeleton.vue";
import Pagination from "../../components/common/Pagination.vue";
import Modal from "../../components/Modal.vue";
import ModalForm from "../../components/ModalForm.vue";
import { staffBorrowService } from "../../services/borrows/staffBorrowService";
import { deviceService } from "../../services/shared/deviceService";
import { usersService } from "../../services/users/usersService";
import { useDataTable } from "../../composables/fetchData/useDataTable";
import { useForm } from "../../composables/useForm";
import useStatusLabel from "../../composables/utils/statusLabel";
import useFormatDate from "../../composables/utils/formatDate";

export default {
  name: "StaffBorrows",
  components: {
    Table,
    LoadingSkeleton,
    Pagination,
    Modal,
    ModalForm,
  },
  setup() {
    const toast = useToast();
    const { statusReverseLabel: statusLabel, statusClasses } = useStatusLabel();
    const { formatDate } = useFormatDate();

    const filters = reactive({
      status: "",
    });

    const headers = {
      id: "Mã",
      borrower: "Người mượn",
      borrowed_date: "Ngày mượn",
      expected_return_date: "Hạn trả",
      status: "Trạng thái",
    };

    const statusMap = {
      pending: "Chờ duyệt",
      approved: "Đã duyệt",
      borrowed: "Đang mượn",
      returned: "Đã trả",
      rejected: "Từ chối",
      cancelled: "Đã hủy",
    };

    const {
      items: borrows,
      isLoading,
      pagination,
      loadData: loadBorrows,
    } = useDataTable({
      fetchData: (params) =>
        staffBorrowService.list({
          ...params,
          status: filters.status ? [filters.status] : undefined,
        }),
      dataKey: "data",
      perPage: 10,
    });

    const {
      form,
      errors,
      showModal,
      modalMode,
      openDetail,
      openReject,
      closeModal,
      save,
    } = useForm({
      rejectData: (id, data) => staffBorrowService.reject(id, data),
      initialForm: {
        id: "",
        reason: "",
        status: "",
        borrower: {},
        details: [],
        borrowed_date: "",
        expected_return_date: "",
        notes: "",
      },
    });

    const showReturnModal = ref(false);
    const returnTarget = ref(null);
    const returnNotes = ref("");
    const returnError = ref("");
    const returnLoading = ref(false);
    const showCreateModal = ref(false);
    const createLoading = ref(false);
    const createErrors = ref({});
    const borrowerResults = ref([]);
    const categories = ref([]);
    const searchTimeout = ref(null);

    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split("T")[0];

    const createEmptyDeviceGroup = () => ({
      category_id: "",
      category_type: "",
      device_id: "",
      quantity: 0,
      device_unit_ids: [],
      devices: [],
      deviceUnits: [],
    });

    const createForm = reactive({
      borrower_id: null,
      borrower_name: "",
      borrower_email: "",
      borrower_search: "",
      deviceGroups: [createEmptyDeviceGroup()],
      expected_return_date: "",
      notes: "",
      commitment_file: "",
    });

    const hasExpensiveDevice = computed(() => {
      return createForm.deviceGroups.some(
        (group) =>
          group.category_type === "expensive" &&
          group.device_unit_ids.length > 0
      );
    });

    const approveBorrow = async (borrow) => {
      if (!confirm("Duyệt phiếu mượn này?")) return;
      try {
        await staffBorrowService.approve(borrow.id);
        toast.success("Đã duyệt phiếu mượn");
        loadBorrows(pagination.current_page);
      } catch (error) {
        toast.error("Không thể duyệt phiếu mượn");
      }
    };

    const openReturnModal = (borrow) => {
      returnTarget.value = borrow;
      returnNotes.value = "";
      returnError.value = "";
      showReturnModal.value = true;
    };

    const closeReturnModal = () => {
      showReturnModal.value = false;
      returnTarget.value = null;
    };

    const submitReturn = async () => {
      returnLoading.value = true;
      try {
        await staffBorrowService.return(returnTarget.value.id, {
          notes: returnNotes.value,
        });
        toast.success("Đã xử lý trả thiết bị");
        closeReturnModal();
        loadBorrows(pagination.current_page);
      } catch (error) {
        returnError.value =
          error.response?.data?.message || "Không thể xử lý trả";
      } finally {
        returnLoading.value = false;
      }
    };

    const loadCategories = async () => {
      try {
        const { data } = await deviceService.getCategories();
        categories.value = data.data || [];
      } catch (error) {
        console.error("Load categories error:", error);
        toast.error("Không thể tải danh sách loại thiết bị");
      }
    };

    const resetCreateForm = () => {
      createForm.borrower_id = null;
      createForm.borrower_name = "";
      createForm.borrower_email = "";
      createForm.borrower_search = "";
      createForm.deviceGroups = [createEmptyDeviceGroup()];
      createForm.expected_return_date = "";
      createForm.notes = "";
      createForm.commitment_file = "";
      borrowerResults.value = [];
      categories.value = [];
      createErrors.value = {};
    };

    const openCreateModal = async () => {
      resetCreateForm();
      await loadCategories();
      showCreateModal.value = true;
    };

    const closeCreateModal = () => {
      showCreateModal.value = false;
      resetCreateForm();
    };

    const searchBorrowers = () => {
      if (searchTimeout.value) clearTimeout(searchTimeout.value);

      if (
        !createForm.borrower_search ||
        createForm.borrower_search.length < 2
      ) {
        borrowerResults.value = [];
        return;
      }

      searchTimeout.value = setTimeout(async () => {
        try {
          const { data } = await usersService.getAllUser({
            search: createForm.borrower_search,
            role: "borrower",
            per_page: 10,
            page: 1,
          });
          borrowerResults.value = data.data?.data || [];
        } catch (error) {
          console.error("Search borrowers error:", error);
          borrowerResults.value = [];
        }
      }, 300);
    };

    const selectBorrower = (user) => {
      createForm.borrower_id = user.id;
      createForm.borrower_name = user.name;
      createForm.borrower_email = user.email;
      borrowerResults.value = [];
      createForm.borrower_search = "";
    };

    const addDeviceGroup = () => {
      createForm.deviceGroups.push(createEmptyDeviceGroup());
    };

    const removeDeviceGroup = (index) => {
      if (createForm.deviceGroups.length > 1) {
        createForm.deviceGroups.splice(index, 1);
      }
    };

    const onCategoryChange = async (groupIndex) => {
      const group = createForm.deviceGroups[groupIndex];
      const categoryId = group.category_id;

      // Reset dependent fields
      group.device_id = "";
      group.devices = [];
      group.deviceUnits = [];
      group.device_unit_ids = [];
      group.quantity = 0;

      if (!categoryId) {
        group.category_type = "";
        return;
      }

      // Set category type
      const category = categories.value.find((c) => c.id == categoryId);
      group.category_type = category?.type || "normal";

      // Load devices for this category
      try {
        const { data } = await borrowerDeviceService.getDevicesByCategory(
          categoryId
        );
        group.devices = data.data || [];
      } catch (error) {
        console.error("Load devices error:", error);
        toast.error("Không thể tải danh sách thiết bị");
      }
    };

    const onDeviceChange = async (groupIndex) => {
      const group = createForm.deviceGroups[groupIndex];
      const deviceId = group.device_id;

      // Reset dependent fields
      group.deviceUnits = [];
      group.device_unit_ids = [];
      group.quantity = 0;

      if (!deviceId) return;

      // Load device units
      try {
        const { data } = await borrowerDeviceService.getDeviceUnitsByDevice(
          deviceId
        );
        group.deviceUnits = data.data || [];
      } catch (error) {
        console.error("Load device units error:", error);
        toast.error("Không thể tải danh sách đơn vị thiết bị");
      }
    };

    const handleConsumableQuantity = (groupIndex) => {
      const group = createForm.deviceGroups[groupIndex];
      const maxAvailable = group.deviceUnits?.length || 0;
      const quantity = Math.max(0, Math.min(group.quantity || 0, maxAvailable));

      group.quantity = quantity;
      // Auto-select first N units
      group.device_unit_ids = group.deviceUnits
        .slice(0, quantity)
        .map((u) => u.id);
    };

    const submitCreate = async () => {
      // Validate
      createErrors.value = {};

      if (!createForm.borrower_id) {
        createErrors.value.borrower_id = "Vui lòng chọn người mượn";
        return;
      }

      // Collect all device units from all groups
      const allDeviceUnits = [];
      for (let i = 0; i < createForm.deviceGroups.length; i++) {
        const group = createForm.deviceGroups[i];

        if (!group.category_id) {
          createErrors.value.devices = `Nhóm ${
            i + 1
          }: Vui lòng chọn loại thiết bị`;
          return;
        }

        if (!group.device_id) {
          createErrors.value.devices = `Nhóm ${i + 1}: Vui lòng chọn thiết bị`;
          return;
        }

        if (group.category_type === "consumable") {
          if (!group.quantity || group.quantity < 1) {
            createErrors.value.devices = `Nhóm ${
              i + 1
            }: Vui lòng nhập số lượng`;
            return;
          }
        } else {
          if (!group.device_unit_ids || group.device_unit_ids.length === 0) {
            createErrors.value.devices = `Nhóm ${
              i + 1
            }: Vui lòng chọn ít nhất một đơn vị`;
            return;
          }
        }

        // Add units from this group
        group.device_unit_ids.forEach((unitId) => {
          allDeviceUnits.push({
            device_unit_id: unitId,
            condition_at_borrow: "tốt",
          });
        });
      }

      if (allDeviceUnits.length === 0) {
        createErrors.value.devices = "Vui lòng chọn ít nhất một thiết bị";
        return;
      }

      if (!createForm.expected_return_date) {
        createErrors.value.expected_return_date =
          "Vui lòng chọn ngày trả dự kiến";
        return;
      }

      if (hasExpensiveDevice.value && !createForm.commitment_file) {
        createErrors.value.commitment_file =
          "Thiết bị đắt tiền yêu cầu file cam kết";
        return;
      }

      createLoading.value = true;

      try {
        const payload = {
          borrower_id: createForm.borrower_id,
          expected_return_date: createForm.expected_return_date,
          devices: allDeviceUnits,
          notes: createForm.notes,
          commitment_file: createForm.commitment_file || null,
        };

        await staffBorrowService.create(payload);
        toast.success("Đã tạo phiếu mượn thành công");
        closeCreateModal();
        loadBorrows(pagination.current_page);
      } catch (error) {
        if (error.response?.status === 422) {
          createErrors.value = error.response.data.errors;
        } else {
          createErrors.value.general =
            error.response?.data?.message || "Không thể tạo phiếu mượn";
        }
      } finally {
        createLoading.value = false;
      }
    };

    onMounted(() => {
      loadBorrows();
    });

    return {
      filters,
      headers,
      statusMap,
      borrows,
      isLoading,
      pagination,
      loadBorrows,
      statusLabel,
      statusClasses,
      formatDate,
      form,
      errors,
      showModal,
      modalMode,
      openDetail,
      openReject,
      closeModal,
      save,
      approveBorrow,
      showReturnModal,
      returnTarget,
      returnNotes,
      returnError,
      returnLoading,
      openReturnModal,
      closeReturnModal,
      submitReturn,
      showCreateModal,
      createForm,
      createErrors,
      createLoading,
      borrowerResults,
      categories,
      tomorrow: tomorrowStr,
      hasExpensiveDevice,
      openCreateModal,
      closeCreateModal,
      searchBorrowers,
      selectBorrower,
      addDeviceGroup,
      removeDeviceGroup,
      onCategoryChange,
      onDeviceChange,
      handleConsumableQuantity,
      submitCreate,
    };
  },
};
</script>
