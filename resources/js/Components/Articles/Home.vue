<template>
    <div class="news-container">
        <h1 v-if="!isLatestRoute" class="main-title animate-fade-in">Actualités</h1>

        <!-- Article Principal -->
        <div class="row mb-5" v-if="mainArticle">
            <div class="col-12">
                <div class="card news-card main-article animate-fade-in delay-100">
                    <div class="row g-0">
                        <div class="col-md-6">
                            <img
                                :src="mainArticle.featured_image"
                                class="img-fluid rounded-start w-100 h-full object-cover"
                                :alt="mainArticle.title"
                            />
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="card-body p-4">
                                <div class="category-tag mb-3">
                                    {{ mainArticle.category?.name || 'Actualités' }}
                                </div>
                                <h2 class="card-title h3 mb-4">{{ mainArticle.title }}</h2>
                                <Link
                                    :href="`/articles/${mainArticle.slug}`"
                                    class="btn btn-outline-yellow"
                                >
                                    Lire l'article
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Articles Secondaires -->
        <div class="row g-4">
            <div
                class="col-md-4"
                v-for="(article, index) in secondaryArticles"
                :key="article.id"
            >
                <div
                    class="card news-card secondary-article h-100 animate-fade-in"
                    :style="`animation-delay: ${index * 100 + 200}ms;`"
                >
                    <img
                        :src="article.featured_image"
                        class="card-img-top h-48 object-cover"
                        :alt="article.title"
                    />
                    <div class="card-body d-flex flex-column">
                        <div class="category-tag mb-3">
                            {{ article.category?.name || 'Actualités' }}
                        </div>
                        <h3 class="card-title h5 mb-4">{{ article.title }}</h3>
                        <Link
                            :href="`/articles/${article.slug}`"
                            class="btn btn-outline-yellow mt-auto"
                        >
                            Lire l'article
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton Voir Plus -->
        <div
            class="row mt-5 animate-fade-in delay-500"
        >
            <div class="col-12 text-center">
                <Link
                    v-if="!isLatestRoute"
                    href="/latest"
                    class="btn btn-outline-yellow px-2 text-sm"
                >
                    Derniéres actualités
                </Link>


                <Link
                    v-else
                    :href="route('articles.all')"
                    class="btn btn-outline-yellow px-2 text-lg"
                >
                    Voir toutes les actualités
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const mainArticle = ref(null);
const secondaryArticles = ref([]);

// Vérifie si la route actuelle est /latest
const isLatestRoute = computed(() => {
    return usePage().url === '/latest';
});

// Récupère les articles
const fetchArticles = async () => {
    try {
        const response = await axios.get('/articles/featured');
        const articles = response.data.articles;

        mainArticle.value = articles[0] || null;
        secondaryArticles.value = articles.slice(1, 4);
    } catch (error) {
        console.error("Erreur lors de la récupération des articles :", error);
    }
};

onMounted(fetchArticles);
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

.delay-100 {
    animation-delay: 100ms;
}

.delay-500 {
    animation-delay: 500ms;
}

/* Section Actualité */
.news-container {
    padding: 2rem;
    border-radius: 10px;
}

.news-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background-color: #fff;
    border: none;
    color: #333;
    overflow: hidden;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

.main-article {
    background-color: #f8f9fa;
}

.main-article img {
    height: 400px;
    object-fit: cover;
}

.secondary-article img {
    height: 200px;
    object-fit: cover;
}

.category-tag {
    font-size: 0.875rem;
    color: #f1c40f;
    text-transform: uppercase;
    font-weight: bold;
}

.main-title {
    font-size: 2.5rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 2rem;
    text-align: left;
}

.btn-outline-yellow {
    border: 2px solid #f1c40f;
    color: #f1c40f;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-outline-yellow:hover {
    background-color: #f1c40f;
    color: #fff;
    transform: translateY(-2px);
}
</style>
