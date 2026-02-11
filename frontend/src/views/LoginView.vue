<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  TabsRoot,
  TabsList,
  TabsTrigger,
  TabsContent,
} from '@/components/ui/tabs'
import { extractErrorMessage } from '@/lib/api-errors'
import { useToastStore } from '@/stores/toast'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToastStore()

// Login
const loginEmail = ref('')
const loginSenha = ref('')
const loginSenhaVisible = ref(false)
const loginLoading = ref(false)

// Cadastro
const cadastroNome = ref('')
const cadastroEmail = ref('')
const cadastroSenha = ref('')
const cadastroSenhaConfirmation = ref('')
const cadastroSenhaVisible = ref(false)
const cadastroSenhaConfirmationVisible = ref(false)
const cadastroLoading = ref(false)

function resetForm() {
  loginEmail.value = ''
  loginSenha.value = ''
  cadastroNome.value = ''
  cadastroEmail.value = ''
  cadastroSenha.value = ''
  cadastroSenhaConfirmation.value = ''
}

async function handleLogin() {
  if (!loginEmail.value.trim() || !loginSenha.value) {
    toast.error('Preencha email e senha.')
    return
  }
  loginLoading.value = true
  try {
    await authStore.login(loginEmail.value.trim(), loginSenha.value)
    toast.success('Login realizado com sucesso!')
    router.push('/')
  } catch (e: unknown) {
    toast.error(extractErrorMessage(e, 'Falha ao entrar. Tente novamente.'))
  } finally {
    loginLoading.value = false
  }
}

async function handleCadastro() {
  if (!cadastroNome.value.trim()) {
    toast.error('Preencha o nome.')
    return
  }
  if (!cadastroEmail.value.trim()) {
    toast.error('Preencha o email.')
    return
  }
  if (!cadastroSenha.value) {
    toast.error('Preencha a senha.')
    return
  }
  if (cadastroSenha.value !== cadastroSenhaConfirmation.value) {
    toast.error('As senhas não coincidem.')
    return
  }
  cadastroLoading.value = true
  try {
    await authStore.register(
      cadastroNome.value.trim(),
      cadastroEmail.value.trim(),
      cadastroSenha.value,
      cadastroSenhaConfirmation.value,
    )
    toast.success('Cadastro realizado com sucesso!')
    router.push('/')
    resetForm()
  } catch (e: unknown) {
    toast.error(extractErrorMessage(e, 'Falha ao cadastrar. Tente novamente.'))
  } finally {
    cadastroLoading.value = false
  }
}

function toggleLoginSenha() {
  loginSenhaVisible.value = !loginSenhaVisible.value
}
function toggleCadastroSenha() {
  cadastroSenhaVisible.value = !cadastroSenhaVisible.value
}
function toggleCadastroSenhaConfirmation() {
  cadastroSenhaConfirmationVisible.value = !cadastroSenhaConfirmationVisible.value
}
</script>

<template>
  <div class="min-h-screen bg-background text-foreground flex flex-col lg:flex-row">
    <div class="absolute top-0 left-0 right-0 h-1 bg-[#1e3a5f] lg:hidden" aria-hidden="true" />

    <aside class="hidden lg:flex lg:flex-1 lg:flex-col lg:justify-center lg:items-center lg:bg-[#1e3a5f] lg:px-12 lg:py-16">
      <div class="max-w-md text-center">
        <img
          src="/logo.svg"
          alt="ReceitaCerta"
          class="w-24 h-24 mx-auto mb-6 drop-shadow-md"
        >
        <h1 class="text-3xl font-bold text-white mb-3">
          Bem-vindo ao <span class="font-brand">RECEITACERTA!</span>
        </h1>
        <p class="text-lg text-white/90">
          Entre ou cadastre-se para guardar suas receitas e descobrir novos sabores.
        </p>
      </div>
    </aside>

    <main class="flex flex-1 flex-col items-center justify-center px-4 py-8 max-w-md mx-auto w-full lg:max-w-lg lg:mx-0 lg:px-12 lg:py-12">
      <header class="flex flex-col items-center text-center mb-8 mt-4 lg:hidden">
        <img
          src="/logo.svg"
          alt="ReceitaCerta"
          class="w-14 h-14 mb-3"
        >
        <h1 class="text-xl font-bold text-foreground mb-1">
          Bem-vindo ao <span class="font-brand text-[#FF724C]">RECEITACERTA!</span>
        </h1>
        <p class="text-sm text-muted-foreground">
          Entre ou cadastre-se para guardar suas receitas e descobrir novos sabores.
        </p>
      </header>

      <div class="w-full lg:rounded-2xl lg:border lg:border-border lg:bg-card lg:shadow-lg lg:p-8">
        <TabsRoot default-value="login" class="w-full flex flex-col flex-1">
      <TabsList class="w-full justify-start rounded-none bg-transparent p-0">
        <TabsTrigger value="login" class="rounded-none">
          Login
        </TabsTrigger>
        <TabsTrigger value="cadastro" class="rounded-none">
          Cadastro
        </TabsTrigger>
      </TabsList>

      <TabsContent value="login" class="flex-1 mt-6">
        <form class="flex flex-col gap-4" @submit.prevent="handleLogin">
          <div class="space-y-2">
            <Label for="login-email" class="sr-only">Email</Label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
              </span>
              <Input
                id="login-email"
                v-model="loginEmail"
                type="email"
                placeholder="Email"
                class="pl-10"
                autocomplete="email"
              />
            </div>
          </div>
          <div class="space-y-2">
            <Label for="login-senha" class="sr-only">Senha</Label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <Input
                id="login-senha"
                v-model="loginSenha"
                :type="loginSenhaVisible ? 'text' : 'password'"
                placeholder="Senha"
                class="pl-10 pr-10"
                autocomplete="current-password"
              />
              <button
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded"
                :aria-label="loginSenhaVisible ? 'Ocultar senha' : 'Mostrar senha'"
                @click="toggleLoginSenha"
              >
                <svg v-if="loginSenhaVisible" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-4.963"/><path d="m2 2 20 20"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>
          <Button
            type="submit"
            class="w-full bg-[#1e3a5f] text-[#FF724C] hover:bg-[#1e3a5f]/90 font-medium"
            :disabled="loginLoading"
          >
            {{ loginLoading ? 'Entrando...' : 'Login' }}
          </Button>
        </form>
      </TabsContent>

      <TabsContent value="cadastro" class="flex-1 mt-6">
        <form class="flex flex-col gap-4" @submit.prevent="handleCadastro">
          <div class="space-y-2">
            <Label for="cadastro-nome" class="sr-only">Nome</Label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </span>
              <Input
                id="cadastro-nome"
                v-model="cadastroNome"
                type="text"
                placeholder="Nome"
                class="pl-10"
                autocomplete="name"
              />
            </div>
          </div>
          <div class="space-y-2">
            <Label for="cadastro-email" class="sr-only">Email</Label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
              </span>
              <Input
                id="cadastro-email"
                v-model="cadastroEmail"
                type="email"
                placeholder="Email"
                class="pl-10"
                autocomplete="email"
              />
            </div>
          </div>
          <div class="space-y-2">
            <Label for="cadastro-senha" class="sr-only">Senha</Label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <Input
                id="cadastro-senha"
                v-model="cadastroSenha"
                :type="cadastroSenhaVisible ? 'text' : 'password'"
                placeholder="Senha"
                class="pl-10 pr-10"
                autocomplete="new-password"
              />
              <button
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded"
                :aria-label="cadastroSenhaVisible ? 'Ocultar senha' : 'Mostrar senha'"
                @click="toggleCadastroSenha"
              >
                <svg v-if="cadastroSenhaVisible" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-4.963"/><path d="m2 2 20 20"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>
          <div class="space-y-2">
            <Label for="cadastro-senha-confirmation" class="sr-only">Confirme sua senha</Label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <Input
                id="cadastro-senha-confirmation"
                v-model="cadastroSenhaConfirmation"
                :type="cadastroSenhaConfirmationVisible ? 'text' : 'password'"
                placeholder="Confirme sua senha"
                class="pl-10 pr-10"
                autocomplete="new-password"
              />
              <button
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded"
                :aria-label="cadastroSenhaConfirmationVisible ? 'Ocultar senha' : 'Mostrar senha'"
                @click="toggleCadastroSenhaConfirmation"
              >
                <svg v-if="cadastroSenhaConfirmationVisible" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-4.963"/><path d="m2 2 20 20"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>
          <Button
            type="submit"
            class="w-full bg-[#1e3a5f] text-white hover:bg-[#1e3a5f]/90 font-medium"
            :disabled="cadastroLoading"
          >
            {{ cadastroLoading ? 'Cadastrando...' : 'Cadastrar' }}
          </Button>
        </form>
      </TabsContent>
    </TabsRoot>
      </div>
    </main>
  </div>
</template>
