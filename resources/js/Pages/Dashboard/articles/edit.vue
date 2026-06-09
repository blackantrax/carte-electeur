<template>
  <AppLayout>
    <PageTitle :title="`Édition de l'article`" :breadcrumbs="breadcrumbs">
      <template #actions>
        <button 
          @click="showPreview" 
          class="btn btn-outline-secondary mr-2"
          :disabled="!canPreview"
        >
          <i class="fas fa-eye mr-2"></i>Aperçu
        </button>
        <button @click="confirmDelete" class="btn btn-outline-danger">
          <i class="fas fa-trash mr-2"></i>Supprimer
        </button>
      </template>
    </PageTitle>

    <div class="editor-container">
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Section Principale -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 border-t pt-6 mt-6">
          <!-- Colonne de contenu -->
          <div class="lg:col-span-2 space-y-6">
            <h3 class="text-lg font-semibold text-gray-700">Informations principales</h3>

            <div class="form-group">
              <label for="title" class="form-label">Titre de l'article</label>
              <input 
                id="title" 
                v-model="form.title" 
                @input="generateSlug" 
                type="text" 
                class="form-input" 
                placeholder="Donnez un titre percutant" 
                required
              >
              <InputError class="mt-2" :message="form.errors.title" />
            </div>

            <div class="form-group">
              <label class="form-label">Contenu</label>
              <div 
                ref="editor" 
                class="editor-content" 
                :class="{ 'border-red-300': contentError || form.errors.content }"
              ></div>
              <p v-if="contentError" class="text-red-500 text-sm mt-1">
                {{ contentError }}
              </p>
              <InputError class="mt-2" :message="form.errors.content" />
            </div>
          </div>

          <!-- Colonne latérale -->
          <div class="space-y-6 p-4 rounded-md bg-gray-50 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700">Paramètres supplémentaires</h3>

            <div class="form-group">
              <label for="slug" class="form-label">URL personnalisée</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm">
                  {{ $page.url }}/articles/
                </div>
                <input 
                  id="slug" 
                  v-model="form.slug" 
                  type="text" 
                  class="form-input pl-32 text-sm" 
                  required
                >
              </div>
              <InputError class="mt-2" :message="form.errors.slug" />
            </div>

            <div class="form-group">
              <label for="status" class="form-label">Statut</label>
              <select 
                id="status" 
                v-model="form.status" 
                class="form-select" 
                required
              >
                <option value="draft">Brouillon</option>
                <option value="published">Publié</option>
                <option value="archived">Archivé</option>
              </select>
              <InputError class="mt-2" :message="form.errors.status" />
            </div>

            <div class="form-group">
              <label for="category" class="form-label">Catégorie</label>
              <select 
                id="category" 
                v-model="form.category_id" 
                class="form-select" 
                required
              >
                <option value="">Sélectionnez une catégorie</option>
                <option 
                  v-for="category in categories" 
                  :key="category.id" 
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
              <InputError class="mt-2" :message="form.errors.category_id" />
            </div>

            <div class="form-group">
              <label class="form-label">Image mise en avant</label>
              <ImageUploader 
                :initial-image="form.featured_image_url"
                @image-changed="handleImageUpload"
                @clear-image="form.featured_image = null"
              />
              <p class="text-xs text-gray-500 mt-1">
                Formats supportés: JPG, PNG, WEBP (Max. 5MB)
              </p>
              <InputError class="mt-2" :message="form.errors.featured_image" />
            </div>

            <div class="form-group">
              <label class="form-label">Options</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input 
                    type="checkbox" 
                    v-model="form.featured" 
                    class="form-checkbox"
                  >
                  <span class="ml-2">Mettre en avant</span>
                </label>
                <label class="flex items-center">
                  <input 
                    type="checkbox" 
                    v-model="form.allow_comments" 
                    class="form-checkbox"
                  >
                  <span class="ml-2">Autoriser les commentaires</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-between border-t pt-4">
          <Link 
            :href="route('articles.index')" 
            class="btn btn-secondary"
          >
            Annuler
          </Link>
          <button 
            type="submit" 
            class="btn btn-primary" 
            :disabled="form.processing"
          >
            <span v-if="form.processing">
              <i class="fas fa-spinner fa-spin mr-2"></i>Enregistrement...
            </span>
            <span v-else>
              <i class="fas fa-save mr-2"></i>Mettre à jour
            </span>
          </button>
        </div>
      </form>
    </div>

    <!-- Aperçu -->
    <ArticlePreviewModal 
      v-if="previewModal" 
      :article="previewArticle" 
      @close="previewModal = false" 
    />

    <!-- Confirmation suppression -->
    <ConfirmationDialog 
      :show="deleteModal" 
      title="Supprimer l'article" 
      message="Êtes-vous sûr de vouloir supprimer définitivement cet article ? Cette action est irréversible." 
      confirm-text="Supprimer" 
      confirm-color="danger" 
      @close="deleteModal = false" 
      @confirm="deleteArticle" 
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
import ArticlePreviewModal from '@/Components/Articles/PreviewModal.vue';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';

const props = defineProps({
  article: {
    type: Object,
    required: true,
    default: () => ({
      id: null,
      title: '',
      slug: '',
      content: '',
      status: 'draft',
      category_id: null,
      featured_image: null,
      featured_image_url: null,
      featured: false,
      allow_comments: true,
      created_at: null,
      updated_at: null
    })
  },
  categories: {
    type: Array,
    required: true,
    default: () => []
  }
});

const form = useForm({
  _method: 'PUT', // Important pour Laravel
  id: props.article.id,
  title: props.article.title,
  slug: props.article.slug,
  content: props.article.content,
  status: props.article.status,
  category_id: props.article.category_id,
  featured_image: null,
  featured_image_url: props.article.featured_image_url,
  featured: props.article.featured,
  allow_comments: props.article.allow_comments
});

const editor = ref(null);
const quill = ref(null);
const previewModal = ref(false);
const deleteModal = ref(false);
const contentError = ref(null);

const canPreview = computed(() => {
  return form.title && form.content;
});

const windowLocation = computed(() => window.location.origin);

const breadcrumbs = [
  { label: 'Articles', url: route('articles.index') },
  { label: 'Édition', active: true }
];

const previewArticle = computed(() => ({
  ...form.data(),
  content: form.content,
  featured_image: form.featured_image_url
}));

onMounted(() => {
  initializeEditor();
});

const initializeEditor = () => {
  quill.value = new Quill(editor.value, {
    theme: 'snow',
    placeholder: 'Écrivez votre contenu ici...',
    modules: {
      toolbar: [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link', 'image', 'video'],
        ['clean']
      ]
    }
  });

  if (form.content) {
    quill.value.root.innerHTML = form.content;
  }

  quill.value.on('text-change', () => {
    form.content = quill.value.root.innerHTML;
    validateContent();
  });
};

const validateContent = () => {
  const text = quill.value.getText().trim();
  contentError.value = text.length < 50 
    ? 'Le contenu doit faire au moins 50 caractères' 
    : null;
};

const slugify = (str) => {
  return str
    .toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()
    .replace(/\s+/g, '-')
    .replace(/[^\w-]+/g, '')
    .replace(/--+/g, '-');
};

const generateSlug = () => {
  if (!form.slug || form.slug === slugify(form.title)) {
    form.slug = slugify(form.title);
  }
};

const handleImageUpload = (file) => {
  form.featured_image = file;
};

const showPreview = () => {
  validateContent();
  if (!contentError.value) {
    previewModal.value = true;
  }
};

const submitForm = () => {
  validateContent();
  if (contentError.value) return;

  form.post(route('articles.update', form.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Notification de succès optionnelle
    },
    onError: () => {
      // Gestion des erreurs automatique via Inertia
    }
  });
};

const confirmDelete = () => {
  deleteModal.value = true;
};

const deleteArticle = () => {
  useForm({}).delete(route('articles.destroy', form.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirection gérée par Inertia
    }
  });
};
</script>

<style scoped>
.editor-container {
  @apply bg-white rounded-lg shadow-sm p-6;
}

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

.form-checkbox {
  @apply h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500;
}

.editor-content {
  @apply min-h-[300px] rounded-md border border-gray-300 bg-white;
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

.btn-outline-danger {
  @apply border-red-500 text-red-500 hover:bg-red-50 focus:ring-red-500;
}

.btn-outline-secondary {
  @apply border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500;
}
</style>