<template>
    <FrontLayout>
        <Head :title="'Tous les vidéos'" />
        <PageTitle title="Tous les vidéos" :breadcrumbs="breadcrumbs" />

        <div class="container mx-auto px-4 py-8">
            <div v-if="isLoadingCategories" class="text-center py-4">
                <span class="animate-spin inline-block w-6 h-6 border-4 border-blue-500 rounded-full border-t-transparent"></span>
                <span class="ml-2">Chargement des catégories...</span>
            </div>

            <Filter
                :categories="categories"
                v-model:filters="filters"
                v-model:viewMode="viewMode"
            />

            <div v-if="isLoadingVideos" class="text-center py-4">
                <span class="animate-spin inline-block w-6 h-6 border-4 border-blue-500 rounded-full border-t-transparent"></span>
                <span class="ml-2">Chargement des vidéos...</span>
            </div>

            <div v-if="errorMessage" class="text-center text-red-500 py-4">
                {{ errorMessage }}
            </div>

            <template v-else>
                <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <VideoCard v-for="video in filteredVideos" :key="video.id" :video="video" />
                </div>

                <div v-else class="space-y-4">
                    <VideoListItem v-for="video in filteredVideos" :key="video.id" :video="video" />
                </div>

                <div v-if="filteredVideos.length === 0 && !isLoadingVideos" class="text-center text-gray-600 py-8">
                    Aucun vidéo trouvé.
                </div>

                <div v-if="videos.links?.length" class="mt-8 flex justify-center">
                    <nav class="inline-flex rounded-md shadow-sm">
                        <a v-for="(link, index) in videos.links"
                           :key="index"
                           :href="link.url"
                           class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                           :class="{ 'bg-gray-200': link.active }"
                           v-html="link.label">
                        </a>
                    </nav>
                </div>
            </template>
        </div>
    </FrontLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import FrontLayout from '@/Layouts/FrontLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import VideoCard from '@/Components/Videos/Card.vue';
import VideoListItem from '@/Components/Videos/List.vue';
import Filter from '@/Components/Filter.vue';
import { Head } from '@inertiajs/vue3';

const breadcrumbs = [
    { label: 'Accueil', url: '/', active: false },
    { label: 'Vidéos', url: '/videos', active: false },
    { label: 'Tous les vidéos', active: true }
];

const categories = ref([]);
const filters = ref({ search: '', category: '', year: '' });
const viewMode = ref('grid');
const videos = ref({ data: [], links: [] });
const isLoadingCategories = ref(false);
const isLoadingVideos = ref(false);
const errorMessage = ref('');

const fetchCategoriesByType = async (type) => {
    isLoadingCategories.value = true;
    try {
        const response = await axios.get(`/categories/type/${type}`);
        categories.value = response.data;
    } catch (error) {
        console.error('Erreur de chargement des catégories:', error);
        errorMessage.value = 'Une erreur s\'est produite lors du chargement des catégories.';
    } finally {
        isLoadingCategories.value = false;
    }
};

const fetchVideos = async () => {
    isLoadingVideos.value = true;
    errorMessage.value = '';
    try {
        const response = await axios.get('/videos/allitems', {
            params: {
                search: filters.value.search,
                category: filters.value.category,
                year: filters.value.year
            }
        });

        videos.value = response.data?.videos ?? { data: [], links: [] };
    } catch (error) {
        console.error('Erreur de chargement des vidéos:', error);
        errorMessage.value = error.response?.data?.error || 'Une erreur s\'est produite lors du chargement des vidéos.';
    } finally {
        isLoadingVideos.value = false;
    }
};

const filteredVideos = computed(() => {
    return videos.value.data.filter(video => {
        const matchesSearch = !filters.value.search ||
            video.title?.toLowerCase().includes(filters.value.search.toLowerCase()) ||
            video.description?.toLowerCase().includes(filters.value.search.toLowerCase());

        const matchesCategory = !filters.value.category || video.category?.id == filters.value.category;

        const matchesYear = !filters.value.year ||
            (video.created_at && new Date(video.created_at).getFullYear() == Number(filters.value.year));

        return matchesSearch && matchesCategory && matchesYear;
    });
});

watch(filters, fetchVideos, { deep: true });

onMounted(async () => {
    await fetchCategoriesByType('video');
    await fetchVideos();
});
</script>

<style scoped>
.video-card:hover {
    transform: translateY(-5px);
}
</style>
