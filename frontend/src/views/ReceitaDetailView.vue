<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getReceita } from '@/api/receitas'
import { listCategorias } from '@/api/categorias'
import type { Receita, Categoria } from '@/api/types'
import {
  ArrowLeftIcon,
  PencilIcon,
  PrinterIcon,
} from '@heroicons/vue/24/outline'
import { Button } from '@/components/ui/button'
import { extractErrorMessage } from '@/lib/api-errors'
import { useToastStore } from '@/stores/toast'

const route = useRoute()
const router = useRouter()
const toast = useToastStore()

const id = computed(() => Number(route.params.id))
const receita = ref<Receita | null>(null)
const categorias = ref<Categoria[]>([])
const loading = ref(true)

const categoriaNome = computed(() => {
  if (!receita.value) return ''
  const c = categorias.value.find((x) => x.id === receita.value!.id_categorias)
  return c?.nome ?? String(receita.value.id_categorias)
})

function editar() {
  router.push(`/receitas/${id.value}/editar`)
}

function voltar() {
  router.push('/receitas')
}

function imprimir() {
  window.print()
}

onMounted(async () => {
  try {
    const [recRes, catRes] = await Promise.all([
      getReceita(id.value),
      listCategorias(),
    ])
    receita.value = recRes.data
    categorias.value = catRes.data ?? []
  } catch (e) {
    toast.error(extractErrorMessage(e, 'Receita não encontrada.'))
    router.push('/receitas')
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <div v-if="loading" class="rounded-lg border border-border bg-card p-8 text-center text-muted-foreground">
      Carregando...
    </div>

    <template v-else-if="receita">
      <div class="flex gap-4 sm:items-center justify-between print:hidden">
        <Button variant="outline" size="sm" aria-label="Voltar" @click="voltar">
          <ArrowLeftIcon class="size-4" />
          <span class="hidden sm:inline">Voltar</span>
        </Button>
        <div class="flex gap-2">
          <Button variant="outline" size="sm" aria-label="Editar" @click="editar">
            <PencilIcon class="size-4" />
            <span class="hidden sm:inline">Editar</span>
          </Button>
          <Button
            class="bg-app-primary text-app-primary-foreground hover:bg-app-primary/90"
            size="sm"
            aria-label="Imprimir"
            @click="imprimir"
          >
            <PrinterIcon class="size-4" />
            <span class="hidden sm:inline">Imprimir</span>
          </Button>
        </div>
      </div>

      <article class="rounded-lg border border-border bg-card p-6 shadow-sm print:border-0 print:shadow-none">
        <img
          v-if="receita.foto_url"
          :src="receita.foto_url"
          :alt="receita.nome"
          class="mb-4 w-full rounded-lg object-cover max-h-64 print:max-h-48"
        >
        <header class="border-b border-border pb-4 mb-4">
          <h1 class="text-2xl font-semibold text-foreground">{{ receita.nome }}</h1>
          <p class="text-sm text-muted-foreground mt-1">
            {{ categoriaNome }} · {{ receita.tempo_preparo_minutos }} min · {{ receita.porcoes }} porções
          </p>
        </header>

        <section class="space-y-4">
          <div>
            <h2 class="text-lg font-medium text-foreground mb-2">Ingredientes</h2>
            <p class="text-foreground whitespace-pre-wrap">{{ receita.ingredientes }}</p>
          </div>
          <div>
            <h2 class="text-lg font-medium text-foreground mb-2">Modo de preparo</h2>
            <p class="text-foreground whitespace-pre-wrap">{{ receita.modo_preparo }}</p>
          </div>
        </section>
      </article>
    </template>
  </div>
</template>
