<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          Bảng điều khiển nhân viên
        </h1>
        <p class="text-sm text-gray-500">
          Theo dõi và xử lý các yêu cầu đặt trước.
        </p>
      </div>
      <RouterLink
        :to="{ name: 'staff.reservations' }"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500"
      >
        <font-awesome-icon icon="clipboard-list" />
        Xem danh sách đặt trước
      </RouterLink>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
      <StatCard label="Chờ duyệt" :value="stats.pending">
        <template #icon>
          <font-awesome-icon icon="hourglass-half" />
        </template>
      </StatCard>
      <StatCard label="Đã duyệt" :value="stats.approved">
        <template #icon>
          <font-awesome-icon icon="circle-check" />
        </template>
      </StatCard>
      <StatCard label="Đã từ chối" :value="stats.rejected">
        <template #icon>
          <font-awesome-icon icon="circle-xmark" />
        </template>
      </StatCard>
      <StatCard label="Hôm nay" :value="todayCount">
        <template #icon>
          <font-awesome-icon icon="calendar-day" />
        </template>
      </StatCard>
    </div>

    <section class="bg-white rounded-2xl shadow-sm border border-gray-100">
      <header
        class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
      >
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Yêu cầu gần đây</h2>
          <p class="text-sm text-gray-500">5 yêu cầu cập nhật mới nhất</p>
        </div>
        <RouterLink
          class="text-sm text-indigo-600"
          :to="{ name: 'staff.reservations' }"
        >
          Xử lý ngay
        </RouterLink>
      </header>
      <div v-if="recentLoading" class="p-6 text-sm text-gray-400">
        Đang tải dữ liệu...
      </div>
      <div
        v-else-if="!recentReservations.length"
        class="p-6 text-sm text-gray-400"
      >
        Chưa có yêu cầu nào
      </div>
      <ul v-else class="divide-y divide-gray-100">
        <li
          v-for="item in recentReservations"
          :key="item.id"
          class="px-6 py-4 flex flex-col gap-2"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-900">
                #{{ item.id }} · {{ item.user?.name || "Người dùng" }}
              </p>
              <p class="text-xs text-gray-500">
                {{ formatDate(item.reserved_from) }} →
                {{ formatDate(item.reserved_until) }}
              </p>
            </div>
            <span
              class="px-3 py-1 rounded-full text-xs font-medium"
              :class="statusClasses(item.status)"
            >
              {{ statusLabel(item.status) }}
            </span>
          </div>
          <p class="text-xs text-gray-500 line-clamp-2">
            {{ item.notes || "Không có ghi chú" }}
          </p>
        </li>
      </ul>
    </section>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { RouterLink } from "vue-router";
import { useToast } from "vue-toastification";
import StatCard from "../../components/StatCard.vue";
import { reservationsService } from "../../services/reservations/reservationsService";

const toast = useToast();

const stats = ref({
  pending: 0,
  approved: 0,
  rejected: 0,
});

const todayCount = ref(0);
const recentReservations = ref([]);
const recentLoading = ref(false);

const formatDate = (value) => {
  if (!value) return "—";
  return new Date(value).toLocaleDateString("vi-VN");
};

const statusLabel = (status) => {
  const map = {
    pending: "Chờ duyệt",
    approved: "Đã duyệt",
    rejected: "Từ chối",
    completed: "Hoàn thành",
    cancelled: "Đã hủy",
  };
  return map[status] || status;
};

const statusClasses = (status) => {
  switch (status) {
    case "approved":
      return "bg-green-100 text-green-700";
    case "pending":
      return "bg-amber-100 text-amber-700";
    case "rejected":
      return "bg-red-100 text-red-600";
    default:
      return "bg-gray-100 text-gray-600";
  }
};

const fetchStats = async () => {
  try {
    const statuses = ["pending", "approved", "rejected"];
    const responses = await Promise.allSettled(
      statuses.map((status) =>
        reservationsService.listStaff({ status: [status], per_page: 1 })
      )
    );

    responses.forEach((res, index) => {
      if (res.status === "fulfilled") {
        const payload = res.value.data.data;
        stats.value[statuses[index]] = payload?.total || 0;
      }
    });

    const today = new Date().toISOString().split("T")[0];
    const { data } = await reservationsService.listStaff({
      from_date: today,
      to_date: today,
      per_page: 1,
    });
    todayCount.value = data.data?.total || 0;
  } catch (error) {
    toast.error("Không thể tải thống kê");
  }
};

const fetchRecentReservations = async () => {
  recentLoading.value = true;
  try {
    const { data } = await reservationsService.listStaff({ per_page: 5 });
    recentReservations.value = data.data?.data || [];
  } catch (error) {
    if (error.response?.status === 404) {
      recentReservations.value = [];
    } else {
      toast.error("Không thể tải dữ liệu gần đây");
    }
  } finally {
    recentLoading.value = false;
  }
};

onMounted(() => {
  fetchStats();
  fetchRecentReservations();
});
</script>
