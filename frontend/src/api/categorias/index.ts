import { api } from '@/api/client'
import type { Categoria } from '@/api/types'

export function listCategorias() {
  return api.get<Categoria[]>('/api/categorias')
}
