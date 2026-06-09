<template>
  <Modal 
    :show="show" 
    @close="$emit('close')"
    max-width="5xl"
    class="article-preview-modal"
  >
    <div class="modal-content">
      <!-- En-tête du modal -->
      <div class="modal-header">
        <h2 class="modal-title">Aperçu de l'article</h2>
        <button 
          @click="$emit('close')" 
          class="modal-close-btn"
          aria-label="Fermer l'aperçu"
        >
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Contenu du modal -->
      <div class="modal-body">
        <!-- Bannière de l'article -->
        <div 
          v-if="article.featuredImage" 
          class="article-banner"
        >
          <img 
            :src="article.featuredImage" 
            :alt="'Bannière: ' + article.title"
            class="banner-image"
          >
        </div>

        <!-- En-tête de l'article -->
        <div class="article-header">
          <div class="article-meta">
            <span class="article-category">
              {{ article.category?.name || 'Non catégorisé' }}
            </span>
            <span class="article-date">
              <i class="far fa-calendar-alt mr-1"></i>
              {{ formattedDate }}
            </span>
            <span 
              class="article-status"
              :class="statusClass"
            >
              {{ statusLabel }}
            </span>
          </div>
          <h1 class="article-title">{{ article.title }}</h1>
          <div class="article-author">
            <img 
              :src="authorAvatar" 
              alt="Auteur"
              class="author-avatar"
            >
            <span class="author-name">Par {{ authorName }}</span>
          </div>
        </div>

        <!-- Contenu de l'article -->
        <div 
          class="article-content prose max-w-none"
          v-html="article.content"
        ></div>

        <!-- Pied de page de l'article -->
        <div class="article-footer">
          <div class="article-tags" v-if="article.tags?.length">
            <span 
              v-for="tag in article.tags" 
              :key="tag"
              class="article-tag"
            >
              #{{ tag }}
            </span>
          </div>
        </div>
      </div>

      <!-- Pied de page du modal -->
      <div class="modal-footer">
        <button 
          @click="$emit('close')" 
          class="btn btn-secondary"
        >
          Fermer
        </button>
        <Link 
          v-if="article.id"
          :href="`/articles/${article.id}/edit`" 
          class="btn btn-primary ml-3"
        >
          <i class="fas fa-edit mr-2"></i> Continuer l'édition
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
  show: Boolean,
  article: {
    type: Object,
    required: true,
    default: () => ({
      title: '',
      content: '',
      featuredImage: null,
      category: null,
      status: 'draft',
      created_at: null,
      tags: [],
      user: null
    })
  }
});

const emit = defineEmits(['close']);

// Données calculées
const formattedDate = computed(() => {
  if (!props.article.created_at) return 'Non publié';
  return new Date(props.article.created_at).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

const statusLabel = computed(() => {
  const statusMap = {
    draft: 'Brouillon',
    published: 'Publié',
    archived: 'Archivé'
  };
  return statusMap[props.article.status] || props.article.status;
});

const statusClass = computed(() => {
  return {
    draft: 'bg-gray-100 text-gray-800',
    published: 'bg-green-100 text-green-800',
    archived: 'bg-yellow-100 text-yellow-800'
  }[props.article.status] || '';
});

const authorName = computed(() => {
  return props.article.user?.name || 'Auteur inconnu';
});

const authorAvatar = computed(() => {
  return props.article.user?.avatar || '/default-avatar.jpg';
});
</script>

<style scoped>
.article-preview-modal {
  @apply flex items-center justify-center p-4;
}

.modal-content {
  @apply bg-white rounded-lg shadow-xl overflow-hidden w-full max-h-[90vh] flex flex-col;
}

.modal-header {
  @apply px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50;
}

.modal-title {
  @apply text-xl font-semibold text-gray-900;
}

.modal-close-btn {
  @apply p-1 rounded-full hover:bg-gray-200 transition-colors text-gray-500;
}

.modal-body {
  @apply p-6 overflow-y-auto flex-1;
}

.article-banner {
  @apply mb-6 rounded-lg overflow-hidden;
}

.banner-image {
  @apply w-full h-64 object-cover;
}

.article-header {
  @apply mb-8;
}

.article-meta {
  @apply flex flex-wrap items-center gap-3 text-sm text-gray-500 mb-3;
}

.article-category {
  @apply px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium;
}

.article-date {
  @apply flex items-center;
}

.article-status {
  @apply px-2 py-1 rounded-full text-xs font-medium;
}

.article-title {
  @apply text-3xl font-bold text-gray-900 mb-4;
}

.article-author {
  @apply flex items-center gap-3;
}

.author-avatar {
  @apply w-8 h-8 rounded-full object-cover;
}

.author-name {
  @apply text-sm text-gray-600;
}

.article-content {
  @apply py-4 border-t border-gray-200;
}

.article-footer {
  @apply mt-8 pt-6 border-t border-gray-200;
}

.article-tags {
  @apply flex flex-wrap gap-2;
}

.article-tag {
  @apply px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs;
}

.modal-footer {
  @apply px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end;
}

.btn {
  @apply px-4 py-2 rounded-md font-medium transition-colors;
}

.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700;
}

.btn-secondary {
  @apply bg-gray-200 text-gray-800 hover:bg-gray-300;
}

/* Styles pour le contenu de l'article */
.prose :deep(h1) {
  @apply text-2xl font-bold mt-6 mb-4;
}

.prose :deep(h2) {
  @apply text-xl font-bold mt-5 mb-3;
}

.prose :deep(h3) {
  @apply text-lg font-bold mt-4 mb-2;
}

.prose :deep(p) {
  @apply my-4 leading-relaxed;
}

.prose :deep(a) {
  @apply text-blue-600 hover:underline;
}

.prose :deep(img) {
  @apply my-4 rounded-lg shadow-sm max-w-full h-auto;
}

.prose :deep(ul), 
.prose :deep(ol) {
  @apply pl-5 my-4;
}

.prose :deep(blockquote) {
  @apply border-l-4 border-gray-300 pl-4 italic text-gray-600 my-4;
}

.prose :deep(code) {
  @apply bg-gray-100 px-2 py-1 rounded text-sm font-mono;
}

.prose :deep(pre) {
  @apply bg-gray-800 text-gray-100 p-4 rounded-lg overflow-x-auto my-4;
}
</style>