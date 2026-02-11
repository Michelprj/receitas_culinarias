import axios from 'axios'

const apiBaseUrl =
  import.meta.env.VITE_API_URL?.replace(/\/$/, '') ?? ''

export const api = axios.create({
  baseURL: apiBaseUrl || undefined,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

let bearerToken: string | null = null

export function setToken(token: string | null): void {
  bearerToken = token
}

export function getToken(): string | null {
  return bearerToken
}

api.interceptors.request.use((config) => {
  if (bearerToken) {
    config.headers.Authorization = `Bearer ${bearerToken}`
  }
  return config
})

export default api
