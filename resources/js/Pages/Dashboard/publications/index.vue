<template>
  <AppLayout>
    <PageTitle title="Contenu en Attente de Publication" :breadcrumbs="breadcrumbs">
      <template #actions>
        <button 
          @click="publishSelection"
          class="btn btn-primary shadow-lg hover:shadow-xl transition-shadow"
          :disabled="!canPublish"
        >
          <i class="fas fa-paper-plane mr-2"></i>
          Publier la sélection
        </button>
      </template>
    </PageTitle>

    <div class="container mx-auto px-4 py-6">
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Sidebar - Sélection actuelle -->
        <aside class="w-full lg:w-1/4 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="space-y-6">
            <!-- Article Principal -->
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-100 dark:border-blue-800">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 flex items-center">
                  <i class="fas fa-star mr-2"></i>
                  Article Principal
                </h3>
                <span class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 rounded-full">
                  Obligatoire
                </span>
              </div>
              
              <div v-if="mainContent" class="bg-white dark:bg-gray-700 p-3 rounded-md shadow-inner border border-gray-200 dark:border-gray-600">
                <div class="flex justify-between items-start">
                  <div>
                    <h4 class="font-medium text-gray-900 dark:text-white line-clamp-2">{{ mainContent.title }}</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      Créé le {{ formatDate(mainContent.created_at) }}
                    </p>
                  </div>
                  <button 
                    @click="removeMain"
                    class="text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors"
                    title="Retirer comme article principal"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div v-else class="text-center py-4 bg-gray-50 dark:bg-gray-700/50 rounded-md">
                <i class="fas fa-info-circle text-gray-400 dark:text-gray-500 mr-2"></i>
                <span class="text-sm text-gray-500 dark:text-gray-400">Aucun article principal</span>
              </div>
            </div>

            <!-- Articles Secondaires -->
            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-100 dark:border-green-800">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-green-800 dark:text-green-200 flex items-center">
                  <i class="fas fa-thumbtack mr-2"></i>
                  Articles Secondaires
                </h3>
                <span class="text-xs px-2 py-1 bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200 rounded-full">
                  {{ secondaryContent.length }}/3
                </span>
              </div>

              <ul class="space-y-3">
                <li 
                  v-for="item in secondaryContent" 
                  :key="item.id"
                  class="bg-white dark:bg-gray-700 p-3 rounded-md shadow-inner border border-gray-200 dark:border-gray-600"
                >
                  <div class="flex justify-between items-start">
                    <div>
                      <h4 class="font-medium text-gray-900 dark:text-white line-clamp-2">{{ item.title }}</h4>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Créé le {{ formatDate(item.created_at) }}
                      </p>
                    </div>
                    <button 
                      @click="removeSecondary(item)"
                      class="text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors"
                      title="Retirer des articles secondaires"
                    >
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </li>
              </ul>

              <div v-if="secondaryContent.length === 0" class="text-center py-4 bg-gray-50 dark:bg-gray-700/50 rounded-md">
                <i class="fas fa-info-circle text-gray-400 dark:text-gray-500 mr-2"></i>
                <span class="text-sm text-gray-500 dark:text-gray-400">Aucun article secondaire</span>
              </div>
            </div>
          </div>
        </aside>

        <!-- Contenu principal - Liste des articles -->
        <main class="w-full lg:w-3/4">
          <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <!-- Filtres et recherche -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between gap-4">
              <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-search text-gray-400"></i>
                </div>
                <input
                  v-model="searchQuery"
                  type="text"
                  class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600"
                  placeholder="Rechercher des articles..."
                >
              </div>

              <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600 dark:text-gray-300">Filtrer :</label>
                <select 
                  v-model="categoryFilter"
                  class="text-sm rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600"
                >
                  <option value="">Toutes catégories</option>
                  <option v-for="category in categories" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Liste des articles -->
            <div v-if="filteredContent.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <article 
                v-for="item in filteredContent" 
                :key="item.id"
                class="group relative bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-600 hover:shadow-lg transition-shadow"
                :class="{
                  'ring-2 ring-yellow-400': isMain(item),
                  'ring-2 ring-green-400': isSecondary(item)
                }"
              >
                <!-- Badge de statut -->
                <div class="absolute top-3 left-3 z-10">
                  <span 
                    v-if="isMain(item)"
                    class="text-xs font-medium px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full flex items-center"
                  >
                    <i class="fas fa-star mr-1"></i> Principal
                  </span>
                  <span 
                    v-else-if="isSecondary(item)"
                    class="text-xs font-medium px-2 py-1 bg-green-100 text-green-800 rounded-full flex items-center"
                  >
                    <i class="fas fa-thumbtack mr-1"></i> Secondaire
                  </span>
                </div>

                <!-- Image -->
                <div class="relative h-40 overflow-hidden">
                  <img 
                    v-if="item.imageUrl"
                    :src="item.imageUrl" 
                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                    :alt="item.title"
                  >
                  <div v-else class="w-full h-full bg-gray-100 dark:bg-gray-600 flex items-center justify-center text-gray-400 dark:text-gray-500">
                    <i class="fas fa-image text-3xl"></i>
                  </div>
                </div>

                <!-- Contenu -->
                <div class="p-4">
                  <h3 class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2">
                    {{ item.title }}
                  </h3>
                  <div class="flex items-center text-xs text-gray-500 dark:text-gray-400 mb-3">
                    <span class="flex items-center mr-3">
                      <i class="fas fa-calendar-alt mr-1"></i>
                      {{ formatDate(item.created_at) }}
                    </span>
                    <span v-if="item.category" class="flex items-center">
                      <i class="fas fa-tag mr-1"></i>
                      {{ item.category.name }}
                    </span>
                  </div>

                  <!-- Actions -->
                  <div class="flex flex-wrap gap-2 mt-4">
                    <button
                      @click.stop="setMain(item)"
                      class="flex-1 min-w-[120px] btn btn-yellow"
                      :disabled="isMain(item)"
                      :class="{ 'opacity-50 cursor-not-allowed': isMain(item) }"
                    >
                      <i class="fas fa-star mr-2"></i>
                      {{ isMain(item) ? 'Déjà principal' : 'Définir principal' }}
                    </button>
                    <button
                      @click.stop="toggleSecondary(item)"
                      class="flex-1 min-w-[120px] btn btn-green"
                      :disabled="isMain(item) || (!isSecondary(item) && secondaryContent.length >= 3)"
                      :class="{ 
                        'opacity-50 cursor-not-allowed': isMain(item) || (!isSecondary(item) && secondaryContent.length >= 3),
                        'bg-red-100 hover:bg-red-200 text-red-700': isSecondary(item)
                      }"
                    >
                      <i class="fas fa-thumbtack mr-2"></i>
                      {{ isSecondary(item) ? 'Retirer' : 'Ajouter' }}
                    </button>
                  </div>
                </div>
              </article>
            </div>

            <!-- État vide -->
            <div v-else class="text-center py-12">
              <div class="mx-auto w-20 h-20 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center text-blue-500 dark:text-blue-400 mb-4">
                <i class="fas fa-newspaper text-3xl"></i>
              </div>
              <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">
                Aucun article trouvé
              </h3>
              <p class="text-gray-500 dark:text-gray-400">
                {{
                  searchQuery || categoryFilter 
                    ? "Aucun résultat ne correspond à vos critères de recherche."
                    : "Aucun article disponible pour publication."
                }}
              </p>
            </div>
          </div>
        </main>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageTitle from '@/Components/PageTitle.vue'

const toast = useToast()

// Données
const content = ref([])
const mainContent = ref(null)
const secondaryContent = ref([])
const categories = ref([])
const searchQuery = ref('')
const categoryFilter = ref('')
const isLoadingCategories = ref(false)

const breadcrumbs = [
  { label: 'Accueil', url: '/' },
  { label: 'Dashboard', url: '/dashboard' },
  { label: 'Contenu en Attente', active: true }
]

// Computed
const filteredContent = computed(() => {
  return content.value.filter(item => {
    const matchesSearch = item.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = categoryFilter.value ? item.category?.id == categoryFilter.value : true
    return matchesSearch && matchesCategory
  })
})

const canPublish = computed(() => {
  return mainContent.value && secondaryContent.value.length > 0
})

// Méthodes
const fetchPublishedArticles = async () => {
  try {
    const response = await axios.get('/articles/published', {
      params: { published: false }
    })
    content.value = response.data.articles
    mainContent.value = content.value.find(item => item.status === 'main') || null
    secondaryContent.value = content.value.filter(item => item.status === 'secondary')
  } catch (error) {
    toast.error('Erreur lors du chargement des articles')
    console.error(error)
  }
}

const fetchCategoriesByType = async (type) => {
    isLoadingCategories.value = true;
    try {
        const response = await axios.get(`/categories/type/${type}`);
        categories.value = response.data;
    } catch (error) {
        console.error('Erreur de chargement des catégories:', error);
        errorMessage.value = 'Une erreur s\'est produite lors du chargement des catégories.';
    } finally {
        isLoadingCategories.value = false;
    }
};

const updateArticleStatus = async (item, status) => {
  try {
    await axios.post('/articles/update-status', { 
      id: item.id, 
      status 
    })
    item.status = status
  } catch (error) {
    toast.error('Erreur lors de la mise à jour de l\'article')
    console.error(error)
  }
}

const publishSelection = async () => {
  try {
    await axios.post('/articles/publish-selection', {
      mainId: mainContent.value?.id,
      secondaryIds: secondaryContent.value.map(item => item.id)
    })
    toast.success('Sélection publiée avec succès')
    fetchPublishedArticles()
  } catch (error) {
    toast.error('Erreur lors de la publication')
    console.error(error)
  }
}

const setMain = (item) => {
  if (isSecondary(item)) {
    removeSecondary(item)
  }
  
  // Retirer l'ancien article principal s'il existe
  if (mainContent.value) {
    updateArticleStatus(mainContent.value, 'normal')
  }
  
  mainContent.value = item
  updateArticleStatus(item, 'main')
}

const removeMain = () => {
  if (mainContent.value) {
    updateArticleStatus(mainContent.value, 'normal')
    mainContent.value = null
  }
}

const toggleSecondary = (item) => {
  if (isSecondary(item)) {
    removeSecondary(item)
  } else if (!isMain(item) && secondaryContent.value.length < 3) {
    secondaryContent.value.push(item)
    updateArticleStatus(item, 'secondary')
  }
}

const removeSecondary = (item) => {
  secondaryContent.value = secondaryContent.value.filter(i => i.id !== item.id)
  updateArticleStatus(item, 'normal')
}

const isSecondary = (item) => {
  return secondaryContent.value.some(i => i.id === item.id)
}

const isMain = (item) => {
  return mainContent.value?.id === item.id
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

// Initialisation
onMounted(async () => {
  await fetchPublishedArticles()
  await fetchCategoriesByType('article');
})
</script>

<style scoped>
.btn {
  @apply inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors;
}

.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white;
}

.btn-yellow {
  @apply bg-yellow-100 hover:bg-yellow-200 focus:ring-yellow-500 text-yellow-800;
}

.btn-green {
  @apply bg-green-100 hover:bg-green-200 focus:ring-green-500 text-green-800;
}

.btn-red {
  @apply bg-red-100 hover:bg-red-200 focus:ring-red-500 text-red-800;
}

.dark .btn-yellow {
  @apply bg-yellow-900/20 hover:bg-yellow-900/30 text-yellow-200;
}

.dark .btn-green {
  @apply bg-green-900/20 hover:bg-green-900/30 text-green-200;
}

.dark .btn-red {
  @apply bg-red-900/20 hover:bg-red-900/30 text-red-200;
}
</style>