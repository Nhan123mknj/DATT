<template>
  <div class="damage-history-page">
    <!-- Header -->
    <div class="header">
      <button
        @click="$router.push('/admin/reports/device-damage')"
        class="back-button"
      >
        ← Quay lại Báo cáo hư hỏng
      </button>
      <h1>Lịch sử hư hỏng thiết bị</h1>
      <div class="device-info">
        <h2>{{ device.device_name }}</h2>
        <p class="serial">
          Serial Number: <strong>{{ device.serial_number }}</strong>
        </p>
        <span class="status-badge" :class="`status-${device.current_status}`">
          {{ device.current_status }}
        </span>
      </div>
    </div>

    <div v-if="isLoading" class="loading">
      <div class="spinner"></div>
      <p>Đang tải dữ liệu...</p>
    </div>

    <div v-else>
      <div class="summary-grid">
        <div class="stat-card">
          <div class="stat-icon">🔧</div>
          <div class="stat-content">
            <p class="stat-label">Tổng số lần hư hỏng</p>
            <p class="stat-value">{{ totalDamages }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">💰</div>
          <div class="stat-content">
            <p class="stat-label">Tổng chi phí sửa chữa</p>
            <p class="stat-value">{{ formatCurrency(totalRepairCost) }}</p>
          </div>
        </div>
      </div>

      <div v-if="damageHistory.length === 0" class="no-data">
        <p>✅ Thiết bị chưa từng bị hư hỏng</p>
      </div>
      <div v-else class="damage-table">
        <h2>Chi tiết các lần hư hỏng</h2>

        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Thời gian</th>
                <th>Người gây ra</th>
                <th>Phiếu mượn</th>
                <th>Mô tả hư hỏng</th>
                <th>Mức độ</th>
                <th>Chi phí</th>
                <th>Phát hiện bởi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="damage in damageHistory" :key="damage.id">
                <td>
                  <div class="timestamp">
                    <strong>{{ damage.damage_date }}</strong>
                    <small>{{ damage.damage_date_human }}</small>
                  </div>
                </td>

                <td>
                  <div class="user-info">
                    <strong>{{ damage.caused_by.name }}</strong>
                    <small>{{ damage.caused_by.code }}</small>
                    <small>{{ damage.caused_by.email }}</small>
                    <span class="badge">{{
                      getRoleLabel(damage.caused_by.role)
                    }}</span>
                  </div>
                </td>

                <td>
                  <router-link
                    :to="`/admin/borrows?id=${damage.borrow.id}`"
                    class="borrow-link"
                  >
                    #{{ damage.borrow.id }}
                  </router-link>
                  <div class="borrow-dates">
                    <small>Mượn: {{ damage.borrow.borrowed_date }}</small>
                    <small>Trả: {{ damage.borrow.return_date }}</small>
                    <small v-if="damage.borrow.is_late" class="late-badge">
                      ⚠️ Trễ {{ damage.borrow.late_days }} ngày
                    </small>
                  </div>
                </td>

                <td>
                  <p class="damage-desc">{{ damage.damage_description }}</p>
                  <small class="condition-change">{{
                    damage.condition_change
                  }}</small>
                </td>

                <!-- Mức độ -->
                <td>
                  <span
                    class="damage-badge"
                    :class="getDamageLevelClass(damage.damage_level)"
                  >
                    {{ damage.damage_level }}
                  </span>
                </td>

                <td>
                  <strong class="cost">{{
                    formatCurrency(damage.damage_fee)
                  }}</strong>
                </td>

                <td>
                  <div class="staff-info">
                    <strong>{{ damage.detected_by.name }}</strong>
                    <small>{{ damage.detected_by.at }}</small>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useToast } from "vue-toastification";
import { activityLogService } from "../../../services/activityLogService";

const route = useRoute();
const toast = useToast();

const isLoading = ref(true);
const device = ref({});
const totalDamages = ref(0);
const totalRepairCost = ref(0);
const damageHistory = ref([]);

onMounted(async () => {
  await loadDamageHistory();
});

const loadDamageHistory = async () => {
  try {
    isLoading.value = true;
    const response = await activityLogService.getDeviceUnitDamageHistory(
      route.params.deviceUnitId
    );
    device.value = response.data.device_unit;
    totalDamages.value = response.data.total_damages;
    totalRepairCost.value = response.data.total_repair_cost;
    damageHistory.value = response.data.damage_history;
  } catch (error) {
    console.error("Error loading damage history:", error);
    toast.error("Không thể tải lịch sử hư hỏng");
  } finally {
    isLoading.value = false;
  }
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
};

const getDamageLevelClass = (level) => {
  const classes = {
    "Hư hỏng nhẹ": "damage-minor",
    "Hư hỏng nặng": "damage-major",
    "Hỏng hoàn toàn": "damage-broken",
  };
  return classes[level] || "";
};

const getRoleLabel = (role) => {
  const labels = {
    student: "Sinh viên",
    teacher: "Giảng viên",
    staff: "Nhân viên",
    admin: "Quản trị viên",
  };
  return labels[role] || role;
};
</script>

<style scoped>
.damage-history-page {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.header {
  margin-bottom: 2rem;
}

.back-button {
  background: #f3f4f6;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  cursor: pointer;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.back-button:hover {
  background: #e5e7eb;
}

.header h1 {
  font-size: 2rem;
  font-weight: bold;
  color: #111827;
  margin-bottom: 0.5rem;
}

.device-info {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-top: 1rem;
}

.device-info h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.5rem;
}

.serial {
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
}

.status-available {
  background: #d1fae5;
  color: #065f46;
}

.status-maintenance {
  background: #fed7aa;
  color: #9a3412;
}

.loading {
  text-align: center;
  padding: 4rem 0;
}

.spinner {
  border: 4px solid #f3f4f6;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin: 2rem 0;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  gap: 1rem;
  align-items: center;
}

.stat-icon {
  font-size: 3rem;
}

.stat-label {
  color: #6b7280;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
  color: #dc2626;
}

.no-data {
  background: white;
  padding: 4rem 2rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.no-data p {
  font-size: 1.25rem;
  color: #059669;
}

.damage-table {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-top: 2rem;
}

.damage-table h2 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.table-responsive {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th {
  background: #f9fafb;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.875rem;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  white-space: nowrap;
}

td {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  vertical-align: top;
}

.timestamp {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.timestamp strong {
  color: #111827;
}

.timestamp small {
  color: #6b7280;
  font-size: 0.75rem;
}

.user-info,
.staff-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-info strong,
.staff-info strong {
  color: #111827;
}

.user-info small,
.staff-info small {
  color: #6b7280;
  font-size: 0.75rem;
}

.badge {
  display: inline-block;
  padding: 0.125rem 0.5rem;
  background: #dbeafe;
  color: #1e40af;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  margin-top: 0.25rem;
  width: fit-content;
}

.borrow-link {
  color: #3b82f6;
  font-weight: 600;
  text-decoration: none;
  font-size: 1.125rem;
}

.borrow-link:hover {
  text-decoration: underline;
}

.borrow-dates {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  margin-top: 0.5rem;
}

.borrow-dates small {
  color: #6b7280;
  font-size: 0.75rem;
}

.late-badge {
  color: #dc2626 !important;
  font-weight: 600 !important;
}

.damage-desc {
  color: #111827;
  margin-bottom: 0.5rem;
}

.condition-change {
  color: #6b7280;
  font-size: 0.75rem;
}

.damage-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
  display: inline-block;
}

.damage-minor {
  background: #fef3c7;
  color: #92400e;
}

.damage-major {
  background: #fed7aa;
  color: #9a3412;
}

.damage-broken {
  background: #fecaca;
  color: #991b1b;
}

.cost {
  color: #dc2626;
  font-size: 1.125rem;
}
</style>
