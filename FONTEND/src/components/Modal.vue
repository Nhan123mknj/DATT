<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
      <div
        class="fixed inset-0 bg-white/20 backdrop-blur-sm transition-opacity"
        @click="$emit('close')"
      ></div>

      <div class="flex items-center justify-center min-h-screen p-4">
        <div
          class="relative bg-white rounded-lg shadow-xl w-full mx-auto"
          :class="{
            'max-w-md': size === 'small' || !size,
            'max-w-2xl': size === 'medium',
            'max-w-4xl': size === 'large',
            'max-w-6xl': size === 'xl',
          }"
        >
          <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-medium">{{ title }}</h3>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-500 focus:outline-none"
            >
              <span class="text-2xl">&times;</span>
            </button>
          </div>

          <!-- Body -->
          <div class="p-6 max-h-[calc(100vh-200px)] overflow-y-auto">
            <slot></slot>
          </div>

          <!-- Footer -->
          <div v-if="$slots.footer" class="flex justify-end gap-2 p-4 border-t">
            <slot name="footer"></slot>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  size: {
    type: String,
    default: "small",
    validator: (value) => ["small", "medium", "large", "xl"].includes(value),
  },
});

defineEmits(["close"]);
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
