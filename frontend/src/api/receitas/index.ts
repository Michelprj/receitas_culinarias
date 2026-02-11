import { api } from '@/api/client'
import type {
  Receita,
  ReceitaCreate,
  ReceitaUpdate,
  ReceitasListParams,
  PaginatedResponse,
} from '@/api/types'

export function listReceitas(params?: ReceitasListParams) {
  return api.get<PaginatedResponse<Receita>>('/api/receitas', { params })
}

export function getReceita(id: number) {
  return api.get<Receita>(`/api/receitas/${id}`)
}

export function createReceita(data: ReceitaCreate) {
  return api.post<Receita>('/api/receitas', data)
}

export function updateReceita(id: number, data: ReceitaUpdate) {
  return api.put<Receita>(`/api/receitas/${id}`, data)
}

export function deleteReceita(id: number) {
  return api.delete<{ message: string }>(`/api/receitas/${id}`)
}
