<template>
  <AppLayout title="Gestion des Pays">
    <PageTitle title="Gestion des Pays" :breadcrumbs="breadcrumbs">
      <template #actions>
        <button @click="openFormModal(null)" class="btn btn-primary">
          <i class="bi bi-plus-lg mr-2"></i> Nouveau Pays
        </button>
      </template>
    </PageTitle>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="bi bi-search text-gray-400"></i>
          </div>
          <input
            v-model="form.search"
            @input="debouncedSearch"
            placeholder="Rechercher..."
            class="pl-10 w-full rounded-md border-gray-300"
          />
        </div>

        <select v-model="form.continent" @change="submitFilters" class="rounded-md border-gray-300">
          <option value="">Tous continents</option>
          <option v-for="c in continents" :key="c" :value="c">{{ c }}</option>
        </select>

        <select v-model="sortBy" @change="applySort" class="rounded-md border-gray-300">
          <option value="nom-asc">Nom (A-Z)</option>
          <option value="nom-desc">Nom (Z-A)</option>
          <option value="code-asc">Code (A-Z)</option>
          <option value="code-desc">Code (Z-A)</option>
        </select>

        <button @click="resetFilters" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-counterclockwise mr-2"></i> Réinitialiser
        </button>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="pays.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
        <div v-for="p in pays.data" :key="p.id" class="border rounded-lg p-4 hover:shadow-md transition-shadow">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="font-bold text-lg">{{ p.nom }}</h3>
              <div class="text-gray-600 text-sm mt-1">
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-mono">{{ p.code }}</span>
                <span class="ml-2 font-mono">+{{ p.indicatif_telephonique }}</span>
              </div>
              <span class="badge" :class="getContinentClasses(p.continent)">
                {{ p.continent }}
              </span>
            </div>
            <span class="text-2xl" :title="p.nom">{{ getFlagEmoji(p.code) }}</span>
          </div>
          
          <div class="mt-4 flex justify-end gap-2">
            <button @click="openFormModal(p)" class="btn btn-sm btn-outline-primary" title="Modifier">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button @click="confirmDelete(p)" class="btn btn-sm btn-outline-danger" title="Supprimer">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>

      <div v-else class="p-8 text-center text-gray-500">
        <i class="bi bi-globe-americas text-4xl mb-4"></i>
        <p>Aucun pays trouvé pour les filtres actuels.</p>
      </div>

      <Pagination v-if="pays.data.length" :links="pays.links" class="p-4 border-t" />
    </div>

    <Modal :show="showFormModal" @close="closeFormModal" maxWidth="lg">
      <div class="p-6">
        <h2 class="text-xl font-bold mb-4">
          {{ formPays.id ? 'Modifier le pays' : 'Ajouter un pays' }}
        </h2>

        <form @submit.prevent="submitForm">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block mb-1">Code ISO *</label>
              <input
                v-model="formPays.code"
                type="text"
                maxlength="2"
                class="w-full rounded-md border-gray-300 uppercase"
                required
              />
               <div v-if="formPays.errors.code" class="text-sm text-red-600 mt-1">{{ formPays.errors.code }}</div>
            </div>

            <div>
              <label class="block mb-1">Nom *</label>
              <input
                v-model="formPays.nom"
                type="text"
                class="w-full rounded-md border-gray-300"
                required
              />
              <div v-if="formPays.errors.nom" class="text-sm text-red-600 mt-1">{{ formPays.errors.nom }}</div>
            </div>

            <div>
              <label class="block mb-1">Continent *</label>
              <select
                v-model="formPays.continent"
                class="w-full rounded-md border-gray-300"
                required
              >
                <option disabled value="">Sélectionner...</option>
                <option v-for="c in continents" :key="`form-${c}`" :value="c">{{ c }}</option>
              </select>
              <div v-if="formPays.errors.continent" class="text-sm text-red-600 mt-1">{{ formPays.errors.continent }}</div>
            </div>

            <div>
              <label class="block mb-1">Indicatif Tél. *</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                  <span class="text-gray-500">+</span>
                </div>
                <input
                  v-model="formPays.indicatif_telephonique"
                  type="tel"
                  class="pl-8 w-full rounded-md border-gray-300"
                  required
                />
              </div>
              <div v-if="formPays.errors.indicatif_telephonique" class="text-sm text-red-600 mt-1">{{ formPays.errors.indicatif_telephonique }}</div>
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
              :disabled="formPays.processing"
            >
              <span v-if="formPays.processing">
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
  @confirm="deletePays"
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
      <strong>"{{ selectedPays?.nom }}"</strong> ?
    </p>
  </template>

  <template #footer>
    <button
      @click="showDeleteModal = false"
      class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded"
    >
      Annuler
    </button>
    <button
      @click="deletePays"
      class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded"
      :disabled="deleteForm.processing"
    >
      Supprimer
    </button>
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
  pays: Object,
  filters: Object,
  continents: Array
});

// États
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const selectedPays = ref(null);

// Formulaires
const form = useForm({
  search: props.filters.search || '',
  continent: props.filters.continent || '',
  sort_field: props.filters.sort_field || 'nom',
  sort_direction: props.filters.sort_direction || 'asc'
});

const formPays = useForm({
  id: null,
  code: '',
  nom: '',
  continent: '',
  indicatif_telephonique: ''
});

const deleteForm = useForm({});

const sortBy = ref(`${form.sort_field}-${form.sort_direction}`);

// Synchroniser les filtres avec les props (pour la navigation de l'historique)
watch(() => props.filters, (newFilters) => {
    form.defaults({
        search: newFilters.search || '',
        continent: newFilters.continent || '',
        sort_field: newFilters.sort_field || 'nom',
        sort_direction: newFilters.sort_direction || 'asc',
    }).reset();
    sortBy.value = `${form.sort_field}-${form.sort_direction}`;
}, { deep: true });

const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Géographie', url: route('admin.geo.index') },
  { label: 'Pays', active: true }
];

// Méthodes pour le formulaire de création/modification
const openFormModal = (pays) => {
  if (pays && pays.code !== null && pays.code !== undefined) {
    // Modification ici : assigner directement les valeurs plutôt que d'utiliser defaults()
    formPays.id = pays.code;
    formPays.code = pays.code;
    formPays.nom = pays.nom;
    formPays.continent = pays.continent;
    formPays.indicatif_telephonique = pays.indicatif_telephonique;
  } else {
    // Réinitialiser pour la création
    formPays.reset();
    formPays.code = null; // S'assurer que l'ID est bien null pour une création
  }
  showFormModal.value = true;
};

const closeFormModal = () => {
  showFormModal.value = false;
  formPays.clearErrors();
  formPays.reset();
};

const submitForm = () => {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeFormModal(),
  };

  if (formPays.id) {
    formPays.put(route('admin.geo.pays.update', formPays.id), options);
  } else {
    formPays.post(route('admin.geo.pays.store'), options);
  }
};

// Méthodes pour la suppression
const confirmDelete = (pays) => {
  selectedPays.value = pays;
  showDeleteModal.value = true;
};

const deletePays = () => {
  deleteForm.delete(route('admin.geo.pays.destroy', selectedPays.value.code), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      selectedPays.value = null;
    }
  });
};

// Méthodes pour les filtres
const debouncedSearch = debounce(() => {
  submitFilters();
}, 300);

const submitFilters = () => {
  form.get(route('admin.geo.pays.index'), {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
    form.defaults({
        search: '',
        continent: '',
        sort_field: 'nom',
        sort_direction: 'asc',
    }).reset();
    sortBy.value = 'nom-asc';
    submitFilters();
};

const applySort = () => {
  const [field, direction] = sortBy.value.split('-');
  form.sort_field = field;
  form.sort_direction = direction;
  submitFilters();
};

// Utilitaires
const getContinentClasses = (continent) => {
  const colors = {
    'Afrique': 'bg-green-100 text-green-800',
    'Europe': 'bg-blue-100 text-blue-800',
    'Asie': 'bg-yellow-100 text-yellow-800',
    'Amérique': 'bg-red-100 text-red-800',
    'Océanie': 'bg-purple-100 text-purple-800'
  };
  return colors[continent] || 'bg-gray-100 text-gray-800';
};

const getFlagEmoji = (countryCode) => {
  if (!countryCode || countryCode.length !== 2) return '🌐';
  // Formule pour convertir un code pays (ex: 'FR') en emoji drapeau (ex: '🇫🇷')
  return String.fromCodePoint(...[...countryCode.toUpperCase()].map(c => 127397 + c.charCodeAt(0)));
};
</script>

<style scoped>
.badge {
  @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2;
}
</style>