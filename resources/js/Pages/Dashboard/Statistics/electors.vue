<template>
  <AppLayout>
    <PageTitle title="Gestion des Inscriptions Électorales" :breadcrumbs="breadcrumbs">
      <template #actions>
        <Link 
          :href="route('admin.vote.inscriptions.index')"
          class="flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-gray-900 rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all"
        >
          <i class="fas fa-user-plus"></i>
          <span>Nouvelle inscription</span>
        </Link>
      </template>
    </PageTitle>

    <div class="bg-gray-800 rounded-lg border border-gray-700 shadow-sm">
      <!-- Barre de filtres améliorée -->
      <div class="p-4 border-b border-gray-700 bg-gray-900">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-2">
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Rechercher (nom, prénom, N° électeur...)"
                class="w-full px-4 py-2 pl-10 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-yellow-500 focus:border-yellow-500"
                @keyup.enter="handleSearch"
              >
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                <i class="fas fa-search"></i>
              </div>
            </div>
          </div>
          <select
            v-model="regionFilter"
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500"
            @change="handleFilterChange"
          >
            <option value="">Toutes régions</option>
            <option v-for="region in regions" :key="region.code" :value="region.code">
              {{ region.nom }}
            </option>
          </select>
          <select
            v-model="bureauFilter"
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500"
            @change="handleFilterChange"
          >
            <option value="">Tous bureaux</option>
            <option v-for="bureau in filteredBureaux" :key="bureau.code" :value="bureau.code">
              {{ bureau.nom }}
            </option>
          </select>
        </div>
      </div>

      <!-- Tableau des inscriptions -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
          <thead class="bg-gray-800">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-400 uppercase tracking-wider">
                <SortableHeader 
                  label="Électeur" 
                  sort-key="nom"
                  :current-sort="sortField"
                  :current-direction="sortDirection"
                  @sort="handleSort"
                />
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-400 uppercase tracking-wider">
                N° Électeur
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-400 uppercase tracking-wider">
                Localisation
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-400 uppercase tracking-wider">
                <SortableHeader 
                  label="Date Inscription" 
                  sort-key="created_at"
                  :current-sort="sortField"
                  :current-direction="sortDirection"
                  @sort="handleSort"
                />
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-yellow-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-gray-800 divide-y divide-gray-700">
            <!-- Indicateur de chargement -->
            <tr v-if="loading">
              <td colspan="5" class="px-6 py-4 text-center">
                <div class="flex justify-center items-center text-gray-400">
                  <i class="fas fa-spinner fa-spin mr-2"></i>
                  Chargement des données...
                </div>
              </td>
            </tr>

            <!-- Message si aucune donnée -->
            <tr v-else-if="filteredInscriptions.length === 0">
              <td colspan="5" class="px-6 py-4 text-center text-gray-400">
                <div class="flex flex-col items-center justify-center py-4">
                  <i class="fas fa-user-slash text-3xl text-gray-500 mb-2"></i>
                  <p>Aucune inscription trouvée</p>
                  <p class="text-xs mt-1">Essayez de modifier vos critères de recherche</p>
                </div>
              </td>
            </tr>

            <!-- Lignes de données -->
            <tr 
              v-for="inscription in filteredInscriptions" 
              :key="inscription.id" 
              class="hover:bg-gray-700 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 bg-gray-700 rounded-full flex items-center justify-center text-yellow-400">
                    <i class="fas fa-user"></i>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-white">
                      {{ inscription.nom || 'N/A' }} {{ inscription.prenom || '' }}
                    </div>
                    <div class="text-xs text-gray-400">
                      {{ inscription.age ? `${inscription.age} ans` : '' }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                <span class="font-mono bg-gray-900 px-2 py-1 rounded">
                  {{ inscription.numero_elecam || 'N/A' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-white">
                  {{ inscription.commune?.nom || 'N/A' }}
                </div>
                <div class="text-xs text-gray-400 flex items-center">
                  <i class="fas fa-map-marker-alt text-xs mr-1"></i>
                  {{ inscription.bureau_vote?.nom || 'Non affecté' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                <div class="flex items-center">
                  <i class="fas fa-calendar-day text-gray-500 mr-2"></i>
                  {{ formatDate(inscription.created_at) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                  <Link 
                    :href="route('admin.vote.inscriptions.show', inscription.id)" 
                    class="text-yellow-400 hover:text-yellow-300 p-2 rounded-full hover:bg-gray-600 transition-colors"
                    title="Voir détails"
                  >
                    <i class="fas fa-eye"></i>
                  </Link>
                  <Link 
                    :href="route('admin.vote.inscriptions.edit', inscription.id)" 
                    class="text-blue-400 hover:text-blue-300 p-2 rounded-full hover:bg-gray-600 transition-colors"
                    title="Modifier"
                  >
                    <i class="fas fa-edit"></i>
                  </Link>
                  <button 
                    @click="confirmDelete(inscription.id)"
                    class="text-red-400 hover:text-red-300 p-2 rounded-full hover:bg-gray-600 transition-colors"
                    title="Supprimer"
                  >
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination et statistiques -->
      <div 
        class="px-4 py-3 bg-gray-900 border-t border-gray-700 flex flex-col md:flex-row items-center justify-between gap-3"
      >
        <div class="text-sm text-gray-400">
          Affichage de <span class="font-medium text-white">{{ filteredInscriptions.length }}</span> inscriptions
          <span v-if="totalInscriptions" class="ml-1">sur {{ totalInscriptions }}</span>
        </div>
        
        <Pagination 
          v-if="paginationLinks.length > 3"
          :links="paginationLinks"
          :current-page="currentPage"
          @navigate="handlePagination"
        />
        
        <button 
          v-if="hasFilters"
          @click="resetFilters"
          class="text-xs text-gray-400 hover:text-yellow-400 flex items-center"
        >
          <i class="fas fa-times mr-1"></i>
          Réinitialiser les filtres
        </button>
      </div>
    </div>

    <!-- Confirmation de suppression -->
    <ConfirmationModal 
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="deleteInscription"
    >
      <template #title>
        <div class="flex items-center text-red-500">
          <i class="fas fa-exclamation-triangle mr-2"></i>
          Confirmer la suppression
        </div>
      </template>
      <template #content>
        <p class="text-gray-300 mb-3">Êtes-vous sûr de vouloir supprimer définitivement cette inscription ?</p>
        <p class="text-xs text-gray-500">Cette action est irréversible et supprimera toutes les données associées à cet électeur.</p>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import SortableHeader from '@/Components/SortableHeader.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
  inscriptions: {
    type: Array,
    default: () => []
  },
  regions: {
    type: Array,
    default: () => []
  },
  bureaux: {
    type: Array,
    default: () => []
  },
  paginationLinks: {
    type: Array,
    default: () => []
  },
  totalInscriptions: {
    type: Number,
    default: 0
  },
  loading: {
    type: Boolean,
    default: false
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Inscriptions électorales', active: true },
];

// États réactifs
const searchQuery = ref(props.filters.search || '');
const regionFilter = ref(props.filters.region || '');
const bureauFilter = ref(props.filters.bureau || '');
const currentPage = ref(props.filters.page || 1);
const sortField = ref(props.filters.sort || 'created_at');
const sortDirection = ref(props.filters.direction || 'desc');
const showDeleteModal = ref(false);
const inscriptionToDelete = ref(null);

// Computed
const filteredBureaux = computed(() => {
  if (!regionFilter.value) return props.bureaux;
  return props.bureaux.filter(b => b.region_code === regionFilter.value);
});

const hasFilters = computed(() => {
  return searchQuery.value || regionFilter.value || bureauFilter.value;
});

const filteredInscriptions = computed(() => {
  let results = [...props.inscriptions];

  // Filtrage par recherche
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    results = results.filter(i => {
      const nom = i.nom?.toLowerCase() || '';
      const prenom = i.prenom?.toLowerCase() || '';
      const numero = i.numero_elecam || '';
      return nom.includes(query) || prenom.includes(query) || numero.includes(query);
    });
  }

  // Filtrage par région
  if (regionFilter.value) {
    results = results.filter(i => 
      i.commune?.region_code === regionFilter.value
    );
  }

  // Filtrage par bureau
  if (bureauFilter.value) {
    results = results.filter(i => 
      i.bureau_vote?.code === bureauFilter.value
    );
  }

  // Tri
  try {
    results.sort((a, b) => {
      const fieldA = a[sortField.value]?.toString().toLowerCase() || '';
      const fieldB = b[sortField.value]?.toString().toLowerCase() || '';

      if (fieldA < fieldB) return sortDirection.value === 'asc' ? -1 : 1;
      if (fieldA > fieldB) return sortDirection.value === 'asc' ? 1 : -1;
      return 0;
    });
  } catch (error) {
    console.error("Erreur lors du tri:", error);
  }

  return results;
});

// Méthodes
const formatDate = (dateString) => {
  try {
    return dateString ? new Date(dateString).toLocaleDateString('fr-FR') : 'N/A';
  } catch {
    return 'N/A';
  }
};

const handleSort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
  updateResults();
};

const confirmDelete = (id) => {
  inscriptionToDelete.value = id;
  showDeleteModal.value = true;
};

const deleteInscription = () => {
  if (!inscriptionToDelete.value) return;
  
  router.delete(route('admin.vote.inscriptions.destroy', inscriptionToDelete.value), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      inscriptionToDelete.value = null;
    },
    onError: () => {
      showDeleteModal.value = false;
    }
  });
};

const handlePagination = (page) => {
  currentPage.value = page;
  updateResults();
};

const handleFilterChange = () => {
  currentPage.value = 1;
  updateResults();
};

const handleSearch = () => {
  currentPage.value = 1;
  updateResults();
};

const resetFilters = () => {
  searchQuery.value = '';
  regionFilter.value = '';
  bureauFilter.value = '';
  currentPage.value = 1;
  updateResults();
};

const updateResults = () => {
  router.get(route('admin.vote.inscriptions.index', { 
    page: currentPage.value,
    search: searchQuery.value,
    region: regionFilter.value,
    bureau: bureauFilter.value,
    sort: sortField.value,
    direction: sortDirection.value
  }), {}, {
    preserveScroll: true,
    preserveState: true,
    replace: true
  });
};

// Watchers
watch(regionFilter, (newVal) => {
  if (!newVal) bureauFilter.value = '';
});
</script>

<style scoped>
/* Styles personnalisés */
table {
  min-width: 1000px;
}

tbody tr {
  transition: background-color 0.2s ease;
}

/* Animation de survol pour les boutons d'action */
.action-button {
  @apply p-2 rounded-full transition-colors hover:bg-gray-600;
}

/* Scrollbar personnalisée */
::-webkit-scrollbar {
  height: 6px;
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #374151;
}

::-webkit-scrollbar-thumb {
  background: #6B7280;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #9CA3AF;
}

/* Animation pour les transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>