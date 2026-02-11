export { api, setToken, getToken } from './client'
export type {
  User,
  Categoria,
  Receita,
  ReceitaCreate,
  ReceitaUpdate,
  ReceitasListParams,
  PaginatedResponse,
  AuthResponse,
} from './types'

export { login, register, logout } from './auth'
export type { LoginParams, RegisterParams } from './auth'

export { getCurrentUser } from './user'

export { listCategorias } from './categorias'

export {
  listReceitas,
  getReceita,
  createReceita,
  updateReceita,
  deleteReceita,
} from './receitas'
