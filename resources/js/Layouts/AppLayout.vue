<template>
    <div class="flex bg-gray-50 h-screen overflow-hidden">
      <!-- Sidebar -->
      <Sidebar :is-open="isSidebarOpen" @close="toggleSidebar(false)" class="absolute lg:relative h-full" />

      <!-- Main Content -->
      <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <Header @menu-click="toggleSidebar(true)" />

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-auto">
            <slot/>
        </main>
      </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import Sidebar from '@/Components/Backend/Sidebar.vue';
import Header from '@/Components/Backend/Header.vue';

const isSidebarOpen = ref(false);

function toggleSidebar(state) {
    isSidebarOpen.value = state;
}
</script>

<style scoped>
/* Empêcher le défilement de la sidebar */
.sidebar {
    position: absolute;
    height: 100vh;
    overflow-y: hidden;
}

/* Permettre le défilement du contenu principal */
main {
    overflow-y: auto;
}
</style>
