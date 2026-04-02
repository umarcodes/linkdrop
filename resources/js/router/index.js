import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    redirect: '/app/dashboard',
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
    path: '/app/dashboard',
    name: 'dashboard',
    component: () => import('../views/Dashboard.vue'),
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
})

export default router
