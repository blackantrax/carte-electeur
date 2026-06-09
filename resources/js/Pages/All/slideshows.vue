<template>
    <FrontLayout>
        <Head title="Tous les photos" />
        <PageTitle title="Tous les photos" :breadcrumbs="breadcrumbs" />

        <div class="container mx-auto px-4 py-8">
            <!-- Chargement des catégories -->
            <div v-if="isLoadingCategories" class="text-center py-4">
                <span class="loader"></span> <span>Chargement des catégories...</span>
            </div>

            <!-- Message d'erreur des catégories -->
            <div v-if="errorCategories" class="text-center text-red-500 py-4">
                {{ errorCategories }}
            </div>

            <!-- Filtres -->
            <Filter :categories="categories" v-model:filters="filters" v-model:viewMode="viewMode" />

            <!-- Chargement des photos -->
            <div v-if="isLoadingSlideshows" class="text-center py-4">
                <span class="loader"></span> <span>Chargement des photos...</span>
            </div>

            <!-- Message d'erreur des photos -->
            <div v-if="errorSlideshows" class="text-center text-red-500 py-4">
                {{ errorSlideshows }}
            </div>

            <template v-else>
                <div v-if="slideshows.data.length > 0">
                    <!-- Affichage en grille -->
                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <SlideshowCard v-for="slideshow in slideshows.data"
                        :key="slideshow.id"
                        :diaporama="slideshow"
                        @view="viewDiaporama"
                         />
                    </div>

                    <!-- Affichage en liste -->
                    <div v-else class="flex flex-col gap-4">
                        <SlideshowListItem v-for="slideshow in slideshows.data" :key="slideshow.id" :diaporama="slideshow" @view="viewDiaporama"/>
                    </div>
                </div>

                <div v-else class="text-center text-gray-600 py-8">
                    Aucun vidéo trouvé.
                </div>

                <!-- Pagination -->
                <nav v-if="slideshows.links?.length" class="mt-8 flex justify-center">
                    <Link v-for="(link, index) in slideshows.links"
                          :key="index"
                          :href="link.url"
                          class="pagination-link"
                          :class="{ 'active': link.active }"
                          v-html="link.label">
                    </Link>
                </nav>
            </template>
            <!-- MODAL CARROUSEL -->
        <div v-if="selectedDiaporama" class="fixed inset-0 flex items-center justify-center bg-white bg-opacity-30 z-50">
            <div class="relative bg-white p-6 rounded-lg w-3/4 max-h-[85vh] overflow-hidden shadow-xl">
                <button @click="closeCarousel" class="close-button">✖</button>

                <h2 class="text-2xl font-bold mb-4 text-center text-gray-700">{{ selectedDiaporama.title }}</h2>

                <carousel :items-to-show="1" :wrap-around="true" :autoplay="3000">
                    <template #addons>
                        <navigation />
                        <pagination />
                    </template>

                    <slide v-for="image in selectedDiaporama.images" :key="image.id">
                        <div class="carousel-slide">
                            <img :src="image.url" class="carousel-image" />
                            <div class="carousel-description">
                                <p>{{ image.description }}</p>
                            </div>
                        </div>
                    </slide>
                </carousel>
            </div>
        </div>
        </div>
    </FrontLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import FrontLayout from '@/Layouts/FrontLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import SlideshowCard from '@/Components/Slideshows/Card.vue';
import SlideshowListItem from '@/Components/Slideshows/List.vue';
import Filter from '@/Components/Filter.vue';
import { Head, Link } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Carousel, Slide, Navigation, Pagination } from 'vue3-carousel';
import 'vue3-carousel/dist/carousel.css';

// Breadcrumbs
const breadcrumbs = [
    { label: 'Accueil', url: '/', active: false },
    { label: 'photos', url: '/slideshows', active: false },
    { label: 'Tous les photos', active: true }
];

// États réactifs
const categories = ref([]);
const filters = ref({ search: '', category: '', year: '' });
const viewMode = ref('grid');
const slideshows = ref({ data: [], links: [] });
const isLoadingCategories = ref(false);
const isLoadingSlideshows = ref(false);
const errorCategories = ref('');
const errorSlideshows = ref('');
const selectedDiaporama = ref(null);

// Récupération des catégories
const fetchCategoriesByType = async (type) => {
    isLoadingCategories.value = true;
    errorCategories.value = '';
    try {
        const response = await axios.get(`/categories/type/${type}`);
        categories.value = response.data;
    } catch (error) {
        console.error('Erreur de chargement des catégories:', error);
        errorCategories.value = 'Erreur lors du chargement des catégories.';
    } finally {
        isLoadingCategories.value = false;
    }
};

// Récupération des diaporamas avec filtres côté serveur
const fetchSlideshows = async () => {
    isLoadingSlideshows.value = true;
    errorSlideshows.value = '';
    try {
        const response = await axios.get('/slideshows/allitems', { params: filters.value });
        slideshows.value = response.data?.slideshows ?? { data: [], links: [] };
    } catch (error) {
        console.error('Erreur de chargement des photos:', error);
        errorSlideshows.value = error.response?.data?.error || 'Erreur lors du chargement des photos.';
    } finally {
        isLoadingSlideshows.value = false;
    }
};

// Déclenchement avec debounce pour éviter les appels excessifs
const debouncedFetchSlideshows = debounce(fetchSlideshows, 500);

// Réaction aux changements des filtres
watch(filters, debouncedFetchSlideshows, { deep: true });

// ✅ Correction : Ajouter `viewDiaporama`
const viewDiaporama = (diaporama) => {
    selectedDiaporama.value = diaporama;
};

// Fermer le carrousel
const closeCarousel = () => {
    selectedDiaporama.value = null;
};

// Chargement initial
onMounted(async () => {
    await fetchCategoriesByType('photo');
    await fetchSlideshows();
});
</script>

<style scoped>
.loader {
    display: inline-block;
    width: 24px;
    height: 24px;
    border: 4px solid #3490dc;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.pagination-link {
    padding: 8px 12px;
    border: 1px solid #ddd;
    background: white;
    text-decoration: none;
    color: #333;
    transition: background 0.3s;
}

.pagination-link:hover {
    background: #f1f1f1;
}

.pagination-link.active {
    background: #f1c40f;
    color: white;
    font-weight: bold;
}


/* Carrousel */
.carousel-slide {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.carousel-image {
  width: 100%;
  height: 400px;
  object-fit: cover;
  border-radius: 12px;
  transition: transform 0.3s ease-in-out;
}
.carousel-image:hover {
  transform: scale(1.03);
}

/* Description en bas de l'image */
.carousel-description {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  background: rgba(0, 0, 0, 0.5);
  color: white;
  padding: 10px;
  font-size: 14px;
  text-align: center;
}

/* Bouton de fermeture */
.close-button {
    position: absolute;
    top: 10px;
    right: 10px;
    background: transparent;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #333;
}
.close-button:hover {
    color: #000;
}
</style>
