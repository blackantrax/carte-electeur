<template>
  <AppLayout>
    <PageTitle title="Créer un Diaporama" :breadcrumbs="breadcrumbs">
      <template #actions>
        <Link 
          :href="route('admin.medias.videos.index')" 
          class="btn btn-secondary mr-2"
        >
          <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
        </Link>
        <button 
          type="button"
          @click="showPreview"
          class="btn btn-secondary mr-2"
          :disabled="slideshow.images.length === 0"
        >
          <i class="fas fa-eye mr-2"></i>Aperçu
        </button>
        <button 
          type="submit"
          form="slideshow-form"
          class="btn btn-primary"
          :disabled="loading || !isFormValid"
        >
          <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
          <i v-else class="fas fa-save mr-2"></i>
          {{ loading ? 'Enregistrement...' : 'Enregistrer' }}
        </button>
      </template>
    </PageTitle>

    <section class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
      <!-- Progress bar -->
      <div class="h-1 bg-gray-200 dark:bg-gray-700">
        <div 
          class="h-full bg-blue-500 transition-all duration-300"
          :style="{ width: `${formProgress}%` }"
        ></div>
      </div>

      <form 
        id="slideshow-form"
        @submit.prevent="submitSlideshow"
        class="p-6 space-y-6"
      >
        <!-- Section Informations de base -->
        <div class="space-y-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
            <span class="flex items-center justify-center w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full mr-3 text-sm">1</span>
            Informations de base
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Titre -->
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 required">
                Titre du diaporama
              </label>
              <input
                id="title"
                v-model="slideshow.title"
                type="text"
                class="form-input w-full"
                placeholder="Mon super diaporama"
                required
                @input="generateSlug"
              >
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Donnez un titre clair et descriptif à votre diaporama
              </p>
            </div>

            <!-- Slug -->
            <div>
              <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 required">
                URL personnalisée
              </label>
              <div class="flex rounded-md shadow-sm">
                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 text-sm">
                  {{ baseUrl }}/diaporamas/
                </span>
                <input
                  id="slug"
                  v-model="slideshow.slug"
                  type="text"
                  class="form-input flex-1 rounded-l-none"
                  placeholder="mon-super-diaporama"
                  required
                  pattern="[a-z0-9-]+"
                  title="Ne doit contenir que des lettres minuscules, chiffres et tirets"
                >
              </div>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                L'URL doit être unique et ne contenir que des lettres minuscules, chiffres et tirets
              </p>
            </div>

            <!-- Catégorie -->
            <div>
              <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 required">
                Catégorie
              </label>
              <select
                id="category_id"
                v-model="slideshow.category_id"
                class="form-select w-full"
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
            </div>

            <!-- Statut -->
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 required">
                Statut
              </label>
              <select
                id="status"
                v-model="slideshow.status"
                class="form-select w-full"
                required
              >
                <option value="draft">Brouillon</option>
                <option value="published">Publié</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Section Images -->
        <div class="space-y-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
            <span class="flex items-center justify-center w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full mr-3 text-sm">2</span>
            Images du diaporama
            <span class="ml-auto text-sm font-normal text-gray-500 dark:text-gray-400">
              {{ slideshow.images.length }} image(s)
            </span>
          </h3>

          <!-- Zone de dépôt -->
          <div 
            @dragover.prevent="dragOver = true"
            @dragleave="dragOver = false"
            @drop.prevent="handleDrop"
            class="border-2 border-dashed rounded-lg p-8 text-center transition-colors"
            :class="{
              'border-blue-500 bg-blue-50 dark:bg-blue-900/20': dragOver,
              'border-gray-300 dark:border-gray-600': !dragOver
            }"
          >
            <div class="max-w-md mx-auto">
              <i class="fas fa-images text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                Glissez-déposez vos images ici
              </h4>
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Formats supportés : JPG, PNG, WEBP (max 5MB par image)
              </p>
              <label 
                for="image-upload"
                class="btn btn-secondary cursor-pointer"
              >
                <i class="fas fa-upload mr-2"></i>
                Sélectionner des fichiers
                <input 
                  id="image-upload"
                  type="file"
                  multiple
                  accept="image/jpeg, image/png, image/webp"
                  class="hidden"
                  @change="handleFileSelect"
                >
              </label>
            </div>
          </div>

          <!-- Liste des images -->
          <div v-if="slideshow.images.length > 0" class="space-y-4">
            <div 
              v-for="(image, index) in slideshow.images" 
              :key="image.id"
              class="border rounded-lg overflow-hidden transition-all hover:shadow-md"
            >
              <div class="flex flex-col sm:flex-row">
                <!-- Miniature -->
                <div class="w-full sm:w-32 h-32 flex-shrink-0 relative bg-gray-100 dark:bg-gray-700">
                  <img 
                    :src="image.preview" 
                    class="w-full h-full object-cover"
                    :alt="`Prévisualisation image ${index + 1}`"
                  >
                  <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs px-2 py-1 truncate">
                    {{ image.file.name }}
                  </div>
                  <button 
                    type="button"
                    @click="removeImage(index)"
                    class="absolute top-2 right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors"
                    aria-label="Supprimer cette image"
                  >
                    <i class="fas fa-times text-xs"></i>
                  </button>
                </div>

                <!-- Détails -->
                <div class="flex-1 p-4">
                  <div class="mb-4">
                    <label :for="`description-${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Description de l'image
                    </label>
                    <textarea
                      :id="`description-${index}`"
                      v-model="image.description"
                      class="form-textarea w-full"
                      rows="2"
                      placeholder="Décrivez cette image..."
                    ></textarea>
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ formatFileSize(image.file.size) }}
                      <span v-if="image.error" class="text-red-500 ml-2">{{ image.error }}</span>
                    </div>
                    <div class="flex space-x-2">
                      <button 
                        type="button"
                        @click="moveImage(index, index - 1)"
                        :disabled="index === 0"
                        class="w-8 h-8 flex items-center justify-center rounded-md border border-gray-300 text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                        :title="index === 0 ? 'Déjà en première position' : 'Déplacer vers le haut'"
                      >
                        <i class="fas fa-arrow-up"></i>
                      </button>
                      <button 
                        type="button"
                        @click="moveImage(index, index + 1)"
                        :disabled="index === slideshow.images.length - 1"
                        class="w-8 h-8 flex items-center justify-center rounded-md border border-gray-300 text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                        :title="index === slideshow.images.length - 1 ? 'Déjà en dernière position' : 'Déplacer vers le bas'"
                      >
                        <i class="fas fa-arrow-down"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </section>

    <!-- Modal d'aperçu -->
    <TransitionRoot appear :show="showModal" as="template">
      <Dialog as="div" class="relative z-50" @close="closeModal">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-4xl transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left align-middle shadow-xl transition-all">
                <!-- En-tête -->
                <div class="bg-blue-500 dark:bg-blue-600 px-6 py-4 flex items-center justify-between">
                  <DialogTitle as="h3" class="text-lg font-medium text-white">
                    Aperçu du diaporama
                  </DialogTitle>
                  <button 
                    @click="closeModal"
                    class="text-white hover:text-gray-200 transition-colors"
                    aria-label="Fermer"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>

                <!-- Contenu -->
                <div class="p-6">
                  <div class="mb-6">
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                      {{ slideshow.title || 'Titre du diaporama' }}
                    </h4>
                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 space-x-4">
                      <span>
                        <i class="fas fa-layer-group mr-1"></i>
                        {{ slideshow.images.length }} image(s)
                      </span>
                      <span>
                        <i class="fas fa-clock mr-1"></i>
                        {{ estimatedDuration }} min de lecture
                      </span>
                    </div>
                  </div>

                  <!-- Carousel -->
                  <div v-if="slideshow.images.length > 0" class="rounded-xl overflow-hidden shadow-lg">
                    <Swiper
                      :modules="[SwiperAutoplay, SwiperPagination, SwiperNavigation]"
                      :slides-per-view="1"
                      :loop="true"
                      :autoplay="{
                        delay: 3000,
                        disableOnInteraction: false
                      }"
                      :pagination="{
                        clickable: true,
                        dynamicBullets: true
                      }"
                      :navigation="true"
                      class="aspect-video"
                    >
                      <SwiperSlide v-for="(image, index) in slideshow.images" :key="index">
                        <div class="relative h-full w-full">
                          <img 
                            :src="image.preview" 
                            class="w-full h-full object-cover"
                            :alt="image.description || `Image ${index + 1} du diaporama`"
                          >
                          <div 
                            v-if="image.description"
                            class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-6 text-white"
                          >
                            <p class="text-lg font-medium">{{ image.description }}</p>
                          </div>
                        </div>
                      </SwiperSlide>
                    </Swiper>
                  </div>

                  <div v-else class="text-center py-12">
                    <i class="fas fa-images text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400">
                      Aucune image à afficher. Ajoutez des images pour prévisualiser votre diaporama.
                    </p>
                  </div>
                </div>

                <!-- Pied de page -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end">
                  <button
                    type="button"
                    @click="closeModal"
                    class="btn btn-primary"
                  >
                    Fermer l'aperçu
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { Link, router } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay as SwiperAutoplay, Pagination as SwiperPagination, Navigation as SwiperNavigation } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import 'swiper/css/navigation'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageTitle from '@/Components/PageTitle.vue'

const toast = useToast()

// Données du formulaire
const slideshow = ref({
  title: '',
  slug: '',
  category_id: '',
  status: 'draft',
  images: []
})

const baseUrl = ref(window.location.origin)
const categories = ref([])
const loading = ref(false)
const loadingCategories = ref(false)
const showModal = ref(false)
const dragOver = ref(false)

// Breadcrumbs
const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Médiathèque', url: route('admin.medias.index') },
  { label: 'Créer un diaporama', active: true }
]

// Calculs
const formProgress = computed(() => {
  let progress = 0
  if (slideshow.value.title) progress += 25
  if (slideshow.value.slug) progress += 25
  if (slideshow.value.category_id) progress += 25
  if (slideshow.value.images.length > 0) progress += 25
  return progress
})

const isFormValid = computed(() => {
  return (
    slideshow.value.title &&
    slideshow.value.slug &&
    slideshow.value.category_id &&
    slideshow.value.images.length > 0
  )
})

const estimatedDuration = computed(() => {
  const imagesCount = slideshow.value.images.length
  return Math.ceil(imagesCount * 0.5) // Estimation 30s par image
})

// Méthodes
const fetchCategoriesByType = async (type) => {
  try {
    loadingCategories.value = true;
    const response = await axios.get(`/categories/type/${type}`);
    categories.value = response.data;
  } catch (error) {
    toast.error('Erreur lors du chargement des catégories')
    console.error('Erreur de chargement des catégories:', error);
    // Vous pouvez ajouter ici un système de notification/toast
  } finally {
    loadingCategories.value = false;
  }
};

const generateSlug = () => {
  if (!slideshow.value.slug) {
    slideshow.value.slug = slideshow.value.title
      ?.toLowerCase()
      .normalize('NFD') // Normaliser les caractères accentués
      .replace(/[\u0300-\u036f]/g, '') // Supprimer les accents
      .replace(/[^a-z0-9\s-]/g, '') // Supprimer les caractères spéciaux
      .replace(/\s+/g, '-') // Remplacer les espaces par des tirets
      .replace(/-+/g, '-') // Supprimer les tirets multiples
      .substring(0, 50) // Limiter la longueur
  }
}

const validateImage = (file) => {
  const validTypes = ['image/jpeg', 'image/png', 'image/webp']
  const maxSize = 5 * 1024 * 1024 // 5MB

  if (!validTypes.includes(file.type)) {
    return `Le format ${file.type} n'est pas supporté`
  }

  if (file.size > maxSize) {
    return `L'image dépasse la taille maximale de 5MB`
  }

  return null
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  if (files.length === 0) return

  files.forEach(file => {
    const error = validateImage(file)
    if (error) {
      toast.warning(`"${file.name}": ${error}`)
      return
    }

    slideshow.value.images.push({
      file,
      description: '',
      preview: URL.createObjectURL(file),
      id: Date.now() + Math.random().toString(36).substring(2, 9),
      error: null
    })
  })

  event.target.value = '' // Reset input pour permettre la sélection des mêmes fichiers
}

const handleDrop = (event) => {
  dragOver.value = false
  handleFileSelect({ target: { files: event.dataTransfer.files } })
}

const removeImage = (index) => {
  // Libérer l'URL de l'image prévisualisée
  URL.revokeObjectURL(slideshow.value.images[index].preview)
  slideshow.value.images.splice(index, 1)
  toast.success('Image supprimée')
}

const moveImage = (fromIndex, toIndex) => {
  if (toIndex < 0 || toIndex >= slideshow.value.images.length) return
  
  const imageToMove = slideshow.value.images[fromIndex]
  slideshow.value.images.splice(fromIndex, 1)
  slideshow.value.images.splice(toIndex, 0, imageToMove)
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

const showPreview = () => {
  if (slideshow.value.images.length === 0) {
    toast.warning('Ajoutez des images pour prévisualiser le diaporama')
    return
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const submitSlideshow = async () => {
  if (!isFormValid.value) {
    toast.warning('Veuillez remplir tous les champs obligatoires')
    return
  }

  loading.value = true

  try {
    const formData = new FormData()
    formData.append('title', slideshow.value.title)
    formData.append('slug', slideshow.value.slug)
    formData.append('category_id', slideshow.value.category_id)
    formData.append('status', slideshow.value.status)

    slideshow.value.images.forEach((image, index) => {
      formData.append(`images[${index}]`, image.file)
      formData.append(`descriptions[${index}]`, image.description)
    })

    const response = await axios.post(route('admin.medias.slideshows.store'), formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    toast.success('Diaporama créé avec succès')
    router.visit(route('admin.medias.slideshows.index', response.data.id))
  } catch (error) {
    console.error(error)
    
    // Gestion des erreurs de validation
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      Object.keys(errors).forEach(key => {
        toast.error(errors[key][0])
      })
    } else {
      toast.error(error.response?.data?.message || 'Erreur lors de la création du diaporama')
    }
  } finally {
    loading.value = false
  }
}

// Initialisation
onMounted(async () => {
  await fetchCategoriesByType('photo');
})
</script>

<style scoped>
.required:after {
  content: " *";
  color: #ef4444;
}

.form-input,
.form-select,
.form-textarea {
  @apply w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white;
}

.btn {
  @apply inline-flex items-center px-4 py-2 border border-transparent rounded-md font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors;
}

.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700;
}

.btn-secondary {
  @apply bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600;
}
</style>