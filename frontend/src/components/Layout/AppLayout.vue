<script setup lang="ts">
import { ArrowRightOnRectangleIcon, UserCircleIcon } from '@heroicons/vue/24/outline'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogout() {
  try {
    await authStore.logout()
    router.push('/login')
  } catch {
    router.push('/login')
  }
}
</script>

<template>
  <div class="min-h-screen bg-background text-foreground flex flex-col">
    <header class="sticky top-0 z-40 border-b border-border bg-background/95 backdrop-blur print:hidden">
      <div class="flex h-14 items-center justify-between px-4 lg:px-8">
        <router-link to="/receitas" class="flex items-center gap-2">
          <img src="/logo.svg" alt="ReceitaCerta" class="h-8 w-8">
          <span class="font-brand text-app-brand text-lg font-bold">RECEITACERTA</span>
        </router-link>
        <div class="flex items-center gap-3">
          <router-link
            to="/perfil"
            class="flex items-center gap-1.5 rounded text-sm text-muted-foreground hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            aria-label="Meu perfil"
          >
            <UserCircleIcon class="size-5 shrink-0" />
            <span class="hidden sm:inline">{{ authStore.user?.nome }}</span>
          </router-link>
          <Button variant="outline" size="sm" aria-label="Sair" @click="handleLogout">
            <ArrowRightOnRectangleIcon class="size-4" />
            Sair
          </Button>
        </div>
      </div>
    </header>
    <main class="flex-1 bg-muted/30 p-4 lg:p-8">
      <RouterView />
    </main>
  </div>
</template>
