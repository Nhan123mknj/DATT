import { requireAuth, requireRole } from "./guards";

const staff = [
    {
        path: '/staff',
        name: 'Staff',
        component: () => import('../layout/StaffLayout.vue'),
        beforeEnter: [requireAuth, requireRole('staff')],
        children: [
            {
                path: '',
                name: 'staff.dashboard',
                component: () => import('../pages/staff/Dashboard.vue'),
                meta: {
                    title: 'Dashboard'
                }
            },
          
        ]
    }
]

export default staff;