<template>
    <div class="flex flex-col gap-4 mt-4">
        <div v-for="article in articles" :key="article.id" class="flex items-start gap-4 p-4 border-b border-gray-200">
            <!-- Image -->
            <img :src="article.featured_image" :alt="`Image de ${article.title}`"
                 class="w-32 h-20 object-cover rounded-lg flex-shrink-0">

            <!-- Contenu -->
            <div class="flex flex-col gap-2 flex-grow">
                <!-- Métadonnées -->
                <div class="flex items-center gap-3 text-gray-500 text-xs">
                    <div class="flex items-center gap-1">
                        📅 <span>{{ formatDate(article.created_at) }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        📰 <span v-if="article.category">{{ article.category.name }}</span>
                    </div>
                </div>

                <!-- Titre -->
                <h2 class="article-title">
                    {{ article.title }}
                </h2>

                <!-- Extrait -->
                <p class="text-gray-600 text-sm line-clamp-2">
                    {{ article.excerpt }}
                </p>

                <!-- Lien "Lire la suite" -->
                <a :href="`/articles/${article.slug}`" class="read-more">
                    Voir Plus
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
    return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<style scoped>
.article-title {
    color: #1a365d;
    font-size: 1rem;
    font-weight: bold;
    margin-top: 4px;
}
.read-more {
    display: inline-block;
    margin-top: 8px;
    font-weight: bold;
    color: #fbbf24;
    text-decoration: none;
    transition: color 0.2s ease-in-out;
}
.read-more:hover {
    color: #d35400;
}
</style>
