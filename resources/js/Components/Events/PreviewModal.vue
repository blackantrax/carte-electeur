<template>
  <Modal
    :show="show && event"
    @close="$emit('close')"
    max-width="4xl"
    class="event-preview-modal"
  >
    <div v-if="event" class="p-6">
      <div class="flex justify-between items-start mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ event.title }}</h2>
        <button 
          @click="$emit('close')" 
          class="text-gray-500 hover:text-gray-700 transition-colors"
          aria-label="Fermer le modal"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Vidéo YouTube -->
        <div class="video-container bg-black rounded-lg overflow-hidden shadow-md">
          <iframe
            v-if="youtubeId"
            class="w-full aspect-video"
            :src="`https://www.youtube.com/embed/${youtubeId}?autoplay=0`"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
            loading="lazy"
          ></iframe>
          <div v-else class="flex items-center justify-center h-full text-white p-8 text-center bg-gray-800">
            <span>Aucune vidéo disponible</span>
          </div>
        </div>

        <!-- Détails -->
        <div class="space-y-4">
          <div class="detail-item">
            <span class="detail-label">Date :</span>
            <span class="detail-value">{{ formattedDate }}</span>
          </div>

          <div class="detail-item">
            <span class="detail-label">Lieu :</span>
            <span class="detail-value">{{ event.location || 'Non spécifié' }}</span>
          </div>

          <div class="detail-item">
            <span class="detail-label">Statut :</span>
            <span :class="statusClass">
              {{ statusLabel }}
            </span>
          </div>

          <div class="description mt-6">
            <h3 class="font-semibold text-lg mb-2">Description :</h3>
            <div class="prose max-w-none" v-html="event.description"></div>
          </div>
        </div>
      </div>

      <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end">
        <button
          @click="$emit('close')"
          class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
        >
          Fermer
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  event: {
    type: Object,
    default: null,
    validator: (value) => {
      // Permet null ou un objet avec les propriétés requises
      return value === null || (
        typeof value === 'object' && 
        'title' in value &&
        'date' in value &&
        'status' in value &&
        'description' in value
      );
    }
  },
  show: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close']);

// Extrait l'ID YouTube de l'URL
const youtubeId = computed(() => {
  if (!props.event?.url) return null;
  try {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = props.event.url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
  } catch {
    return null;
  }
});

// Formatage de la date
const formattedDate = computed(() => {
  if (!props.event?.date) return 'Non spécifiée';
  try {
    const date = new Date(props.event.date);
    return date.toLocaleDateString('fr-FR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch {
    return 'Date invalide';
  }
});

// Traduction du statut
const statusLabel = computed(() => {
  const statusMap = {
    draft: 'Brouillon',
    published: 'Publié',
    archived: 'Archivé'
  };
  return props.event?.status ? statusMap[props.event.status] || props.event.status : 'Inconnu';
});

// Classes CSS pour le statut
const statusClass = computed(() => {
  const base = 'px-3 py-1 rounded-full text-sm font-medium';
  const status = props.event?.status || 'draft';
  
  return {
    draft: `${base} bg-yellow-100 text-yellow-800`,
    published: `${base} bg-green-100 text-green-800`,
    archived: `${base} bg-gray-100 text-gray-800`
  }[status];
});
</script>

<style scoped>
.video-container {
  position: relative;
  padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
}

.video-container iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.detail-item {
  @apply flex items-start gap-4;
}

.detail-label {
  @apply font-medium text-gray-600 w-24 flex-shrink-0;
}

.detail-value {
  @apply text-gray-800;
}

/* Styles pour le contenu riche */
.prose :deep(h1) {
  @apply text-2xl font-bold mt-6 mb-4 text-gray-900;
}

.prose :deep(h2) {
  @apply text-xl font-bold mt-5 mb-3 text-gray-900;
}

.prose :deep(h3) {
  @apply text-lg font-bold mt-4 mb-2 text-gray-900;
}

.prose :deep(p) {
  @apply my-4 leading-relaxed text-gray-700;
}

.prose :deep(a) {
  @apply text-blue-600 hover:underline;
}

.prose :deep(img) {
  @apply my-4 rounded-lg shadow-sm max-w-full h-auto;
}

.prose :deep(ul) {
  @apply list-disc pl-5 my-4;
}

.prose :deep(ol) {
  @apply list-decimal pl-5 my-4;
}

.prose :deep(li) {
  @apply mb-1 text-gray-700;
}

.prose :deep(blockquote) {
  @apply border-l-4 border-gray-300 pl-4 italic text-gray-600 my-4;
}

.prose :deep(code) {
  @apply bg-gray-100 px-2 py-1 rounded text-sm font-mono text-gray-800;
}

.prose :deep(pre) {
  @apply bg-gray-800 text-gray-100 p-4 rounded-lg overflow-x-auto my-4;
}
</style>