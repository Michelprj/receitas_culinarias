import type { AxiosError } from 'axios'

interface ApiErrorResponse {
  message?: string
  errors?: Record<string, string[]>
}

export function extractErrorMessage(
  error: unknown,
  fallback: string = 'Ocorreu um erro. Tente novamente.',
): string {
  if (!error || typeof error !== 'object') {
    return fallback
  }

  const axiosError = error as AxiosError<ApiErrorResponse>
  
  if (axiosError.response?.data) {
    const { message, errors } = axiosError.response.data

    if (typeof message === 'string' && message.trim()) {
      return message.trim()
    }

    if (errors && typeof errors === 'object') {
      const firstKey = Object.keys(errors)[0]
      if (firstKey && Array.isArray(errors[firstKey]) && errors[firstKey].length > 0) {
        const firstMessage = errors[firstKey][0]
        if (typeof firstMessage === 'string' && firstMessage.trim()) {
          return firstMessage.trim()
        }
      }
    }
  }

  if (axiosError.response?.statusText) {
    const statusText = axiosError.response.statusText
    if (statusText && statusText !== 'Unknown Error') {
      return statusText
    }
  }

  if ('message' in error && typeof (error as Error).message === 'string') {
    const msg = (error as Error).message
    if (msg && msg !== 'Network Error') {
      return msg
    }
  }

  return fallback
}

export function extractValidationErrors(
  error: unknown,
): Record<string, string[]> | null {
  if (!error || typeof error !== 'object') {
    return null
  }

  const axiosError = error as AxiosError<ApiErrorResponse>
  const errors = axiosError.response?.data?.errors

  if (errors && typeof errors === 'object') {
    return errors
  }

  return null
}
