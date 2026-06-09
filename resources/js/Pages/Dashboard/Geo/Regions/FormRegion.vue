<template>
  <AppLayout :title="title">
    <!-- Header avec bouton d'action -->
    <PageTitle :title="title" :breadcrumbs="breadcrumbs">
      <template #actions>
        <button 
          @click="openCreateModal" 
          class="btn btn-primary group"
        >
          <i class="bi bi-plus-lg mr-2 group-hover:scale-110 transition-transform"></i>
          Nouvelle Région
        </button>
      </template>
    </PageTitle>

    <!-- Section Filtres -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <!-- Champ de recherche -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
          <div class="relative mt-1 rounded-lg shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="bi bi-search text-gray-400"></i>
            </div>
            <input
              v-model="filters.search"
              @input="debouncedSearch"
              placeholder="Nom, code ou chef-lieu..."
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
            />
          </div>
        </div>

        <!-- Sélecteur de pays -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
          <select 
            v-model="filters.pays" 
            @change="applyFilters"
            class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
          >
            <option value="">Tous les pays</option>
            <option 
              v-for="p in allPays" 
              :key="p.code" 
              :value="p.code"
              class="py-1"
            >
              {{ p.nom }}
            </option>
          </select>
        </div>

        <!-- Tri -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Trier par</label>
          <select 
            v-model="sortBy"
            @change="applySort"
            class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
          >
            <option value="nom-asc">Nom (A-Z)</option>
            <option value="nom-desc">Nom (Z-A)</option>
            <option value="code-asc">Code (A-Z)</option>
            <option value="code-desc">Code (Z-A)</option>
          </select>
        </div>

        <!-- Bouton réinitialiser -->
        <button 
          @click="resetFilters"
          class="btn btn-outline-secondary h-[42px] flex items-center justify-center hover:bg-gray-50 transition"
        >
          <i class="bi bi-arrow-counterclockwise mr-2"></i>
          Réinitialiser
        </button>
      </div>
    </div>

    <!-- Liste des régions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <!-- Carte de région -->
      <div v-if="regions.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-5">
        <div
          v-for="region in regions.data"
          :key="region.code"
          class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-all hover:border-blue-200 hover:-translate-y-1 cursor-pointer"
          @click="openEditModal(region)"
        >
          <div class="flex justify-between items-start">
            <div>
              <h3 class="font-bold text-lg text-gray-800 flex items-center">
                {{ region.nom }}
                <span class="ml-2 text-blue-600 text-sm font-normal">{{ region.code }}</span>
              </h3>
              
              <div class="flex items-center mt-2 text-sm text-gray-600">
                <i class="bi bi-geo-alt mr-1.5 text-gray-400"></i>
                <span>{{ region.chef_lieu }}</span>
              </div>
              
              <div class="mt-2 flex items-center">
                <span class="bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ region.pays?.nom || 'Non spécifié' }}
                </span>
              </div>
            </div>
          </div>

          <div class="mt-4 flex justify-end gap-2">
            <button 
              @click.stop="openEditModal(region)" 
              class="btn btn-sm btn-outline-primary hover:bg-blue-50 transition"
              title="Modifier"
            >
              <i class="bi bi-pencil-square"></i>
              <span class="sr-only">Modifier</span>
            </button>
            <button 
              @click.stop="confirmDelete(region)" 
              class="btn btn-sm btn-outline-danger hover:bg-red-50 transition"
              title="Supprimer"
            >
              <i class="bi bi-trash"></i>
              <span class="sr-only">Supprimer</span>
            </button>
          </div>
        </div>
      </div>

      <!-- État vide -->
      <div v-else class="p-8 text-center">
        <div class="mx-auto w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <i class="bi bi-map text-3xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-700 mb-2">Aucune région trouvée</h3>
        <p class="text-gray-500 max-w-md mx-auto">
          Aucun résultat ne correspond à vos critères de recherche.
        </p>
        <button 
          @click="resetFilters" 
          class="mt-4 btn btn-outline-primary inline-flex items-center"
        >
          <i class="bi bi-arrow-counterclockwise mr-2"></i>
          Réinitialiser les filtres
        </button>
      </div>

      <!-- Pagination -->
      <Pagination 
        v-if="regions.links.length > 3" 
        :links="regions.links" 
        class="px-5 py-4 border-t border-gray-100" 
      />
    </div>

    <!-- Modale de création -->
    <Modal 
      :show="showCreateModal" 
      @close="showCreateModal = false"
      maxWidth="2xl"
    >
      <template #header>
        <div class="flex items-center">
          <i class="bi bi-plus-circle text-blue-500 mr-2"></i>
          <span>Créer une nouvelle région</span>
        </div>
      </template>
      
      <template #body>
        <FormRegion 
          :region="null"
          :allPays="allPays"
          @close="showCreateModal = false"
          @saved="onRegionSaved"
        />
      </template>
    </Modal>

    <!-- Modale d'édition -->
    <Modal 
      :show="showEditModal" 
      @close="showEditModal = false"
      maxWidth="2xl"
    >
      <template #header>
        <div class="flex items-center">
          <i class="bi bi-pencil-square text-blue-500 mr-2"></i>
          <span>Modifier la région</span>
        </div>
      </template>
      
      <template #body>
        <FormRegion 
          :region="selectedRegion"
          :allPays="allPays"
          @close="showEditModal = false"
          @saved="onRegionSaved"
        />
      </template>
    </Modal>

    <!-- Modale de confirmation de suppression -->
    <ConfirmationModal
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="deleteRegion"
      :is-processing="deleteForm.processing"
    >
      <template #icon>
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
          <i class="bi bi-exclamation-triangle text-red-600 text-xl"></i>
        </div>
      </template>
      <template #title>
        <h3 class="text-lg font-medium text-center text-gray-900">
          Confirmer la suppression
        </h3>
      </template>
      <template #content>
        <div class="text-center">
          <p class="text-gray-600">
            Vous êtes sur le point de supprimer définitivement :
          </p>
          <p class="font-bold text-lg text-gray-800 mt-1">
            {{ selectedRegion?.nom }} ({{ selectedRegion?.code }})
          </p>
          <p class="text-red-500 text-sm mt-2">
            <i class="bi bi-exclamation-circle mr-1"></i>
            Cette action est irréversible
          </p>
        </div>
      </template>
      <template #footer>
        <div class="flex justify-center gap-3">
          <button
            type="button"
            @click="showDeleteModal = false"
            class="btn btn-outline-secondary"
            :disabled="deleteForm.processing"
          >
            Annuler
          </button>
          <button
            type="button"
            @click="deleteRegion"
            class="btn btn-danger"
            :disabled="deleteForm.processing"
          >
            <span v-if="deleteForm.processing">
              <i class="bi bi-arrow-repeat animate-spin mr-2"></i>
              Suppression...
            </span>
            <span v-else>
              <i class="bi bi-trash mr-2"></i>
              Confirmer
            </span>
          </button>
        </div>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { debounce } from 'lodash';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Modal from '@/Components/Modal.vue';
import FormRegion from './FormRegion.vue';

const props = defineProps({
  regions: {
    type: Object,
    required: true,
    default: () => ({ data: [], links: [] })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  allPays: {
    type: Array,
    required: true,
    default: () => []
  },
  title: {
    type: String,
    default: 'Gestion des Régions'
  }
});

// Breadcrumbs
const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Géographie', url: route('admin.geo.index') },
  { label: 'Régions', active: true }
];

// Filtres avec valeurs par défaut sécurisées
const filters = ref({
  search: props.filters.search || '',
  pays: props.filters.pays || '',
  sort_field: props.filters.sort_field || 'nom',
  sort_direction: props.filters.sort_direction || 'asc'
});

// Tri
const sortBy = ref(`${filters.value.sort_field}-${filters.value.sort_direction}`);
const debouncedSearch = debounce(() => applyFilters(), 300);

// Appliquer les filtres
const applyFilters = () => {
  router.get(route('regions.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  });
};

// Réinitialiser les filtres
const resetFilters = () => {
  filters.value = {
    search: '',
    pays: '',
    sort_field: 'nom',
    sort_direction: 'asc'
  };
  sortBy.value = 'nom-asc';
  applyFilters();
};

// Appliquer le tri
const applySort = () => {
  const [field, direction] = sortBy.value.split('-');
  filters.value.sort_field = field;
  filters.value.sort_direction = direction;
  applyFilters();
};

// Gestion des modales
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedRegion = ref(null);

const openCreateModal = () => {
  selectedRegion.value = null;
  showCreateModal.value = true;
};

const openEditModal = (region) => {
  selectedRegion.value = region;
  showEditModal.value = true;
};

const onRegionSaved = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  selectedRegion.value = null;
  applyFilters();
};

// Gestion de la suppression
const showDeleteModal = ref(false);
const deleteForm = useForm({});

const confirmDelete = (region) => {
  selectedRegion.value = region;
  showDeleteModal.value = true;
};

const deleteRegion = () => {
  if (!selectedRegion.value?.code) return;

  deleteForm.delete(route('regions.destroy', selectedRegion.value.code), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      selectedRegion.value = null;
    },
    onError: () => {
      showDeleteModal.value = false;
    }
  });
};
</script>

<style scoped>
/* Styles spécifiques au composant */
.card-enter-active,
.card-leave-active {
  transition: all 0.3s ease;
}
.card-enter-from,
.card-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.btn {
  @apply inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition;
}

.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white;
}

.btn-outline-primary {
  @apply text-blue-600 border-blue-600 hover:bg-blue-50 focus:ring-blue-500;
}

.btn-danger {
  @apply bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white;
}

.btn-outline-danger {
  @apply text-red-600 border-red-600 hover:bg-red-50 focus:ring-red-500;
}

.btn-outline-secondary {
  @apply text-gray-700 border-gray-300 hover:bg-gray-50 focus:ring-gray-500;
}

.btn-sm {
  @apply px-2.5 py-1.5 text-xs;
}
</style>

<style>
/* Styles globaux pour les modales */
.modal-overlay {
  @apply fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center;
}

.modal-container {
  @apply bg-white rounded-lg shadow-xl max-w-full mx-4;
}

.modal-header {
  @apply px-6 py-4 border-b border-gray-200 flex justify-between items-center;
}

.modal-body {
  @apply px-6 py-4;
}

.modal-footer {
  @apply px-6 py-4 border-t border-gray-200 flex justify-end space-x-3;
}
</style>