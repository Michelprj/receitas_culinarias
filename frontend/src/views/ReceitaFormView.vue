<script setup lang="ts">
import { ref, onMounted, computed, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { getReceita, createReceita, updateReceita } from '@/api/receitas'
import { listCategorias } from '@/api/categorias'
import type { ReceitaCreate, ReceitaUpdate, Categoria } from '@/api/types'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { CameraIcon } from '@heroicons/vue/24/outline'
import { extractErrorMessage } from '@/lib/api-errors'
import { useToastStore } from '@/stores/toast'

const MAX_FOTO_SIZE = 2 * 1024 * 1024 // 2MB

const router = useRouter()
const route = useRoute()
const toast = useToastStore()

const id = computed(() =>
  route.name === 'receitas-editar' && route.params.id ? Number(route.params.id) : null
)
const isEdit = computed(() => id.value != null)

const categorias = ref<Categoria[]>([])
const loading = ref(true)
const saving = ref(false)

const id_categorias = ref<number | ''>('')
const nome = ref('')
const tempo_preparo_minutos = ref<number | ''>('')
const porcoes = ref<number | ''>('')
const modo_preparo = ref('')
const ingredientes = ref('')

const inputFileRef = ref<HTMLInputElement | null>(null)
const fotoFile = ref<File | null>(null)
const fotoPreviewUrl = ref<string | null>(null)
const existingFotoUrl = ref<string | null>(null)

async function loadCategorias() {
  try {
    const { data } = await listCategorias()
    categorias.value = data ?? []
  } catch {
    categorias.value = []
  }
}

async function loadReceita() {
  if (!id.value) return
  loading.value = true
  try {
    const { data } = await getReceita(id.value)
    id_categorias.value = data.id_categorias
    nome.value = data.nome
    tempo_preparo_minutos.value = data.tempo_preparo_minutos
    porcoes.value = data.porcoes
    modo_preparo.value = data.modo_preparo ?? ''
    ingredientes.value = data.ingredientes ?? ''
    existingFotoUrl.value = data.foto_url ?? null
  } catch (e) {
    toast.error(extractErrorMessage(e, 'Receita não encontrada.'))
    router.push('/receitas')
  } finally {
    loading.value = false
  }
}

function triggerFileInput() {
  inputFileRef.value?.click()
}

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  input.value = ''
  if (!file) return
  if (!file.type.startsWith('image/')) {
    toast.error('Selecione uma imagem (JPG, PNG, etc.).')
    return
  }
  if (file.size > MAX_FOTO_SIZE) {
    toast.error('A imagem deve ter no máximo 2 MB.')
    return
  }
  if (fotoPreviewUrl.value) {
    URL.revokeObjectURL(fotoPreviewUrl.value)
  }
  fotoFile.value = file
  fotoPreviewUrl.value = URL.createObjectURL(file)
}

const fotoDisplayUrl = computed(() => fotoPreviewUrl.value ?? existingFotoUrl.value)

function valid(): boolean {
  if (id_categorias.value === '' || !nome.value.trim()) {
    toast.error('Preencha categoria e nome.')
    return false
  }
  const t = Number(tempo_preparo_minutos.value)
  const p = Number(porcoes.value)
  if (!Number.isInteger(t) || t < 1) {
    toast.error('Tempo de preparo deve ser um número inteiro maior que zero.')
    return false
  }
  if (!Number.isInteger(p) || p < 1) {
    toast.error('Porções deve ser um número inteiro maior que zero.')
    return false
  }
  if (!modo_preparo.value.trim() || !ingredientes.value.trim()) {
    toast.error('Preencha modo de preparo e ingredientes.')
    return false
  }
  return true
}

async function submit() {
  if (!valid()) return
  saving.value = true
  try {
    const payload: ReceitaCreate = {
      id_categorias: Number(id_categorias.value),
      nome: nome.value.trim(),
      tempo_preparo_minutos: Number(tempo_preparo_minutos.value),
      porcoes: Number(porcoes.value),
      modo_preparo: modo_preparo.value.trim(),
      ingredientes: ingredientes.value.trim(),
    }
    if (isEdit.value && id.value) {
      const updatePayload: ReceitaUpdate = { ...payload }
      await updateReceita(id.value, updatePayload, fotoFile.value)
      toast.success('Receita atualizada.')
      router.push(`/receitas/${id.value}`)
    } else {
      const { data } = await createReceita(payload, fotoFile.value)
      toast.success('Receita cadastrada.')
      router.push(`/receitas/${data.id}`)
    }
  } catch (e) {
    toast.error(extractErrorMessage(e, 'Erro ao salvar.'))
  } finally {
    saving.value = false
  }
}

function cancel() {
  router.push('/receitas')
}

onMounted(async () => {
  await loadCategorias()
  if (id.value) await loadReceita()
  else loading.value = false
})

onBeforeUnmount(() => {
  if (fotoPreviewUrl.value) {
    URL.revokeObjectURL(fotoPreviewUrl.value)
  }
})
</script>

<template>
  <div class="mx-auto max-w-2xl space-y-6 lg:max-w-4xl">
    <h1 class="text-center text-2xl font-semibold text-foreground">
      {{ isEdit ? 'Editar receita' : 'Nova receita' }}
    </h1>

    <div v-if="loading" class="rounded-lg border border-border bg-card p-8 text-center text-muted-foreground">
      Carregando...
    </div>

    <form v-else class="rounded-lg border border-border bg-card p-6 shadow-sm lg:p-8" @submit.prevent="submit">
      <!-- Foto + Nome + Categoria -->
      <input
        ref="inputFileRef"
        type="file"
        accept="image/*"
        class="hidden"
        aria-label="Selecionar foto da receita"
        @change="onFileChange"
      >
      <div class="grid grid-cols-1 gap-5 lg:grid-cols-[7rem_1fr] lg:gap-6">
        <button
          type="button"
          class="flex aspect-square w-28 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border bg-muted/50 text-muted-foreground transition-colors hover:bg-muted/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring self-center justify-self-center lg:w-28 lg:justify-self-start lg:self-start"
          @click="triggerFileInput"
        >
          <img
            v-if="fotoDisplayUrl"
            :src="fotoDisplayUrl"
            alt="Foto da receita"
            class="h-full w-full object-cover"
          >
          <span v-else class="flex flex-col items-center gap-1 text-xs">
            <CameraIcon class="size-6" />
            Adicionar foto
          </span>
        </button>
        <div class="flex min-w-0 flex-col gap-4">
          <div class="space-y-1.5">
            <Label for="nome">Nome da receita <span class="text-red-500">*</span></Label>
            <Input id="nome" v-model="nome" type="text" maxlength="45" placeholder="Ex: Bolo de chocolate" required />
          </div>
          <div class="space-y-1.5">
            <Label for="categoria">Categoria</Label>
            <select
              id="categoria"
              v-model="id_categorias"
              required
              class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
            >
              <option value="">Selecione</option>
              <option v-for="c in categorias" :key="c.id" :value="c.id">
                {{ c.nome }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tempo e porções -->
      <div class="grid grid-cols-2 gap-4 pt-1">
        <div class="space-y-1.5">
          <Label for="tempo">Tempo de preparo (min)</Label>
          <input
            id="tempo"
            v-model.number="tempo_preparo_minutos"
            type="number"
            min="1"
            placeholder="45"
            required
            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
          >
        </div>
        <div class="space-y-1.5">
          <Label for="porcoes">Porções</Label>
          <input
            id="porcoes"
            v-model.number="porcoes"
            type="number"
            min="1"
            placeholder="8"
            required
            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
          >
        </div>
      </div>

      <!-- Ingredientes e modo de preparo -->
      <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 lg:gap-6">
        <div class="space-y-1.5">
          <Label for="ingredientes">Ingredientes</Label>
          <textarea
            id="ingredientes"
            v-model="ingredientes"
            rows="5"
            required
            placeholder="Liste os ingredientes..."
            class="min-h-[120px] w-full resize-y rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
          />
        </div>
        <div class="space-y-1.5">
          <Label for="modo">Modo de preparo</Label>
          <textarea
            id="modo"
            v-model="modo_preparo"
            rows="5"
            required
            placeholder="Descreva o passo a passo..."
            class="min-h-[120px] w-full resize-y rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
          />
        </div>
      </div>

      <!-- Ações -->
      <div class="flex flex-wrap gap-2 border-t border-border pt-5">
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
