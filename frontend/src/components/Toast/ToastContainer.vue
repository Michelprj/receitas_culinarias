<script setup lang="ts">
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { storeToRefs } from 'pinia'
import { useToastStore } from '@/stores/toast'

const toastStore = useToastStore()
const { toasts } = storeToRefs(toastStore)

function variantClasses(variant: string) {
  switch (variant) {
    case 'error':
      return 'bg-destructive/95 text-white border-destructive'
    case 'success':
      return 'bg-green-600/95 text-white border-green-700'
    case 'info':
    default:
      return 'bg-primary text-primary-foreground border-primary'
  }
}
</script>

<template>
  <div
    class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none"
    role="region"
    aria-label="Notificações"
  >
    <TransitionGroup
      name="toast"
      tag="div"
      class="flex flex-col gap-2"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto rounded-lg border shadow-lg px-4 py-3 text-sm"
        :class="variantClasses(toast.variant)"
      >
        <div class="flex items-start gap-3">
          <p class="flex-1 font-medium">
            {{ toast.message }}
          </p>
          <button
            type="button"
            class="shrink-0 rounded opacity-80 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-transparent"
            :aria-label="'Fechar notificação'"
            @click="toastStore.removeToast(toast.id)"
          >
            <XMarkIcon class="size-4" />
          </button>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
.toast-move {
  transition: transform 0.3s ease;
}
</style>
