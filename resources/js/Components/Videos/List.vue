<template>
    <div class="video-list-item flex items-start bg-white rounded-lg shadow-md overflow-hidden p-4 mt-4">
        <!-- Aperçu vidéo -->
        <div
            class="video-thumbnail w-40 h-24 flex-shrink-0 bg-gray-200 bg-cover bg-center"
            :style="{ backgroundImage: `url(https://img.youtube.com/vi/${getYouTubeVideoId(video.url)}/hqdefault.jpg)` }"
        >
            <a
                :href="video.url"
                target="_blank"
                class="flex items-center justify-center w-full h-full bg-black bg-opacity-50 hover:bg-opacity-70 transition duration-300"
            >
                <i class="fas fa-play text-white text-2xl"></i>
            </a>
        </div>

        <!-- Contenu de la vidéo -->
        <div class="ml-4 flex-1">
            <!-- Date -->
            <span class="text-sm text-gray-500">{{ formatDate(video.created_at) }}</span>

            <!-- Titre -->
            <h3 class="text-lg font-semibold mt-1">{{ video.title }}</h3>

            <!-- Description avec "Voir plus" -->
            <div class="text-sm text-gray-700 mt-1">
                <span v-if="!expanded" v-html="truncateText(video.description, 100)"></span>
                <span v-else v-html="video.description"></span>

                <button
                    v-if="video.description.length > 100"
                    @click="toggleDescription"
                    class="text-yellow-500 hover:underline ml-1">
                    {{ expanded ? 'Voir moins' : 'Voir plus' }}
                </button>
            </div>

            <!-- Bouton "Watch the Video" -->
            <div class="mt-3">
                <a
                    :href="video.url"
                    target="_blank"
                    class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition duration-300"
                >
                    <i class="fas fa-play mr-2"></i> Watch
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
.video-list-item {
    transition: transform 0.2s ease-in-out;
}

.video-list-item:hover {
    transform: translateY(-3px);
}

.video-thumbnail {
    position: relative;
    border-radius: 8px;
}
</style>
