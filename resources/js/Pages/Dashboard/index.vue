<template>
  <AppLayout title="Tableau de Bord">
    <div class="container-fluid py-4">
      <!-- Header amélioré -->
      <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
          <h1 class="h2 fw-bold mb-1 text-gray-800">Tableau de Bord</h1>
          <p class="text-muted mb-0">
            <i class="bi bi-clock-history me-1"></i> Mis à jour à {{ lastUpdated }}
          </p>
        </div>
        <div class="d-flex align-items-center">
          <button @click="refreshData" class="btn btn-sm btn-outline-secondary me-2" :disabled="loading">
            <i class="bi bi-arrow-clockwise" :class="{ 'animate-spin': loading }"></i>
          </button>
          <div class="bg-light rounded-circle p-2 position-relative">
            <i class="bi bi-person fs-4"></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
              <span class="visually-hidden">Statut</span>
            </span>
          </div>
        </div>
      </div>

      <!-- Statistiques améliorées -->
      <div class="row g-4 mb-4">
        <!-- Carte Articles -->
        <StatCard 
          type="articles"
          :stats="stats.articles"
          color="primary"
          icon="file-earmark-text"
          :loading="loading"
          @click="activeTab = 'articles'"
        />

        <!-- Carte Événements -->
        <StatCard 
          type="events"
          :stats="stats.events"
          color="success"
          icon="calendar-event"
          :loading="loading"
          @click="activeTab = 'events'"
        />

        <!-- Carte Inscriptions -->
        <StatCard 
          type="inscriptions"
          :stats="stats.inscriptions"
          color="warning"
          icon="people"
          :loading="loading"
          @click="activeTab = 'inscriptions'"
        />

        <!-- Carte Médias -->
        <StatCard 
          type="media"
          :stats="stats.media"
          color="info"
          icon="collection"
          :loading="loading"
          @click="activeTab = 'media'"
        />
      </div>

      <!-- Contenu principal avec onglets -->
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card h-100">
            <div class="card-header bg-white border-bottom-0">
              <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item" v-for="tab in tabs" :key="tab.id">
                  <button
                    class="nav-link"
                    :class="{ active: activeTab === tab.id }"
                    @click="activeTab = tab.id"
                  >
                    <i class="bi" :class="`bi-${tab.icon}`"></i> {{ tab.label }}
                  </button>
                </li>
              </ul>
            </div>
            <div class="card-body">
              <!-- Contenu des onglets -->
              <div v-show="activeTab === 'articles'">
                <h3 class="h5 mb-3">Derniers articles</h3>
                <ArticleList :articles="stats.articles.latest" />
                <Link href="/admin/blogs" class="btn btn-sm btn-outline-primary mt-3">
                  Voir tous les articles
                </Link>
              </div>

              <div v-show="activeTab === 'events'">
                <h3 class="h5 mb-3">Prochains événements</h3>
                <EventList :events="stats.events.latest" />
                <Link href="/admin/events" class="btn btn-sm btn-outline-success mt-3">
                  Voir tous les événements
                </Link>
              </div>

              <div v-show="activeTab === 'inscriptions'">
                <InscriptionStats :data="stats.inscriptions" />
              </div>

              <div v-show="activeTab === 'media'">
                <MediaStats :data="stats.media" />
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar droite -->
        <div class="col-lg-4">
          <!-- Actions rapides -->
          <QuickActions class="mb-4" />

          <!-- Activité récente -->
          <RecentActivity :activities="recentActivity" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import ArticleList from '@/Components/Dashboard/ArticleList.vue';
import EventList from '@/Components/Dashboard/EventList.vue';
import InscriptionStats from '@/Components/Dashboard/InscriptionStats.vue';
import MediaStats from '@/Components/Dashboard/MediaStats.vue';
import QuickActions from '@/Components/Dashboard/QuickActions.vue';
import RecentActivity from '@/Components/Dashboard/RecentActivity.vue';

const props = defineProps({
  stats: Object,
  recentActivity: Array
});

const loading = ref(false);
const activeTab = ref('articles');
const lastUpdated = computed(() => new Date().toLocaleTimeString());

const tabs = [
  { id: 'articles', label: 'Articles', icon: 'file-earmark-text' },
  { id: 'events', label: 'Événements', icon: 'calendar-event' },
  { id: 'inscriptions', label: 'Inscriptions', icon: 'people' },
  { id: 'media', label: 'Médias', icon: 'collection' }
];

const refreshData = async () => {
  loading.value = true;
  await router.reload({
    only: ['stats', 'recentActivity'],
    preserveScroll: true,
    onFinish: () => {
      loading.value = false;
    }
  });
};
</script>

<style scoped>
.nav-tabs .nav-link {
  border: none;
  color: #6c757d;
  font-weight: 500;
}

.nav-tabs .nav-link.active {
  color: #0d6efd;
  border-bottom: 2px solid #0d6efd;
  background-color: transparent;
}

.nav-tabs .nav-link:hover:not(.active) {
  color: #0d6efd;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.card {
  transition: all 0.2s ease;
}

.card:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}
</style>