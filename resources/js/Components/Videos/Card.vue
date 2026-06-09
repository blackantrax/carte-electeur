<template>
    <div class="video-card bg-white rounded-lg shadow-md overflow-hidden mt-4">
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
                <span v-if="!expanded" v-html="truncateText(video.description, 150)"></span>
                <span v-else v-html="video.description"></span>

                <button
                    v-if="video.description.length > 150"
                    @click="toggleDescription"
                    class="text-yellow-500 hover:underline ml-1">
                    {{ expanded ? 'Voir moins' : 'Voir plus' }}
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
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    video: Object
});

const expanded = ref(false);

// Basculer l'affichage de la description complète
const toggleDescription = () => {
    expanded.value = !expanded.value;
};

// Tronquer le texte à une certaine longueur
const truncateText = (text, length) => {
    if (!text) return "";
    return text.length > length ? text.substring(0, length) + "..." : text;
};

// Extraire l'ID de la vidéo YouTube
const getYouTubeVideoId = (url) => {
    const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/);
    return match ? match[1] : null;
};

// Formatage de la date
const formatDate = (dateString) => {
    if (!dateString) return "Date inconnue";
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return "Date invalide";
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<style scoped>
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
</style>
