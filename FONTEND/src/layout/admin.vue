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

<script>
import Header from "../components/ComponentHeader.vue";
import SideBar from "../components/common/SideBar.vue";

export default {
  name: "AdminLayout",
  components: {
    Header,
    SideBar,
  },
};
</script>
