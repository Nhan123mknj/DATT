<template>
  <nav class="flex justify-center mt-6">
    <ul class="inline-flex items-center space-x-2">
      <li v-for="(link, index) in links" :key="index">
        <button
          {{
          link.label
          }}
          @click="goTo(link.url)"
          :disabled="!link.url || link.active"
          :class="[
            'min-w-[38px] h-[38px] px-3 flex items-center justify-center text-sm font-medium border rounded-lg transition-all duration-200',
            link.active
              ? 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 cursor-default shadow-sm'
              : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400',
            !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
          ]"
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
