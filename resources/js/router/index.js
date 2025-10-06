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
  },
  // User management routes
{
  path: '/users',
  name: 'Users',
  component: () => import('../views/Users/index.vue'),
  meta: { requiresAuth: true, permission: 'manage users' }
},
{
  path: '/users/create',
  name: 'UserCreate',
  component: () => import('../views/Users/UserForm.vue'),
  meta: { requiresAuth: true, permission: 'manage users' }
},
{
  path: '/users/:id/edit',
  name: 'UserEdit',
  component: () => import('../views/Users/UserForm.vue'),
  meta: { requiresAuth: true, permission: 'manage users' }
},
{
  path: '/users/roles',
  name: 'RoleManagement',
  component: () => import('../views/Users/RoleManagement.vue'),
  meta: { requiresAuth: true, permission: 'manage roles' }
},
{
  path: '/users/invite',
  name: 'UserInvitation',
  component: () => import('../views/Admin/UserInvitation.vue'),
  meta: { 
    requiresAuth: true, 
    permission: 'create users' 
  }
},
// Department routes
{
  path: '/departments',
  name: 'Departments',
  component: () => import('../views/Departments/index.vue'),
  meta: { requiresAuth: true, permission: 'manage classes' }
},
// {
//   path: '/departments/:id',
//   name: 'DepartmentShow',
//   component: () => import('../views/Departments/Show.vue'),
//   meta: { requiresAuth: true }
// },

// Report routes
{
  path: '/reports/books',
  name: 'BookReports',
  component: () => import('../views/Reports/BookReports.vue'),
  meta: { requiresAuth: true, permission: 'view reports' }
},
// {
//   path: '/reports/departments',
//   name: 'DepartmentReports',
//   component: () => import('../views/Reports/DepartmentReports.vue'),
//   meta: { requiresAuth: true, permission: 'view reports' }
// }

  {
    path: '/calendar',
    name: 'Calendar',
    component: () => import('../views/Calendar/Index.vue'),
    meta: { requiresAuth: true }
  },
  {
  path: '/profile',
  name: 'Profile',
  component: () => import('../views/Profile/index.vue'),
  meta: { requiresAuth: true }
},
{
  path: '/library',
  name: 'Library',
  component: () => import('../views/Library/Index.vue'),
  meta: { requiresAuth: true }
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
