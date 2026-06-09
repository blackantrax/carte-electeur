<template>
  <AppLayout>
    <PageTitle title="Gestion des Articles" :breadcrumbs="breadcrumbs">
      <template #actions>
        <Link 
        :href="route('articles.create')" 
        class="btn btn-primary"
        v-if="$page.props.auth.user"
        >
        <i class="fas fa-plus mr-2"></i> Nouvel article
        </Link>
      </template>
    </PageTitle>

    <div class="dashboard-card">
      <!-- Filtres améliorés -->
      <div class="filters-grid">
        <div class="search-filter">
          <i class="fas fa-search filter-icon"></i>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Rechercher un article..." 
            class="filter-input"
          >
        </div>

        <div class="select-filter">
          <i class="fas fa-tag filter-icon"></i>
          <select v-model="selectedCategory" class="filter-select">
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

        <div class="select-filter">
          <i class="fas fa-filter filter-icon"></i>
          <select v-model="selectedStatus" class="filter-select">
            <option value="">Tous statuts</option>
            <option value="draft">Brouillon</option>
            <option value="published">Publié</option>
            <option value="archived">Archivé</option>
          </select>
        </div>

        <button 
          @click="resetFilters" 
          class="btn btn-outline-danger"
        >
          <i class="fas fa-times mr-2"></i> Réinitialiser
        </button>
      </div>

      <!-- Tableau responsive -->
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th class="w-16">Image</th>
              <th class="min-w-[200px]">Titre</th>
              <th>Catégorie</th>
              <th>Statut</th>
              <th class="w-32 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="article in filteredArticles" 
              :key="article.id"
              class="hover:bg-gray-50/50"
            >
              <td>
                <img 
                  :src="article.featured_image || '/placeholder-article.jpg'" 
                  :alt="article.title"
                  class="table-image"
                >
              </td>
              <td>
                <div class="font-medium text-gray-900 truncate max-w-[300px]">
                  {{ article.title }}
                </div>
                <div class="text-xs text-gray-500 mt-1">
                  {{ formatDate(article.created_at) }}
                </div>
              </td>
              <td>
                <span class="badge" :class="categoryColor(article.category)">
                  {{ article.category?.name || 'Non catégorisé' }}
                </span>
              </td>
              <td>
                <select 
                  v-model="article.status" 
                  @change="updateStatus(article)"
                  class="status-select"
                  :class="statusClass(article.status)"
                >
                  <option value="draft">Brouillon</option>
                  <option value="published">Publié</option>
                  <option value="archived">Archivé</option>
                </select>
              </td>
              <td>
                <div class="flex justify-end space-x-1">
                  <button 
                    @click="previewArticle(article)"
                    class="table-action-btn text-blue-500 hover:bg-blue-50"
                    title="Aperçu"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <Link 
                    :href="`/articles/${article.id}/edit`"
                    class="table-action-btn text-yellow-500 hover:bg-yellow-50"
                    title="Éditer"
                  >
                    <i class="fas fa-edit"></i>
                  </Link>
                  <button 
                    @click="confirmDelete(article)"
                    class="table-action-btn text-red-500 hover:bg-red-50"
                    title="Supprimer"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- État vide -->
        <div 
          v-if="filteredArticles.length === 0" 
          class="empty-state"
        >
          <i class="fas fa-newspaper text-4xl text-gray-300 mb-3"></i>
          <p class="text-gray-500">Aucun article trouvé</p>
          <p class="text-sm text-gray-400 mt-1">
            Essayez d'ajuster vos filtres de recherche
          </p>
        </div>
      </div>
    </div>
    <!-- Modal d'aperçu premium -->
<Modal 
  :show="!!previewedArticle" 
  @close="previewedArticle = null"
  max-width="7xl"
  class="overflow-hidden"
>
  <div class="flex flex-col h-[90vh] bg-white rounded-2xl shadow-2xl overflow-hidden">
    <!-- Header avec navigation sticky -->
    <div class="sticky top-0 z-20 bg-white/95 backdrop-blur-sm border-b">
      <div class="flex justify-between items-center p-4">
        <div class="flex items-center space-x-3">
          <button 
            @click="previewedArticle = null"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-500 hover:text-gray-700"
            aria-label="Fermer"
          >
            <i class="fas fa-arrow-left text-lg"></i>
          </button>
          <h2 class="text-xl font-bold text-gray-800 line-clamp-1">
            {{ previewedArticle?.title }}
          </h2>
        </div>
        
        <div class="flex space-x-2">
          <Link 
            v-if="previewedArticle"
            :href="`/articles/${previewedArticle.id}/edit`" 
            class="flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
          >
            <i class="fas fa-pencil-alt mr-2"></i>
            <span class="hidden sm:inline">Éditer</span>
          </Link>
          <button 
            @click="previewedArticle = null"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-500 hover:text-gray-700"
          >
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>
      </div>
      
      <!-- Barre de progression de lecture -->
      <div class="h-1 w-full bg-gray-200">
        <div 
          class="h-full bg-indigo-600 transition-all duration-300"
          :style="{ width: `${scrollProgress}%` }"
        ></div>
      </div>
    </div>

    <!-- Contenu scrollable -->
    <div 
      class="flex-1 overflow-y-auto"
      ref="contentContainer"
      @scroll="updateScrollProgress"
    >
      <!-- Image hero -->
      <div 
        v-if="previewedArticle?.featured_image" 
        class="relative w-full h-96 bg-gray-100 overflow-hidden"
      >
        <img 
          :src="previewedArticle.featured_image" 
          class="w-full h-full object-cover"
          alt="Image mise en avant"
        >
        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-black/10 to-transparent"></div>
      </div>

      <!-- Métadonnées -->
      <div class="px-6 sm:px-8 pt-6">
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-6">
          <div class="flex items-center">
            <i class="fas fa-user-edit mr-2 text-gray-400"></i>
            <span>{{ previewedArticle?.author?.name || 'Auteur inconnu' }}</span>
          </div>
          
          <div class="flex items-center">
            <i class="fas fa-clock mr-2 text-gray-400"></i>
            <span>{{ formatDate(previewedArticle?.created_at, true) }}</span>
            <span v-if="previewedArticle?.created_at !== previewedArticle?.updated_at" 
                  class="ml-1 text-xs text-gray-400">(modifié)</span>
          </div>
          
          <div class="flex items-center">
            <i class="fas fa-tag mr-2 text-gray-400"></i>
            <span>{{ previewedArticle?.category?.name || 'Sans catégorie' }}</span>
          </div>
          
          <span 
            class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
            :class="{
              'bg-green-100 text-green-800': previewedArticle?.status === 'published',
              'bg-amber-100 text-amber-800': previewedArticle?.status === 'draft',
              'bg-gray-100 text-gray-800': previewedArticle?.status === 'archived'
            }"
          >
            {{ statusLabel(previewedArticle?.status) }}
          </span>
        </div>

        <!-- Contenu principal -->
        <article class="prose prose-lg max-w-none w-full mx-auto pb-12">
          <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-6">
            {{ previewedArticle?.title }}
          </h1>
          
          <div 
            class="prose-img:rounded-xl prose-img:shadow-lg prose-img:mx-auto prose-img:max-w-full prose-figcaption:text-center prose-figcaption:text-sm prose-figcaption:text-gray-500"
            v-html="previewedArticle?.content"
          ></div>
        </article>
      </div>
    </div>

    <!-- Footer avec actions -->
    <div class="sticky bottom-0 z-10 bg-white/95 backdrop-blur-sm border-t p-4">
      <div class="flex justify-between items-center">
        <div class="text-sm text-gray-500">
          <span v-if="readingTime > 0">
            <i class="fas fa-book-open mr-1"></i>
            {{ readingTime }} min de lecture
          </span>
        </div>
        
        <div class="flex space-x-3">
          <button 
            v-if="previewedArticle?.status === 'published'"
            @click="shareArticle"
            class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <i class="fas fa-share-alt mr-2 text-gray-600"></i>
            <span class="hidden sm:inline">Partager</span>
          </button>
          
          <button 
            @click="scrollToTop"
            class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors text-gray-600"
            aria-label="Remonter en haut"
          >
            <i class="fas fa-arrow-up"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</Modal>


    <!-- Confirmation de suppression -->
    <ConfirmationDialog 
      :show="!!articleToDelete"
      @close="articleToDelete = null"
      @confirm="deleteArticle"
      title="Confirmer la suppression"
    >
      <p>Êtes-vous sûr de vouloir supprimer définitivement l'article <strong>"{{ articleToDelete?.title }}"</strong> ?</p>
      <template #confirmButton>
        <i class="fas fa-trash mr-2"></i> Supprimer
      </template>
    </ConfirmationDialog>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';

const page = usePage();
const articles = ref(page.props.articles || []);
const previewedArticle = ref(null);
const articleToDelete = ref(null);
const searchQuery = ref('');
const selectedCategory = ref('');
const selectedStatus = ref('');

const breadcrumbs = [
  { label: 'Dashboard', url: '/dashboard' },
  { label: 'Articles', active: true }
];


// Dans votre script setup
const contentContainer = ref(null);
const scrollProgress = ref(0);
const readingTime = ref(0);

const updateScrollProgress = () => {
  if (!contentContainer.value) return;
  
  const { scrollTop, scrollHeight, clientHeight } = contentContainer.value;
  const progress = (scrollTop / (scrollHeight - clientHeight)) * 100;
  scrollProgress.value = Math.min(100, Math.max(0, progress));
};

const scrollToTop = () => {
  contentContainer.value?.scrollTo({ top: 0, behavior: 'smooth' });
};

const calculateReadingTime = () => {
  if (!previewedArticle.value?.content) {
    readingTime.value = 0;
    return;
  }
  
  // Environ 200 mots par minute
  const textContent = previewedArticle.value.content.replace(/<[^>]*>/g, '');
  const wordCount = textContent.split(/\s+/).length;
  readingTime.value = Math.max(1, Math.round(wordCount / 200));
};

watch(() => previewedArticle.value, () => {
  if (previewedArticle.value) {
    nextTick(() => {
      calculateReadingTime();
    });
  }
}, { immediate: true });

const uniqueCategories = computed(() => {
  const categories = articles.value
    .map(article => article.category)
    .filter(Boolean);
  
  return [...new Map(categories.map(cat => [cat.id, cat])).values()];
});

const filteredArticles = computed(() => {
  return articles.value.filter(article => {
    const matchesSearch = article.title.toLowerCase()
      .includes(searchQuery.value.toLowerCase());
    const matchesCategory = selectedCategory.value 
      ? article.category?.id === selectedCategory.value 
      : true;
    const matchesStatus = selectedStatus.value 
      ? article.status === selectedStatus.value 
      : true;
    
    return matchesSearch && matchesCategory && matchesStatus;
  });
});

const resetFilters = () => {
  searchQuery.value = '';
  selectedCategory.value = '';
  selectedStatus.value = '';
};

const previewArticle = (article) => {
  previewedArticle.value = article;
};

const confirmDelete = (article) => {
  articleToDelete.value = article;
};

const deleteArticle = async () => {
  if (!articleToDelete.value) return;

  try {
    await router.delete(`/admin/blogs/${articleToDelete.value.id}`);
    articles.value = articles.value.filter(a => a.id !== articleToDelete.value.id);
  } finally {
    articleToDelete.value = null;
  }
};

const updateStatus = (article) => {
  router.patch(`/admin/blogs/${article.id}/status`, {
    status: article.status
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Mise à jour optimiste
      const index = articles.value.findIndex(a => a.id === article.id);
      if (index !== -1) {
        articles.value[index].status = article.status;
      }
    }
  });
};

const formatDate = (dateString, withTime = false) => {
  if (!dateString) return '';
  const options = withTime 
    ? { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }
    : { day: '2-digit', month: 'short', year: 'numeric' };
  return new Date(dateString).toLocaleDateString('fr-FR', options);
};

const statusLabel = (status) => {
  const labels = {
    draft: 'Brouillon',
    published: 'Publié',
    archived: 'Archivé'
  };
  return labels[status] || status;
};

const statusClass = (status) => {
  return {
    draft: 'bg-gray-100 text-gray-800',
    published: 'bg-green-100 text-green-800',
    archived: 'bg-yellow-100 text-yellow-800'
  }[status] || '';
};

const categoryColor = (category) => {
  if (!category) return 'bg-gray-100 text-gray-800';
  // Vous pouvez personnaliser cette logique selon vos catégories
  return 'bg-blue-100 text-blue-800';
};
</script>

<style scoped>
.dashboard-card {
  @apply bg-white rounded-xl shadow-sm p-6;
}

.filters-grid {
  @apply grid grid-cols-1 md:grid-cols-4 gap-4 mb-6;
}

.search-filter, .select-filter {
  @apply relative flex items-center;
}

.filter-icon {
  @apply absolute left-3 text-gray-400;
}

.filter-input, .filter-select {
  @apply pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
}

.table-responsive {
  @apply overflow-x-auto rounded-lg border border-gray-200;
}

.data-table {
  @apply min-w-full divide-y divide-gray-200;
}

.data-table thead {
  @apply bg-gray-50;
}

.data-table th {
  @apply px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
}

.data-table td {
  @apply px-4 py-4 whitespace-nowrap text-sm;
}

.table-image {
  @apply w-12 h-12 rounded-md object-cover border border-gray-200;
}

.badge {
  @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
}

.status-select {
  @apply px-3 py-1 border rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
}

.table-action-btn {
  @apply p-2 rounded-full transition-colors duration-200;
}

.empty-state {
  @apply p-8 text-center border-t border-gray-200;
}

.modal-close-btn {
  @apply p-2 rounded-full hover:bg-gray-100 transition-colors;
}

.prose :deep(img) {
  @apply rounded-lg my-4;
}

.prose :deep(h2) {
  @apply text-xl font-bold mt-6 mb-3;
}

.prose :deep(p) {
  @apply my-3;
}

/* Ajoutez dans votre section style */
.prose {
  h1, h2, h3 {
    @apply text-gray-900;
  }
  a {
    @apply text-indigo-600 hover:text-indigo-800 underline;
  }
  blockquote {
    @apply border-l-4 border-indigo-300 pl-4 italic text-gray-600;
  }
  code {
    @apply bg-gray-100 px-1.5 py-0.5 rounded text-sm;
  }
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>