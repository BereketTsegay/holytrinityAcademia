import { createRouter, createWebHistory } from 'vue-router';
import store from '../store';

const routes = [
  // Auth routes
{
  path: '/login',
  name: 'Login',
  component: () => import('../views/Auth/Login.vue'),
  meta: { requiresGuest: true }
},
{
  path: '/forgot-password',
  name: 'ForgotPassword',
  component: () => import('../views/Auth/ForgotPassword.vue'),
  meta: { requiresGuest: true }
},
{
  path: '/complete-registration',
  name: 'CompleteRegistration',
  component: () => import('../views/Auth/CompleteRegistration.vue'),
  meta: { requiresAuth: true }
},
{
  path: '/admin/invite-user',
  name: 'UserInvitation',
  component: () => import('../views/Admin/UserInvitation.vue'),
  meta: { requiresAuth: true, permission: 'manage users' }
},
  {
    path: '/',
    name: 'Dashboard',
    component: () => import('../views/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/classes',
    name: 'Classes',
    component: () => import('../views/Classes/Index.vue'),
    meta: { requiresAuth: true, permission: 'view classes' }
  },
  {
    path: '/attendance',
    name: 'Attendance',
    component: () => import('../views/Attendance/Index.vue'),
    meta: { requiresAuth: true, permission: 'take attendance' }
  },
  {
    path: '/library',
    name: 'Library',
    component: () => import('../views/Library/Index.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/calendar',
    name: 'Calendar',
    component: () => import('../views/Calendar/Index.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/reports',
    name: 'Reports',
    component: () => import('../views/Reports/Index.vue'),
    meta: { requiresAuth: true, permission: 'view reports' }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth && !store.getters.isAuthenticated) {
    next('/login');
  } else if (to.meta.requiresGuest && store.getters.isAuthenticated) {
    next('/');
  } else {
    next();
  }
});

export default router;
