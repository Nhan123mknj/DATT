<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div
          class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"
          @click="closeOnBackdrop && $emit('close')"
        ></div>

        <div
          class="flex min-h-full items-center justify-center p-4 text-center"
        >
          <div
            class="relative w-full transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-xl transition-all"
            :class="{
              'max-w-md': size === 'small',
              'max-w-lg': size === 'medium',
              'max-w-2xl': size === 'large',
              'max-w-4xl': size === 'xl',
              'max-w-6xl': size === '2xl',
            }"
          >
            <div
              class="flex items-center justify-between border-b border-gray-100 px-6 py-4"
            >
              <h3 class="text-lg font-semibold leading-6 text-gray-900">
                {{ title }}
              </h3>
              <button
                @click="$emit('close')"
                class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none transition-colors"
              >
                <svg
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>

            <div class="px-6 py-4">
              <slot></slot>
            </div>
            <div
              v-if="$slots.footer"
              class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3"
            >
              <slot name="footer"></slot>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
<script>
export default {
  name: "Modal",
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    title: {
      type: String,
      default: "",
    },
    size: {
      type: String,
      default: "medium",
      validator: (value) =>
        ["small", "medium", "large", "xl", "2xl"].includes(value),
    },
    closeOnBackdrop: {
      type: Boolean,
      default: true,
    },
  },
  emits: ["close"],
  methods: {
    close() {
      this.$emit("close");
    },
  },
};
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

.modal-enter-active .transform,
.modal-leave-active .transform {
  transition: all 0.3s ease-out;
}

.modal-enter-from .transform,
.modal-leave-to .transform {
  opacity: 0;
  transform: scale(0.95);
}
</style>
