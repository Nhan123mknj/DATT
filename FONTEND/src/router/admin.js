import { requireAuth } from './guards.js';

const admin = [
    {
        path: '/admin',
        name: 'Admin',
        component: () => import('../layout/admin.vue'),
        beforeEnter: requireAuth,
        children:[
            {
                path:'users',
                name:'admin.users',
                component: () => import('../pages/admin/users/index.vue')
            },
            {
                path:'dashboard',
                name:'admin.dashboard',
                component: () => import('../pages/admin/users/dashboard.vue')
            }
        ]
    }
]
export default admin;