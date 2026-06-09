<template>
  <AppLayout>
    <PageTitle title="Médiathèque" :breadcrumbs="breadcrumbs">
      <template #actions>
        <div class="flex space-x-2">
          <Link 
            :href="route('admin.medias.videos.create')" 
            class="btn btn-primary"
            v-if="activeTab === 'videos'"
          >
            <i class="fas fa-video mr-2"></i> Nouvelle vidéo
          </Link>
          <Link 
            :href="route('admin.medias.slideshows.create')" 
            class="btn btn-primary"
            v-if="activeTab === 'diaporamas'"
          >
            <i class="fas fa-images mr-2"></i> Nouveau diaporama
          </Link>
        </div>
      </template>
    </PageTitle>

    <section class="bg-white rounded-xl shadow-sm p-6">
      <!-- Navigation par onglets -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.name"
            @click="switchTab(tab.name)"
            :class="[
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === tab.name
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <i :class="tab.icon" class="mr-2"></i>
            {{ tab.label }}
            <span class="ml-1 bg-gray-100 text-gray-600 rounded-full px-2 py-0.5 text-xs">
              {{ tab.count }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Contenu des onglets -->
      <div v-if="activeTab === 'videos'">
        <!-- Filtres vidéos -->
        <div class="mb-6">
          <div class="relative max-w-md mb-4">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input
              v-model="videoFilters.search"
              type="text"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              placeholder="Rechercher une vidéo..."
            >
          </div>

          <div class="flex flex-wrap gap-4">
            <div class="w-full sm:w-48">
              <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
              <select 
                v-model="videoFilters.category"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="">Toutes catégories</option>
                <option 
                  v-for="category in videoCategories"
                  :key="category.id" 
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
            </div>

            <div class="w-full sm:w-48">
              <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
              <select 
                v-model="videoFilters.status"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="">Tous statuts</option>
                <option value="draft">Brouillon</option>
                <option value="published">Publié</option>
                <option value="archived">Archivé</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Liste vidéos -->
        <div v-if="filteredVideos.length > 0" class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
          <Link 
            v-for="video in filteredVideos" 
            :key="video.id" 
            :href="route('admin.medias.videos.edit', video.id)"
            class="border rounded-lg overflow-hidden hover:shadow-md transition-shadow"
          >
            <div class="relative aspect-video bg-gray-100">
              <img 
                :src="`https://img.youtube.com/vi/${getVideoId(video.url)}/mqdefault.jpg`" 
                :alt="video.title"
                class="w-full h-full object-cover"
              >
            </div>
            <div class="p-4">
              <h3 class="font-medium text-gray-900 mb-1">{{ video.title }}</h3>
              <div class="flex justify-between items-center text-sm text-gray-500">
                <span>{{ video.category?.name || 'Sans catégorie' }}</span>
                <span :class="statusClasses(video.status)">{{ formatStatus(video.status) }}</span>
              </div>
            </div>
          </Link>
        </div>
        <div v-else class="py-12 text-center">
          <i class="far fa-file-video text-4xl text-gray-300 mb-4"></i>
          <p class="text-gray-500">Aucune vidéo trouvée</p>
        </div>
      </div>

      <div v-if="activeTab === 'diaporamas'">
        <!-- Filtres diaporamas -->
        <div class="mb-6">
          <div class="relative max-w-md mb-4">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input
              v-model="slideshowFilters.search"
              type="text"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              placeholder="Rechercher un diaporama..."
            >
          </div>

          <div class="flex flex-wrap gap-4">
            <div class="w-full sm:w-48">
              <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
              <select 
                v-model="slideshowFilters.category"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="">Toutes catégories</option>
                <option 
                  v-for="category in slideshowCategories"
                  :key="category.id" 
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
            </div>

            <div class="w-full sm:w-48">
              <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
              <select 
                v-model="slideshowFilters.status"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="">Tous statuts</option>
                <option value="draft">Brouillon</option>
                <option value="published">Publié</option>
                <option value="archived">Archivé</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Liste diaporamas -->
        <div v-if="filteredSlideshows.length > 0" class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
          <Link 
            v-for="slideshow in filteredSlideshows" 
            :key="slideshow.id" 
            :href="route('admin.medias.slideshows.edit', slideshow.id)"
            class="border rounded-lg overflow-hidden hover:shadow-md transition-shadow"
          >
            <div class="relative aspect-video bg-gray-100">
              <img 
                v-if="slideshow.cover_image"
                :src="slideshow.cover_image" 
                :alt="slideshow.title"
                class="w-full h-full object-cover"
              >
              <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                <i class="fas fa-images text-4xl"></i>
              </div>
            </div>
            <div class="p-4">
              <h3 class="font-medium text-gray-900 mb-1">{{ slideshow.title }}</h3>
              <div class="flex justify-between items-center text-sm text-gray-500">
                <span>{{ slideshow.category?.name || 'Sans catégorie' }}</span>
                <span :class="statusClasses(slideshow.status)">{{ formatStatus(slideshow.status) }}</span>
              </div>
            </div>
          </Link>
        </div>
        <div v-else class="py-12 text-center">
          <i class="far fa-images text-4xl text-gray-300 mb-4"></i>
          <p class="text-gray-500">Aucun diaporama trouvé</p>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';

const props = defineProps({
  videos: {
    type: Array,
    default: () => []
  },
  slideshows: {
    type: Array,
    default: () => []
  },
  initialTab: {
    type: String,
    default: 'videos'
  }
});

// États
const activeTab = ref(props.initialTab);
const videoFilters = ref({
  search: '',
  category: '',
  status: ''
});
const slideshowFilters = ref({
  search: '',
  category: '',
  status: ''
});

// Computed
const tabs = computed(() => [
  {
    name: 'videos',
    label: 'Vidéos',
    icon: 'fas fa-video',
    count: props.videos.length
  },
  {
    name: 'diaporamas',
    label: 'Diaporamas',
    icon: 'fas fa-images',
    count: props.slideshows.length
  }
]);

const videoCategories = computed(() => {
  const categories = props.videos.map(v => v.category).filter(Boolean);
  return [...new Map(categories.map(item => [item.id, item])).values()];
});

const slideshowCategories = computed(() => {
  const categories = props.slideshows.map(s => s.category).filter(Boolean);
  return [...new Map(categories.map(item => [item.id, item])).values()];
});

const filteredVideos = computed(() => {
  return props.videos.filter(video => {
    const matchesSearch = video.title.toLowerCase().includes(videoFilters.value.search.toLowerCase());
    const matchesCategory = videoFilters.value.category ? video.category?.id == videoFilters.value.category : true;
    const matchesStatus = videoFilters.value.status ? video.status === videoFilters.value.status : true;
    return matchesSearch && matchesCategory && matchesStatus;
  });
});

const filteredSlideshows = computed(() => {
  return props.slideshows.filter(slideshow => {
    const matchesSearch = slideshow.title.toLowerCase().includes(slideshowFilters.value.search.toLowerCase());
    const matchesCategory = slideshowFilters.value.category ? slideshow.category?.id == slideshowFilters.value.category : true;
    const matchesStatus = slideshowFilters.value.status ? slideshow.status === slideshowFilters.value.status : true;
    return matchesSearch && matchesCategory && matchesStatus;
  });
});

// Méthodes
const switchTab = (tab) => {
  activeTab.value = tab;
  router.get(route('admin.medias.index'), { tab }, {
    preserveState: true,
    preserveScroll: true
  });
};

const getVideoId = (url) => {
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
  const match = url.match(regExp);
  return (match && match[2].length === 11) ? match[2] : '';
};

const formatStatus = (status) => {
  const statusMap = {
    draft: 'Brouillon',
    published: 'Publié',
    archived: 'Archivé'
  };
  return statusMap[status] || status;
};

const statusClasses = (status) => {
  return {
    'text-yellow-600': status === 'draft',
    'text-green-600': status === 'published',
    'text-gray-600': status === 'archived'
  };
};

// Breadcrumbs
const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Médiathèque', active: true }
];
</script>

<style scoped>
/* Styles personnalisés si nécessaire */
</style>