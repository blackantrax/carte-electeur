<template>
  <div>
    <!-- Overlay mobile -->
    <Transition name="fade">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-40 lg:hidden bg-black/30 backdrop-blur-sm"
        @click="$emit('close')"
      />
    </Transition>

    <!-- Sidebar principale -->
    <Transition name="slide">
      <aside
        :class="[
          'fixed inset-y-0 left-0 z-50 w-72 bg-[#1d1d1b] shadow-xl transform transition-all duration-300',
          'lg:translate-x-0 lg:static lg:inset-0',
          isOpen ? 'translate-x-0' : '-translate-x-full'
        ]"
        class="flex flex-col h-screen border-r border-gray-700"
      >
        <!-- En-tête -->
        <div class="h-20 flex items-center px-6 border-b border-gray-700 bg-[#1d1d1b]">
          <img 
            src="/images/logo.png" 
            alt="Logo Admin" 
            class="h-10 mr-3"
          />
          <span class="font-bold text-lg text-yellow-400">ADMIN PANEL</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 custom-scrollbar">
          <template v-for="section in sections" :key="section.title">
            <div class="mb-6 last:mb-2">
              <h3 class="px-3 text-xs font-semibold text-yellow-400 uppercase tracking-wider mb-2">
                {{ section.title }}
              </h3>
              <div class="space-y-1">
                <NavLink
                  v-for="item in section.items"
                  :key="item.name"
                  :item="item"
                  :is-active="isActive(item.route)"
                  :current-url="currentUrl"
                />
              </div>
            </div>
          </template>
        </nav>

        <!-- Section utilisateur -->
        <div class="p-4 border-t border-gray-700 bg-[#1a1a1a]">
          <div class="flex items-center">
            <div class="flex-shrink-0 relative">
              <img 
                :src="user.profile_photo_url" 
                :alt="user.name"
                class="w-10 h-10 rounded-full object-cover border-2 border-gray-600 shadow"
              >
              <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-[#1a1a1a]"></span>
            </div>
            <div class="ml-3 flex-1 min-w-0">
              <p class="text-sm font-medium text-white truncate">{{ user.name }}</p>
              <p class="text-xs text-gray-300 truncate">{{ user.role }}</p>
            </div>
            <button 
              @click="$emit('logout')"
              class="ml-2 text-gray-400 hover:text-yellow-400 transition-colors"
              title="Déconnexion"
            >
              <i class="fas fa-sign-out-alt"></i>
            </button>
          </div>
        </div>
      </aside>
    </Transition>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import NavLink from '@/Components/NavLink.vue'

const props = defineProps({
  isOpen: Boolean
})

const emit = defineEmits(['close', 'logout'])

const page = usePage()
const currentUrl = computed(() => page.url)

const user = computed(() => ({
  name: page.props?.auth?.user?.name || 'Administrateur',
  profile_photo_url: page.props?.auth?.user?.profile_photo_url || '/images/default-avatar.png',
  role: page.props?.auth?.user?.role || 'Admin'
}))

const isActive = (route) => {
  const normalizedCurrent = currentUrl.value.replace(/\/$/, '')
  const normalizedRoute = route.replace(/\/$/, '')
  return normalizedCurrent.startsWith(normalizedRoute) || 
         normalizedCurrent === normalizedRoute
}

const sections = [
  {
    title: 'Tableaux de bord',
    items: [
      { name: 'Analytique', icon: 'chart-line', route: '/admin/dashboard' },
      { name: 'Performances', icon: 'tachometer-alt', route: '/admin/analytics' }
    ]
  },
  {
    title: 'Gestion électorale',
    items: [
      { 
        name: 'Tableau de bord', 
        icon: 'vote-yea', 
        route: '/admin/vote/dashboard',
        badge: 'Nouveau'
      },
      { 
        name: 'Inscriptions', 
        icon: 'user-plus', 
        route: '/admin/vote/inscriptions',
        badge: computed(() => page.props?.pendingRegistrations || 0)
      },
      { 
        name: 'Géographie électorale', 
        icon: 'globe', 
        route: '/admin/geo',
        children: [
          { 
            name: 'Pays', 
            icon: 'flag',
            route: '/admin/geo/pays',
            children: [
              { name: 'Liste', route: '/admin/geo/pays' },
              { name: 'Créer', route: '/admin/geo/pays/create' }
            ]
          },
          { 
            name: 'Régions', 
            icon: 'map-marked-alt',
            route: '/admin/geo/regions',
            children: [
              { name: 'Liste', route: '/admin/geo/regions' },
              { name: 'Créer', route: '/admin/geo/regions/create' }
            ]
          },
          { 
            name: 'Départements', 
            icon: 'map-marked',
            route: '/admin/geo/departements',
            children: [
              { name: 'Liste', route: '/admin/geo/departements' },
              { name: 'Créer', route: '/admin/geo/departements/create' }
            ]
          },
          { 
            name: 'Communes', 
            icon: 'map',
            route: '/admin/geo/communes',
            children: [
              { name: 'Liste', route: '/admin/geo/communes' },
              { name: 'Créer', route: '/admin/geo/communes/create' }
            ]
          },
          { 
            name: 'Arrondissements', 
            icon: 'map-pin',
            route: '/admin/geo/arrondissements',
            children: [
              { name: 'Liste', route: '/admin/geo/arrondissements' },
              { name: 'Créer', route: '/admin/geo/arrondissements/create' }
            ]
          },
          { 
            name: 'Bureaux de vote', 
            icon: 'landmark',
            route: '/admin/geo/bureaux-de-vote',
            children: [
              { name: 'Liste', route: '/admin/geo/bureaux-de-vote' },
              { name: 'Créer', route: '/admin/geo/bureaux-de-vote/create' }
            ]
          },
          { 
            name: 'Ambassades', 
            icon: 'globe-africa',
            route: '/admin/geo/ambassades',
            children: [
              { name: 'Liste', route: '/admin/geo/ambassades' },
              { name: 'Créer', route: '/admin/geo/ambassades/create' }
            ]
          }
        ]
      },
      { 
        name: 'Statistiques', 
        icon: 'chart-pie', 
        route: '/admin/vote/stats',
        children: [
          { name: 'Par région', route: '/admin/vote/stats/regions' },
          { name: 'Par bureau', route: '/admin/vote/stats/bureaux' }
        ]
      }
    ]
  },
  {
    title: 'Gestion de contenu',
    items: [
      { name: 'Articles', icon: 'newspaper', route: '/admin/blogs' },
      { name: 'Événements', icon: 'calendar-day', route: '/admin/events' },
      { name: 'Médias', icon: 'photo-video', route: '/admin/media' },
      { name: 'Publications', icon: 'book', route: '/admin/publications' }
    ]
  },
  {
    title: 'Administration',
    items: [
      { name: 'Utilisateurs', icon: 'users-cog', route: '/admin/users' },
      { name: 'Catégories', icon: 'tags', route: '/admin/categories' },
      { name: 'Contacts', icon: 'envelope', route: '/admin/contacts' },
      { name: 'Paramètres', icon: 'cogs', route: '/admin/settings' }
    ]
  }
]
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}

.custom-scrollbar::-webkit-scrollbar {
  @apply w-2;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  @apply bg-gray-600 rounded-full hover:bg-gray-500;
}
</style>