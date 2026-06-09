<template>
  <AppLayout :title="title">
    <PageTitle :title="title" :breadcrumbs="breadcrumbs">
      <template #actions>
        <button @click="openFormModal(null)" class="btn btn-primary">
          <i class="bi bi-plus-lg mr-2"></i> Nouveau Bureau
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
            placeholder="Nom, code ou adresse..."
            class="pl-10 w-full rounded-md border-gray-300"
          />
        </div>

        <select v-model="filters.commune_code" @change="submitFilters" class="rounded-md border-gray-300">
          <option value="">Toutes les communes</option>
          <option 
            v-for="commune in allCommunes" 
            :key="commune.code" 
            :value="commune.code"
          >
            {{ commune.nom }} ({{ commune.departement_nom }})
          </option>
        </select>

        <select v-model="filters.arrondissement_code" @change="submitFilters" class="rounded-md border-gray-300">
          <option value="">Tous les arrondissements</option>
          <option 
            v-for="arrondissement in allArrondissements" 
            :key="arrondissement.code" 
            :value="arrondissement.code"
          >
            {{ arrondissement.nom }} ({{ arrondissement.commune_nom }})
          </option>
        </select>

        <select v-model="sortBy" @change="applySort" class="rounded-md border-gray-300">
          <option value="nom-asc">Nom (A-Z)</option>
          <option value="nom-desc">Nom (Z-A)</option>
          <option value="nombre_electeurs-asc">Électeurs (croissant)</option>
          <option value="nombre_electeurs-desc">Électeurs (décroissant)</option>
        </select>

        <button @click="resetFilters" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-counterclockwise mr-2"></i> Réinitialiser
        </button>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="bureaux.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
        <div 
          v-for="bureau in bureaux.data" 
          :key="bureau.code" 
          class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
          @click="openFormModal(bureau)"
        >
          <div class="flex justify-between items-start">
            <div>
              <h3 class="font-bold text-lg">{{ bureau.nom }}</h3>
              <div class="text-gray-600 text-sm mt-1">
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-mono">{{ bureau.code }}</span>
                <span class="ml-2 badge bg-green-100 text-green-800">
                  {{ bureau.nombre_electeurs }} électeurs
                </span>
              </div>
              
              <div class="mt-2 text-sm text-gray-600">
                <div class="flex items-center">
                  <i class="bi bi-geo-alt mr-1.5 text-gray-400"></i>
                  <span>{{ bureau.adresse }}</span>
                </div>
                <div class="flex items-center mt-1">
                  <i class="bi bi-building mr-1.5 text-gray-400"></i>
                  <span>
                    {{ bureau.commune?.nom || 'Commune inconnue' }}
                    <span v-if="bureau.arrondissement?.nom">
                      / {{ bureau.arrondissement.nom }}
                    </span>
                  </span>
                </div>
                <div v-if="bureau.localisation" class="flex items-center mt-1">
                  <i class="bi bi-geo-fill mr-1.5 text-gray-400"></i>
                  <span>{{ bureau.localisation }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="mt-4 flex justify-end gap-2">
            <button 
              @click.stop="openFormModal(bureau)" 
              class="btn btn-sm btn-outline-primary" 
              title="Modifier"
            >
              <i class="bi bi-pencil-square"></i>
            </button>
            <button 
              @click.stop="confirmDelete(bureau)" 
              class="btn btn-sm btn-outline-danger" 
              title="Supprimer"
            >
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>

      <div v-else class="p-8 text-center text-gray-500">
        <i class="bi bi-building text-4xl mb-4"></i>
        <p>Aucun bureau de vote trouvé pour les filtres actuels.</p>
        <button 
          @click="resetFilters" 
          class="mt-4 btn btn-outline-primary inline-flex items-center"
        >
          <i class="bi bi-arrow-counterclockwise mr-2"></i>
          Réinitialiser les filtres
        </button>
      </div>

      <Pagination v-if="bureaux.links.length > 3" :links="bureaux.links" class="p-4 border-t" />
    </div>

    <!-- Modale de création/édition -->
    <Modal :show="showFormModal" @close="closeFormModal" maxWidth="lg">
      <div class="p-6">
        <h2 class="text-xl font-bold mb-4">
          {{ formBureau.code ? 'Modifier le bureau' : 'Ajouter un bureau' }}
        </h2>

        <form @submit.prevent="submitForm">
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1">Code *</label>
                <input
                  v-model="formBureau.code"
                  type="text"
                  maxlength="20"
                  class="w-full rounded-md border-gray-300"
                  :class="{ 'bg-gray-100': formBureau.code }"
                  :disabled="formBureau.code"
                  required
                />
                <div v-if="formBureau.errors.code" class="text-sm text-red-600 mt-1">{{ formBureau.errors.code }}</div>
                <p class="text-xs text-gray-500 mt-1">Format: ARR-NNNN (ex: LT-01-01-01-001)</p>
              </div>

              <div>
                <label class="block mb-1">Nom *</label>
                <input
                  v-model="formBureau.nom"
                  type="text"
                  class="w-full rounded-md border-gray-300"
                  required
                />
                <div v-if="formBureau.errors.nom" class="text-sm text-red-600 mt-1">{{ formBureau.errors.nom }}</div>
              </div>
            </div>

            <div>
              <label class="block mb-1">Adresse *</label>
              <input
                v-model="formBureau.adresse"
                type="text"
                class="w-full rounded-md border-gray-300"
                required
              />
              <div v-if="formBureau.errors.adresse" class="text-sm text-red-600 mt-1">{{ formBureau.errors.adresse }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1">Commune *</label>
                <select
                  v-model="formBureau.commune_code"
                  @change="formBureau.arrondissement_code = null"
                  class="w-full rounded-md border-gray-300"
                  required
                >
                  <option value="">Sélectionner...</option>
                  <option 
                    v-for="commune in allCommunes" 
                    :key="`form-${commune.code}`" 
                    :value="commune.code"
                  >
                    {{ commune.nom }} ({{ commune.departement_nom }})
                  </option>
                </select>
                <div v-if="formBureau.errors.commune_code" class="text-sm text-red-600 mt-1">{{ formBureau.errors.commune_code }}</div>
              </div>

              <div>
                <label class="block mb-1">Arrondissement</label>
                <select
                  v-model="formBureau.arrondissement_code"
                  class="w-full rounded-md border-gray-300"
                  :disabled="!formBureau.commune_code"
                >
                  <option value="">Sélectionner...</option>
                  <option 
                    v-for="arrondissement in filteredArrondissements" 
                    :key="`form-${arrondissement.code}`" 
                    :value="arrondissement.code"
                  >
                    {{ arrondissement.nom }}
                  </option>
                </select>
                <div v-if="formBureau.errors.arrondissement_code" class="text-sm text-red-600 mt-1">{{ formBureau.errors.arrondissement_code }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1">Nombre d'électeurs *</label>
                <input
                  v-model="formBureau.nombre_electeurs"
                  type="number"
                  min="0"
                  class="w-full rounded-md border-gray-300"
                  required
                />
                <div v-if="formBureau.errors.nombre_electeurs" class="text-sm text-red-600 mt-1">{{ formBureau.errors.nombre_electeurs }}</div>
              </div>

              <div>
                <label class="block mb-1">Localisation (GPS)</label>
                <input
                  v-model="formBureau.localisation"
                  type="text"
                  class="w-full rounded-md border-gray-300"
                  placeholder="Ex: 12.34567, -12.34567"
                />
                <div v-if="formBureau.errors.localisation" class="text-sm text-red-600 mt-1">{{ formBureau.errors.localisation }}</div>
              </div>
            </div>

            <div>
              <label class="block mb-1">Description</label>
              <textarea
                v-model="formBureau.description"
                class="w-full rounded-md border-gray-300"
                rows="3"
              ></textarea>
              <div v-if="formBureau.errors.description" class="text-sm text-red-600 mt-1">{{ formBureau.errors.description }}</div>
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
              :disabled="formBureau.processing"
            >
              <span v-if="formBureau.processing">
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
      @confirm="deleteBureau"
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
          <strong>"{{ selectedBureau?.nom }}"</strong> ({{ selectedBureau?.code }}) ?
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
            @click="deleteBureau"
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
import { ref, computed, watch } from 'vue';
import { debounce } from 'lodash';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  bureaux: {
    type: Object,
    required: true,
    default: () => ({ data: [], links: [] })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  allCommunes: {
    type: Array,
    required: true,
    default: () => []
  },
  allArrondissements: {
    type: Array,
    required: true,
    default: () => []
  },
  title: {
    type: String,
    default: 'Gestion des Bureaux de Vote'
  }
});

// États
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const selectedBureau = ref(null);

// Filtres et tri
const filters = useForm({
  search: props.filters.search || '',
  commune_code: props.filters.commune_code || '',
  arrondissement_code: props.filters.arrondissement_code || '',
  sort_field: props.filters.sort_field || 'nom',
  sort_direction: props.filters.sort_direction || 'asc'
});

const sortBy = ref(`${filters.sort_field}-${filters.sort_direction}`);

// Formulaires
const formBureau = useForm({
  code: '',
  nom: '',
  adresse: '',
  commune_code: '',
  arrondissement_code: null,
  nombre_electeurs: 0,
  localisation: '',
  description: ''
});

const deleteForm = useForm({});

// Computed
const filteredArrondissements = computed(() => {
  if (!formBureau.commune_code) return [];
  return props.allArrondissements.filter(a => 
    a.commune_nom === props.allCommunes.find(c => c.code === formBureau.commune_code)?.nom
  );
});

// Breadcrumbs
const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Géographie', url: route('admin.geo.index') },
  { label: 'Bureaux de Vote', active: true }
];

// Synchroniser les filtres avec les props
watch(() => props.filters, (newFilters) => {
  filters.defaults({
    search: newFilters.search || '',
    commune_code: newFilters.commune_code || '',
    arrondissement_code: newFilters.arrondissement_code || '',
    sort_field: newFilters.sort_field || 'nom',
    sort_direction: newFilters.sort_direction || 'asc'
  }).reset();
  sortBy.value = `${filters.sort_field}-${filters.sort_direction}`;
}, { deep: true });

// Méthodes pour le formulaire
const openFormModal = (bureau) => {
  if (bureau) {
    formBureau.code = bureau.code;
    formBureau.nom = bureau.nom;
    formBureau.adresse = bureau.adresse;
    formBureau.commune_code = bureau.commune_code;
    formBureau.arrondissement_code = bureau.arrondissement_code;
    formBureau.nombre_electeurs = bureau.nombre_electeurs;
    formBureau.localisation = bureau.localisation || '';
    formBureau.description = bureau.description || '';
  } else {
    formBureau.reset();
    formBureau.nombre_electeurs = 0;
  }
  showFormModal.value = true;
};

const closeFormModal = () => {
  showFormModal.value = false;
  formBureau.clearErrors();
  formBureau.reset();
};

const submitForm = () => {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeFormModal(),
  };

  if (formBureau.code) {
    formBureau.put(route('admin.geo.bureaux-de-vote.update', formBureau.code), options);
  } else {
    formBureau.post(route('admin.geo.bureaux-de-vote.store'), options);
  }
};

// Méthodes pour la suppression
const confirmDelete = (bureau) => {
  selectedBureau.value = bureau;
  showDeleteModal.value = true;
};

const deleteBureau = () => {
  deleteForm.delete(route('admin.geo.bureaux-de-vote.destroy', selectedBureau.value.code), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      selectedBureau.value = null;
    }
  });
};

// Méthodes pour les filtres
const debouncedSearch = debounce(() => {
  submitFilters();
}, 300);

const submitFilters = () => {
  filters.get(route('admin.geo.bureaux-de-vote.index'), {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  filters.defaults({
    search: '',
    commune_code: '',
    arrondissement_code: '',
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