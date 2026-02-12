<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { listReceitas, deleteReceita } from '@/api/receitas'
import { listCategorias } from '@/api/categorias'
import type { Receita, Categoria } from '@/api/types'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import ConfirmModal from '@/components/ConfirmModal.vue'
import {
  DocumentMagnifyingGlassIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  PlusIcon,
  PrinterIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'
import { extractErrorMessage } from '@/lib/api-errors'
import { useToastStore } from '@/stores/toast'

const router = useRouter()
const toast = useToastStore()

const receitas = ref<Receita[]>([])
const categorias = ref<Categoria[]>([])
const loading = ref(true)
const q = ref('')
const categoriaId = ref<number | ''>('')
const page = ref(1)
const lastPage = ref(1)
const total = ref(0)
const receitaToDelete = ref<Receita | null>(null)

const modalExcluirTitulo = computed(() =>
  receitaToDelete.value ? `Deseja excluir a receita "${receitaToDelete.value.nome}"?` : ''
)

function categoriaNome(id: number): string {
  return categorias.value.find((c) => c.id === id)?.nome ?? String(id)
}

function tempoLabel(minutos: number): string {
  if (minutos >= 60) {
    const h = Math.floor(minutos / 60)
    const m = minutos % 60
    return m > 0 ? `${h}h ${m}min` : `${h}h`
  }
  return `${minutos} min`
}

async function fetchReceitas() {
  loading.value = true
  try {
    const params: { q?: string; categoria_id?: number; page?: number } = { page: page.value }
    if (q.value.trim()) params.q = q.value.trim()
    if (categoriaId.value !== '') params.categoria_id = Number(categoriaId.value)
    const { data } = await listReceitas(params)
    receitas.value = data.data ?? data
    lastPage.value = data.last_page ?? 1
    total.value = data.total ?? 0
  } catch (e) {
    toast.error(extractErrorMessage(e, 'Erro ao carregar receitas.'))
  } finally {
    loading.value = false
  }
}

function goToPage(p: number) {
  if (p >= 1 && p <= lastPage.value) {
    page.value = p
  }
}

function novaReceita() {
  router.push('/receitas/nova')
}

function verReceita(r: Receita) {
  router.push(`/receitas/${r.id}`)
}

function editarReceita(r: Receita) {
  router.push(`/receitas/${r.id}/editar`)
}

function abrirModalExcluir(r: Receita) {
  receitaToDelete.value = r
}

function fecharModalExcluir() {
  receitaToDelete.value = null
}

async function confirmarExcluir() {
  const r = receitaToDelete.value
  if (!r) return
  try {
    await deleteReceita(r.id)
    toast.success('Receita excluída.')
    receitaToDelete.value = null
    await fetchReceitas()
  } catch (e) {
    toast.error(extractErrorMessage(e, 'Erro ao excluir.'))
  }
}

watch([q, categoriaId], () => {
  page.value = 1
  fetchReceitas()
})
watch(page, fetchReceitas)

onMounted(async () => {
  try {
    const { data } = await listCategorias()
    categorias.value = data ?? []
  } catch {
    categorias.value = []
  }
  await fetchReceitas()
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
      <h1 class="text-2xl font-semibold text-foreground">Minhas receitas</h1>
      <Button
        class="w-full shrink-0 bg-app-primary text-app-primary-foreground hover:bg-app-primary/90 sm:w-auto"
        @click="novaReceita"
      >
        <PlusIcon class="size-5" />
        Nova receita
      </Button>
    </div>

    <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:gap-4">
        <div class="relative min-w-0 flex-1 lg:flex lg:items-center lg:gap-2">
          <Label for="busca" class="sr-only">Buscar</Label>
          <span
            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground lg:static lg:left-0 lg:top-0 lg:translate-y-0 lg:shrink-0"
            aria-hidden="true"
          >
            <MagnifyingGlassIcon class="size-[18px] lg:size-5" />
          </span>
          <Input
            id="busca"
            v-model="q"
            type="search"
            placeholder="Buscar por nome ou ingredientes..."
            class="w-full pl-9 lg:pl-3"
          />
        </div>
        <div class="w-full shrink-0 lg:w-48">
          <Label for="categoria" class="text-foreground">Categoria</Label>
          <select
            id="categoria"
            v-model="categoriaId"
            class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
          >
            <option value="">Todas</option>
            <option v-for="c in categorias" :key="c.id" :value="c.id">
              {{ c.nome }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="loading" class="rounded-lg border border-border bg-card p-8 text-center text-muted-foreground">
      Carregando...
    </div>

    <div v-else-if="receitas.length === 0" class="flex flex-col items-center justify-center gap-3 rounded-lg border border-border bg-card p-12 text-center text-muted-foreground">
      <DocumentMagnifyingGlassIcon class="size-14 shrink-0 opacity-60" aria-hidden="true" />
      <p class="font-medium">Nenhuma receita encontrada.</p>
    </div>

    <template v-else>
      <!-- Mobile: cards -->
      <ul class="space-y-3 lg:hidden">
        <li
          v-for="r in receitas"
          :key="r.id"
          class="flex flex-col gap-3 rounded-lg border border-border bg-card p-4 shadow-sm"
        >
          <h2 class="font-semibold text-foreground">{{ r.nome }}</h2>
          <p class="text-sm text-muted-foreground">
            {{ categoriaNome(r.id_categorias) }} · {{ tempoLabel(r.tempo_preparo_minutos) }} · {{ r.porcoes }} porções
          </p>
          <div class="flex flex-wrap gap-2">
            <Button
              variant="outline"
              size="icon-sm"
              aria-label="Ver / Imprimir"
              @click="verReceita(r)"
            >
              <PrinterIcon class="size-4" />
            </Button>
            <Button
              variant="outline"
              size="icon-sm"
              aria-label="Editar"
              @click="editarReceita(r)"
            >
              <PencilIcon class="size-4" />
            </Button>
            <Button
              variant="outline"
              size="icon-sm"
              aria-label="Excluir"
              class="text-destructive hover:bg-destructive/10"
              @click="abrirModalExcluir(r)"
            >
              <TrashIcon class="size-4" />
            </Button>
          </div>
        </li>
      </ul>
      <!-- Desktop: tabela -->
      <div class="hidden overflow-hidden rounded-lg border border-border bg-card shadow-sm lg:block">
        <table class="w-full text-left text-sm">
          <thead class="border-b border-border bg-muted/50">
            <tr>
              <th class="px-4 py-3 font-medium text-foreground">Nome</th>
              <th class="px-4 py-3 font-medium text-foreground">Categoria</th>
              <th class="px-4 py-3 font-medium text-foreground">Tempo</th>
              <th class="px-4 py-3 font-medium text-foreground">Porções</th>
              <th class="px-4 py-3 font-medium text-foreground text-right">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-for="r in receitas" :key="r.id" class="hover:bg-muted/30">
              <td class="px-4 py-3 font-medium text-foreground">{{ r.nome }}</td>
              <td class="px-4 py-3 text-muted-foreground">{{ categoriaNome(r.id_categorias) }}</td>
              <td class="px-4 py-3 text-muted-foreground">{{ tempoLabel(r.tempo_preparo_minutos) }}</td>
              <td class="px-4 py-3 text-muted-foreground">{{ r.porcoes }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex justify-end gap-2">
                  <Button
                    variant="outline"
                    class="text-black hover:bg-destructive hover:text-blue-500"
                    size="icon-sm"
                    title="Ver / Imprimir"
                    aria-label="Ver / Imprimir"
                    @click="verReceita(r)"
                  >
                    <PrinterIcon class="size-4" />
                  </Button>
                  <Button
                    variant="outline"
                    class="text-black hover:bg-destructive hover:text-orange-500"
                    size="icon-sm"
                    title="Editar"
                    aria-label="Editar"
                    @click="editarReceita(r)"
                  >
                    <PencilIcon class="size-4" />
                  </Button>
                  <Button
                    variant="outline"
                    size="icon-sm"
                    aria-label="Excluir"
                    class="text-black hover:bg-destructive/10 hover:text-destructive"
                    @click="abrirModalExcluir(r)"
                  >
                    <TrashIcon class="size-4" />
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <div
      v-if="lastPage > 1"
      class="flex flex-wrap items-center justify-center gap-2 text-sm"
    >
      <Button variant="outline" size="sm" :disabled="page <= 1" @click="goToPage(page - 1)">
        Anterior
      </Button>
      <span class="text-muted-foreground px-2">
        Página {{ page }} de {{ lastPage }} ({{ total }} no total)
      </span>
      <Button variant="outline" size="sm" :disabled="page >= lastPage" @click="goToPage(page + 1)">
        Próxima
      </Button>
    </div>

    <ConfirmModal
      :open="receitaToDelete !== null"
      :title="modalExcluirTitulo"
      confirm-label="Excluir"
      cancel-label="Cancelar"
      variant="danger"
      @confirm="confirmarExcluir"
      @cancel="fecharModalExcluir"
    />
  </div>
</template>
