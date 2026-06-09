<template>
    <FrontLayout>
        <Head :title="title" />
        <PageTitle title="Dernières Nouvelles" :breadcrumbs="breadcrumbs" />

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8">
            <!-- Page Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 animate-fade-in">
                    Dernières Nouvelles
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto animate-fade-in delay-100">
                    Restez informé des dernières actualités, photos, vidéos et événements.
                </p>
            </div>

            <!-- Navigation Links -->
            <div class="flex flex-wrap justify-center gap-4 mb-12">
                <a
                    v-for="(section, index) in sections"
                    :key="index"
                    :href="`#${section.id}`"
                    class="px-6 py-3 rounded-full text-sm font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-300"
                >
                    {{ section.label }}
                </a>
            </div>

            <!-- Sections -->
            <div>
                <!-- Section News -->
                <section id="news" class="mb-16 scroll-mt-20">
                    <h2 class="text-3xl font-bold text-gray-900 mb-8">Actualités</h2>
                    <Home />
                </section>

                <!-- Section photos -->
                <section id="photos" class="mb-16 scroll-mt-20">
                    <h2 class="text-3xl font-bold text-gray-900 mb-8">Photos</h2>
                    <LatestPhoto />
                </section>

                <!-- Section Vidéos -->
                <section id="videos" class="mb-16 scroll-mt-20">
                    <h2 class="text-3xl font-bold text-gray-900 mb-8">Vidéos</h2>
                    <LatestVideo />
                </section>

                <!-- Ajoutez d'autres sections ici (Photos, Publications, Événements) -->
            </div>
        </div>
    </FrontLayout>
</template>

<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import Home from '@/Components/Articles/Home.vue';
import LatestVideo from '@/Components/Videos/Latest.vue';
import LatestPhoto from '@/Components/Slideshows/Latest.vue';
import PageTitle from '@/Components/PageTitle.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs = [
    { label: 'Accueil', url: '/', active: false },
    { label: 'Dernières nouvelles', active: true }
];

const sections = [
    { id: 'news', label: 'Actualités' },
    { id: 'photos', label: 'Photos' },
    { id: 'videos', label: 'Vidéos' },
    { id: 'evenements', label: 'Événements' }
];

defineProps({
    title: String,
    description: String
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
    animation: fade-in 0.6s ease-out;
}

.delay-100 {
    animation-delay: 0.1s;
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Custom Styles */
.photo-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

.main-title {
    background: linear-gradient(45deg, #f59e0b, #fbbf24);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.btn-outline-yellow {
    border: 2px solid #f59e0b;
    color: #f59e0b;
    transition: all 0.3s ease;
}

.btn-outline-yellow:hover {
    background-color: #f59e0b;
    color: white;
    transform: translateY(-2px);
}

.news-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

.category-tag {
    background-color: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
    transition: all 0.3s ease;
}

.category-tag:hover {
    background-color: rgba(245, 158, 11, 0.2);
}
</style>
