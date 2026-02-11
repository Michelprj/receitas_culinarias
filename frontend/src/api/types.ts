export interface User {
  id: number
  nome: string
  login: string
}

export interface AuthResponse {
  token: string
  user?: User
}

export interface Categoria {
  id: number
  nome: string
}

export interface Receita {
  id: number
  id_categorias: number
  nome: string
  tempo_preparo_minutos: number
  porcoes: number
  modo_preparo: string
  ingredientes: string
  criado_em?: string
  alterado_em?: string
}

export interface ReceitaCreate {
  id_categorias: number
  nome: string
  tempo_preparo_minutos: number
  porcoes: number
  modo_preparo: string
  ingredientes: string
}

export interface ReceitaUpdate {
  id_categorias?: number
  nome?: string
  tempo_preparo_minutos?: number
  porcoes?: number
  modo_preparo?: string
  ingredientes?: string
}

export interface ReceitasListParams {
  q?: string
  categoria_id?: number
  page?: number
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
}
