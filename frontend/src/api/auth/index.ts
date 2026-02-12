import { api, setToken } from '@/api/client'
import type { AuthResponse, User } from '@/api/types'

export interface LoginParams {
  login: string
  senha: string
}

export interface RegisterParams {
  nome: string
  login: string
  senha: string
  senha_confirmation: string
}

export function login(params: LoginParams) {
  return api.post<AuthResponse>('/api/login', params)
}

export function register(params: RegisterParams) {
  return api.post<AuthResponse>('/api/register', params)
}

export function logout() {
  return api.post<{ message: string }>('/api/logout').finally(() => {
    setToken(null)
  })
}

export function getCurrentUser() {
  return api.get<User>('/api/user')
}

export interface UpdateProfileParams {
  nome?: string
  login?: string
  senha_atual?: string
  nova_senha?: string
  nova_senha_confirmation?: string
}

export function updateProfile(params: UpdateProfileParams) {
  return api.patch<User>('/api/user', params)
}
