import { createRouter, createWebHistory } from 'vue-router'
import admin from './admin.js'
import staff from './staff.js'
import borrower from './borrower.js'
import { requireAuth, redirectIfAuthenticated } from './guards.js'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../layout/AuthLayout.vue'),
    beforeEnter: redirectIfAuthenticated,
    meta: { title: 'Đăng nhập' },
  },
  {
    path: '/forbidden',
    name: 'forbidden',
    component: () => import('../pages/Forbiden.vue'),
    meta: { title: 'Không có quyền truy cập' },
  },
  ...admin,
  ...staff,
  ...borrower,
  {
    path: '/',
    redirect: '/login',
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// router.beforeEach(async (to, from, next) => {
//   const publicPages = ['login', 'forbidden'];
//   if (publicPages.includes(to.name)) {
//     next()
//     return
//   }

//   await requireAuth(to, from, next)
// })

export default router
