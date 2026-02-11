/**
 * URL base da API.
 * - Com proxy do Vite (dev): use '' para chamadas relativas (/api/...).
 * - Sem proxy (ex.: build): use VITE_API_URL (ex.: http://localhost:8000).
 */
export const apiBaseUrl =
  import.meta.env.VITE_API_URL?.replace(/\/$/, '') ?? ''

/**
 * Faz uma requisição para a API. Use caminhos como '/api/receitas'.
 */
export async function apiFetch(path: string, init?: RequestInit): Promise<Response> {
  const url = path.startsWith('http') ? path : `${apiBaseUrl}${path}`
  return fetch(url, {
    ...init,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...init?.headers,
    },
  })
}
