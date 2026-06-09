<template>
  <AppLayout>
    <PageTitle title="Création d'un Événement" :breadcrumbs="breadcrumbs">
      <template #actions>
        <button
          @click="showPreview"
          class="btn btn-secondary"
          :disabled="!canPreview"
        >
          <i class="fas fa-eye mr-2"></i> Aperçu
        </button>
      </template>
    </PageTitle>

    <section class="bg-white rounded-xl shadow-sm p-6">
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Section Principale -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Colonne de gauche -->
          <div class="space-y-6">
            <!-- Titre -->
            <div class="form-group">
              <label for="title" class="form-label">Titre de l'événement</label>
              <input
                id="title"
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
              <label for="slug" class="form-label">URL personnalisée</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm">
                  {{ $page.url }}/events/
                </div>
                <input
                  id="slug"
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
              <label for="category" class="form-label">Catégorie</label>
              <select
                id="category"
                v-model="form.category_id"
                class="form-select"
                required
              >
                <option value="" disabled>Sélectionnez une catégorie</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
              <InputError :message="form.errors.category_id" />
            </div>

            <!-- Date et Lieu -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="form-group">
                <label for="date" class="form-label">Date</label>
                <input
                  id="date"
                  type="datetime-local"
                  v-model="form.date"
                  class="form-input"
                  required
                />
                <InputError :message="form.errors.date" />
              </div>

              <div class="form-group">
                <label for="location" class="form-label">Lieu</label>
                <input
                  id="location"
                  type="text"
                  v-model="form.location"
                  class="form-input"
                  placeholder="Lieu ou adresse"
                  required
                />
                <InputError :message="form.errors.location" />
              </div>
            </div>

            <!-- URL -->
            <div class="form-group">
              <label for="url" class="form-label">Lien YouTube</label>
              <input
                id="url"
                type="url"
                v-model="form.url"
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
                @image-changed="handleImageUpload"
                @clear-image="form.featured_image = null"
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
              <i class="fas fa-save mr-2"></i> Créer l'événement
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

const props = defineProps({
  categories: {
    type: Array,
    required: true,
    default: () => []
  }
});

const editor = ref(null);
const previewModal = ref(false);
const form = useForm({
  title: '',
  slug: '',
  category_id: '',
  date: '',
  location: '',
  url: '',
  description: '',
  featured_image: null,
  status: 'draft'
});

const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Événements', url: route('admin.events.index') },
  { label: 'Création', active: true }
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
  youtube_id: youtubeVideoId.value
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
};

const showPreview = () => {
  if (canPreview.value) {
    previewModal.value = true;
  }
};

const submitForm = () => {
  const formData = new FormData();
  Object.entries(form.data()).forEach(([key, value]) => {
    if (value !== null) formData.append(key, value);
  });

  form.post(route('admin.events.store'), {
    data: formData,
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
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
</style>