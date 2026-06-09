<template>
    <div class="mt-8">
        <!-- Grille des diaporamas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="diaporama in diaporamas" :key="diaporama.id" class="diaporama-card bg-white rounded-lg shadow-md overflow-hidden">

                <!-- Aperçu du diaporama -->
                <div
                    class="diaporama-container relative aspect-video bg-gray-200 bg-cover bg-center"
                    :style="{ backgroundImage: `url(${diaporama.images.length ? diaporama.images[0].url : defaultThumbnail})` }"
                ></div>

                <!-- Contenu du diaporama -->
                <div class="p-4">
                    <!-- Date -->
                    <span class="text-sm text-gray-500">{{ formatDate(diaporama.created_at) }}</span>

                    <!-- Description avec "Voir plus" -->
                    <div class="text-sm text-gray-700 mt-2">
                        <span v-if="!diaporama.expanded" v-html="truncateText(diaporama.title, 150)"></span>
                        <span v-else v-html="diaporama.title"></span>

                        <button
                            v-if="diaporama.title.length > 150"
                            @click="toggleDescription(diaporama)"
                            class="text-yellow-500 hover:underline ml-1">
                            {{ diaporama.expanded ? 'Voir moins' : 'Voir plus' }}
                        </button>
                    </div>

                    <!-- Bouton "Voir le diaporama" -->
                    <div class="mt-4">
                        <button
                            @click="viewDiaporama(diaporama)"
                            class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors duration-300"
                        >
                            <i class="fas fa-images mr-2"></i> Voir le diaporama
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton Voir Plus -->
        <div class="mt-8 text-center animate-fade-in delay-300">
            <Link
                :href="route('slideshows.all')"
                class="inline-flex items-center px-6 py-3 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition-colors duration-300"
            >
                Voir tous les diaporamas
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </Link>
        </div>

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
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Carousel, Slide, Navigation, Pagination } from 'vue3-carousel';
import 'vue3-carousel/dist/carousel.css';
import { Link } from '@inertiajs/vue3';

// Données des diaporamas
const diaporamas = ref([]);
const selectedDiaporama = ref(null);
const defaultThumbnail = ref('https://via.placeholder.com/300x200?text=No+Thumbnail');

// Formatage de la date
const formatDate = (dateString) => {
    if (!dateString) return "Date inconnue";
    const date = new Date(dateString);
    return isNaN(date.getTime()) ? "Date invalide" : date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};

// Tronquer le texte à une certaine longueur
const truncateText = (text, length) => {
    return text && text.length > length ? text.substring(0, length) + "..." : text;
};

// Basculer l'affichage de la description complète
const toggleDescription = (diaporama) => {
    diaporama.expanded = !diaporama.expanded;
};

// Récupération des diaporamas
const fetchDiaporamas = async () => {
    try {
        const response = await axios.get('/slideshows/latest');
        diaporamas.value = response.data.slideshows.map(diaporama => ({
            ...diaporama,
            expanded: false,
        }));
    } catch (error) {
        console.error("Erreur lors de la récupération des diaporamas :", error);
    }
};

// Voir le diaporama (ouvre le modal)
const viewDiaporama = (diaporama) => {
    selectedDiaporama.value = diaporama;
};

// Fermer le carrousel
const closeCarousel = () => {
    selectedDiaporama.value = null;
};

// Appeler fetchDiaporamas lorsque le composant est monté
onMounted(fetchDiaporamas);
</script>

<style scoped>
/* Animations */
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out forwards;
    opacity: 0;
}

/* Cartes */
.diaporama-card {
    transition: transform 0.2s ease-in-out;
}

.diaporama-card:hover {
    transform: translateY(-5px);
}

/* Conteneur des images */
.diaporama-container {
    height: 200px;
    background-size: cover;
    background-position: center;
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
