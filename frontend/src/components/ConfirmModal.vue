<script setup lang="ts">
import { Button } from '@/components/ui/button'

defineProps<{
  open: boolean
  title: string
  confirmLabel?: string
  cancelLabel?: string
  variant?: 'danger' | 'default'
}>()

const emit = defineEmits<{
  confirm: []
  cancel: []
}>()
</script>

<template>
  <Teleport to="body">
    <div
      v-show="open"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
      :aria-hidden="!open"
    >
      <div
        class="absolute inset-0 bg-black/50"
        aria-hidden="true"
        @click="emit('cancel')"
      />
      <div
        class="relative w-full max-w-sm rounded-xl border border-border bg-card p-6 shadow-lg"
        @click.stop
      >
        <p class="text-center font-semibold text-foreground">
          {{ title }}
        </p>
        <div class="mt-6 flex gap-3">
          <Button
            variant="outline"
            class="flex-1"
            @click="emit('cancel')"
          >
            {{ cancelLabel ?? 'Cancelar' }}
          </Button>
          <Button
            :variant="variant === 'danger' ? 'destructive' : 'default'"
            class="flex-1"
            @click="emit('confirm')"
          >
            {{ confirmLabel ?? 'Confirmar' }}
          </Button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
