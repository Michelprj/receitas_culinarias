<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { TabsContent as TabsContentPrimitive } from 'reka-ui'
import { cn } from '@/lib/utils'

const props = defineProps<{
  value: string | number
  forceMount?: boolean
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <TabsContentPrimitive
    :value="props.value"
    :force-mount="props.forceMount"
    :class="cn(
      'mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
      props.class,
    )"
  >
    <slot />
  </TabsContentPrimitive>
</template>
