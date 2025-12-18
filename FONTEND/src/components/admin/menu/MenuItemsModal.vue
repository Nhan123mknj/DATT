<template>
  <ModalForm
    :show="menuStore.showItemsModal"
    :title="`Items: ${menuStore.currentMenu?.name}`"
    @close="menuStore.closeItemsModal()"
    size="large"
  >
    <div class="space-y-4">
      <button
        @click="menuStore.openCreateItem()"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium"
      >
        <font-awesome-icon icon="plus" class="mr-2" />
        Thêm Item
      </button>

      <!-- Items Tree -->
      <div
        v-if="menuStore.rootItems.length > 0"
        class="space-y-2 max-h-96 overflow-y-auto"
      >
        <MenuItemRow
          v-for="item in menuStore.rootItems"
          :key="item.id"
          :item="item"
          @edit="menuStore.editItem"
          @delete="menuStore.deleteItemConfirm"
        />
      </div>

      <div v-else class="text-center py-6 text-gray-500">Chưa có item nào</div>
    </div>

    <template #footer>
      <button
        @click="menuStore.closeItemsModal()"
        class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800 font-medium"
      >
        Đóng
      </button>
    </template>
  </ModalForm>
</template>

<script setup>
import { useMenuStore } from "../../../stores/menuStore";
import ModalForm from "../../ModalForm.vue";
import MenuItemRow from "../../../pages/admin /MenuItemRow.vue";

const menuStore = useMenuStore();
</script>
