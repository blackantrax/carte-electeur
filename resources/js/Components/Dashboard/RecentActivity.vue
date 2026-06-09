<template>
  <div class="card h-100">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h2 class="h5 mb-0">Activité Récente</h2>
      <Link href="/activity" class="btn btn-sm btn-outline-secondary">
        Voir tout
      </Link>
    </div>
    <div class="card-body p-0">
      <ul class="list-group list-group-flush">
        <li 
          v-for="(activity, index) in activities" 
          :key="index" 
          class="list-group-item border-0 px-3 py-2 hover-bg"
        >
          <Link :href="activity.url" class="text-decoration-none text-dark d-block">
            <div class="d-flex">
              <div class="flex-shrink-0">
                <div class="bg-light rounded-circle p-2 me-3">
                  <i class="bi" :class="activity.icon"></i>
                </div>
              </div>
              <div class="flex-grow-1">
                <p class="mb-0 fw-bold">{{ activity.title }}</p>
                <small class="text-muted d-block">
                  {{ activity.action }} · {{ activity.time }}
                </small>
              </div>
            </div>
          </Link>
        </li>
        <li v-if="!activities.length" class="list-group-item border-0 px-3 py-4 text-center text-muted">
          <i class="bi bi-inbox fs-4 d-block mb-2"></i>
          Aucune activité récente
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  activities: {
    type: Array,
    required: true,
    default: () => []
  }
});
</script>

<style scoped>
.hover-bg:hover {
  background-color: rgba(0, 0, 0, 0.03);
}
</style>