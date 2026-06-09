<template>
    <FrontLayout>
        <PageTitle :title="article?.title" :breadcrumbs="breadcrumbs" />

        <h2 class="mt-2 text-lg font-semibold">{{ article?.title }}</h2>

        <img v-if="article?.featured_image" :src="article.featured_image" class="mt-2 rounded-md w-full max-h-60 object-cover" />

        <div class="mt-4 text-gray-700" v-html="article?.content"></div>
    </FrontLayout>
</template>

<script setup>
import { defineProps } from 'vue';
import FrontLayout from '@/Layouts/FrontLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import HomeEvent from '@/Components/Events/HomeEvent.vue';

const props = defineProps({
    article: Object // Déclaration des props venant du backend
});

const breadcrumbs = [
    { label: 'Accueil', url: '/' },
    { label: 'Articles', url: '/articles/all' },
    { label: props.article?.slug || '', active: true } // Correction de l'accès à article
];
</script>

