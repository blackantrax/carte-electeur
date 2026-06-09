<template>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
        <div v-for="article in articles" :key="article.id" class="article-card">
            <!-- Image -->
            <img :src="article.featured_image" alt="Article image" class="article-image" />

            <div class="p-4">
                <!-- Métadonnées -->
                <div class="flex justify-between text-gray-500 text-xs">
                    <span v-if="article.category" class="text-blue-500 font-medium">
                        {{ article.category.name }}
                    </span>
                    <span class="font-bold"> {{ formatDate(article.created_at) }} </span>
                </div>

                <!-- Titre -->
                <h2 class="article-title">
                    {{ article.title }}
                </h2>

                <!-- Lien "Lire la suite" -->
                <a :href="`/articles/${article.slug}`" class="read-more">
                    Voir plus
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    articles: Array
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
};
</script>

<style scoped>
.article-card {
    background-color: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out;
}

.article-card:hover {
    transform: translateY(-5px);
}

.article-image {
    width: 100%;
    height: 160px;
    object-fit: cover;
}

.article-title {
    color: #111827;
    font-size: 1.1rem;
    font-weight: bold;
    margin-top: 8px;
}

.read-more {
    display: inline-block;
    margin-top: 12px;
    font-weight: bold;
    color: #fbbf24;
    text-decoration: none;
    transition: color 0.2s ease-in-out;
}

.read-more:hover {
    color: #d35400;
}
</style>
