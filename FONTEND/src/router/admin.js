const admin = [
    {
        path: '/admin',
        name: 'Admin',
        component: () => import('../layout/admin.vue'),
        children:[
            {
                path:'users',
                name:'admin.users',
                component: () => import('../pages/admin/users/index.vue')
            }
        ]
    }
]
export default admin;