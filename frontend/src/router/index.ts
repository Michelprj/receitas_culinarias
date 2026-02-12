import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LoginView from '@/views/LoginView.vue'
import { AppLayout } from '@/components/Layout'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { guest: true },
    },
    {
      path: '/',
      component: AppLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: '/receitas',
        },
        {
          path: 'receitas',
          name: 'receitas',
          component: () => import('../views/ReceitasListView.vue'),
        },
        {
          path: 'receitas/nova',
          name: 'receitas-nova',
          component: () => import('../views/ReceitaFormView.vue'),
        },
        {
          path: 'receitas/:id',
          name: 'receita-detalhe',
          component: () => import('../views/ReceitaDetailView.vue'),
        },
        {
          path: 'receitas/:id/editar',
          name: 'receitas-editar',
          component: () => import('../views/ReceitaFormView.vue'),
        },
        {
          path: 'perfil',
          name: 'perfil',
          component: () => import('../views/ProfileView.vue'),
        },
      ],
    },
  ],
})

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore()
  const isLoggedIn = authStore.isAuthenticated

  if (to.meta.requiresAuth && !isLoggedIn) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }
  if (to.meta.guest && isLoggedIn) {
    next({ path: '/receitas' })
    return
  }
  next()
})

export default router
