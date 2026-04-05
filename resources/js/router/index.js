import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    beforeEnter: () => {
      // On a custom domain, fall through to render the profile via domain-lookup.
      const appHost = import.meta.env.VITE_APP_HOST
      if (appHost && window.location.hostname !== appHost) {
        return undefined // allow through to Profile.vue
      }
      // On the main domain: show landing page (no redirect)
    },
    component: () => {
      const appHost = import.meta.env.VITE_APP_HOST
      if (appHost && window.location.hostname !== appHost) {
        return import('../views/Profile.vue')
      }
      return import('../views/Landing.vue')
    },
  },
  {
    path: '/app/login',
    name: 'login',
    component: () => import('../views/Login.vue'),
    meta: { guest: true },
  },
  {
    path: '/app/register',
    name: 'register',
    component: () => import('../views/Register.vue'),
    meta: { guest: true },
  },
  {
    path: '/app/forgot-password',
    name: 'forgot-password',
    component: () => import('../views/ForgotPassword.vue'),
    meta: { guest: true },
  },
  {
    path: '/app/reset-password',
    name: 'reset-password',
    component: () => import('../views/ResetPassword.vue'),
    meta: { guest: true },
  },
  {
    path: '/app/verify-email',
    name: 'verify-email',
    component: () => import('../views/VerifyEmail.vue'),
  },
  {
    path: '/app/dashboard',
    name: 'dashboard',
    component: () => import('../views/Dashboard.vue'),
    meta: { auth: true },
  },
  {
    path: '/app/guide',
    name: 'guide',
    component: () => import('../views/Guide.vue'),
    meta: { auth: true },
  },
  {
    path: '/:username',
    name: 'profile',
    component: () => import('../views/Profile.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const token = localStorage.getItem('linkdrop_token')

  if (to.meta.auth && !token) return { name: 'login' }
  if (to.meta.guest && token) return { name: 'dashboard' }
  // Authenticated users hitting the landing page go straight to dashboard
  if (to.path === '/' && token) {
    const appHost = import.meta.env.VITE_APP_HOST
    if (!appHost || window.location.hostname === appHost) {
      return { name: 'dashboard' }
    }
  }
})

export default router
