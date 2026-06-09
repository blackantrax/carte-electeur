<template>
    <FrontLayout>
        <Head title="Accueil" />
        <Service />
        <Home />
        <MapComponent />
    </FrontLayout>
</template>

<script setup>
    import FrontLayout from '@/Layouts/FrontLayout.vue';
    import Service from '@/Components/Service.vue';
    import Home from '@/Components/Articles/Home.vue';
    import HomeEvent from '@/Components/Events/HomeEvent.vue';
    import MapComponent from '@/Components/Maps/MapComponent.vue';
    import { Head } from '@inertiajs/vue3';
</script>

<style scoped>
/* Vos styles ici */
</style>
