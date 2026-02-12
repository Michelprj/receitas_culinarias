import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { AuthResponse, User } from '@/api/types'
import { setToken } from '@/api/client'
import * as authApi from '@/api/auth'

const AUTH_TOKEN_KEY = 'receitas_token'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(null)
  const user = ref<User | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  function normalizeUser(data: AuthResponse & { usuario?: User }) {
    const u = data.user ?? data.usuario ?? null
    user.value = u ? { id: u.id, nome: u.nome, login: u.login } : null
  }

  function clearSession() {
    token.value = null
    user.value = null
    setToken(null)
      localStorage.removeItem(AUTH_TOKEN_KEY)
  }

  async function login(loginValue: string, senha: string) {
    const { data } = await authApi.login({ login: loginValue, senha })
    token.value = data.token
    normalizeUser(data as AuthResponse & { usuario?: User })
    setToken(data.token)
    localStorage.setItem(AUTH_TOKEN_KEY, data.token)
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
    localStorage.setItem(AUTH_TOKEN_KEY, data.token)
    return data
  }

  function logout() {
    clearSession()
    return authApi.logout()
  }

  /** Restaura a sessão a partir do localStorage (chamar na inicialização do app). */
  async function init() {
    try {
      const stored = localStorage.getItem(AUTH_TOKEN_KEY)
      if (!stored) return
      setToken(stored)
      token.value = stored
      const { data } = await authApi.getCurrentUser()
      user.value = { id: data.id, nome: data.nome, login: data.login }
    } catch {
      clearSession()
    }
  }

  function setAuth(newToken: string | null, newUser: User | null) {
    token.value = newToken
    user.value = newUser
    setToken(newToken)
    if (newToken) localStorage.setItem(AUTH_TOKEN_KEY, newToken)
    else localStorage.removeItem(AUTH_TOKEN_KEY)
  }

  return {
    token,
    user,
    isAuthenticated,
    login,
    register,
    logout,
    setAuth,
    init,
  }
})
