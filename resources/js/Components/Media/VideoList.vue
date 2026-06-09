<template>
  <div>
    <!-- En-tête avec comptage et tri -->
    <div class="flex justify-between items-center mb-4">
      <p class="text-sm text-gray-600">
        {{ filteredVideos.length }} vidéo(s) trouvée(s)
      </p>
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-600">Trier par :</label>
        <select
          v-model="sortBy"
          @change="sortVideos"
          class="text-sm border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="date-desc">Date (récent)</option>
          <option value="date-asc">Date (ancien)</option>
          <option value="title-asc">Titre (A-Z)</option>
          <option value="title-desc">Titre (Z-A)</option>
        </select>
      </div>
    </div>

    <!-- Liste des vidéos -->
    <div v-if="filteredVideos.length > 0" class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
      <div 
        v-for="video in sortedVideos" 
        :key="video.id" 
        class="video-card group"
        :class="{
          'border-l-4 border-blue-500': video.status === 'published',
          'border-l-4 border-yellow-500': video.status === 'draft',
          'border-l-4 border-gray-500': video.status === 'archived'
        }"
      >
        <!-- Miniature vidéo -->
        <div class="relative aspect-video overflow-hidden rounded-t-lg">
          <img 
            :src="`https://img.youtube.com/vi/${getYouTubeVideoId(video.url)}/maxresdefault.jpg`" 
            :alt="video.title"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
          >
          <div class="absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
            <button 
              @click.stop="$emit('preview', video)"
              class="w-16 h-16 rounded-full bg-white bg-opacity-80 text-blue-600 flex items-center justify-center hover:bg-opacity-100 hover:text-blue-700 transition-all"
              aria-label="Voir la vidéo"
            >
              <i class="fas fa-play text-xl"></i>
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
            >
              {{ getStatusLabel(video.status) }}
            </span>
          </div>

          <h3 class="text-lg font-bold text-gray-900 mb-1 line-clamp-2">
            {{ video.title }}
          </h3>

          <p class="text-sm text-gray-500 mb-3">
            {{ video.category?.name || 'Non catégorisé' }}
          </p>

          <!-- Actions -->
          <div class="flex justify-between items-center">
            <select
              v-model="video.status"
              @change="$emit('status-updated', video, video.status)"
              :disabled="isLoading"
              class="text-xs py-1 px-2 rounded border focus:ring-blue-500 focus:border-blue-500"
              :class="{
                'border-yellow-300 bg-yellow-50 text-yellow-800': video.status === 'draft',
                'border-green-300 bg-green-50 text-green-800': video.status === 'published',
                'border-gray-300 bg-gray-50 text-gray-800': video.status === 'archived'
              }"
            >
              <option value="draft">Brouillon</option>
              <option value="published">Publié</option>
              <option value="archived">Archivé</option>
            </select>

            <div class="flex gap-2">
              <button 
                @click.stop="$emit('edit', video)"
                class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 flex items-center justify-center transition-colors"
                aria-label="Modifier"
              >
                <i class="fas fa-edit"></i>
              </button>
              <button 
                @click.stop="$emit('delete', video)"
                class="w-8 h-8 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center transition-colors"
                aria-label="Supprimer"
              >
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- État vide -->
    <div v-else class="py-12 text-center">
      <i class="far fa-file-video text-5xl text-gray-300 mb-4"></i>
      <h3 class="text-xl font-medium text-gray-700 mb-2">Aucune vidéo trouvée</h3>
      <p class="text-gray-500 max-w-md mx-auto">
        Essayez d'ajuster vos filtres ou ajoutez une nouvelle vidéo
      </p>
      <Link 
        :href="route('admin.medias.videos.create')" 
        class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <i class="fas fa-plus mr-2"></i> Ajouter une vidéo
      </Link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  videos: {
    type: Array,
    required: true
  },
  isLoading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['preview', 'edit', 'delete', 'status-updated']);

const sortBy = ref('date-desc');

// Computed
const filteredVideos = computed(() => {
  return props.videos;
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
const getYouTubeVideoId = (url) => {
  if (!url) return '';
  const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
  return match ? match[1] : '';
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

const getStatusLabel = (status) => {
  const statusMap = {
    draft: 'Brouillon',
    published: 'Publié',
    archived: 'Archivé'
  };
  return statusMap[status] || status;
};

const sortVideos = () => {
  // Le tri est géré automatiquement par la computed sortedVideos
};
</script>

<style scoped>
.video-card {
  @apply bg-white rounded-lg shadow-sm overflow-hidden transition-all hover:shadow-md;
}

.video-card:hover {
  @apply transform -translate-y-1;
}

.empty-state {
  @apply py-12 text-center;
}

.empty-state h3 {
  @apply text-xl font-medium text-gray-700 mb-2;
}

.empty-state p {
  @apply text-gray-500 max-w-md mx-auto;
}
</style>