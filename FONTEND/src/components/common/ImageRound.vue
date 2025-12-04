<template>
  <div>
    <img :src="src" :alt="alt" :class="imgClass" />
  </div>
</template>

<script>
export default {
  name: "ImageRound",
  props: {
    src: {
      type: String,
      required: true,
    },
    alt: {
      type: String,
      required: true,
    },
    size: {
      type: String,
      default: "w-10 h-10",
    },
  },
  computed: {
    imgClass() {
      return `${this.size} rounded-full object-cover`;
    },
  },
};
</script>
