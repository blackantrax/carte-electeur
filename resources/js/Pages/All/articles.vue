<template>
    <FrontLayout>
        <Head :title="'Tous les articles'" />
        <PageTitle title="Tous les articles" :breadcrumbs="breadcrumbs" />

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

            <div v-if="isLoadingArticles" class="text-center py-4">
                <span class="animate-spin inline-block w-6 h-6 border-4 border-blue-500 rounded-full border-t-transparent"></span>
                <span class="ml-2">Chargement des articles...</span>
            </div>

            <div v-if="errorMessage" class="text-center text-red-500 py-4">
                {{ errorMessage }}
            </div>

            <template v-else>
                <ArticleGrid v-if="viewMode === 'grid'" :articles="filteredArticles" />
                <ArticleList v-else :articles="filteredArticles" />

                <div v-if="filteredArticles.length === 0 && !isLoadingArticles" class="text-center text-gray-600 py-8">
                    Aucun article trouvé.
                </div>

                <div v-if="articles.links && articles.links.length" class="mt-8 flex justify-center">
                    <nav class="inline-flex rounded-md shadow-sm">
                        <a v-for="(link, index) in articles.links"
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
import Filter from '@/Components/Filter.vue';
import ArticleGrid from '@/Components/Articles/Grid.vue';
import ArticleList from '@/Components/Articles/List.vue';
import { Head } from '@inertiajs/vue3';

const breadcrumbs = [
    { label: 'Accueil', url: '/', active: false },
    { label: 'Tous les articles', active: true }
];

const categories = ref([]);
const filters = ref({ search: '', category: '', year: '' });
const viewMode = ref('grid');
const articles = ref({ data: [], links: [] });
const isLoadingCategories = ref(false);
const isLoadingArticles = ref(false);
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

const fetchArticles = async () => {
    isLoadingArticles.value = true;
    errorMessage.value = '';
    try {
        const params = {
            search: filters.value.search,
            category: filters.value.category,
            year: filters.value.year
        };
        const response = await axios.get('/articles/allitems', { params });

        if (response.data && response.data.articles) {
            articles.value = response.data.articles;
        } else {
            articles.value = { data: [], links: [] };
        }
    } catch (error) {
        console.error('Erreur de chargement des articles:', error);
        errorMessage.value = 'Une erreur s\'est produite lors du chargement des articles.';
    } finally {
        isLoadingArticles.value = false;
    }
};

const filteredArticles = computed(() => {
    return articles.value.data.filter(article => {
        const matchesSearch = !filters.value.search ||
            (article.title && article.title.toLowerCase().includes(filters.value.search.toLowerCase())) ||
            (article.description && article.description.toLowerCase().includes(filters.value.search.toLowerCase()));

            const matchesCategory = !filters.value.category ||
            (article.category && filters.value.category == article.category.id);

        const matchesYear = !filters.value.year || (article.created_at && new Date(article.created_at.replace(" ", "T")).getFullYear() == Number(filters.value.year));
        return matchesSearch && matchesCategory && matchesYear;
    });
});

watch(filters, fetchArticles, { deep: true });

onMounted(async () => {
    await fetchCategoriesByType('article');
    await fetchArticles();
});
</script>
