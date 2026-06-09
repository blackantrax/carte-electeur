<template>
    <div class="diaporama-list-item flex items-center bg-white rounded-lg shadow-md overflow-hidden p-4 mt-4">
        <!-- Miniature du diaporama -->
        <div
            class="diaporama-thumbnail flex-shrink-0 w-48 h-32 bg-gray-200 bg-cover bg-center rounded-md"
            :style="{ backgroundImage: `url(${diaporama.images.length ? diaporama.images[0].url : defaultThumbnail})` }"
        ></div>

        <!-- Contenu du diaporama -->
        <div class="flex-grow ml-4">
            <!-- Titre -->
            <h3 class="text-lg font-semibold text-gray-800">{{ diaporama.title }}</h3>

            <!-- Date -->
            <span class="text-sm text-gray-500">{{ formatDate(diaporama.created_at) }}</span>

            <!-- Description avec "Voir plus" -->
            <p class="text-sm text-gray-700 mt-2">
                <span v-if="!diaporama.expanded" v-html="truncateText(diaporama.title, 150)"></span>
                <span v-else v-html="diaporama.title"></span>

                <button
                    v-if="diaporama.title.length > 150"
                    @click="toggleDescription"
                    class="text-yellow-500 hover:underline ml-1">
                    {{ diaporama.expanded ? 'Voir moins' : 'Voir plus' }}
                </button>
            </p>

            <!-- Bouton "Voir le diaporama" -->
            <button
                @click="$emit('view', diaporama)"
                class="mt-2 inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors duration-300"
            >
                <i class="fas fa-images mr-2"></i> Voir le diaporama
            </button>
        </div>
    </div>
</template>

<script setup>
import { defineProps, ref } from 'vue';

// Props du composant
const props = defineProps({
    diaporama: Object
});

// État local pour la description
const diaporama = ref({ ...props.diaporama, expanded: false });
const defaultThumbnail = 'https://via.placeholder.com/300x200?text=No+Thumbnail';

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
const toggleDescription = () => {
    diaporama.value.expanded = !diaporama.value.expanded;
};
</script>

<style scoped>
.diaporama-list-item {
    display: flex;
    align-items: center;
    transition: transform 0.2s ease-in-out;
}

.diaporama-list-item:hover {
    transform: translateY(-3px);
}

.diaporama-thumbnail {
    background-size: cover;
    background-position: center;
}
</style>
