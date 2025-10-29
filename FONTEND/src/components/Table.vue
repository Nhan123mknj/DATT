<template>
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-500">
      <thead class="text-xs text-gray-700 uppercase bg-gray-50">
        <tr>
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
        >
          <td class="px-6 py-4 text-center text-gray-700 font-medium">
            <slot name="STT" :index="index" :item="item">
              {{ index + 1 }}
            </slot>
          </td>
          <td v-for="(header, key) in headers" :key="key" class="px-6 py-4">
            <slot :name="key" :item="item" :index="index">
              {{ item[key] }}
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
            :colspan="Object.keys(headers).length + 1"
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

<script setup>
defineProps({
  data: { type: Array, required: true, default: () => [] },
  headers: { type: Object, required: true, default: () => ({}) },
});

defineEmits(["edit", "delete"]);
</script>
