<template>
  <AppLayout>
    <PageTitle title="Suivi des Votants" :breadcrumbs="breadcrumbs">
      <template #actions>
        <Link 
          :href="route('admin.vote.inscriptions.index')"
          class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-all"
        >
          <i class="fas fa-user-plus"></i>
          <span>Nouvelle inscription</span>
        </Link>
      </template>
    </PageTitle>

    <div class="space-y-6">
      <!-- Indicateurs Clés -->
      <section>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard title="Total Votants" :value="stats.total_inscriptions" icon="users" trend="up" :percentage="12"
            color="from-blue-50 to-blue-100" text-color="text-blue-800" icon-color="text-blue-500" />

          <StatCard title="Votants Validés" :value="stats.inscriptions_verified" icon="user-check" trend="up" :percentage="8"
            color="from-green-50 to-green-100" text-color="text-green-800" icon-color="text-green-500" />

          <StatCard title="En Attente" :value="stats.inscriptions_pending" icon="user-clock" trend="down" :percentage="5"
            color="from-amber-50 to-amber-100" text-color="text-amber-800" icon-color="text-amber-500" />

          <StatCard title="Taux de Participation" value="72%" icon="percentage" trend="up" :percentage="3"
            color="from-purple-50 to-purple-100" text-color="text-purple-800" icon-color="text-purple-500" />
        </div>
      </section>

      <!-- Cartographie et Activité -->
      <section class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Carte des inscriptions -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden lg:col-span-2">
          <div class="p-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <i class="fas fa-map-marked-alt text-indigo-500"></i>
                <span>Répartition géographique</span>
              </h3>
              <div class="flex gap-2">
                <button class="text-xs px-3 py-1 border border-gray-200 rounded-md hover:bg-gray-50">
                  <i class="fas fa-layer-group mr-1"></i>
                  Couches
                </button>
                <select class="text-xs border-gray-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500 px-3 py-1 bg-gray-50">
                  <option>Tous bureaux</option>
                  <option>Urbains</option>
                  <option>Ruraux</option>
                </select>
              </div>
            </div>
            <div class="h-80 bg-gray-50 rounded-md flex items-center justify-center">
              <div class="text-center text-gray-500">
                <i class="fas fa-map text-4xl mb-2"></i>
                <p>Carte interactive des votants</p>
                <p class="text-sm mt-1">(Intégration avec OpenStreetMap ou Google Maps)</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Derniers Votants -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
              <i class="fas fa-history text-indigo-500"></i>
              <span>Derniers inscrits</span>
            </h3>
            <div class="space-y-3">
              <div 
                v-for="inscription in latestInscriptions" 
                :key="inscription.id" 
                class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition-colors cursor-pointer"
                @click="$inertia.visit(route('admin.vote.inscriptions.show', inscription.id))"
              >
                <div class="flex-shrink-0">
                  <div class="h-10 w-10 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-600">
                    <i class="fas fa-user text-sm"></i>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-gray-900 truncate">{{ inscription.prenom }} {{ inscription.nom }}</p>
                  <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span>{{ inscription.commune?.nom }}</span>
                    <span>•</span>
                    <span>{{ formatDate(inscription.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
            <Link 
              :href="route('admin.vote.inscriptions.index')" 
              class="mt-4 flex items-center justify-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-medium group"
            >
              <span>Voir tous les votants</span>
              <i class="fas fa-chevron-right text-xs transition-transform group-hover:translate-x-0.5"></i>
            </Link>
          </div>
        </div>
      </section>

      <!-- Analyse par Bureau -->
      <section class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <i class="fas fa-poll-h text-indigo-500"></i>
              <span>Performance des bureaux</span>
            </h3>
            <Link 
              :href="route('admin.geo.bureaux-de-vote.index')" 
              class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1"
            >
              <span>Voir tous</span>
              <i class="fas fa-chevron-right text-xs"></i>
            </Link>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div 
              v-for="bureau in topBureaux" 
              :key="bureau.code" 
              class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow cursor-pointer"
              @click="$inertia.visit(route('admin.geo.bureaux-de-vote.show', bureau.code))"
            >
              <div class="flex items-start gap-3">
                <div class="flex-shrink-0 h-10 w-10 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600">
                  <i class="fas fa-vote-yea"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="font-bold text-gray-900 truncate">{{ bureau.nom }}</h4>
                  <div class="flex items-center gap-1 text-xs text-gray-500 mt-1">
                    <i class="fas fa-map-marker-alt"></i>
                    <span class="truncate">{{ bureau.commune?.nom }}</span>
                  </div>
                </div>
              </div>
              <div class="mt-4">
                <div class="flex justify-between text-xs text-gray-600 mb-1">
                  <span>Inscriptions</span>
                  <span class="font-medium">{{ bureau.inscriptions_count }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                  <div 
                    class="bg-indigo-600 h-1.5 rounded-full" 
                    :style="`width: ${Math.min(100, (bureau.inscriptions_count / stats.total_inscriptions) * 100)}%`"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Tendance des inscriptions -->
      <section class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-chart-line text-indigo-500"></i>
            <span>Évolution des inscriptions</span>
          </h3>
          <div class="h-80">
            <LineChart :data="evolutionChartData" :options="chartOptions" />
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import StatCard from '@/Components/StatCard.vue';
import LineChart from '@/Components/Charts/LineChart.vue';

const props = defineProps({
  stats: Object,
  latestInscriptions: Array,
  topBureaux: Array,
  byRegion: {
    type: Array,
    default: () => []
  },
  evolutionData: {
    type: Array,
    default: () => []
  }
});

const breadcrumbs = [
  { label: 'Dashboard', url: route('dashboard') },
  { label: 'Suivi des votants', active: true },
];

const regionChartData = computed(() => {
  const data = props.byRegion ?? [];
  return {
    labels: data.map(r => r.region_name),
    datasets: [{
      label: 'Votants',
      backgroundColor: '#6366F1',
      borderColor: '#4F46E5',
      borderWidth: 1,
      borderRadius: 4,
      data: data.map(r => r.inscriptions_count),
    }]
  };
});

const evolutionChartData = computed(() => {
  const data = props.evolutionData ?? [];
  return {
    labels: data.map(item => item.date),
    datasets: [{
      label: 'Inscriptions',
      data: data.map(item => item.count),
      borderColor: '#6366F1',
      backgroundColor: 'rgba(99, 102, 241, 0.1)',
      borderWidth: 2,
      tension: 0.3,
      fill: true
    }]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#111827',
      titleFont: { size: 12, weight: 'bold' },
      bodyFont: { size: 11 },
      padding: 10,
      cornerRadius: 8,
      displayColors: false
    }
  },
  scales: {
    y: { beginAtZero: true, grid: { drawBorder: false } },
    x: { grid: { display: false } }
  }
};

const formatDate = (dateString) => new Date(dateString).toLocaleDateString('fr-FR', {
  day: 'numeric',
  month: 'short',
  year: 'numeric'
});
</script>

<style scoped>
.hover-card {
  transition: all 0.2s ease;
}
.hover-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
.scroll-container::-webkit-scrollbar {
  height: 6px;
}
.scroll-container::-webkit-scrollbar-thumb {
  background-color: #E5E7EB;
  border-radius: 3px;
}
</style>
