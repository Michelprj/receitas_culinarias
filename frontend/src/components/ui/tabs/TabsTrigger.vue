<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { TabsTrigger as TabsTriggerPrimitive } from 'reka-ui'
import { useAttrs } from 'vue'
import { cn } from '@/lib/utils'

const props = withDefaults(
  defineProps<{
    value: string | number
    disabled?: boolean
    class?: HTMLAttributes['class']
  }>(),
  { disabled: false },
)

const attrs = useAttrs()
</script>

<template>
  <TabsTriggerPrimitive
    :value="props.value"
    :disabled="props.disabled"
    :class="cn(
      'inline-flex items-center justify-center whitespace-nowrap px-1 pb-2 pt-1 text-sm font-medium text-muted-foreground transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border-b-2 border-transparent -mb-px data-[state=active]:text-[#FF724C] data-[state=active]:border-[#FF724C]',
      props.class,
    )"
  >
    <slot />
  </TabsTriggerPrimitive>
</template>
