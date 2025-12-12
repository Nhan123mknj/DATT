<template>
  <div class="relative">
    <!-- Hierarchy Line -->
    <div
      v-if="depth > 0"
      class="absolute left-0 top-0 bottom-0 border-l-2 border-gray-100"
      :style="{ left: `${depth * 20 + 12}px` }"
    ></div>

    <div
      class="group flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg hover:border-indigo-200 hover:shadow-sm transition-all duration-200 mb-2 relative"
      :style="{ marginLeft: `${depth * 24}px` }"
    >
      <!-- Connector Line (Horizontal) -->
      <div
        v-if="depth > 0"
        class="absolute -left-6 top-1/2 w-6 border-t-2 border-gray-100"
      ></div>

      <div class="flex-1 min-w-0 flex items-center gap-3">
        <!-- Drag Handle (Visual) -->
        <div
          class="text-gray-300 cursor-move opacity-0 group-hover:opacity-100 transition-opacity"
        >
          <font-awesome-icon icon="grip-vertical" />
        </div>

        <!-- Icon -->
        <div
          class="h-8 w-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 group-hover:text-indigo-600 group-hover:bg-indigo-50 transition-colors"
        >
          <font-awesome-icon :icon="item.icon || 'circle'" class="text-xs" />
        </div>

        <div class="min-w-0">
          <div class="flex items-center gap-2">
            <span
              class="font-medium text-gray-900 truncate group-hover:text-indigo-700 transition-colors"
            >
              {{ item.label }}
            </span>
            <span
              v-if="item.badge"
              class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
              :class="`bg-${item.badge_color}-100 text-${item.badge_color}-700`"
            >
              {{ item.badge }}
            </span>
            <span
              v-if="!item.is_active"
              class="h-1.5 w-1.5 rounded-full bg-gray-300"
              title="Đang ẩn"
            ></span>
          </div>
          <p class="text-xs text-gray-500 truncate mt-0.5 font-mono">
            {{ item.url || "—" }}
          </p>
        </div>
      </div>

      <div
        class="flex gap-1 ml-2 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity"
      >
        <button
          @click="$emit('edit', item)"
          class="h-8 w-8 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
          title="Sửa"
        >
          <font-awesome-icon icon="edit" class="text-sm" />
        </button>
        <button
          @click="$emit('delete', item)"
          class="h-8 w-8 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
          title="Xóa"
        >
          <font-awesome-icon icon="trash" class="text-sm" />
        </button>
      </div>
    </div>

    <!-- Children -->
    <div v-if="item.children && item.children.length > 0" class="relative">
      <MenuItem
        v-for="child in item.children"
        :key="child.id"
        :item="child"
        :depth="depth + 1"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: "MenuItem",
  props: {
    item: {
      type: Object,
      required: true,
    },
    depth: {
      type: Number,
      default: 0,
    },
  },
  emits: ["edit", "delete"],
};
</script>
