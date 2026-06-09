<template>
    <AppLayout>
        <PageTitle title="Liste des Événements" :breadcrumbs="breadcrumbs">
            <template #actions>
                <Link 
                    :href="route('admin.events.create')" 
                    class="btn btn-primary"
                >
                    <i class="fas fa-plus mr-2"></i> Nouvel événement
                </Link>
            </template>
        </PageTitle>

        <section class="bg-white rounded-xl shadow-sm p-6">
            <!-- Filtres améliorés -->
            <div class="filters-container">
                <!-- Barre de recherche avec bouton intégré -->
                <div class="search-container">
                    <div class="search-input">
                        <i class="fas fa-search"></i>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Rechercher par titre..."
                            @keyup.enter="applyFilters"
                        >
                        <button 
                            v-if="searchQuery" 
                            @click="searchQuery = ''" 
                            class="clear-search"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Filtres groupés -->
                <div class="filter-group">
                    <div class="filter-item">
                        <label>Catégorie</label>
                        <select v-model="selectedCategory" @change="applyFilters">
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

                    <div class="filter-item">
                        <label>Statut</label>
                        <select v-model="selectedStatus" @change="applyFilters">
                            <option value="">Tous statuts</option>
                            <option value="draft">Brouillon</option>
                            <option value="published">Publié</option>
                            <option value="archived">Archivé</option>
                        </select>
                    </div>

                    <button 
                        @click="resetFilters" 
                        class="reset-btn"
                        :disabled="!hasActiveFilters"
                    >
                        <i class="fas fa-undo-alt mr-2"></i>
                        Réinitialiser
                    </button>
                </div>
            </div>

            <!-- Résultats et statistiques -->
            <div class="results-header">
                <div class="results-count">
                    {{ filteredEvents.length }} événement(s) trouvé(s)
                </div>
                <div class="sort-options">
                    <label>Trier par :</label>
                    <select v-model="sortBy" @change="sortEvents">
                        <option value="date-desc">Date (récent)</option>
                        <option value="date-asc">Date (ancien)</option>
                        <option value="title-asc">Titre (A-Z)</option>
                        <option value="title-desc">Titre (Z-A)</option>
                    </select>
                </div>
            </div>

            <!-- Liste des événements -->
            <div v-if="filteredEvents.length > 0" class="events-grid">
                <div 
                    v-for="event in filteredEvents" 
                    :key="event.id" 
                    class="event-card"
                    :class="{
                        'border-l-4 border-blue-500': event.status === 'published',
                        'border-l-4 border-yellow-500': event.status === 'draft',
                        'border-l-4 border-gray-500': event.status === 'archived'
                    }"
                >
                    <!-- Miniature vidéo avec overlay -->
                    <div class="video-thumbnail">
                        <img 
                            :src="`https://img.youtube.com/vi/${getYouTubeVideoId(event.url)}/mqdefault.jpg`" 
                            :alt="event.title"
                            class="thumbnail-image"
                        >
                        <div class="thumbnail-overlay">
                            <button @click.stop="previewEvent(event)" class="play-button">
                                <i class="fas fa-play"></i>
                            </button>
                            <span class="video-duration">2:45</span>
                        </div>
                    </div>

                    <!-- Contenu de la carte -->
                    <div class="event-details">
                        <div class="event-meta">
                            <span class="event-date">
                                <i class="far fa-calendar-alt mr-1"></i>
                                {{ formatDate(event.date) }}
                            </span>
                            <span class="event-category" :style="{ backgroundColor: event.category?.color || '#e2e8f0' }">
                                {{ event.category?.name || 'Non catégorisé' }}
                            </span>
                        </div>

                        <h3 class="event-title">{{ event.title }}</h3>
                        <p class="event-excerpt">{{ stripHtml(truncateText(event.description, 100)) }}</p>

                        <!-- Actions -->
                        <div class="event-actions">
                            <div class="status-selector">
                                <select 
                                    v-model="event.status" 
                                    @change="updateEventStatus(event)"
                                    :disabled="isLoading"
                                    class="status-select"
                                    :class="{
                                        'status-draft': event.status === 'draft',
                                        'status-published': event.status === 'published',
                                        'status-archived': event.status === 'archived'
                                    }"
                                    >
                                    <option value="draft">Brouillon</option>
                                    <option value="published">Publié</option>
                                    <option value="archived">Archivé</option>
                                    </select>
                            </div>

                            <div class="action-buttons">
                                <button @click.stop="editEvent(event)" class="btn-edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click.stop="previewEvent(event)" class="btn-view">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button @click.stop="confirmDelete(event)" class="btn-delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- État vide -->
            <div v-else class="empty-state">
                <i class="empty-icon far fa-calendar-times"></i>
                <h3>Aucun événement trouvé</h3>
                <p>Essayez d'ajuster vos filtres ou créez un nouvel événement</p>
                <Link :href="route('admin.events.create')" class="btn btn-primary mt-4">
                    <i class="fas fa-plus mr-2"></i> Créer un événement
                </Link>
            </div>
        </section>

        <!-- Modal d'aperçu -->
        <EventPreviewModal 
        :show="!!previewedEvent"
        :event="previewedEvent"
        @close="previewedEvent = null"
        />

        <!-- Confirmation de suppression -->
        <ConfirmationDialog
            :show="!!eventToDelete"
            title="Confirmer la suppression"
            @close="eventToDelete = null"
            @confirm="deleteEvent"
        >
            <p>Êtes-vous sûr de vouloir supprimer définitivement l'événement <strong>"{{ eventToDelete?.title }}"</strong> ?</p>
        </ConfirmationDialog>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import EventPreviewModal from '@/Components/Events/PreviewModal.vue';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const page = usePage();

const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Événements', url: route('admin.events.index') }
];

// Réactifs
const events = ref(page.props.events || []);
const previewedEvent = ref(null);
const eventToDelete = ref(null);
const searchQuery = ref('');
const selectedCategory = ref('');
const selectedStatus = ref('');
const sortBy = ref('date-desc');
const isLoading = ref(false);


const previewEvent = (event) => {
  previewedEvent.value = {
    ...event,
    // Formatage de la date pour l'affichage
    formattedDate: formatDate(event.date),
    // Extraction de l'ID YouTube
    youtubeId: getYouTubeVideoId(event.url),
    // Conversion de la description en texte brut si nécessaire
    cleanDescription: stripHtml(event.description)
  };
};

// Computed
const hasActiveFilters = computed(() => searchQuery.value || selectedCategory.value || selectedStatus.value);

const uniqueCategories = computed(() => {
  const categories = events.value
    .map(event => event.category)
    .filter(Boolean);
  return [...new Map(categories.map(cat => [cat.id, cat])).values()];
});

const filteredEvents = computed(() => {
  let results = events.value.filter(event => {
    return (
      event.title.toLowerCase().includes(searchQuery.value.toLowerCase()) &&
      (!selectedCategory.value || event.category?.id == selectedCategory.value) &&
      (!selectedStatus.value || event.status === selectedStatus.value)
    );
  });

  return sortEvents(results);
});


// Ajoutez cette fonction dans vos méthodes
const formatDate = (dateString) => {
  if (!dateString) return '';
  
  const options = { 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  
  return new Date(dateString).toLocaleDateString('fr-FR', options);
}
 // Méthodes
function sortEvents(events) {
  const [field, direction] = sortBy.value.split('-');
  const modifier = direction === 'asc' ? 1 : -1;
  
  return [...events].sort((a, b) => {
    if (field === 'date') {
      return (new Date(a.date) - new Date(b.date)) * modifier;
    }
    return a[field].localeCompare(b[field]) * modifier;
  });
}

function stripHtml(html) {
  if (!html) return '';
  const div = document.createElement('div');
  div.innerHTML = html;
  return div.textContent || div.innerText || '';
}

function truncateText(text, maxLength = 100) {
  const cleanText = stripHtml(text);
  return cleanText.length <= maxLength 
    ? cleanText 
    : `${cleanText.substring(0, maxLength)}...`;
}

function getYouTubeVideoId(url) {
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
  const match = url.match(regExp);
  return (match && match[2].length === 11) ? match[2] : null;
}

const updateEventStatus = async (event) => {
  const originalStatus = event.status;
  
  try {
    await router.patch(
      route('admin.events.update-status', { event: event.id }),
      { status: event.status },
      {
        preserveScroll: true,
        onSuccess: () => {
          // Utilisation directe du toast sans accès à response
          toast.success('Statut mis à jour avec succès');
        },
        onError: (errors) => {
          event.status = originalStatus; // Annule le changement en cas d'erreur
          toast.error(errors.message || 'Échec de la mise à jour');
        }
      }
    );
  } catch (error) {
    console.error('Erreur:', error);
    event.status = originalStatus;
    toast.error('Erreur de connexion au serveur');
  }
};

function editEvent(event) {
  router.visit(route('admin.events.edit', { event: event.id }));
}

function confirmDelete(event) {
  eventToDelete.value = event;
}

async function deleteEvent() {
  if (!eventToDelete.value || isLoading.value) return;
  
  isLoading.value = true;
  try {
    await router.delete(route('admin.events.destroy', { 
      event: eventToDelete.value.id 
    }), {
      preserveScroll: true,
      onSuccess: () => toast.success('Événement supprimé'),
      onError: () => toast.error('Échec de la suppression')
    });
  } catch (error) {
    toast.error(error.message || 'Erreur inattendue');
    console.error(error);
  } finally {
    isLoading.value = false;
    eventToDelete.value = null;
  }
}
</script>

<style scoped>
/* Conteneurs */
.filters-container {
    @apply mb-8 space-y-4;
}

.search-container {
    @apply relative;
}

.search-input {
    @apply flex items-center bg-gray-50 rounded-lg px-4 py-2 border border-gray-200;
    @apply focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500;
}

.search-input i {
    @apply text-gray-500 mr-2;
}

.search-input input {
    @apply bg-transparent border-none outline-none w-full;
}

.clear-search {
    @apply text-gray-400 hover:text-gray-600 ml-2;
}

.filter-group {
    @apply flex flex-wrap items-end gap-4;
}

.filter-item {
    @apply flex-1 min-w-[150px];
}

.filter-item label {
    @apply block text-sm font-medium text-gray-700 mb-1;
}

.filter-item select {
    @apply w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500;
}

.reset-btn {
    @apply px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200;
    @apply disabled:opacity-50 disabled:cursor-not-allowed;
}

/* En-tête des résultats */
.results-header {
    @apply flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4;
}

.results-count {
    @apply text-sm text-gray-600;
}

.sort-options {
    @apply flex items-center gap-2;
}

.sort-options label {
    @apply text-sm text-gray-600;
}

.sort-options select {
    @apply text-sm border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500;
}

/* Grille d'événements */
.events-grid {
    @apply grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4;
}

/* Carte d'événement */
.event-card {
    @apply bg-white rounded-lg shadow-sm overflow-hidden transition-all hover:shadow-md;
}

.video-thumbnail {
    @apply relative aspect-video bg-gray-200 overflow-hidden;
}

.thumbnail-image {
    @apply w-full h-full object-cover;
}

.thumbnail-overlay {
    @apply absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity;
}

.play-button {
    @apply w-12 h-12 rounded-full bg-white bg-opacity-80 text-blue-600 flex items-center justify-center;
    @apply hover:bg-opacity-100 hover:text-blue-700 transition-all;
}

.video-duration {
    @apply absolute bottom-2 right-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded;
}

.event-details {
    @apply p-4 space-y-3;
}

.event-meta {
    @apply flex justify-between items-center text-xs text-gray-500;
}

.event-category {
    @apply px-2 py-1 rounded-full text-xs font-medium;
}

.event-title {
    @apply font-bold text-lg line-clamp-2;
}

.event-excerpt {
    @apply text-gray-600 text-sm line-clamp-3;
}

.event-actions {
    @apply flex justify-between items-center pt-2 border-t border-gray-100;
}

.status-selector {
    @apply flex-1;
}

.status-dropdown {
    @apply text-xs py-1 px-2 rounded border;
}

.status-draft {
    @apply border-yellow-300 bg-yellow-50 text-yellow-800;
}

.status-published {
    @apply border-green-300 bg-green-50 text-green-800;
}

.status-archived {
    @apply border-gray-300 bg-gray-50 text-gray-800;
}

.action-buttons {
    @apply flex gap-2;
}

.action-buttons button {
    @apply w-8 h-8 rounded-full flex items-center justify-center transition-colors;
}

.btn-view {
    @apply bg-blue-100 text-blue-600 hover:bg-blue-200;
}

.btn-edit {
    @apply bg-yellow-100 text-yellow-600 hover:bg-yellow-200;
}

.btn-delete {
    @apply bg-red-100 text-red-600 hover:bg-red-200;
}

/* État vide */
.empty-state {
    @apply py-12 text-center;
}

.empty-icon {
    @apply text-5xl text-gray-300 mb-4;
}

.empty-state h3 {
    @apply text-xl font-medium text-gray-700 mb-2;
}

.empty-state p {
    @apply text-gray-500 max-w-md mx-auto;
}
</style>