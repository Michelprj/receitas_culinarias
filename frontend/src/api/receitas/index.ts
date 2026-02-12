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

function buildReceitaFormData(
  data: Record<string, string | number>,
  file?: File | null
): FormData {
  const form = new FormData()
  for (const [key, value] of Object.entries(data)) {
    if (value !== undefined && value !== null && value !== '') {
      form.append(key, String(value))
    }
  }
  if (file) {
    form.append('foto', file)
  }
  return form
}

export function createReceita(data: ReceitaCreate, file?: File | null) {
  const payload = {
    id_categorias: data.id_categorias,
    nome: data.nome,
    tempo_preparo_minutos: data.tempo_preparo_minutos,
    porcoes: data.porcoes,
    modo_preparo: data.modo_preparo,
    ingredientes: data.ingredientes,
  }
  if (file) {
    return api.post<Receita>('/api/receitas', buildReceitaFormData(payload, file))
  }
  return api.post<Receita>('/api/receitas', payload)
}

export function updateReceita(id: number, data: ReceitaUpdate, file?: File | null) {
  const payload: Record<string, string | number> = {}
  if (data.id_categorias !== undefined) payload.id_categorias = data.id_categorias
  if (data.nome !== undefined) payload.nome = data.nome
  if (data.tempo_preparo_minutos !== undefined) payload.tempo_preparo_minutos = data.tempo_preparo_minutos
  if (data.porcoes !== undefined) payload.porcoes = data.porcoes
  if (data.modo_preparo !== undefined) payload.modo_preparo = data.modo_preparo
  if (data.ingredientes !== undefined) payload.ingredientes = data.ingredientes
  if (file) {
    const form = buildReceitaFormData(payload, file)
    form.append('_method', 'PUT')
    return api.post<Receita>(`/api/receitas/${id}`, form)
  }
  return api.put<Receita>(`/api/receitas/${id}`, data)
}

export function deleteReceita(id: number) {
  return api.delete<{ message: string }>(`/api/receitas/${id}`)
}
