<template>
  <div class="animate-pulse">
    <!-- Table Header Skeleton -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div class="h-8 bg-gray-200 rounded w-48"></div>
        <div class="flex gap-3">
          <div class="h-10 bg-gray-200 rounded w-32"></div>
          <div class="h-10 bg-gray-200 rounded w-24"></div>
        </div>
      </div>
    </div>

    <!-- Table Skeleton -->
    <div class="overflow-x-auto">
      <div class="relative shadow-md sm:rounded-lg bg-white">
        <!-- Table Header -->
        <div class="px-6 py-3 bg-gray-50 border-b">
          <div class="flex space-x-6">
            <div
              v-for="i in 5"
              :key="i"
              class="h-4 bg-gray-200 rounded w-20"
            ></div>
            <div class="h-4 bg-gray-200 rounded w-16 ml-auto"></div>
          </div>
        </div>

        <!-- Table Body Rows -->
        <div class="divide-y divide-gray-200">
          <div v-for="i in 8" :key="i" class="px-6 py-4">
            <div class="flex space-x-6 items-center">
              <!-- Row cells -->
              <div class="h-4 bg-gray-200 rounded w-8"></div>
              <div class="h-4 bg-gray-200 rounded w-32"></div>
              <div class="h-4 bg-gray-200 rounded w-48"></div>
              <div class="h-6 bg-gray-200 rounded-full w-20"></div>
              <div class="h-6 bg-gray-200 rounded-full w-24"></div>
              <!-- Actions -->
              <div class="flex space-x-2 ml-auto">
                <div class="h-6 bg-gray-200 rounded w-12"></div>
                <div class="h-6 bg-gray-200 rounded w-12"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Loading skeleton component - không cần props hay logic gì đặc biệt
</script>

<style scoped>
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>
