<template>
  <div class="w-screen h-screen flex bg-gray-100">
    <SideBar />
    <div class="flex-1 flex flex-col min-h-0">
      <Header />
      <main class="flex-1 overflow-auto p-4">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import Header from "../components/Header.vue";
import SideBar from "../components/SideBar.vue";
</script>

<style scoped></style>
