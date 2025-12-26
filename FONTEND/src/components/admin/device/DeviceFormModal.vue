<template>
  <ModalForm
    :show="show"
    :title="title"
    @close="$emit('close')"
    @submit="submit"
  >
    <div class="space-y-4">
      <div>
        <label class="text-sm font-medium text-gray-700">Tên thiết bị</label>
        <input
          v-model="form.name"
          type="text"
          class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
        />
        <p v-if="errors.name" class="text-xs text-red-500 mt-1">
          {{ errors.name?.[0] || errors.name }}
        </p>
      </div>
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label class="text-sm font-medium text-gray-700">Danh mục</label>
          <select
            v-model="form.category_id"
            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
          >
            <option value="">-- Chọn danh mục --</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
          <p v-if="errors.category_id" class="text-xs text-red-500 mt-1">
            {{ errors.category_id?.[0] || errors.category_id }}
          </p>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Nhà sản xuất</label>
          <input
            v-model="form.manufacturer"
            type="text"
            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
          />
          <p v-if="errors.manufacturer" class="text-xs text-red-500 mt-1">
            {{ errors.manufacturer?.[0] || errors.manufacturer }}
          </p>
        </div>
      </div>
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label class="text-sm font-medium text-gray-700">Model</label>
          <input
            v-model="form.model"
            type="text"
            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
          />
          <p v-if="errors.model" class="text-xs text-red-500 mt-1">
            {{ errors.model?.[0] || errors.model }}
          </p>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Giá (VNĐ)</label>
          <input
            v-model.number="form.price"
            type="number"
            min="0"
            step="1000"
            placeholder="Nhập giá thiết bị"
            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
          />
          <p v-if="errors.price" class="text-xs text-red-500 mt-1">
            {{ errors.price?.[0] || errors.price }}
          </p>
        </div>
      </div>
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label class="text-sm font-medium text-gray-700">Trạng thái</label>
          <select
            v-model="form.is_active"
            class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200"
          >
            <option :value="true">Kích hoạt</option>
            <option :value="false">Tạm dừng</option>
          </select>
        </div>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700 mb-2 block"
          >Thông số kỹ thuật</label
        >
        <div class="space-y-2">
          <div
            v-for="(spec, index) in specList"
            :key="index"
            class="flex gap-2 items-start"
          >
            <input
              v-model="spec.key"
              type="text"
              placeholder="Tên thông số (VD: CPU)"
              class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm"
            />
            <input
              v-model="spec.value"
              type="text"
              placeholder="Giá trị (VD: Core i5)"
              class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm"
            />
            <button
              type="button"
              class="p-2 text-red-500 hover:bg-red-50 rounded-lg"
              @click="removeSpec(index)"
            >
              <font-awesome-icon icon="trash" />
            </button>
          </div>
          <button
            type="button"
            class="text-sm text-indigo-600 font-medium hover:text-indigo-700 flex items-center gap-1"
            @click="addSpec"
          >
            <font-awesome-icon icon="plus" /> Thêm thông số
          </button>
        </div>
      </div>
    </div>
    <template #footer>
      <div class="flex gap-3">
        <button
          type="button"
          class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600"
          @click="$emit('close')"
        >
          Hủy
        </button>
        <button
          type="submit"
          class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500"
          :disabled="isLoading"
        >
          {{ isLoading ? "Đang lưu..." : isEdit ? "Cập nhật" : "Thêm mới" }}
        </button>
      </div>
    </template>
  </ModalForm>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import ModalForm from "../../ModalForm.vue";
import { deviceService } from "../../../services/admin/deviceService";
import { useToast } from "vue-toastification";

const props = defineProps({
  show: Boolean,
  device: {
    type: Object,
    default: null,
  },
  categories: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close", "saved"]);
const toast = useToast();
const isLoading = ref(false);
const errors = ref({});

const isEdit = computed(() => !!props.device);
const title = computed(() =>
  isEdit.value ? "Cập nhật thiết bị" : "Thêm thiết bị"
);

const form = reactive({
  id: null,
  name: "",
  category_id: "",
  manufacturer: "",
  model: "",
  price: null,
  specifications: null,
  is_active: true,
});

const specList = ref([{ key: "", value: "" }]);

const resetForm = () => {
  form.id = null;
  form.name = "";
  form.category_id = "";
  form.manufacturer = "";
  form.model = "";
  form.price = null;
  form.specifications = null;
  form.is_active = true;
  specList.value = [{ key: "", value: "" }];
  errors.value = {};
};

watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      if (props.device) {
        Object.assign(form, props.device);
        if (form.specifications && typeof form.specifications === "object") {
          specList.value = Object.entries(form.specifications).map(
            ([key, value]) => ({ key, value })
          );
        } else {
          specList.value = [{ key: "", value: "" }];
        }
      } else {
        // Create mode
        resetForm();
      }
    }
  }
);

const addSpec = () => {
  specList.value.push({ key: "", value: "" });
};

const removeSpec = (index) => {
  specList.value.splice(index, 1);
};

const submit = async () => {
  isLoading.value = true;
  errors.value = {};

  // Transform specs
  const specs = {};
  specList.value.forEach((item) => {
    if (item.key.trim()) {
      specs[item.key.trim()] = item.value.trim();
    }
  });
  form.specifications = specs;

  try {
    if (isEdit.value) {
      await deviceService.update(form.id, form);
      toast.success("Cập nhật thành công");
    } else {
      await deviceService.create(form);
      toast.success("Thêm mới thành công");
    }
    emit("saved");
    emit("close");
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      toast.error(error.response?.data?.message || "Có lỗi xảy ra");
    }
  } finally {
    isLoading.value = false;
  }
};
</script>
