<template>
    <input
      v-bind:value="strSearch"
      v-on:input ="handleSearch"
      type="text"
      class="px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
      placeholder="Tìm kiếm..."
    />
</template>
<script>
export default {
   name: "SearchBar",
   props: {
      strSearch: {
         type: String,
         default: "",
      },
   },
   data(){
    return {
       
    };
   },
   methods: {
      handleSearch(event) {
        this.$emit("handleSearch", event.target.value);
      },
   },
};
</script>