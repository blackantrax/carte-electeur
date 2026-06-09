<template>
    <div>
        <Header />
            <div class="container mx-auto">
                <slot/>
            </div>
            <div class="container mx-auto">

                <HomeEvent />
            </div>
        <Footer />

        <!-- Bouton flottant pour remonter en haut -->
        <button @click="scrollToTop" v-show="showButton" class="scroll-to-top">
            &#8679;
        </button>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Footer from '@/Components/Footer.vue';
import Header from '@/Components/Header.vue';
import HomeEvent from '@/Components/Events/HomeEvent.vue';

const showButton = ref(false);

const handleScroll = () => {
    showButton.value = window.scrollY > 150;
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
/* Ajoutez des styles spécifiques au layout ici */
.container {
    max-width: 80%;
    margin: 0 auto;
    padding: 2rem 1rem;
    font-family: "Roboto", sans-serif;
    color: #333;
}

/* Style du bouton flottant */
.scroll-to-top {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #f1c40f;
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: opacity 0.3s ease-in-out;
}

.scroll-to-top:hover {
    background-color: #000;
}
</style>
