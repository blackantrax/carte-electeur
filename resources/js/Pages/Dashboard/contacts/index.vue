<template>
  <AppLayout>
    <PageTitle 
      title="Gestion des Contacts" 
      :breadcrumbs="breadcrumbs"
    >
      <template #actions>
        <button 
          @click="reloadContacts"
          class="btn-refresh"
          :disabled="loading"
        >
          <i class="fas fa-sync-alt mr-2" :class="{ 'fa-spin': loading }"></i>
          Actualiser
        </button>
        <button 
          @click="exportContacts"
          class="btn-export ml-2"
          :disabled="loading"
        >
          <i class="fas fa-file-export mr-2"></i>
          Exporter
        </button>
      </template>
    </PageTitle>

    <div class="px-6 pb-6">
      <!-- Filtres avancés -->
      <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Recherche globale</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <input
                v-model="globalFilter"
                type="text"
                placeholder="Nom, email ou message..."
                class="pl-10 w-full input-search"
              />
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
            <select v-model="filters.status" class="input-field">
              <option value="">Tous</option>
              <option value="unread">Non lu</option>
              <option value="read">Lu</option>
              <option value="archived">Archivé</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date de</label>
            <input v-model="filters.startDate" type="date" class="input-field">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date à</label>
            <input v-model="filters.endDate" type="date" class="input-field">
          </div>
        </div>
      </div>

      <!-- Tableau des contacts -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortBy('name')">
                  <div class="flex items-center">
                    Nom
                    <i :class="sortIcon('name')" class="ml-1"></i>
                  </div>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sujet</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortBy('created_at')">
                  <div class="flex items-center">
                    Date
                    <i :class="sortIcon('created_at')" class="ml-1"></i>
                  </div>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading">
                <td colspan="6" class="px-6 py-4 text-center">
                  <i class="fas fa-spinner fa-spin text-gray-400 text-xl"></i>
                </td>
              </tr>
              <tr v-else-if="filteredContacts.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                  Aucun contact trouvé
                </td>
              </tr>
              <tr 
                v-for="contact in filteredContacts" 
                :key="contact.id"
                class="hover:bg-gray-50 transition-colors"
                :class="{
                  'bg-blue-50': contact.status === 'unread',
                  'cursor-pointer': true
                }"
                @click="viewContact(contact)"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                      <i class="fas fa-user text-gray-500"></i>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ contact.name }}</div>
                      <div class="text-sm text-gray-500">{{ formatDate(contact.created_at) }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ contact.email }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 truncate max-w-xs">{{ contact.subject }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ formatDate(contact.created_at) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="statusBadgeClass(contact.status)" class="px-2 py-1 text-xs rounded-full">
                    {{ statusLabel(contact.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button 
                    @click.stop="viewContact(contact)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                    title="Voir"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <button 
                    @click.stop="archiveContact(contact)"
                    class="text-gray-600 hover:text-gray-900 mr-3"
                    title="Archiver"
                  >
                    <i class="fas fa-archive"></i>
                  </button>
                  <button 
                    @click.stop="confirmDelete(contact)"
                    class="text-red-600 hover:text-red-900"
                    title="Supprimer"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && filteredContacts.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex-1 flex justify-between sm:hidden">
            <button @click="prevPage" :disabled="currentPage === 1" class="btn-pagination">
              Précédent
            </button>
            <button @click="nextPage" :disabled="currentPage === totalPages" class="ml-3 btn-pagination">
              Suivant
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Affichage de <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span>
                à <span class="font-medium">{{ Math.min(currentPage * perPage, filteredContacts.length) }}</span>
                sur <span class="font-medium">{{ filteredContacts.length }}</span> contacts
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button 
                  @click="prevPage" 
                  :disabled="currentPage === 1"
                  class="btn-pagination rounded-l-md"
                >
                  <span class="sr-only">Précédent</span>
                  <i class="fas fa-chevron-left"></i>
                </button>
                <button 
                  v-for="page in visiblePages" 
                  :key="page"
                  @click="goToPage(page)"
                  :class="{
                    'bg-blue-50 border-blue-500 text-blue-600': currentPage === page,
                    'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': currentPage !== page
                  }"
                  class="btn-pagination"
                >
                  {{ page }}
                </button>
                <button 
                  @click="nextPage" 
                  :disabled="currentPage === totalPages"
                  class="btn-pagination rounded-r-md"
                >
                  <span class="sr-only">Suivant</span>
                  <i class="fas fa-chevron-right"></i>
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de visualisation -->
    <Modal :show="isModalOpen" @close="closeModal" size="lg">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            Message de {{ currentContact.name }}
          </h3>
          <div class="flex space-x-2">
            <button 
              @click="toggleStatus(currentContact)"
              class="text-gray-400 hover:text-blue-500"
              :title="currentContact.status === 'unread' ? 'Marquer comme lu' : 'Marquer comme non lu'"
            >
              <i :class="currentContact.status === 'unread' ? 'fas fa-envelope-open' : 'fas fa-envelope'"></i>
            </button>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
            <div class="bg-gray-50 px-3 py-2 rounded text-sm text-gray-600">
              {{ currentContact.name }}
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <div class="bg-gray-50 px-3 py-2 rounded text-sm text-gray-600">
              {{ currentContact.email }}
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <div class="bg-gray-50 px-3 py-2 rounded text-sm text-gray-600">
              {{ formatDateTime(currentContact.created_at) }}
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">IP</label>
            <div class="bg-gray-50 px-3 py-2 rounded text-sm text-gray-600">
              {{ currentContact.ip_address }}
            </div>
          </div>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Sujet</label>
          <div class="bg-gray-50 px-3 py-2 rounded text-sm text-gray-600">
            {{ currentContact.subject }}
          </div>
        </div>
        
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
          <div class="bg-gray-50 px-3 py-2 rounded text-sm text-gray-600 whitespace-pre-line">
            {{ currentContact.message }}
          </div>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button @click="replyToContact" class="btn-primary">
            <i class="fas fa-reply mr-2"></i>
            Répondre
          </button>
        </div>
      </div>
    </Modal>

    <!-- Confirmation de suppression -->
    <ConfirmationDialog 
      :show="showDeleteConfirm" 
      title="Confirmer la suppression"
      :message="`Êtes-vous sûr de vouloir supprimer le message de ${currentContact.name} ? Cette action est irréversible.`"
      confirm-text="Supprimer"
      cancel-text="Annuler"
      @confirm="deleteContact"
      @cancel="showDeleteConfirm = false"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';

const toast = useToast();

const breadcrumbs = [
  { label: 'Dashboard', url: '/admin/dashboard' },
  { label: 'Contacts', active: true }
];

const contacts = ref([]);
const loading = ref(false);
const actionLoading = ref(false);
const globalFilter = ref('');
const filters = ref({
  status: '',
  startDate: '',
  endDate: ''
});
const sortField = ref('created_at');
const sortDirection = ref('desc');

// Pagination
const currentPage = ref(1);
const perPage = ref(10);

const reloadContacts = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/admin/contacts');
    contacts.value = response.data;
  } catch (error) {
    toast.error("Erreur lors du chargement des contacts");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  reloadContacts();
});

const filteredContacts = computed(() => {
  let result = [...contacts.value];
  
  // Filtre global
  if (globalFilter.value) {
    const searchTerm = globalFilter.value.toLowerCase();
    result = result.filter(contact => 
      contact.name.toLowerCase().includes(searchTerm) ||
      contact.email.toLowerCase().includes(searchTerm) ||
      contact.subject.toLowerCase().includes(searchTerm) ||
      contact.message.toLowerCase().includes(searchTerm)
    );
  }
  
  // Filtre par statut
  if (filters.value.status) {
    result = result.filter(contact => contact.status === filters.value.status);
  }
  
  // Filtre par date
  if (filters.value.startDate) {
    const startDate = new Date(filters.value.startDate);
    result = result.filter(contact => new Date(contact.created_at) >= startDate);
  }
  
  if (filters.value.endDate) {
    const endDate = new Date(filters.value.endDate);
    endDate.setHours(23, 59, 59, 999); // Fin de journée
    result = result.filter(contact => new Date(contact.created_at) <= endDate);
  }
  
  // Tri
  result.sort((a, b) => {
    if (a[sortField.value] < b[sortField.value]) return sortDirection.value === 'asc' ? -1 : 1;
    if (a[sortField.value] > b[sortField.value]) return sortDirection.value === 'asc' ? 1 : -1;
    return 0;
  });
  
  return result;
});

const paginatedContacts = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;
  return filteredContacts.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredContacts.value.length / perPage.value);
});

const visiblePages = computed(() => {
  const pages = [];
  const maxVisible = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
  let end = Math.min(totalPages.value, start + maxVisible - 1);
  
  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1);
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

const sortBy = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
};

const sortIcon = (field) => {
  if (sortField.value !== field) return 'fas fa-sort';
  return sortDirection.value === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const goToPage = (page) => {
  currentPage.value = page;
};

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('fr-FR', options);
};

const formatDateTime = (dateString) => {
  const options = { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  return new Date(dateString).toLocaleDateString('fr-FR', options);
};

const statusLabel = (status) => {
  const labels = {
    unread: 'Non lu',
    read: 'Lu',
    archived: 'Archivé'
  };
  return labels[status] || status;
};

const statusBadgeClass = (status) => {
  const classes = {
    unread: 'bg-blue-100 text-blue-800',
    read: 'bg-green-100 text-green-800',
    archived: 'bg-gray-100 text-gray-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

// Modal management
const isModalOpen = ref(false);
const showDeleteConfirm = ref(false);
const currentContact = ref({
  id: null,
  name: '',
  email: '',
  subject: '',
  message: '',
  ip_address: '',
  created_at: '',
  status: 'unread'
});

const viewContact = (contact) => {
  currentContact.value = { ...contact };
  if (contact.status === 'unread') {
    markAsRead(contact);
  }
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const confirmDelete = (contact) => {
  currentContact.value = { ...contact };
  showDeleteConfirm.value = true;
};

const markAsRead = async (contact) => {
  try {
    await axios.patch(`/admin/contacts/${contact.id}/read`);
    contact.status = 'read';
    toast.success('Message marqué comme lu');
  } catch (error) {
    toast.error("Erreur lors de la mise à jour du statut");
  }
};

const toggleStatus = async (contact) => {
  try {
    const newStatus = contact.status === 'unread' ? 'read' : 'unread';
    await axios.patch(`/admin/contacts/${contact.id}/status`, { status: newStatus });
    contact.status = newStatus;
    toast.success(`Message marqué comme ${statusLabel(newStatus).toLowerCase()}`);
  } catch (error) {
    toast.error("Erreur lors de la mise à jour du statut");
  }
};

const archiveContact = async (contact) => {
  try {
    await axios.patch(`/admin/contacts/${contact.id}/archive`);
    contact.status = 'archived';
    toast.success('Message archivé');
  } catch (error) {
    toast.error("Erreur lors de l'archivage");
  }
};

const deleteContact = async () => {
  actionLoading.value = true;
  try {
    await axios.delete(`/admin/contacts/${currentContact.value.id}`);
    toast.success('Contact supprimé avec succès');
    showDeleteConfirm.value = false;
    await reloadContacts();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Erreur lors de la suppression');
  } finally {
    actionLoading.value = false;
  }
};

const replyToContact = () => {
  window.location.href = `mailto:${currentContact.value.email}?subject=Re: ${currentContact.value.subject}`;
};

const exportContacts = async () => {
  try {
    const response = await axios.get('/admin/contacts/export', {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `contacts_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    
    toast.success('Export des contacts terminé');
  } catch (error) {
    toast.error("Erreur lors de l'export des contacts");
  }
};
</script>

<style scoped>
.btn-refresh {
  @apply px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.btn-export {
  @apply px-4 py-2 bg-white border border-green-300 rounded-md text-sm font-medium text-green-700 hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500;
}

.input-search {
  @apply block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
}

.input-field {
  @apply block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
}

.btn-pagination {
  @apply relative inline-flex items-center px-4 py-2 border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10;
}

.btn-primary {
  @apply px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500;
}

/* Animation pour les nouveaux messages non lus */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.bg-blue-50 {
  animation: pulse 2s infinite;
}
</style>