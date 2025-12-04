<template>
  <div>
    <div
      class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition mb-2"
      :style="{ paddingLeft: `${(depth + 1) * 16 + 12}px` }"
    >
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <font-awesome-icon
            v-if="item.icon"
            :icon="item.icon"
            class="text-gray-400 text-sm flex-shrink-0"
          />
          <span class="font-medium text-gray-900 truncate">{{
            item.label
          }}</span>
          <span
            v-if="item.badge"
            class="px-2 py-0.5 rounded text-xs font-medium flex-shrink-0"
            :class="`bg-${item.badge_color}-100 text-${item.badge_color}-700`"
          >
            {{ item.badge }}
          </span>
        </div>
        <p class="text-xs text-gray-500 truncate mt-0.5">
          {{ item.url || "—" }}
        </p>
      </div>

      <div class="flex gap-1 ml-2 flex-shrink-0">
        <button
          @click="$emit('edit', item)"
          class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded transition"
          title="Sửa"
        >
          <font-awesome-icon icon="edit" class="text-sm" />
        </button>
        <button
          @click="$emit('delete', item)"
          class="p-1.5 text-red-600 hover:bg-red-50 rounded transition"
          title="Xóa"
        >
          <font-awesome-icon icon="trash" class="text-sm" />
        </button>
      </div>
    </div>

    <!-- Children -->
    <div v-if="item.children && item.children.length > 0">
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
