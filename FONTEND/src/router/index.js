import {createRouter, createWebHistory} from 'vue-router';
import admin from './admin.js';
import staff from './staff.js';
import { requireAuth, redirectIfAuthenticated } from './guards.js';
import authService from '../services/authService';

const routes = [
    // Login route
    {
        path: '/login',
        name: 'login',
        component: () => import('../layout/AuthLayout.vue'),
        beforeEnter: redirectIfAuthenticated
    },
    
    // Admin routes (protected)
    ...admin,
    // Staff routes (protected)
    ...staff,
    // Default redirect
    {
        path: '/',
        redirect: '/login'
    },
    // Catch all route
    {
        path: '/:pathMatch(.*)*',
        redirect: '/login'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Global navigation guard
router.beforeEach(async (to, from, next) => {

    if (to.name === 'login') {
        next()
        return
    }


    await requireAuth(to, from, next)
})

export default router;