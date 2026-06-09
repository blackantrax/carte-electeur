<template>
  <AppLayout :title="title">
    <PageTitle :title="title" :breadcrumbs="breadcrumbs">
      <template #actions>
        <button @click="openFormModal(null)" class="btn btn-primary">
          <i class="bi bi-plus-lg mr-2"></i> Nouvelle Ambassade
        </button>
      </template>
    </PageTitle>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="relative md:col-span-2">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="bi bi-search text-gray-400"></i>
          </div>
          <input
            v-model="filters.search"
            @input="debouncedSearch"
            placeholder="Rechercher par nom, ville ou pays..."
            class="pl-10 w-full rounded-md border-gray-300"
          />
        </div>

        <select v-model="filters.type" @change="submitFilters" class="rounded-md border-gray-300">
          <option value="">Tous les types</option>
          <option value="Ambassade">Ambassade</option>
          <option value="Consulat">Consulat</option>
          <option value="Haut-Commissariat">Haut-Commissariat</option>
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
      <div v-if="ambassades.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
        <div 
          v-for="ambassade in ambassades.data" 
          :key="ambassade.code" 
          class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
          @click="openFormModal(ambassade)"
        >
          <div class="flex justify-between items-start">
            <div>
              <h3 class="font-bold text-lg">{{ ambassade.nom }}</h3>
              <div class="text-gray-600 text-sm mt-1">
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-mono">{{ ambassade.code }}</span>
                <span class="ml-2 badge" :class="typeClasses(ambassade.type)">
                  {{ ambassade.type }}
                </span>
                <span class="ml-2 badge bg-green-100 text-green-800">
                  {{ ambassade.nombre_electeurs }} électeurs
                </span>
              </div>
              
              <div class="mt-2 text-sm text-gray-600">
                <div class="flex items-center">
                  <i class="bi bi-geo-alt mr-1.5 text-gray-400"></i>
                  <span>{{ ambassade.ville }}, {{ ambassade.pays?.nom }}</span>
                </div>
                <div class="flex items-center mt-1">
                  <i class="bi bi-pin-map mr-1.5 text-gray-400"></i>
                  <span>{{ ambassade.adresse }}</span>
                </div>
                <div class="flex items-center mt-1">
                  <i class="bi bi-telephone mr-1.5 text-gray-400"></i>
                  <span>{{ ambassade.telephone }}</span>
                </div>
                <div v-if="ambassade.email" class="flex items-center mt-1">
                  <i class="bi bi-envelope mr-1.5 text-gray-400"></i>
                  <span>{{ ambassade.email }}</span>
                </div>
                <div v-if="ambassade.responsable" class="flex items-center mt-1">
                  <i class="bi bi-person mr-1.5 text-gray-400"></i>
                  <span>{{ ambassade.responsable }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="mt-4 flex justify-end gap-2">
            <button 
              @click.stop="openFormModal(ambassade)" 
              class="btn btn-sm btn-outline-primary" 
              title="Modifier"
            >
              <i class="bi bi-pencil-square"></i>
            </button>
            <button 
              @click.stop="confirmDelete(ambassade)" 
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
        <p>Aucune ambassade trouvée pour les filtres actuels.</p>
        <button 
          @click="resetFilters" 
          class="mt-4 btn btn-outline-primary inline-flex items-center"
        >
          <i class="bi bi-arrow-counterclockwise mr-2"></i>
          Réinitialiser les filtres
        </button>
      </div>

      <Pagination v-if="ambassades.links.length > 3" :links="ambassades.links" class="p-4 border-t" />
    </div>

    <!-- Modale de création/édition -->
    <Modal :show="showFormModal" @close="closeFormModal" maxWidth="lg">
      <div class="p-6">
        <h2 class="text-xl font-bold mb-4">
          {{ formAmbassade.code ? 'Modifier l\'ambassade' : 'Ajouter une ambassade' }}
        </h2>

        <form @submit.prevent="submitForm">
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1">Code *</label>
                <input
                  v-model="formAmbassade.code"
                  type="text"
                  maxlength="15"
                  class="w-full rounded-md border-gray-300"
                  :class="{ 'bg-gray-100': formAmbassade.code }"
                  :disabled="formAmbassade.code"
                  required
                />
                <div v-if="formAmbassade.errors.code" class="text-sm text-red-600 mt-1">{{ formAmbassade.errors.code }}</div>
                <p class="text-xs text-gray-500 mt-1">Format: PAY-NN (ex: FR-01)</p>
              </div>

              <div>
                <label class="block mb-1">Nom *</label>
                <input
                  v-model="formAmbassade.nom"
                  type="text"
                  class="w-full rounded-md border-gray-300"
                  required
                />
                <div v-if="formAmbassade.errors.nom" class="text-sm text-red-600 mt-1">{{ formAmbassade.errors.nom }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1">Type *</label>
                <select
                  v-model="formAmbassade.type"
                  class="w-full rounded-md border-gray-300"
                  required
                >
                  <option value="">Sélectionner...</option>
                  <option value="Ambassade">Ambassade</option>
                  <option value="Consulat">Consulat</option>
                  <option value="Haut-Commissariat">Haut-Commissariat</option>
                </select>
                <div v-if="formAmbassade.errors.type" class="text-sm text-red-600 mt-1">{{ formAmbassade.errors.type }}</div>
              </div>

              <div>
                <label class="block mb-1">Pays *</label>
                <select
                  v-model="formAmbassade.pays_code"
                  class="w-full rounded-md border-gray-300"
                  required
                >
                  <option value="">Sélectionner...</option>
                  <option 
                    v-for="pays in allPays" 
                    :key="pays.code" 
                    :value="pays.code"
                  >
                    {{ pays.nom }}
                  </option>
                </select>
                <div v-if="formAmbassade.errors.pays_code" class="text-sm text-red-600 mt-1">{{ formAmbassade.errors.pays_code }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1">Ville *</label>
                <input
                  v-model="formAmbassade.ville"
                  type="text"
                  class="w-full rounded-md border-gray-300"
                  required
                />
                <div v-if="formAmbassade.errors.ville" class="text-sm text-red-600 mt-1">{{ formAmbassade.errors.ville }}</div>
              </div>

              <div>
                <label class="block mb-1">Téléphone *</label>
                <input
                  v-model="formAmbassade.telephone"
                  type="text"
                  class="w-full rounded-md border-gray-300"
                  required
                />
                <div v-if="formAmbassade.errors.telephone" class="text-sm text-red-600 mt-1">{{ formAmbassade.errors.telephone }}</div>
              </div>
            </div>

            <div>
              <label class="block mb-1">Adresse *</label>
              <input
                v-model="formAmbassade.adresse"
                type="text"
                class="w-full rounded-md border-gray-300"
                required
              />
              <div v-if="formAmbassade.errors.adresse" class="text-sm text-red-600 mt-1">{{ formAmbassade.errors.adresse }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1">Email</label>
                <input
                  v-model="formAmbassade.email"
                  type="email"
                  class="w-full rounded-md border-gray-300"
                />
                <div v-if="formAmbassade.errors.email" class="text-sm text-red-600 mt-1">{{ formAmbassade.errors.email }}</div>
              </div>

              <div>
                <label class="block mb-1">Responsable</label>
                <input
                  v-model="formAmbassade.responsable"
                  type="text"
                  class="w-full rounded-md border-gray-300"
                />
                <div v-if="formAmbassade.errors.responsable" class="text-sm text-red-600 mt-1">{{ formAmbassade.errors.responsable }}</div>
              </div>
            </div>

            <div>
              <label class="block mb-1">Nombre d'électeurs *</label>
              <input
                v-model="formAmbassade.nombre_electeurs"
                type="number"
                min="0"
                class="w-full rounded-md border-gray-300"
                required
              />
              <div v-if="formAmbassade.errors.nombre_electeurs" class="text-sm text-red-600 mt-1">{{ formAmbassade.errors.nombre_electeurs }}</div>
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
              :disabled="formAmbassade.processing"
            >
              <span v-if="formAmbassade.processing">
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
      @confirm="deleteAmbassade"
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
          <strong>"{{ selectedAmbassade?.nom }}"</strong> ({{ selectedAmbassade?.code }}) ?
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
            @click="deleteAmbassade"
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
  ambassades: {
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
    default: 'Gestion des Ambassades'
  }
});

// États
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const selectedAmbassade = ref(null);

// Filtres et tri
const filters = useForm({
  search: props.filters.search || '',
  type: props.filters.type || '',
  sort_field: props.filters.sort_field || 'nom',
  sort_direction: props.filters.sort_direction || 'asc'
});

const sortBy = ref(`${filters.sort_field}-${filters.sort_direction}`);

// Formulaires
const formAmbassade = useForm({
  code: '',
  nom: '',
  type: '',
  pays_code: '',
  ville: '',
  adresse: '',
  telephone: '',
  email: '',
  responsable: '',
  nombre_electeurs: 0
});

const deleteForm = useForm({});

// Computed
const typeClasses = (type) => {
  const classes = {
    'Ambassade': 'bg-blue-100 text-blue-800',
    'Consulat': 'bg-green-100 text-green-800',
    'Haut-Commissariat': 'bg-purple-100 text-purple-800'
  };
  return classes[type] || 'bg-gray-100 text-gray-800';
};

// Breadcrumbs
const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Géographie', url: route('admin.geo.index') },
  { label: 'Ambassades', active: true }
];

// Synchroniser les filtres avec les props
watch(() => props.filters, (newFilters) => {
  filters.defaults({
    search: newFilters.search || '',
    type: newFilters.type || '',
    sort_field: newFilters.sort_field || 'nom',
    sort_direction: newFilters.sort_direction || 'asc'
  }).reset();
  sortBy.value = `${filters.sort_field}-${filters.sort_direction}`;
}, { deep: true });

// Méthodes pour le formulaire
const openFormModal = (ambassade) => {
  if (ambassade) {
    formAmbassade.code = ambassade.code;
    formAmbassade.nom = ambassade.nom;
    formAmbassade.type = ambassade.type;
    formAmbassade.pays_code = ambassade.pays_code;
    formAmbassade.ville = ambassade.ville;
    formAmbassade.adresse = ambassade.adresse;
    formAmbassade.telephone = ambassade.telephone;
    formAmbassade.email = ambassade.email || '';
    formAmbassade.responsable = ambassade.responsable || '';
    formAmbassade.nombre_electeurs = ambassade.nombre_electeurs;
  } else {
    formAmbassade.reset();
    formAmbassade.nombre_electeurs = 0;
  }
  showFormModal.value = true;
};

const closeFormModal = () => {
  showFormModal.value = false;
  formAmbassade.clearErrors();
  formAmbassade.reset();
};

const submitForm = () => {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeFormModal(),
  };

  if (formAmbassade.code) {
    formAmbassade.put(route('admin.geo.ambassades.update', formAmbassade.code), options);
  } else {
    formAmbassade.post(route('admin.geo.ambassades.store'), options);
  }
};

// Méthodes pour la suppression
const confirmDelete = (ambassade) => {
  selectedAmbassade.value = ambassade;
  showDeleteModal.value = true;
};

const deleteAmbassade = () => {
  deleteForm.delete(route('admin.geo.ambassades.destroy', selectedAmbassade.value.code), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      selectedAmbassade.value = null;
    }
  });
};

// Méthodes pour les filtres
const debouncedSearch = debounce(() => {
  submitFilters();
}, 300);

const submitFilters = () => {
  filters.get(route('admin.geo.ambassades.index'), {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  filters.defaults({
    search: '',
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