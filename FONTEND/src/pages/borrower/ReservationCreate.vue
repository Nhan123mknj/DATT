<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          Tạo đặt trước thiết bị
        </h1>
        <p class="text-sm text-gray-500">
          Chọn thời gian và thiết bị bạn muốn mượn.
        </p>
      </div>
      <div class="flex gap-3">
        <Button color="secondary" @click="goBack">
          <font-awesome-icon icon="arrow-left" class="mr-2" />
          Trở lại danh sách
        </Button>
      </div>
    </div>

    <div
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6"
    >
      <form class="space-y-6" @submit.prevent="submitReservation">
        <!-- Thông tin thời gian -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
          <h3
            class="text-sm font-semibold text-gray-900 mb-4 flex items-center"
          >
            <font-awesome-icon icon="calendar" class="mr-2 text-blue-600" />
            Thời gian đặt trước
          </h3>
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Từ ngày <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.reserved_from"
                type="date"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                :min="new Date().toISOString().split('T')[0]"
              />
              <p v-if="errors.reserved_from" class="text-xs text-red-500 mt-1">
                {{ errors.reserved_from?.[0] || errors.reserved_from }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Đến ngày <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.reserved_until"
                type="date"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                :min="
                  form.reserved_from || new Date().toISOString().split('T')[0]
                "
              />
              <p v-if="errors.reserved_until" class="text-xs text-red-500 mt-1">
                {{ errors.reserved_until?.[0] || errors.reserved_until }}
              </p>
            </div>
          </div>
        </div>

        <!-- Danh sách thiết bị -->
        <div>
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
              <font-awesome-icon icon="boxes" class="mr-2 text-indigo-600" />
              Thiết bị đặt trước
              <span
                v-if="totalSelectedDevices > 0"
                class="ml-2 px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium"
              >
                {{ totalSelectedDevices }} thiết bị
              </span>
            </h3>
            <button
              type="button"
              class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition"
              @click="addDeviceGroup"
            >
              <font-awesome-icon icon="plus" class="mr-1.5" />
              Thêm nhóm thiết bị
            </button>
          </div>

          <div class="mb-6">
            <div
              class="text-xs font-semibold text-gray-500 uppercase tracking-wide"
            >
              Hướng dẫn chọn thiết bị
            </div>
            <div
              class="mt-3 overflow-hidden rounded-2xl border border-dashed border-gray-200 bg-white shadow-sm"
            >
              <table class="w-full text-sm text-gray-700">
                <thead
                  class="bg-gray-50 text-xs uppercase tracking-widest text-gray-500"
                >
                  <tr>
                    <th class="px-4 py-3 text-left">Loại thiết bị</th>
                    <th class="px-4 py-3 text-left">Chọn bằng</th>
                    <th class="px-4 py-3 text-left">Lý do</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="guide in deviceTypeGuides"
                    :key="guide.key"
                    class="border-t border-gray-100"
                  >
                    <td class="px-4 py-3 font-medium text-gray-900">
                      {{ guide.label }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ guide.control }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ guide.reason }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="space-y-4">
            <div
              v-for="(group, groupIndex) in deviceGroups"
              :key="groupIndex"
              class="border-2 border-gray-200 rounded-xl p-5 bg-white hover:border-indigo-300 transition shadow-sm"
            >
              <div class="flex items-start justify-between mb-4">
                <div class="flex items-center">
                  <div
                    class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-semibold text-sm mr-3"
                  >
                    {{ groupIndex + 1 }}
                  </div>
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <h4 class="text-base font-semibold text-gray-900">
                        Nhóm thiết bị {{ groupIndex + 1 }}
                      </h4>
                      <span
                        v-if="group.category_type"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ring-1"
                        :class="typeBadgeClass(group.category_type)"
                      >
                        {{ getDeviceTypeLabel(group.category_type) }}
                      </span>
                    </div>
                    <p
                      v-if="getGroupSummary(groupIndex)"
                      class="text-xs text-gray-500 mt-0.5"
                    >
                      {{ getGroupSummary(groupIndex) }}
                    </p>
                    <p
                      v-if="group.category_type"
                      class="text-xs text-gray-500 mt-1 flex items-center gap-1"
                    >
                      <font-awesome-icon
                        icon="info-circle"
                        class="text-gray-400"
                      />
                      {{ getDeviceTypeGuide(group.category_type).reason }}
                    </p>
                  </div>
                </div>
                <button
                  type="button"
                  class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 transition"
                  @click="removeDeviceGroup(groupIndex)"
                  v-if="deviceGroups.length > 1"
                  title="Xóa nhóm"
                >
                  <font-awesome-icon icon="trash" />
                </button>
              </div>

              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Loại thiết bị <span class="text-red-500">*</span>
                  </label>
                  <MultiSelect
                    v-model="group.category_id_array"
                    :options="categoryOptions"
                    label-key="name"
                    value-key="id"
                    :multiple="false"
                    placeholder="Chọn loại thiết bị..."
                    search-placeholder="Tìm kiếm loại thiết bị..."
                    :error="
                      groupIndex === 0 && errors.category_id
                        ? errors.category_id[0] || errors.category_id
                        : ''
                    "
                    @update:modelValue="onCategorySelect(groupIndex, $event)"
                  />
                </div>

                <div
                  v-if="
                    group.category_id &&
                    group.devices &&
                    group.devices.length > 0
                  "
                  class="transition-all"
                >
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Thiết bị <span class="text-red-500">*</span>
                  </label>
                  <MultiSelect
                    v-model="group.device_id_array"
                    :options="getDeviceOptions(groupIndex)"
                    label-key="displayName"
                    value-key="id"
                    :multiple="false"
                    placeholder="Chọn thiết bị..."
                    search-placeholder="Tìm kiếm thiết bị..."
                    @update:modelValue="onDeviceSelect(groupIndex, $event)"
                  />
                </div>

                <div
                  v-if="
                    group.device_id &&
                    group.deviceUnits &&
                    group.deviceUnits.length > 0
                  "
                  class="transition-all"
                >
                  <template v-if="group.category_type === 'consumable'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Số lượng cần mượn <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                      <input
                        type="number"
                        min="0"
                        :max="group.deviceUnits.length"
                        :value="group.quantity"
                        class="w-32 px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="0"
                        @input="
                          handleConsumableQuantity(
                            groupIndex,
                            $event.target.value
                          )
                        "
                      />
                      <span class="text-xs text-gray-500">
                        Tối đa {{ group.deviceUnits.length }} số lượng khả dụng
                      </span>
                    </div>
                    <p
                      v-if="group.quantity > 0"
                      class="text-xs text-emerald-600 mt-2 font-medium"
                    >
                      Đã chọn: {{ group.quantity }} số lượng
                    </p>
                  </template>
                  <template v-else>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Đơn vị thiết bị <span class="text-red-500">*</span>
                      <span class="text-xs font-normal text-gray-500 ml-2">
                        (Có thể chọn nhiều)
                      </span>
                    </label>
                    <div
                      class="space-y-2 rounded-xl border border-gray-200 p-3 max-h-48 overflow-y-auto"
                    >
                      <label
                        v-for="unit in getDeviceUnitOptions(groupIndex)"
                        :key="unit.id"
                        class="flex items-center justify-between px-2 py-1.5 rounded-lg hover:bg-gray-50 text-sm"
                      >
                        <div class="flex items-center gap-3">
                          <input
                            type="checkbox"
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                            :value="unit.id"
                            v-model="group.device_unit_ids"
                          />
                          <span class="font-medium text-gray-700">
                            {{ unit.displayName }}
                          </span>
                        </div>
                        <span class="text-xs text-gray-400">
                          ID: {{ unit.id }}
                        </span>
                      </label>
                    </div>
                    <p
                      v-if="errors.devices && groupIndex === 0"
                      class="text-xs text-red-500 mt-2"
                    >
                      {{ errors.devices[0] || errors.devices }}
                    </p>
                    <p
                      v-if="
                        group.device_unit_ids &&
                        group.device_unit_ids.length > 0
                      "
                      class="text-xs text-gray-500 mt-2"
                    >
                      Đã chọn: {{ group.device_unit_ids.length }} đơn vị
                    </p>
                    <p
                      v-if="group.category_type === 'expensive'"
                      class="text-xs text-amber-600 mt-2 font-medium"
                    >
                      Thiết bị đắt tiền cần theo dõi từng đơn vị riêng lẻ.
                    </p>
                  </template>
                </div>
              </div>
            </div>
          </div>

          <p v-if="errors.devices" class="text-xs text-red-500 mt-2">
            {{ errors.devices?.[0] || errors.devices }}
          </p>
        </div>

        <!-- Tóm tắt thiết bị đã chọn -->
        <div
          v-if="totalSelectedDevices > 0"
          class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-200 rounded-xl p-4"
        >
          <h3
            class="text-sm font-semibold text-gray-900 mb-3 flex items-center"
          >
            <font-awesome-icon icon="list-check" class="mr-2 text-indigo-600" />
            Tóm tắt thiết bị đã chọn
          </h3>
          <div class="space-y-2 max-h-48 overflow-y-auto">
            <div
              v-for="(summary, idx) in getSelectedDevicesSummary()"
              :key="idx"
              class="flex items-center justify-between bg-white rounded-lg px-3 py-2 text-sm"
            >
              <div class="flex items-center">
                <font-awesome-icon
                  icon="check-circle"
                  class="text-green-500 mr-2"
                />
                <span class="font-medium text-gray-700">
                  {{ summary.name }}
                </span>
                <span class="text-gray-500 ml-2">({{ summary.category }})</span>
              </div>
              <span class="text-gray-600 font-medium">
                {{ summary.countLabel }}
              </span>
            </div>
          </div>
          <div class="mt-3 pt-3 border-t border-indigo-200">
            <div class="flex items-center justify-between text-sm">
              <span class="font-semibold text-gray-900">Tổng cộng:</span>
              <span class="font-bold text-indigo-700 text-base">
                {{ totalSelectedDevices }} thiết bị
              </span>
            </div>
          </div>
        </div>

        <!-- Thông tin bổ sung -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
          <h3
            class="text-sm font-semibold text-gray-900 mb-4 flex items-center"
          >
            <font-awesome-icon icon="info-circle" class="mr-2 text-gray-600" />
            Thông tin bổ sung
          </h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Ghi chú
              </label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition resize-none"
                placeholder="Nhập ghi chú cho nhân viên duyệt (nếu có)..."
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                File cam kết
                <span class="text-xs font-normal text-gray-500 ml-1">
                  (Tùy chọn - Yêu cầu cho thiết bị giá trị cao)
                </span>
              </label>
              <div class="flex items-center gap-3">
                <label
                  class="flex-1 cursor-pointer"
                  :class="{
                    'border-2 border-dashed border-indigo-300 bg-indigo-50':
                      !form.commitment_file,
                    'border-2 border-green-300 bg-green-50':
                      form.commitment_file,
                  }"
                >
                  <input
                    type="file"
                    @change="handleFileChange"
                    class="hidden"
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                  />
                  <div class="px-4 py-3 rounded-lg text-center">
                    <font-awesome-icon
                      :icon="form.commitment_file ? 'check-circle' : 'upload'"
                      :class="
                        form.commitment_file
                          ? 'text-green-600'
                          : 'text-indigo-600'
                      "
                      class="mb-2"
                    />
                    <p class="text-sm text-gray-700">
                      {{
                        form.commitment_file
                          ? form.commitment_file.name
                          : "Chọn file cam kết"
                      }}
                    </p>
                  </div>
                </label>
                <button
                  v-if="form.commitment_file"
                  type="button"
                  @click="form.commitment_file = null"
                  class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                >
                  <font-awesome-icon icon="times" />
                </button>
              </div>
              <p
                v-if="errors.commitment_file"
                class="text-xs text-red-500 mt-1"
              >
                {{ errors.commitment_file?.[0] || errors.commitment_file }}
              </p>
            </div>
          </div>
        </div>

        <div
          class="flex items-center justify-between pt-4 border-t border-gray-100"
        >
          <div class="text-sm text-gray-600">
            <span v-if="totalSelectedDevices > 0">
              Tổng:
              <span class="font-semibold text-gray-900">
                {{ totalSelectedDevices }}
              </span>
              thiết bị
            </span>
            <span v-else class="text-gray-400">Chưa chọn thiết bị</span>
          </div>
          <div class="flex gap-3">
            <button
              type="button"
              class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition"
              @click="goBack"
            >
              Hủy
            </button>
            <button
              type="submit"
              class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
              :disabled="submitting || totalSelectedDevices === 0"
            >
              <font-awesome-icon
                v-if="submitting"
                icon="spinner"
                class="animate-spin mr-2"
              />
              <font-awesome-icon v-else icon="paper-plane" class="mr-2" />
              {{ submitting ? "Đang gửi..." : "Gửi yêu cầu" }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import Button from "../../components/Button.vue";
import MultiSelect from "../../components/MultiSelect.vue";
import { reservationsService } from "../../services/reservations/reservationsService";
import { borrowerDeviceService } from "../../services/borrows/borrowerDeviceService";

const router = useRouter();
const toast = useToast();

const DEVICE_TYPE_GUIDES = [
  {
    key: "normal",
    label: "Thiết bị thường",
    control: "Checkbox per unit",
    reason: "Chọn từng unit độc lập",
  },
  {
    key: "expensive",
    label: "Thiết bị đắt tiền",
    control: "Checkbox per unit",
    reason: "Cần theo dõi từng unit",
  },
  {
    key: "consumable",
    label: "Thiết bị tiêu hao",
    control: "Number input",
    reason: "Không có unit, cần số lượng",
  },
];

const DEVICE_TYPE_META = DEVICE_TYPE_GUIDES.reduce((acc, guide) => {
  acc[guide.key] = guide;
  return acc;
}, {});

const deviceTypeGuides = DEVICE_TYPE_GUIDES;

const getDeviceTypeLabel = (type) =>
  DEVICE_TYPE_META[type]?.label || "Thiết bị thường";
const getDeviceTypeGuide = (type) =>
  DEVICE_TYPE_META[type] || DEVICE_TYPE_META.normal;

const typeBadgeClass = (type) => {
  switch (type) {
    case "expensive":
      return "bg-amber-100 text-amber-700 ring-amber-200";
    case "consumable":
      return "bg-emerald-100 text-emerald-700 ring-emerald-200";
    default:
      return "bg-indigo-100 text-indigo-700 ring-indigo-200";
  }
};

const submitting = ref(false);
const form = reactive({
  reserved_from: "",
  reserved_until: "",
  notes: "",
  commitment_file: null,
});
const errors = reactive({});

const categories = ref([]);
const createEmptyDeviceGroup = () => ({
  category_id: "",
  category_id_array: [],
  category_type: "",
  device_id: "",
  device_id_array: [],
  devices: [],
  deviceUnits: [],
  device_unit_ids: [],
  quantity: 0,
});
const deviceGroups = ref([createEmptyDeviceGroup()]);
const loadingCategories = ref(false);
const loadingDevices = ref(false);
const loadingDeviceUnits = ref(false);

const categoryOptions = computed(() => {
  return categories.value.map((cat) => ({
    id: cat.id,
    name: cat.name,
  }));
});

const totalSelectedDevices = computed(() => {
  return deviceGroups.value.reduce((total, group) => {
    if (group.category_type === "consumable") {
      return total + (group.quantity || 0);
    }
    return total + (group.device_unit_ids?.length || 0);
  }, 0);
});

const getGroupSummary = (groupIndex) => {
  const group = deviceGroups.value[groupIndex];
  const parts = [];

  if (group.category_id) {
    const category = categories.value.find((c) => c.id == group.category_id);
    if (category) parts.push(category.name);
  }

  if (group.device_id) {
    const device = group.devices?.find((d) => d.id == group.device_id);
    if (device) parts.push(device.name);
  }

  if (group.category_type === "consumable" && group.quantity > 0) {
    parts.push(`${group.quantity} số lượng`);
  } else if (group.device_unit_ids && group.device_unit_ids.length > 0) {
    parts.push(`${group.device_unit_ids.length} đơn vị`);
  }

  return parts.length > 0 ? parts.join(" • ") : "";
};

const getSelectedDevicesSummary = () => {
  const summary = [];

  deviceGroups.value.forEach((group) => {
    const category = categories.value.find((c) => c.id == group.category_id);
    const device = group.devices?.find((d) => d.id == group.device_id);
    if (!category || !device) return;

    const isConsumable = group.category_type === "consumable";
    const selectedCount = isConsumable
      ? group.quantity || 0
      : group.device_unit_ids?.length || 0;

    if (!selectedCount) return;

    summary.push({
      name: device.name,
      category: category.name,
      count: selectedCount,
      type: group.category_type || "normal",
      countLabel: isConsumable
        ? `${selectedCount} số lượng`
        : `${selectedCount} đơn vị`,
    });
  });

  return summary;
};

const getDeviceOptions = (groupIndex) => {
  const group = deviceGroups.value[groupIndex];
  if (!group.devices || group.devices.length === 0) return [];
  return group.devices.map((device) => ({
    id: device.id,
    displayName: `${device.name} (${device.model}) - ${device.manufacturer}`,
  }));
};

const getDeviceUnitOptions = (groupIndex) => {
  const group = deviceGroups.value[groupIndex];
  if (!group.deviceUnits || group.deviceUnits.length === 0) return [];
  return group.deviceUnits.map((unit) => ({
    id: unit.id,
    displayName: unit.serial_number,
  }));
};

const onCategorySelect = async (groupIndex, selectedIds) => {
  const group = deviceGroups.value[groupIndex];
  const categoryId =
    selectedIds && selectedIds.length > 0 ? selectedIds[0] : "";

  group.category_id = categoryId;
  group.category_id_array = selectedIds || [];
  group.quantity = 0;
  const category = categories.value.find((c) => c.id == categoryId);
  group.category_type = category?.type || "";

  if (!categoryId) {
    group.devices = [];
    group.deviceUnits = [];
    group.device_id = "";
    group.device_id_array = [];
    group.device_unit_ids = [];
    group.category_type = "";
    return;
  }

  loadingDevices.value = true;
  try {
    const { data } = await borrowerDeviceService.getDevicesByCategory(
      categoryId
    );
    group.devices = data.data || [];
    group.device_id = "";
    group.device_id_array = [];
    group.deviceUnits = [];
    group.device_unit_ids = [];
  } catch (error) {
    toast.error("Không thể tải danh sách thiết bị");
  } finally {
    loadingDevices.value = false;
  }
};

const onDeviceSelect = async (groupIndex, selectedIds) => {
  const group = deviceGroups.value[groupIndex];
  const deviceId = selectedIds && selectedIds.length > 0 ? selectedIds[0] : "";

  group.device_id = deviceId;
  group.device_id_array = selectedIds || [];
  group.quantity = 0;

  if (!deviceId) {
    group.deviceUnits = [];
    group.device_unit_ids = [];
    return;
  }

  loadingDeviceUnits.value = true;
  try {
    const { data } = await borrowerDeviceService.getDeviceUnitsByDevice(
      deviceId
    );
    group.deviceUnits = data.data || [];
    group.device_unit_ids = [];
    group.quantity = 0;
  } catch (error) {
    toast.error("Không thể tải danh sách đơn vị thiết bị");
  } finally {
    loadingDeviceUnits.value = false;
  }
};

const loadCategories = async () => {
  loadingCategories.value = true;
  try {
    const { data } = await borrowerDeviceService.getCategories();
    categories.value = data.data || [];
  } catch (error) {
    toast.error("Không thể tải danh sách loại thiết bị");
  } finally {
    loadingCategories.value = false;
  }
};

const handleFileChange = (event) => {
  form.commitment_file = event.target.files?.[0] || null;
};

const handleConsumableQuantity = (groupIndex, value) => {
  const group = deviceGroups.value[groupIndex];
  if (!group) return;

  const maxAvailable = group.deviceUnits?.length || 0;
  const parsedValue = Number(value);
  const sanitizedValue = Number.isFinite(parsedValue) ? parsedValue : 0;
  const normalizedValue = Math.floor(
    Math.max(0, Math.min(sanitizedValue, maxAvailable))
  );

  group.quantity = normalizedValue;
  const selectedUnits =
    group.deviceUnits?.slice(0, normalizedValue).map((unit) => unit.id) || [];
  group.device_unit_ids = selectedUnits;
};

const submitReservation = async () => {
  if (submitting.value) return;
  submitting.value = true;
  Object.keys(errors).forEach((key) => delete errors[key]);
  try {
    for (let i = 0; i < deviceGroups.value.length; i++) {
      const group = deviceGroups.value[i];
      if (!group.category_id) {
        toast.error(`Nhóm thiết bị ${i + 1}: Vui lòng chọn loại thiết bị`);
        submitting.value = false;
        return;
      }

      if (!group.device_id) {
        toast.error(`Nhóm thiết bị ${i + 1}: Vui lòng chọn thiết bị`);
        submitting.value = false;
        return;
      }

      if (group.category_type === "consumable") {
        if (!group.quantity || group.quantity < 1) {
          toast.error(
            `Nhóm thiết bị ${i + 1}: Vui lòng nhập số lượng tiêu hao hợp lệ`
          );
          submitting.value = false;
          return;
        }
      } else if (!group.device_unit_ids || group.device_unit_ids.length === 0) {
        toast.error(
          `Nhóm thiết bị ${i + 1}: Vui lòng chọn ít nhất một đơn vị thiết bị`
        );
        submitting.value = false;
        return;
      }
    }

    const allDeviceUnits = deviceGroups.value.flatMap((group) =>
      (group.device_unit_ids || []).map((unitId) => ({
        device_unit_id: unitId,
      }))
    );

    if (!allDeviceUnits.length) {
      toast.error("Vui lòng chọn ít nhất một đơn vị thiết bị");
      submitting.value = false;
      return;
    }

    const payload = {
      reserved_from: form.reserved_from,
      reserved_until: form.reserved_until,
      notes: form.notes,
      devices: allDeviceUnits,
      commitment_file: form.commitment_file,
    };

    await reservationsService.createBorrower(payload);
    toast.success("Đã gửi yêu cầu đặt trước");
    router.push({ name: "borrower.reservations" });
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(errors, error.response.data.errors);
    } else {
      toast.error("Gửi yêu cầu thất bại");
    }
  } finally {
    submitting.value = false;
  }
};

const addDeviceGroup = () => {
  deviceGroups.value.push(createEmptyDeviceGroup());
};

const removeDeviceGroup = (groupIndex) => {
  if (deviceGroups.value.length > 1) {
    deviceGroups.value.splice(groupIndex, 1);
  }
};

const goBack = () => {
  router.push({ name: "borrower.reservations" });
};

onMounted(() => {
  loadCategories();
});
</script>
