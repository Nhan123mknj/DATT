<template>
  <div class="signature-pad-container">
    <div class="border border-gray-300 rounded-lg bg-white relative">
      <canvas
        ref="canvas"
        class="w-full h-40 touch-none cursor-crosshair"
        @mousedown="startDrawing"
        @mousemove="draw"
        @mouseup="stopDrawing"
        @mouseleave="stopDrawing"
        @touchstart.prevent="startDrawing"
        @touchmove.prevent="draw"
        @touchend.prevent="stopDrawing"
      ></canvas>
      <div class="absolute bottom-2 right-2 flex gap-2">
        <button
          type="button"
          class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded text-gray-600"
          @click="clear"
        >
          Xóa
        </button>
      </div>
      <div
        v-if="isEmpty"
        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none text-gray-300 text-sm"
      >
        Ký tên vào đây
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue"]);

const canvas = ref(null);
const ctx = ref(null);
const isDrawing = ref(false);
const isEmpty = ref(true);

onMounted(() => {
  initCanvas();
  window.addEventListener("resize", resizeCanvas);
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", resizeCanvas);
});

const initCanvas = () => {
  const c = canvas.value;
  if (!c) return;
  const rect = c.getBoundingClientRect();
  c.width = rect.width;
  c.height = rect.height;
  ctx.value = c.getContext("2d");
  ctx.value.lineWidth = 2;
  ctx.value.lineCap = "round";
  ctx.value.strokeStyle = "#000";
};

const resizeCanvas = () => {
  initCanvas();
};

const getPos = (e) => {
  const c = canvas.value;
  const rect = c.getBoundingClientRect();
  const clientX = e.touches ? e.touches[0].clientX : e.clientX;
  const clientY = e.touches ? e.touches[0].clientY : e.clientY;
  return {
    x: clientX - rect.left,
    y: clientY - rect.top,
  };
};

const startDrawing = (e) => {
  isDrawing.value = true;
  isEmpty.value = false;
  const { x, y } = getPos(e);
  ctx.value.beginPath();
  ctx.value.moveTo(x, y);
};

const draw = (e) => {
  if (!isDrawing.value) return;
  const { x, y } = getPos(e);
  ctx.value.lineTo(x, y);
  ctx.value.stroke();
};

const stopDrawing = () => {
  if (isDrawing.value) {
    isDrawing.value = false;
    save();
  }
};

const clear = () => {
  const c = canvas.value;
  ctx.value.clearRect(0, 0, c.width, c.height);
  isEmpty.value = true;
  emit("update:modelValue", "");
};

const save = () => {
  const dataUrl = canvas.value.toDataURL("image/png");
  const base64 = dataUrl.split(",")[1];
  emit("update:modelValue", base64);
};

// Expose methods for parent component if needed
defineExpose({
  clear,
  isEmpty: () => isEmpty.value,
});
</script>
