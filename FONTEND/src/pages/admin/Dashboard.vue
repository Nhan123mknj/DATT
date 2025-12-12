<template>
  <div class="space-y-6">
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Bảng điều khiển</h1>
        <p class="text-gray-500">
          Theo dõi nhanh tình trạng người dùng và thiết bị.
        </p>
      </div>
      <div class="flex flex-wrap gap-3">
        <RouterLink
          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500 transition"
          :to="{ name: 'admin.users' }"
        >
          <font-awesome-icon icon="user-plus" />
          Quản lý người dùng
        </RouterLink>
        <RouterLink
          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:border-indigo-200 hover:text-indigo-600 transition"
          :to="{ name: 'admin.devices' }"
        >
          <font-awesome-icon icon="microchip" />
          Thiết bị
        </RouterLink>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
      <StatCard
        label="Tổng người dùng"
        :value="stats.users"
        description="Bao gồm Admin, Staff và Borrower"
      >
        <template #icon>
          <font-awesome-icon icon="users" />
        </template>
      </StatCard>
      <StatCard
        label="Danh mục thiết bị"
        :value="stats.categories"
        description="Nhóm thiết bị đang hoạt động"
      >
        <template #icon>
          <font-awesome-icon icon="layer-group" />
        </template>
      </StatCard>
      <StatCard
        label="Thiết bị"
        :value="stats.devices"
        description="Số thiết bị đang quản lý"
      >
        <template #icon>
          <font-awesome-icon icon="toolbox" />
        </template>
      </StatCard>
      <StatCard
        label="Đang chờ duyệt"
        :value="stats.pendingReservations"
        description="Yêu cầu đặt trước chưa xử lý"
      >
        <template #icon>
          <font-awesome-icon icon="clock" />
        </template>
      </StatCard>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <section class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <header
          class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
        >
          <div>
            <h2 class="text-lg font-semibold text-gray-900">
              Đặt trước gần đây
            </h2>
            <p class="text-sm text-gray-500">
              Theo dõi những yêu cầu cần xử lý
            </p>
          </div>
          <RouterLink
            :to="{ name: 'staff.reservations' }"
            class="text-sm text-indigo-600 hover:text-indigo-500 font-medium"
          >
            Xem tất cả
          </RouterLink>
        </header>
        <div
          class="divide-y divide-gray-100"
          v-if="!reservationsLoading && recentReservations.length"
        >
          <article
            v-for="reservation in recentReservations"
            :key="reservation.id"
            class="px-6 py-4 flex flex-col gap-2"
          >
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm font-semibold text-gray-900">
                  #{{ reservation.id }} ·
                  {{ reservation.user?.name || "Không rõ người dùng" }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ formatDate(reservation.reserved_from) }} →
                  {{ formatDate(reservation.reserved_until) }}
                </p>
              </div>
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-medium',
                  statusClasses(reservation.status),
                ]"
              >
                {{ statusLabel(reservation.status) }}
              </span>
            </div>
            <p class="text-sm text-gray-500 line-clamp-2">
              {{ reservation.notes || "Không có ghi chú" }}
            </p>
          </article>
        </div>
        <div v-else class="p-6">
          <p v-if="reservationsLoading" class="text-sm text-gray-400">
            Đang tải dữ liệu...
          </p>
          <p v-else class="text-sm text-gray-400">Chưa có đặt trước nào</p>
        </div>
      </section>

      <section class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <header class="px-6 py-4 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-gray-900">Tác vụ nhanh</h2>
          <p class="text-sm text-gray-500">
            Truy cập nhanh tới các chức năng chính
          </p>
        </header>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <RouterLink
            v-for="action in quickActions"
            :key="action.label"
            :to="action.to"
            class="p-4 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50 transition flex flex-col gap-2"
          >
            <div class="text-indigo-600 text-xl">
              <font-awesome-icon :icon="action.icon" />
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">
                {{ action.label }}
              </p>
              <p class="text-xs text-gray-500">{{ action.description }}</p>
            </div>
          </RouterLink>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from "vue";
import { useToast } from "vue-toastification";
import { RouterLink } from "vue-router";
import StatCard from "../../components/common/StatCard.vue";
import { usersService } from "../../services/admin/usersService";
import { deviceCategoriesService } from "../../services/devices/deviceCategoriesService";
import { devicesService } from "../../services/devices/devicesService";
import { reservationsService } from "../../services/admin/reservationsSevice";
import useStatusLabel from "../../composables/utils/statusLabel";
import useFormatDate from "../../composables/utils/formatDate";
export default {
  name: "Dashboard",
  components: {
    StatCard,
    RouterLink,
  },
  setup() {
    const toast = useToast();
    const { statusReverseLabel, statusClasses } = useStatusLabel();
    const { formatDate } = useFormatDate();
    const stats = reactive({
      users: 0,
      categories: 0,
      devices: 0,
      pendingReservations: 0,
    });

    const quickActions = [
      {
        label: "Thêm người dùng",
        description: "Tạo tài khoản mới cho nhân sự",
        to: { name: "admin.users" },
        icon: "user-plus",
      },
      {
        label: "Tạo danh mục",
        description: "Quản lý nhóm thiết bị",
        to: { name: "admin.deviceCategories" },
        icon: "folder-plus",
      },
      {
        label: "Thêm thiết bị",
        description: "Cập nhật kho thiết bị",
        to: { name: "admin.devices" },
        icon: "microchip",
      },
      {
        label: "Đơn vị thiết bị",
        description: "Theo dõi tình trạng thiết bị",
        to: { name: "admin.deviceUnits" },
        icon: "boxes-stacked",
      },
    ];

    const recentReservations = ref([]);
    const reservationsLoading = ref(false);

    const loadReservations = async () => {
      reservationsLoading.value = true;
      try {
        const { data } = await reservationsService.getReservations({
          per_page: 5,
        });
        // console.log(data.data);
        recentReservations.value = data.data?.data || [];
        // console.log(recentReservations.value);
      } catch (error) {
        console.error("Load reservations error:", error);
      } finally {
        reservationsLoading.value = false;
      }
    };

    const fetchStats = async () => {
      try {
        const [usersRes, categoriesRes, devicesRes, pendingRes] =
          await Promise.allSettled([
            usersService.getAllUser({ page: 1 }),
            deviceCategoriesService.list({ page: 1 }),
            devicesService.list({ page: 1 }),
            reservationsService.getReservations({
              status: ["pending"],
              per_page: 1,
            }),
          ]);

        const getTotal = (res, path) => {
          if (res.status !== "fulfilled") return 0;
          const data = path ? res.value.data[path] : res.value.data;
          return data?.total ?? data?.data?.length ?? 0;
        };

        stats.users = getTotal(usersRes);
        stats.categories = getTotal(categoriesRes, "categories");
        stats.devices = getTotal(devicesRes, "devices");
        stats.pendingReservations = getTotal(pendingRes, "data");
      } catch (error) {
        toast.error("Không thể tải thống kê");
      }
    };

    onMounted(() => {
      fetchStats();
      loadReservations();
    });

    return {
      stats,
      quickActions,
      recentReservations,
      reservationsLoading,
      formatDate,
      statusLabel: statusReverseLabel,
      statusClasses,
    };
  },
};
</script>
