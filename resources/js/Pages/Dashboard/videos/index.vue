<template>
  <AppLayout>
    <PageTitle title="Liste des Vidéos" :breadcrumbs="breadcrumbs">
      <template #actions>
        <Link 
          :href="route('admin.medias.videos.create')"
          class="btn btn-primary"
          aria-label="Ajouter une nouvelle vidéo"
        >
          <i class="fas fa-plus mr-2"></i> Ajouter une vidéo
        </Link>
      </template>
    </PageTitle>

    <section class="bg-white rounded-xl shadow-sm p-6">
      <!-- Filtres améliorés avec ARIA -->
      <div class="mb-6 flex flex-col sm:flex-row justify-between items-center gap-4" role="search" aria-label="Filtrer les vidéos">
        <!-- Recherche par titre -->
        <div class="relative flex-grow max-w-md">
          <label for="video-search" class="sr-only">Rechercher des vidéos</label>
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400" aria-hidden="true"></i>
          </div>
          <input
            id="video-search"
            type="text"
            v-model="searchQuery"
            placeholder="Rechercher une vidéo..."
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            @keyup.enter="applyFilters"
            aria-describedby="search-help"
          >
          <button 
            v-if="searchQuery" 
            @click="searchQuery = ''" 
            class="absolute inset-y-0 right-0 pr-3 flex items-center"
            aria-label="Effacer la recherche"
          >
            <i class="fas fa-times text-gray-400 hover:text-gray-500" aria-hidden="true"></i>
          </button>
          <span id="search-help" class="sr-only">Appuyez sur Entrée pour lancer la recherche</span>
        </div>

        <!-- Filtres groupés -->
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
          <!-- Catégorie -->
          <div class="w-full sm:w-48">
            <label for="category-filter" class="sr-only">Filtrer par catégorie</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-list-alt text-gray-400" aria-hidden="true"></i>
              </div>
              <select
                id="category-filter"
                v-model="selectedCategory"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                aria-label="Sélectionnez une catégorie"
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
            </div>
          </div>

          <!-- Statut -->
          <div class="w-full sm:w-48">
            <label for="status-filter" class="sr-only">Filtrer par statut</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-filter text-gray-400" aria-hidden="true"></i>
              </div>
              <select
                id="status-filter"
                v-model="selectedStatus"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                aria-label="Sélectionnez un statut"
              >
                <option value="">Tous statuts</option>
                <option value="draft">Brouillon</option>
                <option value="published">Publié</option>
                <option value="archived">Archivé</option>
              </select>
            </div>
          </div>

          <!-- Réinitialiser -->
          <button
            @click="resetFilters"
            :disabled="!hasActiveFilters"
            class="px-4 py-2 text-sm text-white bg-red-500 rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 flex items-center justify-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            aria-label="Réinitialiser tous les filtres"
          >
            <i class="fas fa-sync-alt" aria-hidden="true"></i>
            <span>Réinitialiser</span>
          </button>
        </div>
      </div>

      <!-- Résultats avec annonce ARIA -->
      <div class="mb-4 flex justify-between items-center">
        <p class="text-sm text-gray-600" aria-live="polite">
          {{ filteredVideos.length }} vidéo(s) trouvée(s)
        </p>
        <div class="flex items-center gap-2">
          <label for="sort-videos" class="text-sm text-gray-600">Trier par :</label>
          <select
            id="sort-videos"
            v-model="sortBy"
            class="text-sm border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"
            aria-label="Options de tri"
          >
            <option value="date-desc">Date (récent)</option>
            <option value="date-asc">Date (ancien)</option>
            <option value="title-asc">Titre (A-Z)</option>
            <option value="title-desc">Titre (Z-A)</option>
          </select>
        </div>
      </div>

      <!-- Liste des vidéos avec navigation au clavier -->
      <div v-if="filteredVideos.length > 0" class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4" role="list" aria-label="Liste des vidéos">
        <div 
          v-for="video in sortedVideos" 
          :key="video.id" 
          class="video-card group"
          :class="{
            'border-l-4 border-blue-500': video.status === 'published',
            'border-l-4 border-yellow-500': video.status === 'draft',
            'border-l-4 border-gray-500': video.status === 'archived'
          }"
          role="listitem"
          tabindex="0"
          @keyup.enter="previewVideo(video)"
        >
          <!-- Miniature vidéo -->
          <div class="relative aspect-video overflow-hidden rounded-t-lg">
            <img 
              :src="`https://img.youtube.com/vi/${getYouTubeVideoId(video.url)}/maxresdefault.jpg`" 
              :alt="`Miniature de la vidéo: ${video.title}`"
              class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
              loading="lazy"
            >
            <div class="absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
              <button 
                @click.stop="previewVideo(video)"
                class="w-16 h-16 rounded-full bg-white bg-opacity-80 text-blue-600 flex items-center justify-center hover:bg-opacity-100 hover:text-blue-700 transition-all"
                aria-label="Voir la vidéo"
              >
                <i class="fas fa-play text-xl" aria-hidden="true"></i>
              </button>
            </div>
          </div>

          <!-- Contenu -->
          <div class="p-4">
            <div class="flex justify-between items-start mb-2">
              <span class="text-xs font-medium text-blue-600">
                {{ formatDate(video.created_at) }}
              </span>
              <span 
                class="text-xs px-2 py-1 rounded-full"
                :class="{
                  'bg-yellow-100 text-yellow-800': video.status === 'draft',
                  'bg-green-100 text-green-800': video.status === 'published',
                  'bg-gray-100 text-gray-800': video.status === 'archived'
                }"
                :aria-label="'Statut: ' + getStatusLabel(video.status)"
              >
                {{ getStatusLabel(video.status) }}
              </span>
            </div>

            <h3 class="text-lg font-bold text-gray-900 mb-1 line-clamp-2">
              {{ video.title }}
            </h3>

            <p class="text-sm text-gray-500 mb-3" aria-label="Catégorie">
              {{ video.category?.name || 'Non catégorisé' }}
            </p>

            <!-- Actions -->
            <div class="flex justify-between items-center">
              <label :for="`status-select-${video.id}`" class="sr-only">Modifier le statut</label>
              <select
                :id="`status-select-${video.id}`"
                v-model="video.status"
                @change="updateVideoStatus(video)"
                :disabled="isLoading"
                class="text-xs py-1 px-2 rounded border focus:ring-blue-500 focus:border-blue-500"
                :class="{
                  'border-yellow-300 bg-yellow-50 text-yellow-800': video.status === 'draft',
                  'border-green-300 bg-green-50 text-green-800': video.status === 'published',
                  'border-gray-300 bg-gray-50 text-gray-800': video.status === 'archived'
                }"
                aria-label="Sélectionner un statut"
              >
                <option value="draft">Brouillon</option>
                <option value="published">Publié</option>
                <option value="archived">Archivé</option>
              </select>

              <div class="flex gap-2">
                <button 
                  @click.stop="editVideo(video)"
                  class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 flex items-center justify-center transition-colors"
                  aria-label="Modifier la vidéo"
                >
                  <i class="fas fa-edit" aria-hidden="true"></i>
                </button>
                <button 
                  @click.stop="confirmDelete(video)"
                  class="w-8 h-8 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center transition-colors"
                  aria-label="Supprimer la vidéo"
                >
                  <i class="fas fa-trash-alt" aria-hidden="true"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- État vide avec bouton d'action -->
      <div v-else class="py-12 text-center">
        <i class="far fa-file-video text-5xl text-gray-300 mb-4" aria-hidden="true"></i>
        <h2 class="text-xl font-medium text-gray-700 mb-2">Aucune vidéo trouvée</h2>
        <p class="text-gray-500 max-w-md mx-auto mb-4">
          Essayez d'ajuster vos filtres ou ajoutez une nouvelle vidéo
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <button
            @click="resetFilters"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300"
            aria-label="Réinitialiser les filtres"
          >
            Réinitialiser les filtres
          </button>
          <Link 
            :href="route('admin.medias.videos.create')" 
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            aria-label="Ajouter une nouvelle vidéo"
          >
            <i class="fas fa-plus mr-2" aria-hidden="true"></i> Ajouter une vidéo
          </Link>
        </div>
      </div>
    </section>

    <!-- Modal d'aperçu -->
    <VideoPreviewModal 
      v-if="previewedVideo"
      :video="previewedVideo"
      @close="previewedVideo = null"
    />

    <!-- Confirmation de suppression -->
    <ConfirmationDialog
      :show="!!videoToDelete"
      title="Confirmer la suppression"
      @close="videoToDelete = null"
      @confirm="deleteVideo"
    >
      <p>Êtes-vous sûr de vouloir supprimer définitivement la vidéo <strong>"{{ videoToDelete?.title }}"</strong> ?</p>
    </ConfirmationDialog>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import VideoPreviewModal from '@/Components/Media/VideoPreviewModal.vue';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';
import { useToast } from 'vue-toastification';

const { toast } = useToast();
const page = usePage();

// Données
const videos = ref(page.props.videos || []);
const previewedVideo = ref(null);
const videoToDelete = ref(null);
const searchQuery = ref('');
const selectedCategory = ref('');
const selectedStatus = ref('');
const sortBy = ref('date-desc');
const isLoading = ref(false);

const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Médiathèque', url: route('admin.medias.videos.index') },
  { label: 'Vidéos', active: true }
];

// Computed
const hasActiveFilters = computed(() => {
  return searchQuery.value || selectedCategory.value || selectedStatus.value;
});

const uniqueCategories = computed(() => {
  const categories = videos.value
    .map(video => video.category)
    .filter(Boolean);
  return [...new Map(categories.map(cat => [cat.id, cat])).values()];
});

const filteredVideos = computed(() => {
  return videos.value.filter(video => {
    const matchesSearch = video.title.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesCategory = selectedCategory.value ? video.category?.id == selectedCategory.value : true;
    const matchesStatus = selectedStatus.value ? video.status === selectedStatus.value : true;
    return matchesSearch && matchesCategory && matchesStatus;
  });
});

const sortedVideos = computed(() => {
  const [field, direction] = sortBy.value.split('-');
  const modifier = direction === 'asc' ? 1 : -1;
  
  return [...filteredVideos.value].sort((a, b) => {
    if (field === 'date') {
      return (new Date(a.created_at) - new Date(b.created_at)) * modifier;
    } else {
      return a[field].localeCompare(b[field]) * modifier;
    }
  });
});

// Méthodes
const applyFilters = () => {
  // Le filtrage est géré automatiquement par les computed properties
};

const resetFilters = () => {
  searchQuery.value = '';
  selectedCategory.value = '';
  selectedStatus.value = '';
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
};

const getYouTubeVideoId = (url) => {
  if (!url) return '';
  const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
  return match ? match[1] : '';
};

const getStatusLabel = (status) => {
  const statusMap = {
    draft: 'Brouillon',
    published: 'Publié',
    archived: 'Archivé'
  };
  return statusMap[status] || status;
};

const previewVideo = (video) => {
  previewedVideo.value = video;
};

const editVideo = (video) => {
  router.visit(route('admin.medias.videos.edit', { video: video.id }));
};

const updateVideoStatus = async (video) => {
  const originalStatus = video.status;
  
  try {
    isLoading.value = true;
    await router.patch(
      route('admin.medias.videos.update-status', { video: video.id }),
      { status: video.status },
      {
        preserveScroll: true,
        onSuccess: () => {
          toast.success('Statut mis à jour avec succès');
        },
        onError: (errors) => {
          video.status = originalStatus;
          toast.error(errors.message || 'Échec de la mise à jour');
        }
      }
    );
  } catch (error) {
    console.error('Erreur:', error);
    video.status = originalStatus;
    toast.error('Erreur de connexion au serveur');
  } finally {
    isLoading.value = false;
  }
};

const confirmDelete = (video) => {
  videoToDelete.value = video;
};

const deleteVideo = async () => {
  if (!videoToDelete.value || isLoading.value) return;
  
  isLoading.value = true;
  try {
    await router.delete(
      route('admin.medias.videos.destroy', { video: videoToDelete.value.id }),
      {
        preserveScroll: true,
        onSuccess: () => {
          toast.success('Vidéo supprimée avec succès');
          // Supprimer la vidéo du tableau local
          videos.value = videos.value.filter(v => v.id !== videoToDelete.value.id);
        },
        onError: () => {
          toast.error('Échec de la suppression');
        }
      }
    );
  } catch (error) {
    toast.error('Erreur inattendue');
    console.error(error);
  } finally {
    isLoading.value = false;
    videoToDelete.value = null;
  }
};
</script>

<style scoped>
.video-card {
  @apply bg-white rounded-lg shadow-sm overflow-hidden transition-all hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.video-card:hover {
  @apply transform -translate-y-1;
}

/* Amélioration du focus pour l'accessibilité */
button:focus, select:focus, [tabindex="0"]:focus {
  @apply outline-none ring-2 ring-blue-500 ring-offset-2;
}

/* Transition pour les interactions */
.btn, .video-card, button, select {
  @apply transition-all duration-200 ease-in-out;
}
</style>