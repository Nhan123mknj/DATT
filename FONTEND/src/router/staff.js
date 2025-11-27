import { requireAuth, requireRole } from './guards.js'
const staffRoleGuard = requireRole('staff');
const staff = [
  {
    path: '/staff',
    name: 'Staff',
    component: () => import('../layout/StaffLayout.vue'),
    beforeEnter: [requireAuth, staffRoleGuard   ],
    children: [
      {
        path: '',
        name: 'staff.dashboard',
        component: () => import('../pages/staff/Dashboard.vue'),
        meta: { title: 'Dashboard' },
      },
      {
        path: 'reservations',
        name: 'staff.reservations',
        component: () => import('../pages/staff/Reservations.vue'),
        meta: { title: 'Duyệt đặt trước' },
      },
      {
        path: 'borrows',
        name: 'staff.borrows',
        component: () => import('../pages/borrower/Borrows.vue'),
        meta: { title: 'Phiếu mượn' },
      },
      {
        path: 'account',
        name: 'staff.account',
        component: () => import('../pages/account/Account.vue'),
        meta: { title: 'Tài khoản của tôi' },
      },
    ],
  },
]

export default staff
