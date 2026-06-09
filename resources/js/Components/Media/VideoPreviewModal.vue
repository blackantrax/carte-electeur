<template>
  <Modal :show="show" @close="$emit('close')" max-width="3xl">
    <div class="bg-white rounded-lg overflow-hidden">
      <!-- En-tête -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">Prévisualisation de la vidéo</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-500">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>

      <!-- Contenu -->
      <div class="p-6">
        <!-- Titre et métadonnées -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ video.title }}</h3>
          <div class="flex flex-wrap gap-4 text-sm text-gray-600">
            <div class="flex items-center">
              <i class="fas fa-calendar-alt mr-2"></i>
              <span>Créé le : {{ formatDate(video.created_at) }}</span>
            </div>
            <div class="flex items-center">
              <i class="fas fa-sync-alt mr-2"></i>
              <span>Mis à jour : {{ formatDate(video.updated_at) }}</span>
            </div>
            <div class="flex items-center">
              <i class="fas fa-tag mr-2"></i>
              <span>{{ video.category?.name || 'Non catégorisé' }}</span>
            </div>
            <div class="flex items-center">
              <i class="fas fa-info-circle mr-2"></i>
              <span class="px-2 py-1 rounded-full" :class="statusClass">
                {{ statusLabel }}
              </span>
            </div>
          </div>
        </div>

        <!-- Lecteur vidéo -->
        <div class="aspect-w-16 aspect-h-9 mb-6 bg-black rounded-lg overflow-hidden">
          <iframe
            :src="embedUrl"
            class="w-full h-full"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
          ></iframe>
        </div>

        <!-- Description -->
        <div v-if="video.description" class="prose max-w-none">
          <h4 class="text-md font-medium text-gray-900 mb-2">Description :</h4>
          <div class="text-gray-700" v-html="video.description"></div>
        </div>
        <div v-else class="text-gray-500 italic">
          Aucune description fournie
        </div>
      </div>

      <!-- Pied de page -->
      <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
        <button
          @click="$emit('close')"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
        >
          Fermer
        </button>
        <Link
          :href="route('admin.medias.videos.edit', { video: video.id })"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <i class="fas fa-edit mr-2"></i> Modifier
        </Link>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  video: {
    type: Object,
    required: true
  },
  show: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close']);

// Computed
const embedUrl = computed(() => {
  const videoId = getYouTubeId(props.video.url);
  return `https://www.youtube.com/embed/${videoId}?autoplay=0&rel=0`;
});

const statusLabel = computed(() => {
  const statusMap = {
    draft: 'Brouillon',
    published: 'Publié',
    archived: 'Archivé'
  };
  return statusMap[props.video.status] || props.video.status;
});

const statusClass = computed(() => {
  return {
    'bg-yellow-100 text-yellow-800': props.video.status === 'draft',
    'bg-green-100 text-green-800': props.video.status === 'published',
    'bg-gray-100 text-gray-800': props.video.status === 'archived'
  };
});

// Méthodes
const getYouTubeId = (url) => {
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
  const match = url.match(regExp);
  return (match && match[2].length === 11) ? match[2] : null;
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};
</script>

<style scoped>
.prose :deep(h1, h2, h3, h4, h5, h6) {
  @apply text-gray-900 font-medium mb-2;
}
.prose :deep(p) {
  @apply mb-3;
}
.prose :deep(ul) {
  @apply list-disc pl-5 mb-3;
}
.prose :deep(ol) {
  @apply list-decimal pl-5 mb-3;
}
.prose :deep(a) {
  @apply text-blue-600 hover:underline;
}
</style>