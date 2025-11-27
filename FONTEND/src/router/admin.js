import { requireAuth, requireRole } from './guards.js'

const admin = [
  {
    path: '/admin',
    name: 'Admin',
    component: () => import('../layout/admin.vue'),
    beforeEnter: [requireAuth, requireRole('admin')],
    children: [
      {
        path: 'dashboard',
        name: 'admin.dashboard',
        component: () => import('../pages/admin/Dashboard.vue'),
        meta: { title: 'Dashboard' },
      },
      {
        path: 'users',
        name: 'admin.users',
        component: () => import('../pages/admin/users/index.vue'),
        meta: { title: 'Quản lý người dùng' },
      },
      {
        path: 'device-categories',
        name: 'admin.deviceCategories',
        component: () => import('../pages/admin/device-categories/index.vue'),
        meta: { title: 'Danh mục thiết bị' },
      },
      {
        path: 'device-categories/create',
        name: 'admin.deviceCategories.create',
        component: () => import('../pages/admin/device-categories/addDeviceCategories.vue'),
        meta: { title: 'Thêm mới danh mục thiết bị' },
      },
      {
        path: 'devices',
        name: 'admin.devices',
        component: () => import('../pages/admin/devices/index.vue'),
        meta: { title: 'Thiết bị' },
      },
      {
        path: 'device-units',
        name: 'admin.deviceUnits',
        component: () => import('../pages/admin/device-units/index.vue'),
        meta: { title: 'Thiết bị đơn vị' },
      },
      {
        path: 'borrows',
        name: 'admin.borrows',
        component: () => import('../pages/borrower/Borrows.vue'),
        meta: { title: 'Phiếu mượn' },
      },
      {
        path: 'account',
        name: 'admin.account',
        component: () => import('../pages/account/Account.vue'),
        meta: { title: 'Tài khoản của tôi' },
      },
    ],
  },
]

export default admin