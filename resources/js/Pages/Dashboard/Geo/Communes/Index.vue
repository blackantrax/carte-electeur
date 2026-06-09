<template>
  <AppLayout :title="title">
    <PageTitle :title="title" :breadcrumbs="breadcrumbs">
      <template #actions>
        <button @click="openFormModal(null)" class="btn btn-primary">
          <i class="bi bi-plus-lg mr-2"></i> Nouvelle Commune
        </button>
      </template>
    </PageTitle>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="relative md:col-span-2">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="bi bi-search text-gray-400"></i>
          </div>
          <input
            v-model="filters.search"
            @input="debouncedSearch"
            placeholder="Nom, code ou maire..."
            class="pl-10 w-full rounded-md border-gray-300"
          />
        </div>

        <select v-model="filters.departement_code" @change="submitFilters" class="rounded-md border-gray-300">
          <option value="">Tous les départements</option>
          <option 
            v-for="d in allDepartements" 
            :key="d.code" 
            :value="d.code"
          >
            {{ d.nom }} ({{ d.region_nom }})
          </option>
        </select>

        <select v-model="filters.type" @change="submitFilters" class="rounded-md border-gray-300">
          <option value="">Tous types</option>
          <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
        </select>

        <select v-model="sortBy" @change="applySort" class="rounded-md border-gray-300">
          <option value="nom-asc">Nom (A-Z)</option>
          <option value="nom-desc">Nom (Z-A)</option>
          <option value="type-asc">Type (A-Z)</option>
          <option value="type-desc">Type (Z-A)</option>
        </select>

        <button @click="resetFilters" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-counterclockwise mr-2"></i> Réinitialiser
        </button>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="communes.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
        <div 
          v-for="commune in communes.data" 
          :key="commune.code" 
          class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
          @click="openFormModal(commune)"
        >
          <div class="flex justify-between items-start">
            <div>
              <h3 class="font-bold text-lg">{{ commune.nom }}</h3>
              <div class="text-gray-600 text-sm mt-1">
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-mono">{{ commune.code }}</span>
                <span class="ml-2 badge" :class="commune.type === 'Urbaine' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'">
                  {{ commune.type }}
                </span>
              </div>
              
              <div class="mt-2 text-sm text-gray-600">
                <div class="flex items-center">
                  <i class="bi bi-geo-alt mr-1.5 text-gray-400"></i>
                  <span>
                        {{ commune.departement?.nom || 'Département inconnu' }}
                        <span v-if="commune.departement?.region">
                        ({{ commune.departement.region.nom }})
                        </span>
                    </span>
                </div>
                <div v-if="commune.maire" class="flex items-center mt-1">
                  <i class="bi bi-person-fill mr-1.5 text-gray-400"></i>
                  <span>{{ commune.maire }}</span>
                </div>
                <div v-if="commune.population" class="flex items-center mt-1">
                  <i class="bi bi-people-fill mr-1.5 text-gray-400"></i>
                  <span>{{ commune.population.toLocaleString() }} hab.</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="mt-4 flex justify-end gap-2">
            <button 
              @click.stop="openFormModal(commune)" 
              class="btn btn-sm btn-outline-primary" 
              title="Modifier"
            >
              <i class="bi bi-pencil-square"></i>
            </button>
            <button 
              @click.stop="confirmDelete(commune)" 
              class="btn btn-sm btn-outline-danger" 
              title="Supprimer"
            >
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>

      <div v-else class="p-8 text-center text-gray-500">
        <i class="bi bi-buildings text-4xl mb-4"></i>
        <p>Aucune commune trouvée pour les filtres actuels.</p>
        <button 
          @click="resetFilters" 
          class="mt-4 btn btn-outline-primary inline-flex items-center"
        >
          <i class="bi bi-arrow-counterclockwise mr-2"></i>
          Réinitialiser les filtres
        </button>
      </div>

      <Pagination v-if="communes.links.length > 3" :links="communes.links" class="p-4 border-t" />
    </div>

    <!-- Modale de création/édition -->
    <Modal :show="showFormModal" @close="closeFormModal" maxWidth="lg">
      <div class="p-6">
        <h2 class="text-xl font-bold mb-4">
          {{ formCommune.code ? 'Modifier la commune' : 'Ajouter une commune' }}
        </h2>

        <form @submit.prevent="submitForm">
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1">Code *</label>
                <input
                  v-model="formCommune.code"
                  type="text"
                  maxlength="8"
                  class="w-full rounded-md border-gray-300"
                  :class="{ 'bg-gray-100': formCommune.code }"
                  :disabled="formCommune.code"
                  required
                />
                <div v-if="formCommune.errors.code" class="text-sm text-red-600 mt-1">{{ formCommune.errors.code }}</div>
                <p class="text-xs text-gray-500 mt-1">Format: DEP-NNN (ex: LT-01-01)</p>
              </div>

              <div>
                <label class="block mb-1">Nom *</label>
                <input
                  v-model="formCommune.nom"
                  type="text"
                  class="w-full rounded-md border-gray-300"
                  required
                />
                <div v-if="formCommune.errors.nom" class="text-sm text-red-600 mt-1">{{ formCommune.errors.nom }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1">Type *</label>
                <select
                  v-model="formCommune.type"
                  class="w-full rounded-md border-gray-300"
                  required
                >
                  <option value="">Sélectionner...</option>
                  <option value="Urbaine">Urbaine</option>
                  <option value="Rurale">Rurale</option>
                </select>
                <div v-if="formCommune.errors.type" class="text-sm text-red-600 mt-1">{{ formCommune.errors.type }}</div>
              </div>

              <div>
                <label class="block mb-1">Département *</label>
                <select
                  v-model="formCommune.departement_code"
                  class="w-full rounded-md border-gray-300"
                  required
                >
                  <option value="">Sélectionner...</option>
                  <option 
                    v-for="d in allDepartements" 
                    :key="`form-${d.code}`" 
                    :value="d.code"
                  >
                    {{ d.nom }} ({{ d.region_nom }})
                  </option>
                </select>
                <div v-if="formCommune.errors.departement_code" class="text-sm text-red-600 mt-1">{{ formCommune.errors.departement_code }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1">Maire</label>
                <input
                  v-model="formCommune.maire"
                  type="text"
                  class="w-full rounded-md border-gray-300"
                />
                <div v-if="formCommune.errors.maire" class="text-sm text-red-600 mt-1">{{ formCommune.errors.maire }}</div>
              </div>

              <div>
                <label class="block mb-1">Population</label>
                <input
                  v-model="formCommune.population"
                  type="number"
                  min="0"
                  class="w-full rounded-md border-gray-300"
                />
                <div v-if="formCommune.errors.population" class="text-sm text-red-600 mt-1">{{ formCommune.errors.population }}</div>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button
              type="button"
              @click="closeFormModal"
              class="btn btn-outline-secondary"
            >
              Annuler
            </button>
            <button
              type="submit"
              class="btn btn-primary"
              :disabled="formCommune.processing"
            >
              <span v-if="formCommune.processing">
                <i class="bi bi-arrow-repeat animate-spin mr-2"></i>
                Enregistrement...
              </span>
              <span v-else>
                <i class="bi bi-check-lg mr-2"></i>
                Enregistrer
              </span>
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <ConfirmationModal
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="deleteCommune"
      :is-processing="deleteForm.processing"
    >
      <template #title>
        <div class="flex items-center justify-center flex-col">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-2">
            <i class="bi bi-exclamation-triangle text-red-600 text-xl"></i>
          </div>
          <h3 class="text-lg font-medium text-center">Confirmer la suppression</h3>
        </div>
      </template>

      <template #content>
        <p class="text-gray-600 text-center">
          Êtes-vous sûr de vouloir supprimer<br />
          <strong>"{{ selectedCommune?.nom }}"</strong> ({{ selectedCommune?.code }}) ?
        </p>
        <p class="text-red-500 text-sm mt-2 text-center">
          <i class="bi bi-exclamation-circle mr-1"></i>
          Cette action est irréversible
        </p>
      </template>

      <template #footer>
        <div class="flex justify-center gap-3">
          <button
            @click="showDeleteModal = false"
            class="btn btn-outline-secondary"
            :disabled="deleteForm.processing"
          >
            Annuler
          </button>
          <button
            @click="deleteCommune"
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
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  communes: {
    type: Object,
    required: true,
    default: () => ({ data: [], links: [] })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  allDepartements: {
    type: Array,
    required: true,
    default: () => []
  },
  types: {
    type: Array,
    required: true,
    default: () => ['Urbaine', 'Rurale']
  },
  title: {
    type: String,
    default: 'Gestion des Communes'
  }
});

// États
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const selectedCommune = ref(null);

// Filtres et tri
const filters = useForm({
  search: props.filters.search || '',
  departement_code: props.filters.departement_code || '',
  type: props.filters.type || '',
  sort_field: props.filters.sort_field || 'nom',
  sort_direction: props.filters.sort_direction || 'asc'
});

const sortBy = ref(`${filters.sort_field}-${filters.sort_direction}`);

// Formulaires
const formCommune = useForm({
  code: '',
  nom: '',
  type: '',
  departement_code: '',
  maire: '',
  population: ''
});

const deleteForm = useForm({});

// Breadcrumbs
const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Géographie', url: route('admin.geo.index') },
  { label: 'Communes', active: true }
];

// Synchroniser les filtres avec les props
watch(() => props.filters, (newFilters) => {
  filters.defaults({
    search: newFilters.search || '',
    departement_code: newFilters.departement_code || '',
    type: newFilters.type || '',
    sort_field: newFilters.sort_field || 'nom',
    sort_direction: newFilters.sort_direction || 'asc'
  }).reset();
  sortBy.value = `${filters.sort_field}-${filters.sort_direction}`;
}, { deep: true });

// Méthodes pour le formulaire
const openFormModal = (commune) => {
  if (commune) {
    formCommune.code = commune.code;
    formCommune.nom = commune.nom;
    formCommune.type = commune.type;
    formCommune.departement_code = commune.departement_code;
    formCommune.maire = commune.maire || '';
    formCommune.population = commune.population || '';
  } else {
    formCommune.reset();
  }
  showFormModal.value = true;
};

const closeFormModal = () => {
  showFormModal.value = false;
  formCommune.clearErrors();
  formCommune.reset();
};

const submitForm = () => {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeFormModal(),
  };

  if (formCommune.code) {
    formCommune.put(route('admin.geo.communes.update', formCommune.code), options);
  } else {
    formCommune.post(route('admin.geo.communes.store'), options);
  }
};

// Méthodes pour la suppression
const confirmDelete = (commune) => {
  selectedCommune.value = commune;
  showDeleteModal.value = true;
};

const deleteCommune = () => {
  deleteForm.delete(route('admin.geo.communes.destroy', selectedCommune.value.code), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      selectedCommune.value = null;
    }
  });
};

// Méthodes pour les filtres
const debouncedSearch = debounce(() => {
  submitFilters();
}, 300);

const submitFilters = () => {
  filters.get(route('admin.geo.communes.index'), {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  filters.defaults({
    search: '',
    departement_code: '',
    type: '',
    sort_field: 'nom',
    sort_direction: 'asc'
  }).reset();
  sortBy.value = 'nom-asc';
  submitFilters();
};

const applySort = () => {
  const [field, direction] = sortBy.value.split('-');
  filters.sort_field = field;
  filters.sort_direction = direction;
  submitFilters();
};
</script>

<style scoped>
.badge {
  @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
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