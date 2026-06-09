<template>
  <AppLayout>
    <PageTitle title="Galerie de Diaporamas" :breadcrumbs="breadcrumbs">
      <template #actions>
        <Link 
          :href="route('admin.medias.slideshows.create')" 
          class="btn btn-primary shadow-lg hover:shadow-xl transition-shadow"
        >
          <i class="fas fa-plus-circle mr-2"></i> Nouveau diaporama
        </Link>
      </template>
    </PageTitle>

    <section class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <!-- Barre de contrôle -->
      <div class="control-bar p-6 bg-gray-50 border-b">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <!-- Barre de recherche -->
          <div class="relative flex-1 max-w-2xl">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input
              v-model="searchQuery"
              type="text"
              class="pl-10 pr-10 w-full rounded-lg border-0 bg-white shadow-sm focus:ring-2 focus:ring-blue-500"
              placeholder="Rechercher des diaporamas..."
              @keyup.enter="applyFilters"
            >
            <button 
              v-if="searchQuery" 
              @click="searchQuery = ''" 
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>

          <!-- Filtres -->
          <div class="flex items-center gap-3">
            <div class="relative">
              <select 
                v-model="selectedStatus"
                @change="applyFilters"
                class="appearance-none pl-3 pr-8 py-2 rounded-lg border-0 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 text-sm"
              >
                <option value="">Tous statuts</option>
                <option value="published" class="text-green-600">Publié</option>
                <option value="draft" class="text-yellow-600">Brouillon</option>
                <option value="archived" class="text-gray-600">Archivé</option>
              </select>
              <i class="fas fa-chevron-down absolute right-3 top-3 text-xs text-gray-400 pointer-events-none"></i>
            </div>

            <div class="relative">
              <select 
                v-model="selectedCategory"
                @change="applyFilters"
                class="appearance-none pl-3 pr-8 py-2 rounded-lg border-0 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 text-sm"
              >
                <option value="">Toutes catégories</option>
                <option 
                  v-for="category in uniqueCategories" 
                  :key="category.id" 
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
              <i class="fas fa-chevron-down absolute right-3 top-3 text-xs text-gray-400 pointer-events-none"></i>
            </div>

            <button 
              @click="resetFilters" 
              class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-blue-600 transition-colors"
              :disabled="!hasActiveFilters"
              :class="{ 'opacity-50 cursor-not-allowed': !hasActiveFilters }"
            >
              <i class="fas fa-sync-alt"></i>
              <span class="hidden md:inline">Réinitialiser</span>
            </button>
          </div>
        </div>
      </div>

      <!-- En-tête des résultats -->
      <div class="px-6 py-4 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <h3 class="text-lg font-semibold text-gray-800">
            <span class="text-blue-600">{{ filteredDiaporamas.length }}</span> diaporama(s)
          </h3>
          
          <div class="relative">
            <select 
              v-model="sortBy" 
              @change="sortDiaporamas"
              class="appearance-none pl-3 pr-8 py-1 rounded-lg border border-gray-200 bg-white text-sm focus:ring-2 focus:ring-blue-500"
            >
              <option value="date-desc">Plus récents</option>
              <option value="date-asc">Plus anciens</option>
              <option value="title-asc">A → Z</option>
              <option value="title-desc">Z → A</option>
            </select>
            <i class="fas fa-sort absolute right-2 top-2 text-xs text-gray-400"></i>
          </div>
        </div>

        <div class="flex items-center gap-2 text-sm text-gray-500">
          <i class="fas fa-info-circle"></i>
          <span>Cliquez sur une image pour prévisualiser</span>
        </div>
      </div>

      <!-- Liste des diaporamas -->
      <div v-if="filteredDiaporamas.length > 0" class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div 
            v-for="diaporama in sortedDiaporamas" 
            :key="diaporama.id" 
            class="group relative bg-white rounded-xl shadow-md overflow-hidden transition-all hover:shadow-lg border border-gray-100"
          >
            <!-- Badge de statut -->
            <div class="absolute top-3 left-3 z-10">
              <span class="text-xs font-medium px-2 py-1 rounded-full shadow"
                :class="{
                  'bg-green-100 text-green-800': diaporama.status === 'published',
                  'bg-yellow-100 text-yellow-800': diaporama.status === 'draft',
                  'bg-gray-100 text-gray-800': diaporama.status === 'archived'
                }"
              >
                {{ getStatusLabel(diaporama.status) }}
              </span>
            </div>

            <!-- Image principale -->
            <div 
              class="relative aspect-video overflow-hidden cursor-pointer"
              @click="previewDiaporama(diaporama)"
            >
              <img 
                :src="getFirstImage(diaporama)"
                :alt="diaporama.title"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              >
              <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                <button class="opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300 bg-white bg-opacity-90 text-blue-600 px-4 py-2 rounded-full shadow-lg flex items-center gap-2">
                  <i class="fas fa-play"></i>
                  <span>Voir le diaporama</span>
                </button>
              </div>
              <div class="absolute bottom-3 right-3 bg-black bg-opacity-60 text-white text-xs px-2 py-1 rounded-full flex items-center gap-1">
                <i class="fas fa-images"></i>
                <span>{{ getImageCount(diaporama) }}</span>
              </div>
            </div>

            <!-- Contenu texte -->
            <div class="p-4">
              <div class="flex justify-between items-start mb-2">
                <span class="text-xs text-gray-500">
                  {{ formatDate(diaporama.created_at) }}
                </span>
                <span v-if="diaporama.category" class="text-xs px-2 py-1 bg-blue-50 text-blue-600 rounded-full">
                  {{ diaporama.category.name }}
                </span>
              </div>

              <h3 class="font-bold text-gray-900 mb-2 line-clamp-2 leading-tight">
                {{ diaporama.title }}
              </h3>

              <!-- Actions -->
              <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100">
                <select
                  v-model="diaporama.status"
                  @change="updateStatus(diaporama)"
                  :disabled="isLoading"
                  class="text-xs py-1 px-2 rounded border focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{
                    'border-yellow-300 bg-yellow-50 text-yellow-800': diaporama.status === 'draft',
                    'border-green-300 bg-green-50 text-green-800': diaporama.status === 'published',
                    'border-gray-300 bg-gray-50 text-gray-800': diaporama.status === 'archived'
                  }"
                >
                  <option value="draft">Brouillon</option>
                  <option value="published">Publié</option>
                  <option value="archived">Archivé</option>
                </select>

                <div class="flex gap-2">
                  <Link 
                    :href="route('admin.medias.slideshows.edit', { slideshow: diaporama.id })"
                    class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 flex items-center justify-center transition-colors"
                    aria-label="Modifier"
                    title="Modifier"
                  >
                    <i class="fas fa-pencil-alt text-sm"></i>
                  </Link>
                  <button 
                    @click.stop="confirmDelete(diaporama)"
                    class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors"
                    aria-label="Supprimer"
                    title="Supprimer"
                  >
                    <i class="fas fa-trash-alt text-sm"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- État vide -->
      <div v-else class="empty-state p-12 text-center">
        <div class="max-w-md mx-auto">
          <div class="w-20 h-20 mx-auto mb-4 bg-blue-50 rounded-full flex items-center justify-center text-blue-500">
            <i class="fas fa-images text-3xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Aucun diaporama trouvé</h3>
          <p class="text-gray-500 mb-6">
            {{
              hasActiveFilters 
                ? "Aucun résultat ne correspond à vos critères de recherche."
                : "Commencez par créer votre premier diaporama."
            }}
          </p>
          <Link 
            :href="route('admin.medias.slideshows.create')" 
            class="btn btn-primary inline-flex items-center shadow-md hover:shadow-lg transition-shadow"
          >
            <i class="fas fa-plus-circle mr-2"></i> Créer un diaporama
          </Link>
        </div>
      </div>
    </section>

    <!-- Modal de prévisualisation -->
    <SlideshowPreviewModal 
      v-if="previewedDiaporama"
      :slideshow="previewedDiaporama"
      @close="previewedDiaporama = null"
    />

    <!-- Confirmation de suppression -->
    <ConfirmationDialog
      :show="!!diaporamaToDelete"
      title="Confirmer la suppression"
      confirm-text="Supprimer"
      confirm-color="red"
      @close="diaporamaToDelete = null"
      @confirm="deleteDiaporama"
    >
      <div class="text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
          <i class="fas fa-exclamation text-red-600 text-xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">
          Supprimer "{{ diaporamaToDelete?.title }}" ?
        </h3>
        <p class="text-sm text-gray-500">
          Toutes les images associées seront également supprimées définitivement.
          Cette action ne peut pas être annulée.
        </p>
      </div>
    </ConfirmationDialog>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageTitle from '@/Components/PageTitle.vue'
import SlideshowPreviewModal from '@/Components/Media/SlideshowPreviewModal.vue'
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue'

const toast = useToast()
const page = usePage()

// Données
const diaporamas = ref(
  (page.props.slideshows || []).map(d => ({
    ...d,
    images: d.images || [] // Garantit que images existe toujours
  }))
)
const previewedDiaporama = ref(null)
const diaporamaToDelete = ref(null)
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStatus = ref('')
const sortBy = ref('date-desc')
const isLoading = ref(false)

const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Médiathèque', url: route('admin.medias.index') },
  { label: 'Diaporamas', active: true }
]

// Méthodes utilitaires
const getFirstImage = (diaporama) => {
  return diaporama.images?.[0]?.url || '/images/default-slideshow.jpg'
}

const getImageCount = (diaporama) => {
  return diaporama.images?.length || 0
}

const getStatusLabel = (status) => {
  const statusMap = {
    draft: 'Brouillon',
    published: 'Publié',
    archived: 'Archivé'
  }
  return statusMap[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

// Computed properties
const hasActiveFilters = computed(() => {
  return searchQuery.value || selectedCategory.value || selectedStatus.value
})

const uniqueCategories = computed(() => {
  const categories = diaporamas.value
    .map(d => d.category)
    .filter(Boolean)
  return [...new Map(categories.map(cat => [cat.id, cat])).values()]
})

const filteredDiaporamas = computed(() => {
  return diaporamas.value.filter(diaporama => {
    const matchesSearch = diaporama.title?.toLowerCase().includes(searchQuery.value.toLowerCase()) ?? true
    const matchesCategory = selectedCategory.value 
      ? diaporama.category?.id == selectedCategory.value 
      : true
    const matchesStatus = selectedStatus.value 
      ? diaporama.status === selectedStatus.value 
      : true
    return matchesSearch && matchesCategory && matchesStatus
  })
})

const sortedDiaporamas = computed(() => {
  const [field, direction] = sortBy.value.split('-')
  const modifier = direction === 'asc' ? 1 : -1
  
  return [...filteredDiaporamas.value].sort((a, b) => {
    if (field === 'date') {
      return (new Date(a.created_at) - new Date(b.created_at)) * modifier
    } else {
      return (a[field]?.localeCompare(b[field]) ?? 0) * modifier
    }
  })
})

// Actions
const applyFilters = () => {
  // Le filtrage est géré automatiquement par les computed properties
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  selectedStatus.value = ''
}

const previewDiaporama = (diaporama) => {
  previewedDiaporama.value = diaporama
}

const updateStatus = async (diaporama) => {
  const originalStatus = diaporama.status
  
  try {
    isLoading.value = true
    await router.patch(
      route('admin.medias.slideshows.update-status', { slideshow: diaporama.id }),
      { status: diaporama.status },
      {
        preserveScroll: true,
        onSuccess: () => {
          toast.success('Statut mis à jour avec succès')
        },
        onError: (errors) => {
          diaporama.status = originalStatus
          toast.error(errors.message || 'Échec de la mise à jour')
        }
      }
    )
  } catch (error) {
    console.error('Erreur:', error)
    diaporama.status = originalStatus
    toast.error('Erreur de connexion au serveur')
  } finally {
    isLoading.value = false
  }
}

const confirmDelete = (diaporama) => {
  diaporamaToDelete.value = diaporama
}

const deleteDiaporama = async () => {
  if (!diaporamaToDelete.value || isLoading.value) return
  
  isLoading.value = true
  try {
    await router.delete(
      route('admin.medias.slideshows.destroy', { slideshow: diaporamaToDelete.value.id }),
      {
        preserveScroll: true,
        onSuccess: () => {
          toast.success('Diaporama supprimé avec succès')
          // Supprimer le diaporama du tableau local
          diaporamas.value = diaporamas.value.filter(d => d.id !== diaporamaToDelete.value.id)
        },
        onError: () => {
          toast.error('Échec de la suppression')
        }
      }
    )
  } catch (error) {
    toast.error('Erreur inattendue')
    console.error(error)
  } finally {
    isLoading.value = false
    diaporamaToDelete.value = null
  }
}
</script>

<style scoped>
.control-bar {
  background: linear-gradient(to right, #f8fafc, #ffffff);
}

.empty-state {
  background: linear-gradient(to bottom, #ffffff, #f9fafb);
}

/* Animations */
.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Style personnalisé pour les selects */
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}
</style>