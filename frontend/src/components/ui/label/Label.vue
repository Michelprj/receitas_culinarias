<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { Label as LabelPrimitive } from 'reka-ui'
import { computed, useAttrs } from 'vue'
import { cn } from '@/lib/utils'

const attrs = useAttrs()
const delegatedProps = computed(() => {
  const { class: _, ...rest } = attrs as HTMLAttributes & { class?: string }
  return rest
})

defineOptions({
  inheritAttrs: false,
})
</script>

<template>
  <LabelPrimitive
    v-bind="delegatedProps"
    :class="cn(
      'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
      (attrs.class as string),
    )"
  >
    <slot />
  </LabelPrimitive>
</template>
