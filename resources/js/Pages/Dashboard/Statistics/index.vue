<template>
  <AppLayout>
    <PageTitle
      title="Tableau de Bord Statistique"
      :breadcrumbs="breadcrumbs"
    >
      <template #actions>
        <button 
          @click="refreshData" 
          class="btn btn-outline-primary mr-2"
          :disabled="loading"
        >
          <i class="bi bi-arrow-repeat" :class="{ 'animate-spin': loading }"></i> Actualiser
        </button>
      </template>
    </PageTitle>

    <div class="dashboard-container">
      <!-- Filtres -->
      <div class="filters-section bg-white p-4 rounded-lg shadow-sm mb-6">
        <div class="flex flex-wrap items-end gap-4">
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Période</label>
            <select 
              v-model="filters.period" 
              class="select-filter"
              @change="fetchData"
            >
              <option value="today">Aujourd'hui</option>
              <option value="week">Cette semaine</option>
              <option value="month">Ce mois</option>
              <option value="year">Cette année</option>
              <option value="all">Toutes périodes</option>
            </select>
          </div>

          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select 
              v-model="filters.type" 
              class="select-filter"
              @change="fetchData"
            >
              <option value="all">Tous types</option>
              <option value="articles">Articles</option>
              <option value="events">Événements</option>
              <option value="media">Médias</option>
            </select>
          </div>

          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
            <select 
              v-model="filters.status" 
              class="select-filter"
              @change="fetchData"
            >
              <option value="all">Tous statuts</option>
              <option value="published">Publié</option>
              <option value="draft">Brouillon</option>
              <option value="pending">En attente</option>
            </select>
          </div>

          <button 
            @click="resetFilters"
            class="btn btn-outline-secondary h-[42px]"
          >
            <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
          </button>
        </div>
      </div>

      <!-- Cartes statistiques -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <StatCard 
          v-for="stat in filteredStats" 
          :key="stat.title"
          :title="stat.title"
          :value="stat.value"
          :change="stat.change"
          :icon="stat.icon"
          :color="stat.color"
          :loading="loading"
        />
      </div>

      <!-- Graphique principal -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">Analytiques</h3>
          <div class="flex space-x-1">
            <button
              v-for="view in chartViews"
              :key="view"
              @click="activeChartView = view"
              class="chart-view-btn"
              :class="{ 'active': activeChartView === view }"
            >
              {{ viewLabels[view] }}
            </button>
          </div>
        </div>
        <div class="h-80">
          <MainChart 
            :data="safeChartData" 
            :type="activeChartView" 
            :loading="chartLoading"
          />
        </div>
      </div>

      <!-- Section Electeurs -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistiques des Cartes d'Électeurs</h3>
        <ElectorsStats 
          :key="electorsKey"
          :loading="electorsLoading"
          @refresh="fetchElectorsData"
        />
      </div>

      <!-- Tableau de données -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-800">Détails des Données</h3>
          <div class="relative w-64">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher..."
              class="search-input"
              @input="handleSearch"
            >
            <i class="bi bi-search absolute right-3 top-3 text-gray-400"></i>
          </div>
        </div>
        <DataTable 
          :items="filteredTableData"
          :columns="columns"
          :loading="tableLoading"
          @sort="handleSort"
          class="border-none"
        />
        <Pagination 
          v-if="pagination.total > pagination.perPage"
          :current="pagination.currentPage"
          :total="pagination.total"
          :per-page="pagination.perPage"
          @page-changed="changePage"
          class="p-4 border-t"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import StatCard from '@/Components/StatCard.vue';
import MainChart from '@/Components/Charts/MainChart.vue';
import DataTable from '@/Components/Datatable.vue';
import Pagination from '@/Components/Pagination.vue';
import ElectorsStats from './electors.vue';

// Validation des props
const props = defineProps({
  initialStats: {
    type: Object,
    default: () => ({
      articles: { total: 0, change: 0, trend: 'stable', chart: { labels: [], datasets: [] } },
      events: { total: 0, change: 0, chart: { labels: [], datasets: [] } },
      views: { total: 0, change: 0, chart: { labels: [], datasets: [] } },
      engagement: { total: 0, change: 0, chart: { labels: [], datasets: [] } }
    })
  },
  initialChartData: {
    type: Array,
    default: () => [],
    validator: value => Array.isArray(value)
  },
  initialTableData: {
    type: Array,
    default: () => [],
    validator: value => Array.isArray(value)
  },
  filters: {
    type: Object,
    default: () => ({
      period: 'week',
      type: 'all',
      status: 'all'
    })
  }
});

// Réactivité
const stats = ref(deepClone(props.initialStats));
const chartData = ref(Array.isArray(props.initialChartData) ? [...props.initialChartData] : getDefaultChartData());
const tableData = ref(Array.isArray(props.initialTableData) ? [...props.initialTableData] : []);
const electorsKey = ref(0);
const electorsLoading = ref(false);
const loading = ref(false);
const tableLoading = ref(false);
const chartLoading = ref(false);
const searchQuery = ref('');

const filters = ref({ ...props.filters });
const activeChartView = ref('line');
const chartViews = ['line', 'bar', 'pie'];
const viewLabels = { line: 'Ligne', bar: 'Barres', pie: 'Camembert' };

const pagination = ref({
  currentPage: 1,
  total: tableData.value.length,
  perPage: 10,
  lastPage: Math.ceil(tableData.value.length / 10)
});

const columns = [
  { key: 'title', label: 'Titre', sortable: true, width: '30%' },
  { key: 'type', label: 'Type', sortable: true, width: '15%' },
  { key: 'status', label: 'Statut', sortable: true, width: '15%' },
  { key: 'views', label: 'Vues', sortable: true, align: 'right', width: '15%' },
  { key: 'date', label: 'Date', sortable: true, width: '25%' }
];

const breadcrumbs = [
  { label: 'Accueil', url: '/' },
  { label: 'Statistiques', active: true }
];

// Computed
const filteredStats = computed(() => {
  return ['articles', 'events', 'views', 'engagement'].map(key => {
    const stat = stats.value[key] || {};
    return {
      title: getTitle(key),
      value: stat.total || 0,
      change: stat.change ?? getDefaultChange(stat.trend),
      icon: getIcon(key),
      color: getColor(key),
      chart: stat.chart || getDefaultChart()
    };
  });
});

const safeChartData = computed(() => {
  return Array.isArray(chartData.value) && chartData.value.length > 0 
    ? chartData.value 
    : getDefaultChartData();
});

const filteredTableData = computed(() => {
  if (!searchQuery.value) return tableData.value;
  
  const query = searchQuery.value.toLowerCase();
  return tableData.value.filter(item => 
    columns.some(col => 
      String(item[col.key]).toLowerCase().includes(query)
    )
  );
});

// Méthodes
function deepClone(obj) {
  return JSON.parse(JSON.stringify(obj));
}

function getTitle(key) {
  const titles = {
    articles: 'Articles',
    events: 'Événements',
    views: 'Vues',
    engagement: 'Engagement'
  };
  return titles[key] || key;
}

function getIcon(key) {
  const icons = {
    articles: 'file-earmark-text',
    events: 'calendar-event',
    views: 'eye',
    engagement: 'people'
  };
  return icons[key] || 'circle';
}

function getColor(key) {
  const colors = {
    articles: 'primary',
    events: 'success',
    views: 'info',
    engagement: 'warning'
  };
  return colors[key] || 'secondary';
}

function getDefaultChange(trend) {
  return trend === 'up' ? 10 : trend === 'down' ? -5 : 0;
}

function getDefaultChart() {
  return { labels: [], datasets: [{ data: [], backgroundColor: [] }] };
}

function getDefaultChartData() {
  return [
    {
      label: 'Données par défaut',
      data: [1, 1, 1],
      backgroundColor: '#f3f4f6'
    }
  ];
}

async function fetchData() {
  try {
    loading.value = true;
    chartLoading.value = true;
    tableLoading.value = true;
    
    const response = await router.get(route('dashboard.statistics'), {
      period: filters.value.period,
      type: filters.value.type,
      status: filters.value.status,
      page: pagination.value.currentPage,
      search: searchQuery.value
    }, {
      preserveState: true,
      preserveScroll: true,
      only: ['initialStats', 'initialChartData', 'initialTableData']
    });

    // Validation des données reçues
    if (response.props.initialStats) {
      stats.value = {
        articles: response.props.initialStats.articles || stats.value.articles,
        events: response.props.initialStats.events || stats.value.events,
        views: response.props.initialStats.views || stats.value.views,
        engagement: response.props.initialStats.engagement || stats.value.engagement
      };
    }

    chartData.value = Array.isArray(response.props.initialChartData)
      ? [...response.props.initialChartData]
      : chartData.value;

    tableData.value = Array.isArray(response.props.initialTableData)
      ? [...response.props.initialTableData]
      : tableData.value;

    // Mise à jour pagination
    pagination.value = {
      currentPage: response.props.page || 1,
      total: response.props.total || tableData.value.length,
      perPage: response.props.perPage || 10,
      lastPage: response.props.lastPage || Math.ceil(tableData.value.length / 10)
    };
    
  } catch (error) {
    console.error('Error fetching data:', error);
  } finally {
    loading.value = false;
    chartLoading.value = false;
    tableLoading.value = false;
  }
}

async function fetchElectorsData() {
  try {
    electorsLoading.value = true;
    electorsKey.value++;
    await fetchData();
  } finally {
    electorsLoading.value = false;
  }
}

function resetFilters() {
  filters.value = { period: 'week', type: 'all', status: 'all' };
  searchQuery.value = '';
  pagination.value.currentPage = 1;
  fetchData();
}

function refreshData() {
  fetchData();
  fetchElectorsData();
}

function changePage(page) {
  if (page < 1 || page > pagination.value.lastPage) return;
  pagination.value.currentPage = page;
  fetchData();
}

function handleSearch() {
  // Debounce pourrait être ajouté ici pour les grosses datasets
  pagination.value.currentPage = 1;
  fetchData();
}

function handleSort(column, direction) {
  tableData.value.sort((a, b) => {
    const valA = a[column];
    const valB = b[column];
    
    if (typeof valA === 'number' && typeof valB === 'number') {
      return direction === 'asc' ? valA - valB : valB - valA;
    }
    
    if (valA instanceof Date && valB instanceof Date) {
      return direction === 'asc' ? valA - valB : valB - valA;
    }
    
    return direction === 'asc' 
      ? String(valA).localeCompare(String(valB))
      : String(valB).localeCompare(String(valA));
  });
}

// Initialisation
onMounted(() => {
  if (!props.initialStats || Object.keys(props.initialStats).length === 0) {
    fetchData();
  }
});
</script>

<style scoped>
.dashboard-container {
  @apply space-y-6;
}

.filters-section {
  @apply border border-gray-200;
}

.select-filter {
  @apply w-full p-2 pl-3 pr-8 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none;
}

.chart-view-btn {
  @apply px-3 py-1 text-sm font-medium rounded-md transition-colors;
  &.active {
    @apply bg-blue-500 text-white;
  }
  &:not(.active) {
    @apply bg-gray-100 text-gray-700 hover:bg-gray-200;
  }
}

.search-input {
  @apply w-full p-2 pl-3 pr-8 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>