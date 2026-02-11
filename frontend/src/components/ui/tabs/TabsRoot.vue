<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { TabsRoot as TabsRootPrimitive } from 'reka-ui'
import { cn } from '@/lib/utils'

const props = withDefaults(
  defineProps<{
    defaultValue?: string | number
    modelValue?: string | number
    orientation?: 'horizontal' | 'vertical'
    activationMode?: 'automatic' | 'manual'
    unmountOnHide?: boolean
    class?: HTMLAttributes['class']
  }>(),
  {
    orientation: 'horizontal',
    activationMode: 'automatic',
    unmountOnHide: true,
  },
)

const emits = defineEmits<{ 'update:modelValue': [payload: string | number] }>()
</script>

<template>
  <TabsRootPrimitive
    :default-value="props.defaultValue"
    :model-value="props.modelValue"
    :orientation="props.orientation"
    :activation-mode="props.activationMode"
    :unmount-on-hide="props.unmountOnHide"
    :class="cn('w-full', props.class)"
    @update:model-value="emits('update:modelValue', $event)"
  >
    <slot />
  </TabsRootPrimitive>
</template>
