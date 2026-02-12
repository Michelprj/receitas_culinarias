<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { EyeIcon, EyeSlashIcon, UserCircleIcon } from '@heroicons/vue/24/outline'
import { extractErrorMessage } from '@/lib/api-errors'
import { useToastStore } from '@/stores/toast'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToastStore()

const nome = ref('')
const login = ref('')
const senhaAtual = ref('')
const novaSenha = ref('')
const novaSenhaConfirmation = ref('')
const senhaAtualVisible = ref(false)
const novaSenhaVisible = ref(false)
const novaSenhaConfirmationVisible = ref(false)
const loading = ref(true)
const saving = ref(false)

function fillFromUser() {
  if (authStore.user) {
    nome.value = authStore.user.nome
    login.value = authStore.user.login
  }
}

async function submit() {
  if (!nome.value.trim()) {
    toast.error('Preencha o nome.')
    return
  }
  if (!login.value.trim()) {
    toast.error('Preencha o login.')
    return
  }
  const changePassword = !!novaSenha.value
  if (changePassword) {
    if (!senhaAtual.value) {
      toast.error('Informe a senha atual para alterar a senha.')
      return
    }
    if (novaSenha.value.length < 6) {
      toast.error('A nova senha deve ter pelo menos 6 caracteres.')
      return
    }
    if (novaSenha.value !== novaSenhaConfirmation.value) {
      toast.error('A confirmação da nova senha não confere.')
      return
    }
  }

  saving.value = true
  try {
    await authStore.updateProfile({
      nome: nome.value.trim(),
      login: login.value.trim(),
      ...(changePassword
        ? {
            senha_atual: senhaAtual.value,
            nova_senha: novaSenha.value,
            nova_senha_confirmation: novaSenhaConfirmation.value,
          }
        : {}),
    })
    toast.success('Perfil atualizado com sucesso!')
    if (changePassword) {
      senhaAtual.value = ''
      novaSenha.value = ''
      novaSenhaConfirmation.value = ''
    }
  } catch (e: unknown) {
    toast.error(extractErrorMessage(e, 'Erro ao atualizar perfil. Tente novamente.'))
  } finally {
    saving.value = false
  }
}

function cancel() {
  router.push('/receitas')
}

onMounted(() => {
  fillFromUser()
  loading.value = false
})
</script>

<template>
  <div class="mx-auto max-w-md space-y-6">
    <h1 class="text-center text-2xl font-semibold text-foreground">
      Meu perfil
    </h1>

    <div v-if="loading" class="rounded-lg border border-border bg-card p-8 text-center text-muted-foreground">
      Carregando...
    </div>

    <form v-else class="rounded-lg border border-border bg-card p-6 shadow-sm" @submit.prevent="submit">
      <div class="flex flex-col gap-4">
        <div class="flex justify-center">
          <UserCircleIcon class="size-16 text-muted-foreground" aria-hidden="true" />
        </div>

        <div class="space-y-1.5">
          <Label for="profile-nome">Nome</Label>
          <Input
            id="profile-nome"
            v-model="nome"
            type="text"
            maxlength="100"
            placeholder="Seu nome"
            required
          />
        </div>

        <div class="space-y-1.5">
          <Label for="profile-login">Login</Label>
          <Input
            id="profile-login"
            v-model="login"
            type="text"
            maxlength="100"
            placeholder="Login ou e-mail para entrar"
            required
          />
        </div>

        <div class="border-t border-border pt-4">
          <p class="mb-3 text-sm font-medium text-foreground">
            Alterar senha
          </p>
          <p class="mb-3 text-xs text-muted-foreground">
            Deixe em branco para manter a senha atual.
          </p>
          <div class="space-y-3">
            <div class="space-y-1.5">
              <Label for="profile-senha-atual">Senha atual</Label>
              <div class="relative">
                <Input
                  id="profile-senha-atual"
                  v-model="senhaAtual"
                  :type="senhaAtualVisible ? 'text' : 'password'"
                  placeholder="Só para alterar a senha"
                  autocomplete="current-password"
                  class="pr-9"
                />
                <button
                  type="button"
                  class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-muted-foreground hover:bg-muted"
                  :aria-label="senhaAtualVisible ? 'Ocultar senha' : 'Mostrar senha'"
                  @click="senhaAtualVisible = !senhaAtualVisible"
                >
                  <EyeSlashIcon v-if="senhaAtualVisible" class="size-[18px]" />
                  <EyeIcon v-else class="size-[18px]" />
                </button>
              </div>
            </div>
            <div class="space-y-1.5">
              <Label for="profile-nova-senha">Nova senha</Label>
              <div class="relative">
                <Input
                  id="profile-nova-senha"
                  v-model="novaSenha"
                  :type="novaSenhaVisible ? 'text' : 'password'"
                  placeholder="Mínimo 6 caracteres"
                  autocomplete="new-password"
                  class="pr-9"
                />
                <button
                  type="button"
                  class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-muted-foreground hover:bg-muted"
                  :aria-label="novaSenhaVisible ? 'Ocultar senha' : 'Mostrar senha'"
                  @click="novaSenhaVisible = !novaSenhaVisible"
                >
                  <EyeSlashIcon v-if="novaSenhaVisible" class="size-[18px]" />
                  <EyeIcon v-else class="size-[18px]" />
                </button>
              </div>
            </div>
            <div class="space-y-1.5">
              <Label for="profile-nova-senha-confirmation">Confirmar nova senha</Label>
              <div class="relative">
                <Input
                  id="profile-nova-senha-confirmation"
                  v-model="novaSenhaConfirmation"
                  :type="novaSenhaConfirmationVisible ? 'text' : 'password'"
                  placeholder="Repita a nova senha"
                  autocomplete="new-password"
                  class="pr-9"
                />
                <button
                  type="button"
                  class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-muted-foreground hover:bg-muted"
                  :aria-label="novaSenhaConfirmationVisible ? 'Ocultar senha' : 'Mostrar senha'"
                  @click="novaSenhaConfirmationVisible = !novaSenhaConfirmationVisible"
                >
                  <EyeSlashIcon v-if="novaSenhaConfirmationVisible" class="size-[18px]" />
                  <EyeIcon v-else class="size-[18px]" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6 flex flex-wrap gap-2 border-t border-border pt-5">
        <Button type="submit" class="bg-app-primary text-app-primary-foreground hover:bg-app-primary/90" :disabled="saving">
          {{ saving ? 'Salvando...' : 'Salvar' }}
        </Button>
        <Button type="button" variant="outline" :disabled="saving" @click="cancel">
          Cancelar
        </Button>
      </div>
    </form>
  </div>
</template>
