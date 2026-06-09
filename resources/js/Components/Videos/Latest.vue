<template>
    <div class="mt-8">
        <!-- Grille des vidéos -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="video in videos" :key="video.id" class="video-card bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Intégration de l'aperçu de la vidéo -->
                <div
                        class="video-container mt-4 relative aspect-video bg-gray-200 bg-cover bg-center flex items-center justify-center"
                        :style="{ backgroundImage: `url(https://img.youtube.com/vi/${getYouTubeVideoId(video.url)}/hqdefault.jpg)` }"
                    >
                        <a
                            :href="video.url"
                            target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-yellow-600 transition-colors duration-300"
                        >
                            <i class="fas fa-play mr-2"></i>
                        </a>
                </div>

                <!-- Contenu de la vidéo -->
                <div class="p-4">
                    <!-- Date -->
                    <span class="text-sm text-gray-500">{{ formatDate(video.created_at) }}</span>

                    <!-- Titre -->
                    <h3 class="text-lg font-semibold mt-2">{{ video.title }}</h3>

                    <!-- Description avec "Voir plus" -->
                    <div class="text-sm text-gray-700 mt-2">
                        <span v-if="!video.expanded" v-html="truncateText(video.description, 150)"></span>
                        <span v-else v-html="video.description"></span>

                        <button
                            v-if="video.description.length > 150"
                            @click="toggleDescription(video)"
                            class="text-yellow-500 hover:underline ml-1">
                            {{ video.expanded ? 'Voir moins' : 'Voir plus' }}
                        </button>
                    </div>

                    <!-- Bouton "Watch the Video" -->
                    <div class="mt-4">
                        <a
                            :href="video.url"
                            target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors duration-300"
                        >
                            <i class="fas fa-play mr-2"></i> Watch the Video
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton Voir Plus -->
        <div class="mt-8 text-center animate-fade-in delay-300">
            <Link
                :href="route('videos.all')"
                class="inline-flex items-center px-6 py-3 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition-colors duration-300"
            >
                Voir toutes les vidéos
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </Link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Link } from '@inertiajs/vue3';

// Données des vidéos
const videos = ref([]);
const defaultThumbnail = ref('https://via.placeholder.com/300x200?text=No+Thumbnail'); // Image par défaut

// Formatage de la date
const formatDate = (dateString) => {
    if (!dateString) return "Date inconnue";
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return "Date invalide";
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};

// Validation de l'URL
const isValidUrl = (url) => {
    try {
        new URL(url);
        return true;
    } catch (error) {
        return false;
    }
};

// Tronquer le texte à une certaine longueur
const truncateText = (text, length) => {
    if (!text) return "";
    return text.length > length ? text.substring(0, length) + "..." : text;
};

const getYouTubeVideoId = (url) => {
    const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/);
    return match ? match[1] : null;
};


// Basculer l'affichage de la description complète
const toggleDescription = (video) => {
    video.expanded = !video.expanded;
};

// Récupération des vidéos
const fetchVideos = async () => {
    try {
        const response = await axios.get('/videos/latest');
        videos.value = response.data.videos.map(video => ({
            ...video,
            expanded: false, // Ajoute une propriété pour gérer l'affichage
            thumbnail: video.url || null // Ajoute la miniature si elle est disponible
        }));
    } catch (error) {
        console.error("Erreur lors de la récupération des vidéos :", error);
    }
};

// Appeler fetchVideos lorsque le composant est monté
onMounted(() => {
    fetchVideos();
});
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

.video-card {
    transition: transform 0.2s ease-in-out;
}

.video-card:hover {
    transform: translateY(-5px);
}

.video-container {
    position: relative;
    overflow: hidden;
}

.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
</style>
