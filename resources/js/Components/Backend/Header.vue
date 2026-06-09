<template>
    <header class="bg-[#1d1d1b] h-16 shadow-sm sticky top-0 z-50">
      <div class="flex justify-between items-center px-4 md:px-6 py-1">
        <!-- Barre de recherche -->
        <div class="hidden md:flex flex-1 max-w-xl">
          <div class="relative w-full">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg"></i>
            <input
              type="search"
              placeholder="Rechercher..."
              class="w-full pl-12 pr-4 py-2 rounded-full border border-gray-700 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-[#2c2c2a] text-gray-200 placeholder-gray-400 transition"
            />
          </div>
        </div>

        <!-- Côté droit: Notifications et profil utilisateur -->
        <div class="flex items-center space-x-4 md:space-x-6">
          <!-- Notifications -->
          <div class="relative">
            <button class="p-2 rounded-full bg-[#2c2c2a] hover:bg-gray-700 focus:ring-2 focus:ring-blue-500 transition">
              <i class="fas fa-bell text-gray-300 text-lg"></i>
              <span class="absolute top-0 right-0 h-5 w-5 bg-red-500 rounded-full text-xs text-white flex items-center justify-center font-bold">
                5
              </span>
            </button>
          </div>

          
        </div>
      </div>
    </header>
  </template>

  <script setup>
  import { ref } from "vue";
  import { usePage, router } from "@inertiajs/vue3"; // ✅ Import corrigé

  const isDropdownOpen = ref(false);
  const user = usePage().props.auth.user; // ✅ Récupération de l'utilisateur

  const hasApiFeatures = true; // Exemple de drapeau de fonctionnalité

  const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
  };

  const logout = () => {
    router.post(route("logout")); // ✅ Déconnexion propre avec Inertia.js
  };
  </script>

  <style scoped>
  header {
    font-family: 'Inter', sans-serif;
  }
  </style>
