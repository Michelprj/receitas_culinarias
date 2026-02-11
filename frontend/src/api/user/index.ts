import { api } from '@/api/client'
import type { User } from '@/api/types'

export function getCurrentUser() {
  return api.get<User>('/api/user')
}
