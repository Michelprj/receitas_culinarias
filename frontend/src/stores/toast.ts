import { ref } from 'vue'
import { defineStore } from 'pinia'

export type ToastVariant = 'error' | 'success' | 'info'

export interface ToastItem {
  id: number
  message: string
  variant: ToastVariant
}

const DEFAULT_DURATION = 5000

export const useToastStore = defineStore('toast', () => {
  const toasts = ref<ToastItem[]>([])
  let nextId = 1

  function addToast(message: string, variant: ToastVariant = 'info', duration = DEFAULT_DURATION) {
    const id = nextId++
    const item: ToastItem = { id, message, variant }
    toasts.value = [...toasts.value, item]

    if (duration > 0) {
      setTimeout(() => removeToast(id), duration)
    }

    return id
  }

  function removeToast(id: number) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  function error(message: string, duration = DEFAULT_DURATION) {
    return addToast(message, 'error', duration)
  }

  function success(message: string, duration = DEFAULT_DURATION) {
    return addToast(message, 'success', duration)
  }

  function info(message: string, duration = DEFAULT_DURATION) {
    return addToast(message, 'info', duration)
  }

  return { toasts, addToast, removeToast, error, success, info }
})
