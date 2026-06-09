<template>
  <AppLayout>
    <PageTitle 
      title="Gestion des Catégories" 
      :breadcrumbs="breadcrumbs"
    >
      <template #actions>
        <button 
          @click="reloadCategories"
          class="btn-refresh"
          :disabled="loading"
        >
          <i class="fas fa-sync-alt mr-2" :class="{ 'fa-spin': loading }"></i>
          Actualiser
        </button>
      </template>
    </PageTitle>

    <div class="px-6 pb-6">
      <!-- Filtre global -->
      <div class="mb-6">
        <div class="relative max-w-md">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input
            v-model="globalFilter"
            type="text"
            placeholder="Rechercher dans toutes les catégories..."
            class="pl-10 w-full input-search"
          />
        </div>
      </div>

      <!-- Cartes des catégories -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <div 
          v-for="(categories, type) in filteredCategories" 
          :key="type"
          class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
        >
          <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-medium text-gray-700 flex items-center">
              <i :class="typeIcons[type]" class="mr-2 text-gray-500"></i>
              {{ columnTitles[type] }}
              <span class="ml-2 text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">
                {{ categories.length }}
              </span>
            </h3>
            <button 
              @click="openModal(type)"
              class="btn-add-category"
              title="Ajouter une catégorie"
            >
              <i class="fas fa-plus"></i>
            </button>
          </div>
          
          <div class="p-3">
            <!-- Filtre spécifique -->
            <div class="mb-3 relative">
              <input
                type="text"
                v-model="filters[type]"
                placeholder="Filtrer..."
                class="input-filter"
              />
            </div>
            
            <!-- Liste des catégories -->
            <ul v-if="!loading" class="space-y-2 max-h-96 overflow-y-auto custom-scrollbar">
              <li 
                v-for="category in categories" 
                :key="category.id"
                class="group flex items-center justify-between px-3 py-2 rounded hover:bg-gray-50 transition-colors cursor-pointer"
                @click="editCategory(category, type)"
              >
                <span class="truncate flex-1">{{ category.name }}</span>
                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex space-x-2">
                  <button 
                    class="text-gray-400 hover:text-blue-500"
                    title="Modifier"
                    @click.stop="editCategory(category, type)"
                  >
                    <i class="fas fa-pencil-alt text-sm"></i>
                  </button>
                  <button 
                    class="text-gray-400 hover:text-red-500"
                    title="Supprimer"
                    @click.stop="confirmDelete(category)"
                  >
                    <i class="fas fa-trash-alt text-sm"></i>
                  </button>
                </div>
              </li>
              <li v-if="categories.length === 0" class="text-center py-4 text-gray-400 text-sm">
                Aucune catégorie trouvée
              </li>
            </ul>
            <div v-else class="text-center py-8">
              <i class="fas fa-spinner fa-spin text-gray-400 text-xl"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal d'édition/ajout -->
    <Modal :show="isModalOpen" @close="closeModal">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            {{ modalMode === 'add' ? 'Ajouter une catégorie' : 'Modifier la catégorie' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <div class="flex items-center bg-gray-100 px-3 py-2 rounded text-sm text-gray-600">
            <i :class="typeIcons[currentGroup]" class="mr-2"></i>
            {{ columnTitles[currentGroup] }}
          </div>
        </div>
        
        <div class="mb-6">
          <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
          <input
            id="categoryName"
            type="text"
            v-model="currentCategory.name"
            placeholder="Entrez le nom de la catégorie"
            class="input-field"
            :class="{ 'border-red-500': currentCategory.name.trim() === '' }"
            @keyup.enter="saveCategory"
          />
          <p v-if="currentCategory.name.trim() === ''" class="text-sm text-red-500 mt-1">
            Le nom est requis.
          </p>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button @click="closeModal" class="btn-cancel">
            Annuler
          </button>
          <button 
            @click="saveCategory" 
            class="btn-primary"
            :disabled="!currentCategory.name.trim() || actionLoading"
          >
            <i v-if="actionLoading" class="fas fa-spinner fa-spin mr-2"></i>
            {{ modalMode === 'add' ? 'Ajouter' : 'Enregistrer' }}
          </button>
        </div>
      </div>
    </Modal>

    <!-- Confirmation de suppression -->
    <ConfirmationDialog 
      :show="showDeleteConfirm" 
      title="Confirmer la suppression"
      message="Êtes-vous sûr de vouloir supprimer cette catégorie ? Cette action est irréversible."
      confirm-text="Supprimer"
      cancel-text="Annuler"
      @confirm="deleteCategory"
      @cancel="showDeleteConfirm = false"
    />
  </AppLayout>
</template>


// src/Pages/Dashboard/categories/index.vue
<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';

const toast = useToast();

const breadcrumbs = [
  { label: 'Dashboard', url: '/admin/dashboard' },
  { label: 'Catégories', active: true }
];

const columnTitles = {
  article: 'Articles',
  publication: 'Publications',
  video: 'Vidéos',
  photo: 'Photos',
  evenement: 'Événements'
};

const typeIcons = {
  article: 'fas fa-newspaper',
  publication: 'fas fa-book',
  video: 'fas fa-video',
  photo: 'fas fa-camera',
  evenement: 'fas fa-calendar-alt'
};

const categories = ref([]);
const loading = ref(false);
const actionLoading = ref(false);
const globalFilter = ref('');
const filters = ref({
  article: '',
  publication: '',
  video: '',
  photo: '',
  evenement: ''
});

const reloadCategories = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/admin/categories/loading');
    console.log('🔍 Réponse API:', {
      status: response.status,
      data: response.data,
      headers: response.headers
    });
    categories.value = response.data;
    console.log('📦 Catégories assignées:', categories.value);
  } catch (error) {
    console.error('❌ Erreur API:', error);
    toast.error("Erreur lors du chargement des catégories");
  } finally {
    loading.value = false;
  }
};

reloadCategories();

const filteredCategories = computed(() => {
  const result = {};
  const globalFilterLower = globalFilter.value.toLowerCase();

  for (const type in columnTitles) {
    const typeFilterLower = filters.value[type].toLowerCase();
    result[type] = categories.value
      .filter(c => c.type === type)
      .filter(c =>
        c.name.toLowerCase().includes(typeFilterLower) &&
        (globalFilter.value === '' || c.name.toLowerCase().includes(globalFilterLower))
      )
      .sort((a, b) => a.name.localeCompare(b.name));
  }

  return result;
});

const isModalOpen = ref(false);
const showDeleteConfirm = ref(false);
const modalMode = ref('add');
const currentCategory = ref({ name: '', id: null });
const currentGroup = ref('');

const openModal = (type) => {
  currentGroup.value = type;
  modalMode.value = 'add';
  currentCategory.value = { name: '', id: null };
  isModalOpen.value = true;
};

const editCategory = (category, type) => {
  currentGroup.value = type;
  modalMode.value = 'edit';
  currentCategory.value = { ...category };
  isModalOpen.value = true;
};

const confirmDelete = (category) => {
  currentCategory.value = { ...category };
  showDeleteConfirm.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  currentCategory.value = { name: '', id: null };
};

const saveCategory = async () => {
  if (!currentCategory.value.name.trim()) return;

  actionLoading.value = true;
  try {
    if (modalMode.value === 'add') {
      await axios.post('/admin/categories', {
        name: currentCategory.value.name,
        type: currentGroup.value
      });
      toast.success('Catégorie ajoutée avec succès');
    } else {
      await axios.put(`/admin/categories/${currentCategory.value.id}`, {
        name: currentCategory.value.name,
        type: currentGroup.value
      });
      toast.success('Catégorie modifiée avec succès');
    }
    closeModal();
    await reloadCategories();
  } catch (error) {
    toast.error(error.response?.data?.message || "Une erreur s'est produite");
  } finally {
    actionLoading.value = false;
  }
};

const deleteCategory = async () => {
  if (!currentCategory.value.id) return;

  actionLoading.value = true;
  try {
    await axios.delete(`/admin/categories/${currentCategory.value.id}`);
    toast.success('Catégorie supprimée avec succès');
    showDeleteConfirm.value = false;
    await reloadCategories();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Erreur lors de la suppression');
  } finally {
    actionLoading.value = false;
  }
};
</script>


<style scoped>
.btn-refresh {
  @apply px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.btn-add-category {
  @apply p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-full transition-colors;
}

.input-search {
  @apply block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
}

.input-filter {
  @apply block w-full px-3 py-1.5 border border-gray-200 rounded text-sm leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500;
}

.input-field {
  @apply block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
}

.btn-cancel {
  @apply px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.btn-primary {
  @apply px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed;
}

.custom-scrollbar::-webkit-scrollbar {
  @apply w-2;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  @apply bg-gray-300 rounded-full hover:bg-gray-400;
}
</style>