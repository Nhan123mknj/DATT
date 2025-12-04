<template>
  <div class="lg:col-span-1">
    <div
      class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
    >
      <!-- User Info Card -->
      <div class="p-6 bg-gradient-to-br from-indigo-500 to-blue-600">
        <div class="flex flex-col items-center text-center">
          <div class="relative mb-3">
            <div
              v-if="currentAvatar"
              class="w-24 h-24 rounded-full ring-4 ring-white/40 overflow-hidden bg-white/20"
            >
              <img
                :src="currentAvatar"
                alt="Ảnh đại diện"
                class="w-full h-full object-cover"
              />
            </div>
            <div
              v-else
              class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white text-2xl font-bold ring-4 ring-white/30"
            >
              {{ getInitials(currentUser.name) }}
            </div>
            <button
              type="button"
              class="absolute bottom-1 right-1 w-9 h-9 rounded-full bg-white text-indigo-600 shadow-lg flex items-center justify-center text-lg hover:bg-indigo-50 transition disabled:opacity-50"
              @click="triggerAvatarPicker"
              :disabled="avatarUploading"
              title="Cập nhật ảnh đại diện"
            >
              <svg
                v-if="avatarUploading"
                class="animate-spin h-4 w-4"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
              </svg>
              <span v-else><font-awesome-icon icon="plus" /> </span>
            </button>
            <input
              ref="avatarInputRef"
              type="file"
              accept="image/*"
              class="hidden"
              @change="handleAvatarChange"
            />
          </div>
          <p v-if="avatarError" class="text-xs text-red-100 mb-2">
            {{ avatarError }}
          </p>
          <p class="text-[11px] text-indigo-100 mb-2">
            PNG, JPG, WEBP • tối đa 5MB
          </p>
          <h3 class="text-lg font-semibold text-white">
            {{ currentUser.name }}
          </h3>
          <p class="text-sm text-indigo-100 mt-1">
            {{ roleLabel(currentUser.role) }}
          </p>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <nav class="p-2" aria-label="Tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="$emit('update:activeTab', tab.id)"
          :class="[
            activeTab === tab.id
              ? 'bg-indigo-50 text-indigo-600 font-semibold'
              : 'text-gray-700 hover:bg-gray-50',
            'w-full flex items-center gap-3 px-4 py-3 rounded-lg transition text-left text-sm',
          ]"
        >
          <span class="text-xl">{{ tab.icon }}</span>
          <span>{{ tab.label }}</span>
        </button>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { useUserHelpers } from "../../composables/useUserHelpers.js";
import { useUserAvatar } from "../../composables/useUserAvatar.js";
import { useUserAccount } from "../../composables/useUserAccount.js";

// Props
defineProps({
  activeTab: { type: String, required: true },
  tabs: { type: Array, required: true },
});

defineEmits(["update:activeTab"]);

const { getInitials, roleLabel } = useUserHelpers();
const { currentUser } = useUserAccount();
const {
  avatarInputRef,
  triggerAvatarPicker,
  handleAvatarChange,
  avatarUploading,
  avatarError,
  currentAvatar,
} = useUserAvatar(currentUser);
</script>
