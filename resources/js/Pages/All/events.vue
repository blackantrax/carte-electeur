<template>
    <FrontLayout>
        <Head :title="'Tous les événements'" />
        <PageTitle title="Tous les Evénements" :breadcrumbs="breadcrumbs" />

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
            <div v-if="isLoadingEvents" class="text-center py-4">
                <span class="animate-spin inline-block w-6 h-6 border-4 border-blue-500 rounded-full border-t-transparent"></span>
                <span class="ml-2">Chargement des événements ...</span>
            </div>

            <div v-if="errorMessage" class="text-center text-red-500 py-4">
                {{ errorMessage }}
            </div>

            <template v-else>
                <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                    <eventCard v-for="event in filteredEvents" :key="event.id" :event="event" />
                </div>

                <div v-else class="space-y-4 events-list">
                    <eventListItem v-for="event in filteredEvents" :key="event.id" :event="event" />
                </div>

                <div v-if="filteredEvents.length === 0 && !isLoadingEvents" class="text-center text-gray-600 py-8">
                    Aucun événement trouvé.
                </div>

                <div v-if="events.links?.length" class="mt-8 flex justify-center">
                    <nav class="inline-flex rounded-md shadow-sm">
                        <a v-for="(link, index) in events.links"
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
import eventCard from '@/Components/Events/Card.vue';
import eventListItem from '@/Components/Events/List.vue';
import Filter from '@/Components/Filter.vue';
import { Head } from '@inertiajs/vue3';

const breadcrumbs = [
    { label: 'Accueil', url: '/', active: false },
    { label: 'Evénements', url: '/events', active: false },
    { label: 'Tous les Evénements', active: true }
];

const categories = ref([]);
const filters = ref({ search: '', category: '', year: '' });
const viewMode = ref('grid');
const events = ref({ data: [], links: [] });
const isLoadingCategories = ref(false);
const isLoadingEvents = ref(false);
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

const fetchEvents = async () => {
    isLoadingEvents.value = true;
    errorMessage.value = '';
    try {
        const response = await axios.get('/events/allitems', {
            params: {
                search: filters.value.search,
                category: filters.value.category,
                year: filters.value.year
            }
        });

        events.value = response.data?.events ?? { data: [], links: [] };
    } catch (error) {
        console.error('Erreur de chargement des événements :', error);
        errorMessage.value = error.response?.data?.error || 'Une erreur s\'est produite lors du chargement des événements.';
    } finally {
        isLoadingEvents.value = false;
    }
};

const filteredEvents = computed(() => {
    return events.value.data.filter(event => {
        const matchesSearch = !filters.value.search ||
            event.title?.toLowerCase().includes(filters.value.search.toLowerCase()) ||
            event.description?.toLowerCase().includes(filters.value.search.toLowerCase());

        const matchesCategory = !filters.value.category || event.category?.id == filters.value.category;

        const matchesYear = !filters.value.year ||
            (event.created_at && new Date(event.created_at).getFullYear() == Number(filters.value.year));

        return matchesSearch && matchesCategory && matchesYear;
    });
});

watch(filters, fetchEvents, { deep: true });

onMounted(async () => {
    await fetchCategoriesByType('evenement');
    await fetchEvents();
});
</script>

<style scoped>
.event-card:hover {
    transform: translateY(-5px);
}
</style>
