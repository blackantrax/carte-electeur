<template>
  <AppLayout>
    <PageTitle title="Créer une nouvelle vidéo" :breadcrumbs="breadcrumbs">
      <template #actions>
        <Link 
          :href="route('admin.medias.videos.index')" 
          class="btn btn-secondary"
        >
          <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
        </Link>
      </template>
    </PageTitle>

    <section class="bg-white rounded-xl shadow-sm p-6">
      <form @submit.prevent="submitVideo" class="space-y-6">
        <!-- Section Principale -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <!-- Colonne de gauche -->
          <div class="space-y-6">
            <!-- Titre -->
            <div class="form-group">
              <label class="form-label">Titre de la vidéo</label>
              <input
                v-model="form.title"
                @input="generateSlug"
                type="text"
                class="form-input"
                placeholder="Titre attractif et descriptif"
                required
              />
              <p class="form-hint">Ce titre apparaîtra sur la plateforme</p>
            </div>

            <!-- Slug -->
            <div class="form-group">
              <label class="form-label">URL personnalisée</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                  {{ windowLocation }}/
                </div>
                <input
                  v-model="form.slug"
                  type="text"
                  class="form-input pl-24"
                  placeholder="url-personnalisee"
                  required
                />
              </div>
              <p class="form-hint">Laissez vide pour générer automatiquement</p>
            </div>

            <!-- Catégorie -->
            <div class="form-group">
              <label class="form-label">Catégorie</label>
              <select
                v-model="form.category_id"
                class="form-select"
                required
                :disabled="loadingCategories"
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
              <p v-if="loadingCategories" class="text-sm text-gray-500">Chargement des catégories...</p>
            </div>

            <!-- URL YouTube -->
            <div class="form-group">
              <label class="form-label">URL YouTube</label>
              <div class="flex items-center space-x-2">
                <input
                  v-model="form.url"
                  type="url"
                  class="form-input flex-1"
                  placeholder="https://www.youtube.com/watch?v=..."
                  required
                  @blur="validateYoutubeUrl"
                />
                <button
                  type="button"
                  @click="showPreview = true"
                  class="btn btn-secondary whitespace-nowrap"
                  :disabled="!form.url || urlError"
                >
                  <i class="fas fa-eye mr-1"></i> Prévisualiser
                </button>
              </div>
              <p v-if="urlError" class="text-sm text-red-500">{{ urlError }}</p>
              <p v-else class="form-hint">Collez le lien complet de la vidéo YouTube</p>
            </div>
          </div>

          <!-- Colonne de droite - Aperçu -->
          <div class="hidden md:block">
            <div class="sticky top-4 space-y-4">
              <h3 class="text-lg font-medium text-gray-700">Aperçu</h3>
              
              <div v-if="form.url && !urlError" class="border rounded-lg overflow-hidden">
                <iframe
                  :src="embedUrl"
                  class="w-full aspect-video"
                  frameborder="0"
                  allowfullscreen
                ></iframe>
                <div class="p-4 border-t">
                  <h4 class="font-medium">{{ form.title || 'Titre de la vidéo' }}</h4>
                  <p class="text-sm text-gray-500 mt-1">
                    {{ form.category_id ? getCategoryName(form.category_id) : 'Catégorie' }}
                  </p>
                </div>
              </div>
              
              <div v-else class="border-2 border-dashed rounded-lg aspect-video flex items-center justify-center bg-gray-50 text-gray-400">
                <div class="text-center p-4">
                  <i class="fas fa-video text-3xl mb-2"></i>
                  <p>Aperçu de la vidéo</p>
                  <p class="text-sm mt-1" v-if="urlError">URL YouTube invalide</p>
                  <p class="text-sm mt-1" v-else>Ajoutez une URL YouTube pour voir l'aperçu</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div class="form-group">
          <label class="form-label">Description</label>
          <div ref="editor" class="mt-1 border border-gray-300 rounded-lg overflow-hidden"></div>
          <p class="form-hint">Décrivez le contenu de votre vidéo en détail</p>
        </div>

        <!-- Statut et Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
          <div class="flex items-center space-x-4">
            <label class="flex items-center space-x-2 cursor-pointer">
              <input 
                type="radio" 
                v-model="form.status" 
                value="draft" 
                class="h-4 w-4 text-blue-600 focus:ring-blue-500"
              >
              <span>Brouillon</span>
            </label>
            <label class="flex items-center space-x-2 cursor-pointer">
              <input 
                type="radio" 
                v-model="form.status" 
                value="published" 
                class="h-4 w-4 text-blue-600 focus:ring-blue-500"
              >
              <span>Publier</span>
            </label>
          </div>

          <div class="flex space-x-3 w-full sm:w-auto">
            <button
              type="button"
              @click="resetForm"
              class="btn btn-secondary w-full sm:w-auto"
            >
              <i class="fas fa-undo mr-2"></i> Réinitialiser
            </button>
            <button
              type="submit"
              class="btn btn-primary w-full sm:w-auto"
              :disabled="loading || loadingCategories"
            >
              <template v-if="loading">
                <i class="fas fa-spinner fa-spin mr-2"></i> Enregistrement...
              </template>
              <template v-else>
                <i class="fas fa-save mr-2"></i> Enregistrer la vidéo
              </template>
            </button>
          </div>
        </div>
      </form>
    </section>

    <!-- Modal d'aperçu -->
    <Modal :show="showPreview" @close="showPreview = false" max-width="2xl">
      <div class="bg-white rounded-lg overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
          <h2 class="text-xl font-bold">Aperçu de la vidéo</h2>
          <button @click="showPreview = false" class="text-gray-400 hover:text-gray-500">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="p-4">
          <div class="aspect-w-16 aspect-h-9 bg-black rounded-lg overflow-hidden">
            <iframe
              :src="embedUrl"
              class="w-full h-full"
              frameborder="0"
              allowfullscreen
            ></iframe>
          </div>
          <div class="mt-4">
            <h3 class="text-lg font-semibold">{{ form.title || 'Titre de la vidéo' }}</h3>
            <div class="prose max-w-none mt-2" v-html="form.description || '<p class=\'text-gray-500 italic\'>Aucune description fournie</p>'"></div>
          </div>
        </div>
        <div class="p-4 border-t flex justify-end">
          <button
            @click="showPreview = false"
            class="btn btn-secondary"
          >
            Fermer
          </button>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import Modal from '@/Components/Modal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';

const editor = ref(null);
const windowLocation = ref(window.location.origin);
const categories = ref([]);
const loading = ref(false);
const loadingCategories = ref(false);
const showPreview = ref(false);
const urlError = ref('');

const form = ref({
  title: '',
  slug: '',
  category_id: '',
  url: '',
  description: '',
  status: 'draft'
});

const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Vidéos', url: route('admin.medias.videos.index') },
  { label: 'Création', active: true }
];

const embedUrl = computed(() => {
  if (!form.value.url || urlError.value) return '';
  const videoId = getYouTubeId(form.value.url);
  return videoId ? `https://www.youtube.com/embed/${videoId}?rel=0` : '';
});

onMounted(async () => {
  await fetchCategoriesByType('video');
  initEditor();
});

const fetchCategoriesByType = async (type) => {
  try {
    loadingCategories.value = true;
    const response = await axios.get(`/categories/type/${type}`);
    categories.value = response.data;
  } catch (error) {
    console.error('Erreur de chargement des catégories:', error);
    // Vous pouvez ajouter ici un système de notification/toast
  } finally {
    loadingCategories.value = false;
  }
};

const initEditor = () => {
  const quill = new Quill(editor.value, {
    theme: 'snow',
    placeholder: 'Décrivez votre vidéo...',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline'],
        [{ 'header': [2, 3, false] }],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['link', 'image'],
        ['clean']
      ]
    }
  });

  quill.on('text-change', () => {
    form.value.description = quill.root.innerHTML;
  });
};

const generateSlug = () => {
  if (!form.value.slug || form.value.slug === slugify(form.value.title)) {
    form.value.slug = slugify(form.value.title);
  }
};

const validateYoutubeUrl = () => {
  if (!form.value.url) {
    urlError.value = '';
    return;
  }
  
  const videoId = getYouTubeId(form.value.url);
  if (!videoId) {
    urlError.value = "L'URL YouTube n'est pas valide. Format attendu : https://www.youtube.com/watch?v=ID_VIDEO";
  } else {
    urlError.value = '';
  }
};

const getYouTubeId = (url) => {
  if (!url) return null;
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
  const match = url.match(regExp);
  return (match && match[2].length === 11) ? match[2] : null;
};

const getCategoryName = (id) => {
  const category = categories.value.find(c => c.id == id);
  return category ? category.name : 'Inconnue';
};

const resetForm = () => {
  form.value = {
    title: '',
    slug: '',
    category_id: '',
    url: '',
    description: '',
    status: 'draft'
  };
  urlError.value = '';
};

const submitVideo = async () => {
  if (urlError.value) return;
  
  loading.value = true;
  try {
    await router.post(route('admin.medias.videos.store'), form.value, {
      onSuccess: () => {
        // Redirection gérée par Inertia
      },
      onError: (errors) => {
        // Gestion des erreurs de validation
        if (errors.url) {
          urlError.value = errors.url;
        }
      }
    });
  } finally {
    loading.value = false;
  }
};

function slugify(text) {
  return text
    ?.toString()
    ?.normalize('NFD')
    ?.replace(/[\u0300-\u036f]/g, '')
    ?.toLowerCase()
    ?.trim()
    ?.replace(/\s+/g, '-')
    ?.replace(/[^\w-]+/g, '')
    ?.replace(/--+/g, '-') || '';
}
</script>

<style scoped>
.form-group {
  @apply space-y-1;
}
.form-label {
  @apply block text-sm font-medium text-gray-700;
}
.form-input {
  @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
}
.form-select {
  @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
}
.form-hint {
  @apply text-xs text-gray-500;
}
.btn {
  @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors;
}
.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white;
}
.btn-secondary {
  @apply bg-white border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-blue-500;
}
.prose :deep(h2) {
  @apply text-lg font-medium text-gray-900 mb-2;
}
.prose :deep(p) {
  @apply text-gray-700 mb-3;
}
.prose :deep(ul) {
  @apply list-disc pl-5 mb-3;
}
.ql-container {
  @apply h-64;
}
</style>