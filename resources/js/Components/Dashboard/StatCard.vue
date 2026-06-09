<template>
  <div 
    class="col-md-6 col-xl-3"
    @click="$emit('click')"
  >
    <div 
      class="card h-100 border-start"
      :class="`border-${color} border-4`"
      :style="{ cursor: 'pointer' }"
    >
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="p-3 rounded me-3" :class="`bg-${color} bg-opacity-10`">
            <i class="bi" :class="`bi-${icon} text-${color} fs-3`"></i>
          </div>
          <div>
            <h3 class="h6 text-muted mb-1">{{ title }}</h3>
            <div v-if="!loading">
              <div class="d-flex align-items-end">
                <p class="h4 mb-0 me-2">{{ safeStats.total }}</p>
                <small 
                  v-if="safeStats.change !== undefined" 
                  class="fw-bold" 
                  :class="`text-${trendColor}`"
                >
                  <i class="bi" :class="trendIcon"></i> {{ safeStats.change }}
                </small>
              </div>
            </div>
            <div v-else class="placeholder-glow">
              <span class="placeholder col-6"></span>
            </div>
          </div>
        </div>
        <hr class="my-3">
        <slot>
          <div v-if="safeStats.latest?.length">
            <h4 class="h6 mb-2">Récents :</h4>
            <ul class="list-unstyled">
              <li v-for="item in safeStats.latest.slice(0, 3)" :key="item.id" class="mb-1">
                <Link 
                  v-if="item.id"
                  :href="getItemUrl(item)" 
                  class="d-flex align-items-center text-decoration-none"
                >
                  <i class="bi me-2" :class="getItemIcon(item)"></i>
                  <span class="text-truncate" style="max-width: 120px;">
                    {{ item.title || item.name || 'Sans titre' }}
                  </span>
                </Link>
              </li>
            </ul>
          </div>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: value => ['articles', 'events', 'inscriptions', 'media'].includes(value)
  },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      change: 0,
      trend: 'stable',
      latest: []
    })
  },
  color: {
    type: String,
    default: 'primary',
    validator: value => ['primary', 'success', 'info', 'warning', 'danger'].includes(value)
  },
  icon: {
    type: String,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
});

// Safe stats with fallback values
const safeStats = computed(() => ({
  total: props.stats?.total || 0,
  change: props.stats?.change ?? 0,
  trend: props.stats?.trend || 'stable',
  latest: Array.isArray(props.stats?.latest) ? props.stats.latest : []
}));

const title = computed(() => ({
  articles: 'Articles',
  events: 'Événements',
  inscriptions: 'Inscriptions',
  media: 'Médias'
})[props.type]);

const trendColor = computed(() => ({
  up: 'success',
  down: 'danger',
  stable: props.color
})[safeStats.value.trend] || props.color);

const trendIcon = computed(() => ({
  up: 'bi-arrow-up',
  down: 'bi-arrow-down',
  stable: 'bi-dash'
})[safeStats.value.trend] || 'bi-dash');

const getItemUrl = (item) => {
  return `/${props.type}/${item.id}/edit`;
};

const getItemIcon = (item) => {
  if (props.type === 'articles') {
    return item.status === 'published' 
      ? 'bi-check-circle text-success' 
      : 'bi-pencil text-secondary';
  }
  if (props.type === 'events') {
    return new Date(item.date) > new Date() 
      ? 'bi-calendar-check text-info' 
      : 'bi-calendar-x text-secondary';
  }
  return 'bi-file-earmark';
};
</script>