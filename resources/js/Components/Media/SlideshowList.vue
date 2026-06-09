<template>
  <div>
    <!-- En-tête avec comptage et tri -->
    <div class="flex justify-between items-center mb-4">
      <p class="text-sm text-gray-600">
        {{ filteredSlideshows.length }} diaporama(s) trouvé(s)
      </p>
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-600">Trier par :</label>
        <select
          v-model="sortBy"
          @change="sortSlideshows"
          class="text-sm border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="date-desc">Date (récent)</option>
          <option value="date-asc">Date (ancien)</option>
          <option value="title-asc">Titre (A-Z)</option>
          <option value="title-desc">Titre (Z-A)</option>
        </select>
      </div>
    </div>

    <!-- Liste des diaporamas -->
    <div v-if="filteredSlideshows.length > 0" class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
      <div 
        v-for="slideshow in sortedSlideshows" 
        :key="slideshow.id" 
        class="slideshow-card group"
        :class="{
          'border-l-4 border-blue-500': slideshow.status === 'published',
          'border-l-4 border-yellow-500': slideshow.status === 'draft',
          'border-l-4 border-gray-500': slideshow.status === 'archived'
        }"
      >
        <!-- Première image du diaporama -->
        <div class="relative aspect-video overflow-hidden rounded-t-lg">
          <img 
            :src="slideshow.images[0]?.url || '/images/default-slideshow.jpg'"
            :alt="slideshow.title"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
          >
          <div class="absolute inset-0 bg-black bg-opacity-20 flex items-end justify-between p-3 opacity-0 group-hover:opacity-100 transition-opacity">
            <span class="text-white text-sm bg-black bg-opacity-50 px-2 py-1 rounded">
              <i class="fas fa-images mr-1"></i>
              {{ slideshow.images.length }} image(s)
            </span>
            <button 
              @click.stop="$emit('preview', slideshow)"
              class="text-white bg-blue-500 hover:bg-blue-600 px-3 py-1 rounded text-sm transition-colors"
              aria-label="Voir le diaporama"
            >
              <i class="fas fa-eye mr-1"></i> Voir
            </button>
          </div>
        </div>

        <!-- Contenu -->
        <div class="p-4">
          <div class="flex justify-between items-start mb-2">
            <span class="text-xs font-medium text-blue-600">
              {{ formatDate(slideshow.created_at) }}
            </span>
            <span 
              class="text-xs px-2 py-1 rounded-full"
              :class="{
                'bg-yellow-100 text-yellow-800': slideshow.status === 'draft',
                'bg-green-100 text-green-800': slideshow.status === 'published',
                'bg-gray-100 text-gray-800': slideshow.status === 'archived'
              }"
            >
              {{ getStatusLabel(slideshow.status) }}
            </span>
          </div>

          <h3 class="text-lg font-bold text-gray-900 mb-1 line-clamp-2">
            {{ slideshow.title }}
          </h3>

          <p class="text-sm text-gray-500 mb-3">
            {{ slideshow.category?.name || 'Non catégorisé' }}
          </p>

          <!-- Actions -->
          <div class="flex justify-between items-center">
            <select
              v-model="slideshow.status"
              @change="$emit('status-updated', slideshow, slideshow.status)"
              :disabled="isLoading"
              class="text-xs py-1 px-2 rounded border focus:ring-blue-500 focus:border-blue-500"
              :class="{
                'border-yellow-300 bg-yellow-50 text-yellow-800': slideshow.status === 'draft',
                'border-green-300 bg-green-50 text-green-800': slideshow.status === 'published',
                'border-gray-300 bg-gray-50 text-gray-800': slideshow.status === 'archived'
              }"
            >
              <option value="draft">Brouillon</option>
              <option value="published">Publié</option>
              <option value="archived">Archivé</option>
            </select>

            <div class="flex gap-2">
              <button 
                @click.stop="$emit('edit', slideshow)"
                class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 flex items-center justify-center transition-colors"
                aria-label="Modifier"
              >
                <i class="fas fa-edit"></i>
              </button>
              <button 
                @click.stop="$emit('delete', slideshow)"
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
      <i class="fas fa-images text-5xl text-gray-300 mb-4"></i>
      <h3 class="text-xl font-medium text-gray-700 mb-2">Aucun diaporama trouvé</h3>
      <p class="text-gray-500 max-w-md mx-auto">
        Essayez d'ajuster vos filtres ou créez un nouveau diaporama
      </p>
      <Link 
        :href="route('admin.medias.slideshows.create')" 
        class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <i class="fas fa-plus mr-2"></i> Créer un diaporama
      </Link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  slideshows: {
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
const filteredSlideshows = computed(() => {
  return props.slideshows;
});

const sortedSlideshows = computed(() => {
  const [field, direction] = sortBy.value.split('-');
  const modifier = direction === 'asc' ? 1 : -1;
  
  return [...filteredSlideshows.value].sort((a, b) => {
    if (field === 'date') {
      return (new Date(a.created_at) - new Date(b.created_at)) * modifier;
    } else {
      return a[field].localeCompare(b[field]) * modifier;
    }
  });
});

// Méthodes
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

const sortSlideshows = () => {
  // Le tri est géré automatiquement par la computed sortedSlideshows
};
</script>

<style scoped>
.slideshow-card {
  @apply bg-white rounded-lg shadow-sm overflow-hidden transition-all hover:shadow-md;
}

.slideshow-card:hover {
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