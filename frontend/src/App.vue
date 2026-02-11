<script setup lang="ts">
import { ToastContainer } from '@/components/Toast'
</script>

<template>
  <div class="min-h-screen">
    <RouterView />
    <ToastContainer />
  </div>
</template>
