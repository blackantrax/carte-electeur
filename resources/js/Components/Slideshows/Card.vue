<template>
    <div class="diaporama-card bg-white rounded-lg shadow-md overflow-hidden mt-4">
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
                    @click="toggleDescription"
                    class="text-yellow-500 hover:underline ml-1">
                    {{ diaporama.expanded ? 'Voir moins' : 'Voir plus' }}
                </button>
            </div>

            <!-- Bouton "Voir le diaporama" -->
            <div class="mt-4">
                <button
                    @click="$emit('view', diaporama)"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors duration-300"
                >
                    <i class="fas fa-images mr-2"></i> Voir le diaporama
                </button>
            </div>
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
.diaporama-card {
    transition: transform 0.2s ease-in-out;
}

.diaporama-card:hover {
    transform: translateY(-5px);
}

.diaporama-container {
    height: 200px;
    background-size: cover;
    background-position: center;
}
</style>
