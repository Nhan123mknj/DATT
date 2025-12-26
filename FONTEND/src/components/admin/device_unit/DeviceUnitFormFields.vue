<template>
  <div class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
      <div>
        <label class="text-sm font-medium text-gray-700">Thiết bị *</label>
        <select
          v-model="form.device_id"
          class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-indigo-500"
        >
          <option value="">-- Chọn thiết bị --</option>
          <option v-for="device in devices" :key="device.id" :value="device.id">
            {{ device.name }}
          </option>
        </select>
        <p v-if="errors.device_id" class="text-xs text-red-500 mt-1">
          {{ errors.device_id?.[0] || errors.device_id }}
        </p>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Serial *</label>
        <input
          v-model="form.serial_number"
          type="text"
          class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-indigo-500"
          placeholder="Nhập serial number"
        />
        <p v-if="errors.serial_number" class="text-xs text-red-500 mt-1">
          {{ errors.serial_number?.[0] || errors.serial_number }}
        </p>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <div>
        <label class="text-sm font-medium text-gray-700">Trạng thái</label>
        <select
          v-model="form.status"
          class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-indigo-500"
        >
          <option
            v-for="status in statusOptions"
            :key="status.value"
            :value="status.value"
          >
            {{ status.label }}
          </option>
        </select>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <div>
        <label class="text-sm font-medium text-gray-700">Ngày mua</label>
        <input
          v-model="form.purchase_date"
          type="date"
          class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-indigo-500"
        />
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Hạn bảo hành</label>
        <input
          v-model="form.warranty_end"
          type="date"
          class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-indigo-500"
        />
      </div>
    </div>

    <div>
      <label class="text-sm font-medium text-gray-700">Ghi chú</label>
      <textarea
        v-model="form.notes"
        rows="3"
        class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-indigo-500 resize-none"
        placeholder="Tình trạng khi nhập kho..."
      ></textarea>
    </div>
  </div>
</template>

<script setup>
defineProps({
  form: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  devices: { type: Array, default: () => [] },
  statusOptions: { type: Array, required: true },
});
</script>
