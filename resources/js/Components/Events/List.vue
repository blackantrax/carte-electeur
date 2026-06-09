<template>
    <div class="event-item bg-white p-6 rounded-lg shadow-md transition-transform transform hover:scale-105 mt-4">
        <!-- Date de l'événement -->
        <div class="event-date text-gray-500 text-sm font-semibold uppercase tracking-wide">
            {{ formatDate(event.date) }}
        </div>

        <!-- Contenu -->
        <div class="flex flex-col gap-2 mt-2">
            <!-- Titre -->
            <h2 class="event-title text-lg font-bold text-gray-800" :class="{'text-yellow-500': event.highlight}">
                {{ event.title }}
            </h2>

            <!-- Lien "Voir plus" -->
            <Link :href="`/events/${event.slug}`" class="see-more inline-flex items-center text-yellow-500 font-semibold mt-2 transition-colors duration-300"
                  :aria-label="'Voir plus sur l\'événement ' + event.title">
                Voir plus
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10.293 15.707a1 1 0 001.414 0l5-5a1 1 0 000-1.414l-5-5a1 1 0 00-1.414 1.414L13.586 10H5a1 1 0 100 2h8.586l-3.293 3.293a1 1 0 000 1.414z" clip-rule="evenodd"></path>
                </svg>
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    event: Object
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<style scoped>
/* Event Card Styles */
.event-item {
    border-left: 4px solid #f1c40f;
    transition: all 0.3s ease-in-out;
}

.event-item:hover {
    background-color: #fef9c3;
}

/* Event Title Styles */
.event-title {
    transition: color 0.3s ease-in-out;
}

/* Link Styles */
.see-more {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    text-decoration: none;
}

.see-more:hover {
    color: #f1c40f;
}
</style>
