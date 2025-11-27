<template>
  <div class="relative" ref="dropdownRef">
    <div
      class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white cursor-pointer flex items-center justify-between"
      :class="{
        'border-indigo-500 ring-1 ring-indigo-500': isOpen,
        'border-red-500': error,
      }"
      @click="toggleDropdown"
    >
      <div class="flex-1 flex flex-wrap gap-1">
        <span v-if="selectedItems.length === 0" class="text-gray-400 text-sm">
          {{ placeholder }}
        </span>
        <span
          v-for="item in selectedItems"
          :key="getItemValue(item)"
          class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-sm"
        >
          {{ getItemLabel(item) }}
          <button
            type="button"
            @click.stop="removeItem(item)"
            class="text-indigo-600 hover:text-indigo-800"
          >
            <font-awesome-icon icon="times" class="text-xs" />
          </button>
        </span>
      </div>
      <font-awesome-icon
        :icon="isOpen ? 'chevron-up' : 'chevron-down'"
        class="text-gray-400 ml-2"
      />
    </div>

    <div
      v-if="isOpen"
      class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-hidden"
    >
      <div class="p-2 border-b border-gray-200">
        <input
          type="text"
          v-model="searchQuery"
          :placeholder="searchPlaceholder"
          class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
          @click.stop
        />
      </div>
      <div class="overflow-y-auto max-h-48">
        <div
          v-if="filteredItems.length === 0"
          class="px-4 py-3 text-sm text-gray-500 text-center"
        >
          Không tìm thấy
        </div>
        <div
          v-for="item in filteredItems"
          :key="getItemValue(item)"
          class="px-4 py-2 hover:bg-gray-50 cursor-pointer flex items-center justify-between"
          :class="{
            'bg-indigo-50': isSelected(item),
          }"
          @click="toggleItem(item)"
        >
          <span class="text-sm text-gray-700">{{ getItemLabel(item) }}</span>
          <font-awesome-icon
            v-if="isSelected(item)"
            icon="check"
            class="text-indigo-600 text-sm"
          />
        </div>
      </div>
    </div>

    <p v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  options: {
    type: Array,
    required: true,
  },
  placeholder: {
    type: String,
    default: "Chọn...",
  },
  searchPlaceholder: {
    type: String,
    default: "Tìm kiếm...",
  },
  labelKey: {
    type: String,
    default: "label",
  },
  valueKey: {
    type: String,
    default: "value",
  },
  multiple: {
    type: Boolean,
    default: true,
  },
  error: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);
const searchQuery = ref("");
const dropdownRef = ref(null);

const selectedItems = computed(() => {
  if (!props.modelValue || props.modelValue.length === 0) return [];
  return props.options.filter((item) =>
    props.modelValue.includes(getItemValue(item))
  );
});

const filteredItems = computed(() => {
  if (!searchQuery.value) return props.options;

  const query = searchQuery.value.toLowerCase();
  return props.options.filter((item) => {
    const label = getItemLabel(item).toLowerCase();
    return label.includes(query);
  });
});

const getItemLabel = (item) => {
  if (typeof item === "string") return item;
  return item[props.labelKey] || item.name || item.label || String(item);
};

const getItemValue = (item) => {
  if (typeof item === "string" || typeof item === "number") return item;
  return item[props.valueKey] || item.id || item.value || item;
};

const isSelected = (item) => {
  const value = getItemValue(item);
  return props.modelValue && props.modelValue.includes(value);
};

const toggleItem = (item) => {
  const value = getItemValue(item);
  let newValue = [...(props.modelValue || [])];

  if (isSelected(item)) {
    newValue = newValue.filter((v) => v !== value);
  } else {
    if (props.multiple) {
      newValue.push(value);
    } else {
      newValue = [value];
      isOpen.value = false;
    }
  }

  emit("update:modelValue", newValue);
};

const removeItem = (item) => {
  const value = getItemValue(item);
  const newValue = (props.modelValue || []).filter((v) => v !== value);
  emit("update:modelValue", newValue);
};

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = "";
  }
};

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>
