<template>
  <div class="w-screen h-screen flex flex-col bg-gray-100">
    <Header />
    <main class="flex-1 overflow-auto p-4">
      <router-view />
    </main>
  </div>
</template>
<script setup>
import Header from "../components/Header.vue";
</script>
<style scoped></style>
