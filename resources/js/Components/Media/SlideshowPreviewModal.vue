<template>
  <TransitionRoot appear :show="!!slideshow" as="template">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-4xl transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-xl transition-all">
              <!-- En-tête -->
              <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-4 flex items-center justify-between">
                <DialogTitle as="h3" class="text-lg font-medium text-white">
                  Aperçu du diaporama
                </DialogTitle>
                <button 
                  @click="$emit('close')"
                  class="text-white hover:text-blue-200 transition-colors"
                  aria-label="Fermer"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <!-- Contenu -->
              <div class="p-6">
                <div class="mb-6">
                  <h4 class="text-xl font-bold text-gray-900 mb-2">
                    {{ slideshow.title || 'Titre du diaporama' }}
                  </h4>
                  <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                    <span class="flex items-center">
                      <i class="fas fa-images mr-1"></i>
                      {{ slideshow.images?.length || 0 }} image(s)
                    </span>
                    <span class="flex items-center">
                      <i class="fas fa-calendar-alt mr-1"></i>
                      Créé le {{ formatDate(slideshow.created_at) }}
                    </span>
                    <span class="flex items-center">
                      <i class="fas fa-layer-group mr-1"></i>
                      {{ slideshow.category?.name || 'Non catégorisé' }}
                    </span>
                    <span 
                      class="flex items-center px-2 py-1 rounded-full text-xs font-medium"
                      :class="{
                        'bg-green-100 text-green-800': slideshow.status === 'published',
                        'bg-yellow-100 text-yellow-800': slideshow.status === 'draft',
                        'bg-gray-100 text-gray-800': slideshow.status === 'archived'
                      }"
                    >
                      {{ getStatusLabel(slideshow.status) }}
                    </span>
                  </div>
                </div>

                <!-- Carousel -->
                <div v-if="slideshow.images?.length > 0" class="rounded-xl overflow-hidden shadow-lg border border-gray-200">
                  <Swiper
                    :modules="[SwiperAutoplay, SwiperPagination, SwiperNavigation]"
                    :slides-per-view="1"
                    :loop="true"
                    :autoplay="{
                      delay: 5000,
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
                          :src="image.url" 
                          class="w-full h-full object-cover"
                          :alt="image.description || `Image ${index + 1} du diaporama`"
                          loading="lazy"
                        />
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

                <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
                  <i class="fas fa-images text-4xl text-gray-300 mb-4"></i>
                  <p class="text-gray-500">
                    Ce diaporama ne contient aucune image
                  </p>
                </div>

                <!-- Description -->
                <div v-if="slideshow.description" class="mt-6 p-4 bg-gray-50 rounded-lg">
                  <h4 class="text-sm font-medium text-gray-500 mb-2">Description</h4>
                  <p class="text-gray-700 whitespace-pre-line">{{ slideshow.description }}</p>
                </div>
              </div>

              <!-- Pied de page -->
              <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                <Link 
                  v-if="slideshow.id"
                  :href="route('admin.medias.slideshows.edit', { slideshow: slideshow.id })"
                  class="btn btn-secondary"
                >
                  <i class="fas fa-edit mr-2"></i> Modifier
                </Link>
                <button
                  @click="$emit('close')"
                  class="btn btn-primary"
                >
                  <i class="fas fa-check mr-2"></i> Fermer
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay as SwiperAutoplay, Pagination as SwiperPagination, Navigation as SwiperNavigation } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import 'swiper/css/navigation'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  slideshow: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close'])

const getStatusLabel = (status) => {
  const statusMap = {
    draft: 'Brouillon',
    published: 'Publié',
    archived: 'Archivé'
  }
  return statusMap[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}
</script>

<style scoped>
/* Animation de transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Style personnalisé pour la navigation du carousel */
:deep(.swiper-button-next),
:deep(.swiper-button-prev) {
  color: white;
  background: rgba(0, 0, 0, 0.5);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  transition: all 0.3s ease;
}

:deep(.swiper-button-next):hover,
:deep(.swiper-button-prev):hover {
  background: rgba(0, 0, 0, 0.8);
}

:deep(.swiper-button-next):after,
:deep(.swiper-button-prev):after {
  font-size: 1rem;
}

:deep(.swiper-pagination-bullet) {
  background: white;
  opacity: 0.6;
}

:deep(.swiper-pagination-bullet-active) {
  background: #3b82f6;
  opacity: 1;
}
</style>