<template>
  <nav class="flex justify-center mt-6">
    <ul class="inline-flex items-center space-x-2">
      <li v-for="(link, index) in links" :key="index">
        <button
          v-html="link.label"
          @click="goTo(link.url)"
          :disabled="!link.url"
          class="min-w-[38px] h-[38px] px-3 flex items-center justify-center text-sm font-medium border rounded-lg transition-all duration-150 border-gray-300 text-gray-700 bg-white hover:bg-gray-100 hover:text-gray-900 disabled:opacity-50 disabled:cursor-not-allowed"
          :class="{
            'bg-indigo-600 text-white border-indigo-600 focus:bg-indigo-700 hover:text-white':
              link.active,
          }"
        ></button>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  name: "Pagination",
  props: {
    links: { type: Array, required: true },
  },
  methods: {
    goTo(url) {
      if (!url) return;
      const page = new URL(url).searchParams.get("page");
      this.$emit("page-changed", Number(page));
    },
  },
};
</script>
