<template>
  <nav class="bg-gray-900 text-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-8">
          <Link 
            v-for="(item, index) in navItems" 
            :key="index"
            :href="item.href"
            class="nav-link relative group"
            :class="{ 'text-yellow-400': $page.url.startsWith(item.href) }"
          >
            {{ item.name }}
            <span 
              v-if="item.subItems"
              class="ml-1 inline-block transition-transform group-hover:rotate-180"
            >
              <i class="fas fa-chevron-down text-xs"></i>
            </span>
            
            <!-- Dropdown Menu -->
            <div 
              v-if="item.subItems"
              class="absolute left-0 mt-2 w-56 origin-top-left bg-gray-800 rounded-md shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform -translate-y-1 group-hover:translate-y-0"
            >
              <div class="py-1">
                <Link
                  v-for="(subItem, subIndex) in item.subItems"
                  :key="subIndex"
                  :href="subItem.href"
                  class="block px-4 py-3 text-sm hover:bg-gray-700 hover:text-yellow-400 transition-colors"
                >
                  {{ subItem.name }}
                </Link>
              </div>
            </div>
          </Link>
        </div>

        <!-- CTA Button -->
        <div class="hidden md:block ml-4">
          <Link 
            :href="route('registered')" 
            class="cta-button flex items-center space-x-2"
          >
            <i class="fas fa-user-edit"></i>
            <span>Enregistrez-vous</span>
          </Link>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden flex items-center">
          <button 
            @click="isOpen = !isOpen"
            class="mobile-menu-button p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-400"
            aria-label="Menu"
          >
            <i 
              class="fas text-xl transition-transform duration-300"
              :class="isOpen ? 'fa-times' : 'fa-bars'"
            ></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div 
        v-show="isOpen"
        class="md:hidden bg-gray-800 shadow-lg"
      >
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
          <Link
            v-for="(item, index) in navItems"
            :key="index"
            :href="item.href"
            class="mobile-nav-link block px-3 py-2 rounded-md text-base font-medium"
            @click="isOpen = false"
          >
            {{ item.name }}
          </Link>
          
          <!-- Mobile CTA Button -->
          <Link 
            :href="route('registered')" 
            class="block w-full text-center mt-4 cta-button-mobile"
            @click="isOpen = false"
          >
            <i class="fas fa-user-edit mr-2"></i>
            Enregistrez-vous
          </Link>
        </div>
      </div>
    </transition>
  </nav>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const isOpen = ref(false);

const navItems = [
  { name: 'Accueil', href: '/' },
  { name: 'Informations', href: '/latest' },
  { 
    name: 'Ressources', 
    href: '#',
    subItems: [
      { name: 'Liens Utiles', href: '/useful-links' },
      { name: 'FAQ', href: '/faq' }
    ]
  },
  { name: 'À Propos', href: '/about' },
  { name: 'Contact', href: '/contact' }
];
</script>

<style scoped>
.nav-link {
  @apply px-3 py-2 rounded-md text-sm font-medium text-white hover:text-yellow-400 transition-colors relative;
}

.nav-link::after {
  @apply absolute bottom-0 left-0 w-0 h-0.5 bg-yellow-400 transition-all duration-300;
  content: '';
}

.nav-link:hover::after,
.nav-link.text-yellow-400::after {
  @apply w-full;
}

.cta-button {
  @apply bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 px-4 py-2 rounded-md font-semibold shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-300;
}

.mobile-nav-link {
  @apply text-white hover:bg-gray-700 hover:text-yellow-400 transition-colors;
}

.cta-button-mobile {
  @apply bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 px-4 py-3 rounded-md font-semibold shadow-md mx-3;
}

/* Smooth dropdown animation */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.3s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>