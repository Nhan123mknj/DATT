<template>
  <div class="space-y-6 max-w-5xl mx-auto">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100"
    >
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Tạo đặt trước thiết bị</h1>
        <p class="text-sm text-gray-500 mt-1">
          Chọn thời gian và thiết bị bạn muốn mượn.
        </p>
      </div>
      <div class="flex gap-3">
        <Button
          color="secondary"
          @click="goBack"
          class="hover:bg-gray-100 border-gray-200"
        >
          <font-awesome-icon icon="arrow-left" class="mr-2" />
          Trở lại danh sách
        </Button>
      </div>
    </div>

    <div
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-8"
    >
      <form class="space-y-8" @submit.prevent="submitReservation">
        <!-- Thông tin thời gian -->
        <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-6">
          <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center">
            <div
              class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3"
            >
              <font-awesome-icon icon="calendar-alt" />
            </div>
            Thời gian đặt trước
          </h3>
          <div class="grid gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Từ ngày <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.reserved_from"
                type="datetime-local"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm"
                :min="new Date().toISOString().split('T')[0]"
              />
              <p
                v-if="errors.reserved_from"
                class="text-xs text-red-500 mt-1.5 font-medium"
              >
                {{ errors.reserved_from?.[0] || errors.reserved_from }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Đến ngày <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.reserved_until"
                type="datetime-local"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm"
                :min="
                  form.reserved_from || new Date().toISOString().split('T')[0]
                "
              />
              <p
                v-if="errors.reserved_until"
                class="text-xs text-red-500 mt-1.5 font-medium"
              >
                {{ errors.reserved_until?.[0] || errors.reserved_until }}
              </p>
            </div>
          </div>
        </div>

        <!-- Danh sách thiết bị -->
        <div>
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-bold text-gray-900 flex items-center">
              <div
                class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3"
              >
                <font-awesome-icon icon="boxes" />
              </div>
              Thiết bị đặt trước
              <span
                v-if="totalSelectedDevices > 0"
                class="ml-3 px-2.5 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold"
              >
                {{ totalSelectedDevices }} thiết bị
              </span>
            </h3>
            <button
              type="button"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition-colors"
              @click="addDeviceGroup"
            >
              <font-awesome-icon icon="plus" class="mr-2" />
              Thêm nhóm thiết bị
            </button>
          </div>

          <div class="mb-8">
            <div
              class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 ml-1"
            >
              Hướng dẫn chọn thiết bị
            </div>
            <div
              class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50/30"
            >
              <table class="w-full text-sm text-gray-700">
                <thead
                  class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500 font-semibold"
                >
                  <tr>
                    <th class="px-6 py-3 text-left">Loại thiết bị</th>
                    <th class="px-6 py-3 text-left">Cách chọn</th>
                    <th class="px-6 py-3 text-left">Lý do</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                  <tr
                    v-for="guide in deviceTypeGuides"
                    :key="guide.key"
                    class="hover:bg-gray-50/50 transition-colors"
                  >
                    <td class="px-6 py-3 font-medium text-gray-900">
                      {{ guide.label }}
                    </td>
                    <td class="px-6 py-3 text-gray-600">{{ guide.control }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ guide.reason }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="space-y-6">
            <div
              v-for="(group, groupIndex) in deviceGroups"
              :key="groupIndex"
              class="border border-gray-200 rounded-xl p-6 bg-white hover:border-indigo-300 hover:shadow-md transition-all duration-200 group relative"
            >
              <div class="flex items-start justify-between mb-6">
                <div class="flex items-center">
                  <div
                    class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center font-bold text-sm mr-3 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors"
                  >
                    {{ groupIndex + 1 }}
                  </div>
                  <div>
                    <div class="flex items-center gap-3 flex-wrap">
                      <h4 class="text-base font-bold text-gray-900">
                        Nhóm thiết bị {{ groupIndex + 1 }}
                      </h4>
                      <span
                        v-if="group.category_type"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ring-1 ring-inset"
                        :class="typeBadgeClass(group.category_type)"
                      >
                        {{ getDeviceTypeLabel(group.category_type) }}
                      </span>
                    </div>
                  </div>
                </div>
                <button
                  type="button"
                  class="text-gray-400 hover:text-red-500 p-2 rounded-lg hover:bg-red-50 transition-colors absolute top-4 right-4"
                  @click="removeDeviceGroup(groupIndex)"
                  v-if="deviceGroups.length > 1"
                  title="Xóa nhóm"
                >
                  <font-awesome-icon icon="trash-alt" />
                </button>
              </div>

              <div class="space-y-5">
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
                    search-placeholder="Tìm kiếm..."
                    class="rounded-xl"
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
                  class="animate-fade-in-down"
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
                    search-placeholder="Tìm kiếm..."
                    class="rounded-xl"
                    @update:modelValue="onDeviceSelect(groupIndex, $event)"
                  />
                </div>

                <div
                  v-if="
                    group.device_id &&
                    group.deviceUnits &&
                    group.deviceUnits.length > 0
                  "
                  class="animate-fade-in-down"
                >
                  <template v-if="group.category_type === 'consumable'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Số lượng cần mượn <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-4">
                      <div class="relative">
                        <input
                          type="number"
                          min="0"
                          :max="group.deviceUnits.length"
                          :value="group.quantity"
                          class="w-32 px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-center"
                          placeholder="0"
                          @input="
                            handleConsumableQuantity(
                              groupIndex,
                              $event.target.value
                            )
                          "
                        />
                      </div>
                      <span class="text-sm text-gray-500 font-medium">
                        / {{ group.deviceUnits.length }} khả dụng
                      </span>
                    </div>
                  </template>
                  <template v-else>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Đơn vị thiết bị <span class="text-red-500">*</span>
                      <span class="text-xs font-normal text-gray-500 ml-2"
                        >(Có thể chọn nhiều)</span
                      >
                    </label>
                    <div
                      class="space-y-1 rounded-xl border border-gray-200 p-2 max-h-60 overflow-y-auto bg-gray-50/30 custom-scrollbar"
                    >
                      <label
                        v-for="unit in getDeviceUnitOptions(groupIndex)"
                        :key="unit.id"
                        class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-white hover:shadow-sm transition-all cursor-pointer group/item"
                        :class="{
                          'bg-indigo-50/50': group.device_unit_ids.includes(
                            unit.id
                          ),
                        }"
                      >
                        <div class="flex items-center gap-3">
                          <input
                            type="checkbox"
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 transition-colors"
                            :value="unit.id"
                            v-model="group.device_unit_ids"
                          />
                          <span
                            class="font-medium text-gray-700 group-hover/item:text-indigo-700 transition-colors"
                            >{{ unit.displayName }}</span
                          >
                        </div>
                        <span
                          class="text-xs text-gray-400 font-mono bg-gray-100 px-2 py-0.5 rounded"
                          >ID: {{ unit.id }}</span
                        >
                      </label>
                    </div>
                    <p
                      v-if="errors.devices && groupIndex === 0"
                      class="text-xs text-red-500 mt-2 font-medium"
                    >
                      {{ errors.devices[0] || errors.devices }}
                    </p>
                    <div class="flex items-center justify-between mt-2">
                      <p
                        v-if="
                          group.device_unit_ids &&
                          group.device_unit_ids.length > 0
                        "
                        class="text-xs font-medium text-indigo-600"
                      >
                        Đã chọn: {{ group.device_unit_ids.length }} đơn vị
                      </p>
                      <p
                        v-if="group.category_type === 'expensive'"
                        class="text-xs text-amber-600 font-medium bg-amber-50 px-2 py-1 rounded"
                      >
                        <font-awesome-icon
                          icon="exclamation-triangle"
                          class="mr-1"
                        />
                        Thiết bị đắt tiền cần theo dõi từng đơn vị riêng lẻ.
                      </p>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </div>

          <p
            v-if="errors.devices"
            class="text-xs text-red-500 mt-3 font-medium text-center bg-red-50 p-2 rounded-lg border border-red-100"
          >
            {{ errors.devices?.[0] || errors.devices }}
          </p>
        </div>

        <!-- Tóm tắt thiết bị đã chọn -->
        <div
          v-if="totalSelectedDevices > 0"
          class="bg-gradient-to-br from-indigo-50 to-white border border-indigo-100 rounded-xl p-6 shadow-sm"
        >
          <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center">
            <div
              class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3"
            >
              <font-awesome-icon icon="list-check" />
            </div>
            Tóm tắt thiết bị đã chọn
          </h3>
          <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar pr-2">
            <div
              v-for="(summary, idx) in getSelectedDevicesSummary()"
              :key="idx"
              class="flex items-center justify-between bg-white rounded-lg px-4 py-3 text-sm border border-gray-100 shadow-sm"
            >
              <div class="flex items-center">
                <font-awesome-icon
                  icon="check-circle"
                  class="text-green-500 mr-3 text-lg"
                />
                <div>
                  <span class="font-bold text-gray-800 block">{{
                    summary.name
                  }}</span>
                  <span class="text-xs text-gray-500 font-medium">{{
                    summary.category
                  }}</span>
                </div>
              </div>
              <span
                class="bg-gray-100 text-gray-700 font-bold px-3 py-1 rounded-full text-xs"
              >
                {{ summary.countLabel }}
              </span>
            </div>
          </div>
          <div
            class="mt-4 pt-4 border-t border-indigo-100 flex items-center justify-between"
          >
            <span class="font-semibold text-gray-700">Tổng cộng:</span>
            <span class="font-bold text-indigo-600 text-lg">
              {{ totalSelectedDevices }} thiết bị
            </span>
          </div>
        </div>

        <!-- Thông tin bổ sung -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
          <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center">
            <div
              class="w-8 h-8 rounded-lg bg-gray-200 text-gray-600 flex items-center justify-center mr-3"
            >
              <font-awesome-icon icon="info-circle" />
            </div>
            Thông tin bổ sung
          </h3>
          <div class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Ghi chú
              </label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition resize-none shadow-sm"
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
              <div class="flex items-center gap-4">
                <label
                  class="flex-1 cursor-pointer group"
                  :class="{
                    'border-2 border-dashed border-indigo-300 bg-indigo-50 hover:bg-indigo-100/50':
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
                  <div
                    class="px-4 py-4 rounded-xl text-center transition-colors"
                  >
                    <font-awesome-icon
                      :icon="
                        form.commitment_file
                          ? 'check-circle'
                          : 'cloud-upload-alt'
                      "
                      :class="
                        form.commitment_file
                          ? 'text-green-600'
                          : 'text-indigo-500'
                      "
                      class="text-2xl mb-2 block mx-auto"
                    />
                    <p
                      class="text-sm font-medium"
                      :class="
                        form.commitment_file
                          ? 'text-green-800'
                          : 'text-indigo-700'
                      "
                    >
                      {{
                        form.commitment_file
                          ? form.commitment_file.name
                          : "Nhấn để tải lên file cam kết"
                      }}
                    </p>
                    <p
                      v-if="!form.commitment_file"
                      class="text-xs text-indigo-400 mt-1"
                    >
                      PDF, DOC, JPG, PNG (Max 5MB)
                    </p>
                  </div>
                </label>
                <button
                  v-if="form.commitment_file"
                  type="button"
                  @click="form.commitment_file = null"
                  class="p-3 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl transition-colors border border-transparent hover:border-red-100"
                  title="Xóa file"
                >
                  <font-awesome-icon icon="trash-alt" class="text-lg" />
                </button>
              </div>
              <p
                v-if="errors.commitment_file"
                class="text-xs text-red-500 mt-2 font-medium"
              >
                {{ errors.commitment_file?.[0] || errors.commitment_file }}
              </p>
            </div>
          </div>
        </div>

        <div
          class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100"
        >
          <Button
            type="button"
            color="secondary"
            class="px-6 py-2.5"
            @click="goBack"
          >
            Hủy bỏ
          </Button>
          <Button
            type="submit"
            color="primary"
            class="px-8 py-2.5 shadow-lg shadow-indigo-200"
            :disabled="submitting || totalSelectedDevices === 0"
            :loading="submitting"
          >
            <font-awesome-icon icon="paper-plane" class="mr-2" />
            Gửi yêu cầu
          </Button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import Button from "../../components/common/Button.vue";
import MultiSelect from "../../components/common/MultiSelect.vue";
import { reservationsService } from "../../services/borrower/reservationsService";
import { deviceService } from "../../services/shared/deviceService";

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

export default {
  name: "BorrowerReservationCreate",
  components: {
    Button,
    MultiSelect,
  },
  data() {
    return {
      submitting: false,
      form: {
        reserved_from: "",
        reserved_until: "",
        notes: "",
        commitment_file: null,
      },
      errors: {},
      categories: [],
      deviceGroups: [this.createEmptyDeviceGroup()],
      loadingCategories: false,
      loadingDevices: false,
      loadingDeviceUnits: false,
      deviceTypeGuides: DEVICE_TYPE_GUIDES,
    };
  },
  computed: {
    categoryOptions() {
      return this.categories.map((cat) => ({
        id: cat.id,
        name: cat.name,
      }));
    },
    totalSelectedDevices() {
      return this.deviceGroups.reduce((total, group) => {
        if (group.category_type === "consumable") {
          return total + (group.quantity || 0);
        }
        return total + (group.device_unit_ids?.length || 0);
      }, 0);
    },
  },
  mounted() {
    this.loadCategories();
  },
  methods: {
    createEmptyDeviceGroup() {
      return {
        category_id: "",
        category_id_array: [],
        category_type: "",
        device_id: "",
        device_id_array: [],
        devices: [],
        deviceUnits: [],
        device_unit_ids: [],
        quantity: 0,
      };
    },
    getDeviceTypeLabel(type) {
      return DEVICE_TYPE_META[type]?.label || "Thiết bị thường";
    },
    typeBadgeClass(type) {
      switch (type) {
        case "expensive":
          return "bg-amber-100 text-amber-700 ring-amber-200";
        case "consumable":
          return "bg-emerald-100 text-emerald-700 ring-emerald-200";
        default:
          return "bg-indigo-100 text-indigo-700 ring-indigo-200";
      }
    },
    getSelectedDevicesSummary() {
      const summary = [];

      this.deviceGroups.forEach((group) => {
        const category = this.categories.find((c) => c.id == group.category_id);
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
    },
    getDeviceOptions(groupIndex) {
      const group = this.deviceGroups[groupIndex];
      if (!group.devices || group.devices.length === 0) return [];
      return group.devices.map((device) => ({
        id: device.id,
        displayName: `${device.name} (${device.model}) - ${device.manufacturer}`,
      }));
    },
    getDeviceUnitOptions(groupIndex) {
      const group = this.deviceGroups[groupIndex];
      if (!group.deviceUnits || group.deviceUnits.length === 0) return [];
      return group.deviceUnits.map((unit) => ({
        id: unit.id,
        displayName: unit.serial_number,
      }));
    },
    async onCategorySelect(groupIndex, selectedIds) {
      const group = this.deviceGroups[groupIndex];
      const categoryId =
        selectedIds && selectedIds.length > 0 ? selectedIds[0] : "";

      group.category_id = categoryId;
      group.category_id_array = selectedIds || [];
      group.quantity = 0;
      const category = this.categories.find((c) => c.id == categoryId);
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

      this.loadingDevices = true;
      try {
        const { data } = await deviceService.getDevicesByCategory(categoryId);
        group.devices = data.data || [];
        group.device_id = "";
        group.device_id_array = [];
        group.deviceUnits = [];
        group.device_unit_ids = [];
      } catch (error) {
        this.toast.error("Không thể tải danh sách thiết bị");
      } finally {
        this.loadingDevices = false;
      }
    },
    async onDeviceSelect(groupIndex, selectedIds) {
      const group = this.deviceGroups[groupIndex];
      const deviceId =
        selectedIds && selectedIds.length > 0 ? selectedIds[0] : "";

      group.device_id = deviceId;
      group.device_id_array = selectedIds || [];
      group.quantity = 0;

      if (!deviceId) {
        group.deviceUnits = [];
        group.device_unit_ids = [];
        return;
      }

      this.loadingDeviceUnits = true;
      try {
        const { data } = await deviceService.getDeviceUnitsByDevice(deviceId);
        group.deviceUnits = data.data || [];
        group.device_unit_ids = [];
        group.quantity = 0;
      } catch (error) {
        this.toast.error("Không thể tải danh sách đơn vị thiết bị");
      } finally {
        this.loadingDeviceUnits = false;
      }
    },
    async loadCategories() {
      this.loadingCategories = true;
      try {
        const { data } = await deviceService.getCategories();
        this.categories = data.data || [];
      } catch (error) {
        this.toast.error("Không thể tải danh sách loại thiết bị");
      } finally {
        this.loadingCategories = false;
      }
    },
    handleFileChange(event) {
      this.form.commitment_file = event.target.files?.[0] || null;
    },
    handleConsumableQuantity(groupIndex, value) {
      const group = this.deviceGroups[groupIndex];
      if (!group) return;

      const maxAvailable = group.deviceUnits?.length || 0;
      const parsedValue = Number(value);
      const sanitizedValue = Number.isFinite(parsedValue) ? parsedValue : 0;
      const normalizedValue = Math.floor(
        Math.max(0, Math.min(sanitizedValue, maxAvailable))
      );

      group.quantity = normalizedValue;
      const selectedUnits =
        group.deviceUnits?.slice(0, normalizedValue).map((unit) => unit.id) ||
        [];
      group.device_unit_ids = selectedUnits;
    },
    async submitReservation() {
      if (this.submitting) return;
      this.submitting = true;
      this.errors = {};
      try {
        for (let i = 0; i < this.deviceGroups.length; i++) {
          const group = this.deviceGroups[i];
          if (!group.category_id) {
            this.toast.error(
              `Nhóm thiết bị ${i + 1}: Vui lòng chọn loại thiết bị`
            );
            this.submitting = false;
            return;
          }

          if (!group.device_id) {
            this.toast.error(`Nhóm thiết bị ${i + 1}: Vui lòng chọn thiết bị`);
            this.submitting = false;
            return;
          }

          if (group.category_type === "consumable") {
            if (!group.quantity || group.quantity < 1) {
              this.toast.error(
                `Nhóm thiết bị ${i + 1}: Vui lòng nhập số lượng tiêu hao hợp lệ`
              );
              this.submitting = false;
              return;
            }
          } else if (
            !group.device_unit_ids ||
            group.device_unit_ids.length === 0
          ) {
            this.toast.error(
              `Nhóm thiết bị ${
                i + 1
              }: Vui lòng chọn ít nhất một đơn vị thiết bị`
            );
            this.submitting = false;
            return;
          }
        }

        const allDeviceUnits = this.deviceGroups.flatMap((group) =>
          (group.device_unit_ids || []).map((unitId) => ({
            device_unit_id: unitId,
          }))
        );

        if (!allDeviceUnits.length) {
          this.toast.error("Vui lòng chọn ít nhất một đơn vị thiết bị");
          this.submitting = false;
          return;
        }

        const payload = {
          reserved_from: this.form.reserved_from,
          reserved_until: this.form.reserved_until,
          notes: this.form.notes,
          devices: allDeviceUnits,
          commitment_file: this.form.commitment_file,
        };

        await reservationsService.createBorrower(payload);
        this.toast.success("Đã gửi yêu cầu đặt trước");
        this.router.push({ name: "borrower.reservations" });
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors;
        } else {
          this.toast.error("Gửi yêu cầu thất bại");
        }
      } finally {
        this.submitting = false;
      }
    },
    addDeviceGroup() {
      this.deviceGroups.push(this.createEmptyDeviceGroup());
    },
    removeDeviceGroup(groupIndex) {
      if (this.deviceGroups.length > 1) {
        this.deviceGroups.splice(groupIndex, 1);
      }
    },
    goBack() {
      this.router.push({ name: "borrower.reservations" });
    },
  },
  setup() {
    const router = useRouter();
    const toast = useToast();
    return { router, toast };
  },
};
</script>
