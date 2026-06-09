<template>
  <AppLayout>
    <PageTitle :title="`Modifier l'événement`" :breadcrumbs="breadcrumbs">
      <template #actions>
        <button 
          @click="showPreview" 
          class="btn btn-secondary mr-2"
          :disabled="!canPreview"
        >
          <i class="fas fa-eye mr-2"></i> Aperçu
        </button>
        <button 
          @click="confirmDelete" 
          class="btn btn-danger"
        >
          <i class="fas fa-trash mr-2"></i> Supprimer
        </button>
      </template>
    </PageTitle>

    <section class="bg-white rounded-xl shadow-sm p-6">
      <form @submit.prevent="submitForm">
        <!-- Section Principale -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Colonne de gauche -->
          <div class="space-y-6">
            <!-- Titre -->
            <div class="form-group">
              <label class="form-label">Titre de l'événement</label>
              <input
                v-model="form.title"
                @input="generateSlug"
                type="text"
                class="form-input"
                placeholder="Nommez votre événement"
                required
              />
              <InputError :message="form.errors.title" />
            </div>

            <!-- Slug -->
            <div class="form-group">
              <label class="form-label">URL personnalisée</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm">
                  {{ $page.url }}/events/
                </div>
                <input
                  v-model="form.slug"
                  type="text"
                  class="form-input pl-24"
                  required
                />
              </div>
              <InputError :message="form.errors.slug" />
            </div>

            <!-- Catégorie -->
            <div class="form-group">
              <label class="form-label">Catégorie</label>
              <select
                v-model="form.category_id"
                class="form-select"
                required
              >
                <option value="" disabled>Sélectionnez une catégorie</option>
                <option 
                  v-for="category in categories" 
                  :key="category.id" 
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
              <InputError :message="form.errors.category_id" />
            </div>

            <!-- Date et Lieu -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="form-group">
                <label class="form-label">Date</label>
                <input
                  v-model="form.date"
                  type="datetime-local"
                  class="form-input"
                  required
                />
                <InputError :message="form.errors.date" />
              </div>

              <div class="form-group">
                <label class="form-label">Lieu</label>
                <input
                  v-model="form.location"
                  type="text"
                  class="form-input"
                  placeholder="Lieu ou adresse"
                  required
                />
                <InputError :message="form.errors.location" />
              </div>
            </div>

            <!-- URL -->
            <div class="form-group">
              <label class="form-label">Lien YouTube</label>
              <input
                v-model="form.url"
                type="url"
                class="form-input"
                placeholder="https://youtube.com/watch?v=..."
                required
              />
              <InputError :message="form.errors.url" />
              <div v-if="youtubeVideoId" class="mt-2 text-sm text-gray-500">
                <i class="fas fa-check-circle text-green-500 mr-1"></i>
                Lien YouTube valide détecté
              </div>
            </div>
          </div>

          <!-- Colonne de droite -->
          <div class="space-y-6">
            <!-- Description -->
            <div class="form-group">
              <label class="form-label">Description</label>
              <div ref="editor" class="editor-content"></div>
              <InputError :message="form.errors.description" />
            </div>

            <!-- Image de couverture -->
            <div class="form-group">
              <label class="form-label">Image de couverture</label>
              <ImageUploader
                :initial-image="featuredImageUrl"
                @image-changed="handleImageUpload"
                @clear-image="handleClearImage"
              />
              <p class="text-xs text-gray-500 mt-1">
                Formats supportés: JPG, PNG, WEBP (Max. 5MB)
              </p>
              <InputError :message="form.errors.featured_image" />
            </div>

            <!-- Statut -->
            <div class="form-group">
              <label class="form-label">Statut</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input
                    type="radio"
                    v-model="form.status"
                    value="draft"
                    class="form-radio"
                  />
                  <span class="ml-2">Brouillon</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="radio"
                    v-model="form.status"
                    value="published"
                    class="form-radio"
                  />
                  <span class="ml-2">Publié</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="radio"
                    v-model="form.status"
                    value="archived"
                    class="form-radio"
                  />
                  <span class="ml-2">Archivé</span>
                </label>
              </div>
              <InputError :message="form.errors.status" />
            </div>
          </div>
        </div>

        <!-- Boutons -->
        <div class="flex justify-between pt-4 border-t">
          <Link :href="route('admin.events.index')" class="btn btn-secondary">
            Annuler
          </Link>
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="form.processing"
          >
            <span v-if="form.processing">
              <i class="fas fa-spinner fa-spin mr-2"></i> Enregistrement...
            </span>
            <span v-else>
              <i class="fas fa-save mr-2"></i> Mettre à jour
            </span>
          </button>
        </div>
      </form>
    </section>

    <!-- Modal d'aperçu -->
    <EventPreviewModal
      v-if="previewModal"
      :event="previewData"
      @close="previewModal = false"
    />

    <!-- Confirmation suppression -->
    <ConfirmationDialog
      :show="showDeleteConfirmation"
      title="Confirmer la suppression"
      message="Êtes-vous sûr de vouloir supprimer définitivement cet événement ?"
      confirm-text="Supprimer"
      confirm-color="danger"
      @close="showDeleteConfirmation = false"
      @confirm="deleteEvent"
    />
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import InputError from '@/Components/InputError.vue';
import ImageUploader from '@/Components/ImageUploader.vue';
import EventPreviewModal from '@/Components/Events/PreviewModal.vue';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';

const props = defineProps({
  event: {
    type: Object,
    required: true,
    default: () => ({
      id: null,
      title: '',
      slug: '',
      category_id: '',
      date: '',
      location: '',
      url: '',
      description: '',
      featured_image: null,
      status: 'draft',
      category: null
    })
  },
  categories: {
    type: Array,
    required: true,
    default: () => []
  }
});

const editor = ref(null);
const previewModal = ref(false);
const showDeleteConfirmation = ref(false);

// Gestion de l'image
const featuredImageUrl = computed(() => {
  return props.event.featured_image 
    ? `/storage/${props.event.featured_image}`
    : null;
});

// Initialisation du formulaire
const form = useForm({
  id: props.event.id,
  title: props.event.title,
  slug: props.event.slug,
  category_id: props.event.category_id,
  date: formatDateTimeForInput(props.event.date),
  location: props.event.location,
  url: props.event.url,
  description: props.event.description,
  featured_image: null,
  featured_image_path: props.event.featured_image,
  status: props.event.status
});

// Formatage de la date pour l'input datetime-local
function formatDateTimeForInput(dateTime) {
  if (!dateTime) return '';
  
  const date = new Date(dateTime);
  const pad = num => num.toString().padStart(2, '0');
  
  return [
    date.getFullYear(),
    pad(date.getMonth() + 1),
    pad(date.getDate())
  ].join('-') + 'T' + [
    pad(date.getHours()),
    pad(date.getMinutes())
  ].join(':');
}

const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Événements', url: route('admin.events.index') },
  { label: 'Modification', active: true }
];

const canPreview = computed(() => {
  return form.title && form.description;
});

const youtubeVideoId = computed(() => {
  if (!form.url) return null;
  const match = form.url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
  return match ? match[1] : null;
});

const previewData = computed(() => ({
  ...form.data(),
  category: props.categories.find(c => c.id == form.category_id),
  youtube_id: youtubeVideoId.value,
  featured_image_url: featuredImageUrl.value
}));

onMounted(() => {
  const quill = new Quill(editor.value, {
    theme: 'snow',
    placeholder: 'Décrivez votre événement...',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline'],
        [{ header: [1, 2, 3, false] }],
        ['link', 'image'],
        ['clean']
      ]
    }
  });

  quill.root.innerHTML = form.description || '';

  quill.on('text-change', () => {
    form.description = quill.root.innerHTML;
  });
});

const generateSlug = () => {
  if (!form.slug) {
    form.slug = form.title
      .toLowerCase()
      .replace(/[^\w\s]/g, '')
      .replace(/\s+/g, '-');
  }
};

const handleImageUpload = (file) => {
  form.featured_image = file;
  form.featured_image_path = null; // Effacer le chemin existant si nouveau fichier
};

const handleClearImage = () => {
  form.featured_image = null;
  form.featured_image_path = null; // Pour supprimer l'image existante
};

const showPreview = () => {
  if (canPreview.value) {
    previewModal.value = true;
  }
};

const confirmDelete = () => {
  showDeleteConfirmation.value = true;
};

const submitForm = () => {
  const formData = new FormData();
  
  // Ajout de tous les champs sauf ceux null
  Object.entries(form.data()).forEach(([key, value]) => {
    if (value !== null && value !== undefined) {
      formData.append(key, value);
    }
  });

  formData.append('_method', 'PUT');

  form.put(route('admin.events.update', { event: form.id }), {
    data: formData,
    preserveScroll: true,
    onSuccess: () => {
      // Notification de succès
    }
  });
};

const deleteEvent = () => {
  useForm({}).delete(route('admin.events.destroy', { event: form.id }), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirection après suppression
    }
  });
};
</script>

<style scoped>
.form-group {
  @apply space-y-2;
}

.form-label {
  @apply block text-sm font-medium text-gray-700;
}

.form-input {
  @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
}

.form-select {
  @apply block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm;
}

.form-radio {
  @apply h-4 w-4 text-blue-600 focus:ring-blue-500;
}

.editor-content {
  @apply min-h-[200px] rounded-md border border-gray-300 bg-white;
}

.btn {
  @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2;
}

.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white;
}

.btn-secondary {
  @apply bg-gray-200 hover:bg-gray-300 focus:ring-gray-500 text-gray-700;
}

.btn-danger {
  @apply bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white;
}
</style>