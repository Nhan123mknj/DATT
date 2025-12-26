<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
    @click.self="$emit('close')"
  >
    <div
      class="bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
    >
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900">Thanh lý thiết bị</h3>
        <p class="text-sm text-gray-500 mt-1">Nhập thông tin thanh lý</p>
      </div>

      <div class="p-6 space-y-4">

        <div v-if="deviceUnit" class="bg-gray-50 rounded-lg p-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600">Thiết bị:</p>
              <p class="font-medium text-gray-900">
                {{ deviceUnit.device?.name }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Serial:</p>
              <p class="font-medium text-gray-900">
                {{ deviceUnit.serial_number }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Giá gốc:</p>
              <p class="font-medium text-gray-900">
                {{ formatCurrency(deviceUnit.device?.price) }}
              </p>
            </div>
            <div v-if="suggestedValue > 0">
              <p class="text-sm text-gray-600">Giá đề xuất:</p>
              <p class="font-medium text-green-600">
                {{ formatCurrency(suggestedValue) }}
              </p>
              <p class="text-xs text-gray-500">
                (Khấu hao {{ depreciationPercentage }}%)
              </p>
            </div>
          </div>
        </div>

        <!-- Retirement Method -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Phương thức thanh lý <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 gap-3">
            <label
              v-for="method in retirementMethods"
              :key="method.value"
              class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all"
              :class="
                form.retirement_method === method.value
                  ? 'border-orange-500 bg-orange-50'
                  : 'border-gray-200 hover:border-gray-300'
              "
            >
              <input
                type="radio"
                v-model="form.retirement_method"
                :value="method.value"
                class="mr-3"
              />
              <div>
                <span class="text-2xl mr-2">{{ method.icon }}</span>
                <span class="font-medium">{{ method.label }}</span>
              </div>
            </label>
          </div>
        </div>

        <!-- Retirement Value (only for sold) -->
        <div v-if="form.retirement_method === 'sold'" class="space-y-3">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <p class="text-sm text-blue-800">
              💡 <strong>Giá đề xuất:</strong>
              {{ formatCurrency(suggestedValue) }}
              <span class="text-xs"
                >(Dựa trên khấu hao {{ depreciationPercentage }}%)</span
              >
            </p>
            <p class="text-xs text-blue-600 mt-1">
              Bạn có thể để trống để dùng giá đề xuất, hoặc nhập giá khác
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Giá bán (VNĐ)
            </label>
            <input
              type="number"
              v-model.number="form.retirement_value"
              :placeholder="suggestedValue"
              min="0"
              step="100000"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Thông tin người mua
            </label>
            <input
              type="text"
              v-model="form.buyer_info"
              placeholder="Tên, SĐT, Email..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Lý do thanh lý <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="form.retire_reason"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
          ></textarea>
          <p v-if="errors.retire_reason" class="text-red-500 text-sm mt-1">
            {{ errors.retire_reason }}
          </p>
        </div>

        <div
          v-if="form.retirement_method"
          class="bg-gray-50 border border-gray-200 rounded-lg p-4"
        >
          <p class="font-medium text-gray-900 mb-2">Tóm tắt:</p>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">Giá gốc:</span>
              <span class="font-medium">{{
                formatCurrency(deviceUnit?.device?.price)
              }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Giá thanh lý:</span>
              <span class="font-medium">{{
                formatCurrency(retirementValue)
              }}</span>
            </div>
            <div class="flex justify-between border-t pt-2">
              <span class="text-gray-600">{{ profitLossLabel }}:</span>
              <span
                :class="profitLoss >= 0 ? 'text-green-600' : 'text-red-600'"
                class="font-bold"
              >
                {{ formatCurrency(profitLoss) }} ({{ profitLossPercentage }}%)
              </span>
            </div>
          </div>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
          <div class="flex">
            <font-awesome-icon
              icon="exclamation-triangle"
              class="text-orange-600 mr-3 mt-0.5"
            />
            <div class="text-sm text-orange-800">
              <p class="font-medium">Lưu ý:</p>
              <ul class="list-disc list-inside mt-1 space-y-1">
                <li>Thiết bị sẽ được đánh dấu là "Đã thanh lý"</li>
                <li>Không thể hoàn tác sau khi thanh lý</li>
                <li>Thông tin thanh lý sẽ được lưu vào lịch sử</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="p-6 border-t border-gray-200 flex gap-3 justify-end">
        <button
          @click="$emit('close')"
          class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700"
        >
          Hủy
        </button>
        <button
          @click="handleRetire"
          :disabled="!canSubmit || isSubmitting"
          class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isSubmitting ? "Đang xử lý..." : "Xác nhận thanh lý" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { deviceUnitService } from "../../../services/admin/deviceUnitService";

const props = defineProps({
  show: Boolean,
  deviceUnit: Object,
});

const emit = defineEmits(["close", "retire"]);

const form = ref({
  retire_reason: "",
  retirement_method: "discarded",
  retirement_value: null,
  buyer_info: "",
});

const errors = ref({});
const isSubmitting = ref(false);
const suggestedValue = ref(0);
const depreciationPercentage = ref(0);

const retirementMethods = [
  { value: "sold", label: "Bán lại", icon: "💰" },
  { value: "discarded", label: "Vứt bỏ/Hủy", icon: "🗑️" },
];

const retirementValue = computed(() => {
  if (form.value.retirement_method === "sold") {
    return form.value.retirement_value || suggestedValue.value;
  }
  return 0;
});

const profitLoss = computed(() => {
  const originalPrice = props.deviceUnit?.device?.price || 0;
  return retirementValue.value - originalPrice;
});

const profitLossPercentage = computed(() => {
  const originalPrice = props.deviceUnit?.device?.price || 0;
  if (originalPrice === 0) return 0;
  return ((profitLoss.value / originalPrice) * 100).toFixed(2);
});

const profitLossLabel = computed(() => {
  return profitLoss.value >= 0 ? "Lời" : "Lỗ";
});

const canSubmit = computed(() => {
  return form.value.retire_reason.trim() && form.value.retirement_method;
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(value || 0);
};

const loadSuggestedValue = async () => {
  if (!props.deviceUnit?.id) return;

  try {
    const response = await deviceUnitService.show(props.deviceUnit.id);
    if (response.data) {
      suggestedValue.value = response.data.suggested_retirement_value || 0;
      depreciationPercentage.value = response.data.depreciation_percentage || 0;
    }
  } catch (error) {
    console.error("Failed to load suggested value:", error);
  }
};

watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      form.value = {
        retire_reason: "",
        retirement_method: "discarded",
        retirement_value: null,
        buyer_info: "",
      };
      errors.value = {};
      isSubmitting.value = false;
      loadSuggestedValue();
    }
  }
);

const handleRetire = () => {
  if (!form.value.retire_reason.trim()) {
    errors.value.retire_reason = "Vui lòng nhập lý do thanh lý";
    return;
  }

  errors.value = {};
  isSubmitting.value = true;
  emit("retire", form.value);
};
</script>
