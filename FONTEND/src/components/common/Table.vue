<template>
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-500">
      <thead class="text-xs text-gray-700 uppercase bg-gray-50">
        <tr>
          <th v-if="selectable" class="p-4 w-4">
            <div class="flex items-center">
              <input
                type="checkbox"
                class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500"
                :checked="isAllSelected"
                @change="toggleAll"
              />
            </div>
          </th>
          <th class="px-6 py-3 w-16 text-center">STT</th>
          <th v-for="(header, key) in headers" :key="key" class="px-6 py-3">
            {{ header }}
          </th>
          <th class="px-6 py-3">Thao tác</th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="(item, index) in data"
          :key="item.id"
          class="bg-white border-b hover:bg-gray-100 transition"
          :class="{ 'bg-indigo-50': isSelected(item) }"
        >
          <td v-if="selectable" class="w-4 p-4">
            <div class="flex items-center">
              <input
                type="checkbox"
                class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500"
                :checked="isSelected(item)"
                @change="toggleItem(item)"
              />
            </div>
          </td>
          <td class="px-6 py-4 text-center text-gray-700 font-medium">
            <slot name="STT" :index="index" :item="item">
              {{ index + 1 }}
            </slot>
          </td>
          <td v-for="(header, key) in headers" :key="key" class="px-6 py-4">
            <slot :name="key" :item="item" :index="index">
              #{{ item[key] }}
            </slot>
          </td>

          <td class="px-6 py-4 space-x-2">
            <slot name="actions" :item="item">
              <button
                @click="$emit('edit', item)"
                class="text-blue-600 hover:underline"
              >
                Sửa
              </button>
              <button
                @click="$emit('delete', item)"
                class="text-red-600 hover:underline"
              >
                Xóa
              </button>
            </slot>
          </td>
        </tr>

        <tr v-if="data.length === 0">
          <td
            :colspan="Object.keys(headers).length + (selectable ? 2 : 1)"
            class="px-6 py-4 text-center"
          >
            <slot name="empty">
              <div class="text-gray-400 italic py-4">
                Không có dữ liệu để hiển thị
              </div>
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { computed } from "vue";

export default {
  name: "DataTable",
  props: {
    data: {
      type: Array,
      required: true,
      default: () => [],
    },
    headers: {
      type: Object,
      required: true,
      default: () => ({}),
    },
    selectable: {
      type: Boolean,
      default: false,
    },
    selectedItems: {
      type: Array,
      default: () => [],
    },
  },
  emits: ["edit", "delete", "update:selectedItems"],
  setup(props, { emit }) {
    const isSelected = (item) => {
      return props.selectedItems.some((selected) => selected.id === item.id);
    };

    const isAllSelected = computed(() => {
      return (
        props.data.length > 0 && props.data.every((item) => isSelected(item))
      );
    });

    const toggleAll = (event) => {
      if (event.target.checked) {
        emit("update:selectedItems", [...props.data]);
      } else {
        emit("update:selectedItems", []);
      }
    };

    const toggleItem = (item) => {
      let newSelectedItems = [...props.selectedItems];
      if (isSelected(item)) {
        newSelectedItems = newSelectedItems.filter((i) => i.id !== item.id);
      } else {
        newSelectedItems.push(item);
      }
      emit("update:selectedItems", newSelectedItems);
    };

    const editItem = (item) => emit("edit", item);
    const deleteItem = (item) => emit("delete", item);

    return {
      isSelected,
      isAllSelected,
      toggleAll,
      toggleItem,
      editItem,
      deleteItem,
    };
  },
};
</script>
