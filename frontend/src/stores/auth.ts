import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { AuthResponse, User } from '@/api/types'
import { setToken } from '@/api/client'
import * as authApi from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(null)
  const user = ref<User | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  function normalizeUser(data: AuthResponse & { usuario?: User }) {
    const u = data.user ?? data.usuario ?? null
    user.value = u ? { id: u.id, nome: u.nome, login: u.login } : null
  }

  async function login(loginValue: string, senha: string) {
    const { data } = await authApi.login({ login: loginValue, senha })
    token.value = data.token
    normalizeUser(data as AuthResponse & { usuario?: User })
    setToken(data.token)
    return data
  }

  async function register(nome: string, loginValue: string, senha: string, senhaConfirmation: string) {
    const { data } = await authApi.register({
      nome,
      login: loginValue,
      senha,
      senha_confirmation: senhaConfirmation,
    })
    token.value = data.token
    normalizeUser(data as AuthResponse & { usuario?: User })
    setToken(data.token)
    return data
  }

  function logout() {
    token.value = null
    user.value = null
    setToken(null)
    return authApi.logout()
  }

  function setAuth(newToken: string | null, newUser: User | null) {
    token.value = newToken
    user.value = newUser
    setToken(newToken)
  }

  return {
    token,
    user,
    isAuthenticated,
    login,
    register,
    logout,
    setAuth,
  }
})
