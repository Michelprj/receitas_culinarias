<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { computed, useAttrs } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  modelValue?: string
  class?: HTMLAttributes['class']
}>()

const emits = defineEmits<{ 'update:modelValue': [value: string] }>()

const attrs = useAttrs()
const delegatedProps = computed(() => {
  const { class: _, ...rest } = attrs as HTMLAttributes & { class?: string }
  return rest
})

defineOptions({
  inheritAttrs: false,
})

function onInput(e: Event) {
  const target = e.target as HTMLInputElement
  emits('update:modelValue', target.value ?? '')
}
</script>

<template>
  <input
    v-bind="delegatedProps"
    :value="props.modelValue"
    :class="cn(
      'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
      props.class,
    )"
    @input="onInput"
  >
</template>
