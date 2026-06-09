<template>
  <header class="sticky top-0 z-50 bg-white shadow-sm">
    <!-- Top Bar -->
    <div class="top-bar bg-gray-900 text-white py-1 px-4 text-sm">
      <div class="container mx-auto flex justify-end items-center">
        <div class="flex items-center space-x-4">
          <a href="#" class="hover:text-yellow-400 transition-colors">
            <i class="fas fa-question-circle mr-1"></i> Aide
          </a>
          <div class="language-switcher flex items-center">
            <button 
              v-for="lang in languages" 
              :key="lang.code"
              @click="changeLanguage(lang.code)"
              class="px-2 hover:text-yellow-400 transition-colors"
              :class="{ 'text-yellow-400 font-medium': currentLanguage === lang.code }"
            >
              {{ lang.label }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Header -->
    <div class="main-header py-3 px-4 border-b border-gray-100">
      <div class="container mx-auto flex flex-col md:flex-row items-center justify-between">
        <!-- Logo Section -->
        <div class="logo-section flex items-center md:mb-0">
          <Link href="/" class="flex items-center group">
            <img 
              src="/images/logo.png" 
              alt="Logo CARTES D'ÉLECTEURS" 
              class="h-24 w-auto mr-3 transition-transform group-hover:scale-105"
            >
            <span class="logo-text text-xl font-bold text-gray-800 group-hover:text-yellow-600 transition-colors">
              VALORISE TON DEVOIR
            </span>
          </Link>
        </div>

        <!-- Search and Actions -->
        <div class="search-actions flex items-center w-full md:w-auto">
          <div class="search-bar relative flex-grow md:flex-grow-0 md:w-64 lg:w-80 mr-4">
            <input 
              type="text" 
              placeholder="Rechercher..." 
              class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all"
              v-model="searchQuery"
              @keyup.enter="performSearch"
            >
            <button 
              @click="performSearch"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-yellow-600 transition-colors"
            >
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <Navbar />
  </header>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import Navbar from './Navbar.vue';

const searchQuery = ref('');
const currentLanguage = ref('fr');

const languages = [
  { code: 'fr', label: 'FR' },
  { code: 'en', label: 'EN' }
];

const performSearch = () => {
  if (searchQuery.value.trim()) {
    // Implémentez la logique de recherche ici
    console.log('Recherche pour:', searchQuery.value);
  }
};

const changeLanguage = (lang) => {
  currentLanguage.value = lang;
  // Implémentez le changement de langue ici
};
</script>

<style scoped>
.top-bar {
  background: linear-gradient(90deg, #1a1a1a 0%, #2d2d2d 100%);
}

.logo-text {
  position: relative;
}

.logo-text::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background-color: #f59e0b;
  transition: width 0.3s ease;
}

.group:hover .logo-text::after {
  width: 100%;
}

.header-icon-btn {
  @apply p-2 rounded-full hover:bg-gray-100 transition-colors relative;
}

.search-bar input:focus {
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
}

@media (max-width: 800px) {
  .search-bar {
    @apply w-full mr-0;
  }
  
  .logo-section {
    @apply justify-center w-full;
  }
  
  .search-actions {
    @apply flex-col space-y-3;
  }
}
</style>