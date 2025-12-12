import { requireAuth, requireRole } from './guards.js'

const borrower = [
  {
    path: '/borrower',
    name: 'Borrower',
    component: () => import('../layout/borrower.vue'),
    beforeEnter: [requireAuth, requireRole('borrower')],
    children: [
      {
        path: 'dashboard',
        name: 'borrower.dashboard',
        component: () => import('../pages/borrower/Dashboard.vue'),
        meta: { title: 'Tổng quan' },
      },
      {
        path: 'reservations',
        name: 'borrower.reservations',
        component: () => import('../pages/borrower/Reservations.vue'),
        meta: { title: 'Đặt trước thiết bị' },
      },
      {
        path: 'reservations/create',
        name: 'borrower.reservations.create',
        component: () => import('../pages/borrower/ReservationCreate.vue'),
        meta: { title: 'Tạo đặt trước' },
      },
      {
        path: 'reservations/:id/edit',
        name: 'borrower.reservations.edit',
        component: () => import('../pages/borrower/ReservationCreate.vue'),
        meta: { title: 'Cập nhật đặt trước' },
      },
      {
        path: 'borrows',
        name: 'borrower.borrows',
        component: () => import('../pages/borrower/Borrows.vue'),
        meta: { title: 'Phiếu mượn của tôi' },
      },
      {
        path: 'account',
        name: 'borrower.account',
        component: () => import('../layout/AccountLayout.vue'),
        meta: { title: 'Tài khoản của tôi' },
      },
    ],
  },
]

export default borrower

