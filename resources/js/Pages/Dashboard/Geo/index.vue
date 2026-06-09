<template>
  <AppLayout title="Tableau de Bord Géographique">
    <PageTitle title="Tableau de Bord Géographique" :breadcrumbs="breadcrumbs">
      <template #actions>
        <button 
              @click="refreshData"
              class="flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg transition"
              :disabled="isRefreshing"
            >
              <i class="bi" :class="isRefreshing ? 'bi-arrow-repeat animate-spin' : 'bi-arrow-clockwise'"></i>
              <span>{{ isRefreshing ? 'Actualisation...' : 'Actualiser' }}</span>
            </button>
      </template>
    </PageTitle>

    <div class="bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Cartes principales en grille -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          <div 
            v-for="(card, index) in mainCards"
            :key="index"
            class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow cursor-pointer"
            @click="navigateTo(card.link)"
          >
            <div class="p-5 flex items-start space-x-4">
              <div class="p-3 rounded-lg" :class="`bg-${card.color}-100 text-${card.color}-600`">
                <i class="bi text-xl" :class="card.icon"></i>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-800">{{ card.title }}</h3>
                <p class="text-3xl font-bold mt-2">{{ card.value }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ card.description }}</p>
              </div>
              <div class="text-gray-400">
                <i class="bi bi-chevron-right"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Accès rapides secondaires -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
              <i class="bi bi-lightning-charge text-blue-600"></i>
              <span>Actions Rapides</span>
            </h2>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-5">
            <button
              v-for="(action, index) in quickActions"
              :key="index"
              @click="navigateTo(action.link)"
              class="group flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors border border-gray-200"
            >
              <div class="p-2 rounded-lg" :class="`bg-${action.color}-100 text-${action.color}-600`">
                <i class="bi" :class="action.icon"></i>
              </div>
              <div class="text-left">
                <h3 class="font-medium text-gray-900 group-hover:text-blue-600">{{ action.title }}</h3>
                <p class="text-xs text-gray-500">{{ action.description }}</p>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageTitle from '@/Components/PageTitle.vue';

const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Géographie', url: route('admin.geo.index') },
  { label: 'Gestion des divisions territoriales', active: true }
];

const props = defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({
      pays: 0,
      regions: 0,
      departements: 0,
      arrondissements: 0,
      communes: 0,
      bureaux: 0,
      ambassades: 0
    })
  }
})

const isRefreshing = ref(false)

const mainCards = [
  {
    title: 'Pays',
    value: props.stats.pays,
    icon: 'bi-flag-fill',
    color: 'blue',
    description: 'Gérer les pays et territoires',
    link: route('admin.geo.pays.index')
  },
  {
    title: 'Régions',
    value: props.stats.regions,
    icon: 'bi-pin-map-fill',
    color: 'emerald',
    description: 'Divisions régionales',
    link: route('admin.geo.regions.index')
  },
  {
    title: 'Départements',
    value: props.stats.departements,
    icon: 'bi-map-fill',
    color: 'cyan',
    description: 'Administration départementale',
    link: route('admin.geo.departements.index')
  },
  {
    title: 'Arrondissements',
    value: props.stats.arrondissements,
    icon: 'bi-geo-fill',
    color: 'amber',
    description: 'Sections administratives',
    link: route('admin.geo.arrondissements.index')
  },
  {
    title: 'Communes',
    value: props.stats.communes,
    icon: 'bi-geo-alt-fill',
    color: 'violet',
    description: 'Gestion communale',
    link: route('admin.geo.communes.index')
  },
  {
    title: 'Bureaux de vote',
    value: props.stats.bureaux,
    icon: 'bi-building-fill',
    color: 'rose',
    description: 'Points de vote',
    link: route('admin.geo.bureaux-de-vote.index')
  }
]

const quickActions = [
  { 
    title: 'Importer données', 
    icon: 'bi-upload', 
    color: 'blue',
    description: 'Import massif',
    link: route('admin.geo.index')
  },
  { 
    title: 'Exporter données', 
    icon: 'bi-download', 
    color: 'indigo',
    description: 'Export Excel/CSV',
    link: route('admin.geo.index')
  },
  { 
    title: 'Ambassades', 
    icon: 'bi-globe', 
    color: 'teal',
    description: 'Représentations',
    link: route('admin.geo.ambassades.index')
  },
  { 
    title: 'Circonscriptions', 
    icon: 'bi-boundary', 
    color: 'purple',
    description: 'Zones électorales',
    link: route('admin.geo.index')
  }
]

const refreshData = async () => {
  isRefreshing.value = true
  try {
    await router.reload({
      only: ['stats'],
      preserveScroll: true
    })
  } finally {
    isRefreshing.value = false
  }
}

const navigateTo = (url) => {
  router.visit(url)
}
</script>

<style scoped>
/* Animation douce pour les interactions */
button, .cursor-pointer {
  transition: all 0.2s ease;
}

/* Effet de survol pour les cartes */
.hover\:shadow-md:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>