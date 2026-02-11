<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { TabsList as TabsListPrimitive } from 'reka-ui'
import { computed, useAttrs } from 'vue'
import { cn } from '@/lib/utils'

const attrs = useAttrs()
const delegatedProps = computed(() => {
  const { class: _, ...rest } = attrs as HTMLAttributes & { class?: string }
  return rest
})
</script>

<template>
  <TabsListPrimitive
    v-bind="delegatedProps"
    :class="cn(
      'inline-flex h-10 items-center justify-center gap-6 border-b border-border',
      (attrs.class as string),
    )"
  >
    <slot />
  </TabsListPrimitive>
</template>
