<template>
  <div>
    <h3 class="h5 mb-3">Statistiques des inscriptions</h3>
    <div class="row mb-4">
      <div class="col-6">
        <div class="card bg-light">
          <div class="card-body text-center">
            <h4 class="text-primary">{{ data.total }}</h4>
            <p class="small mb-0">Total</p>
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="card bg-light">
          <div class="card-body text-center">
            <h4 class="text-success">{{ data.recent }}</h4>
            <p class="small mb-0">7 derniers jours</p>
          </div>
        </div>
      </div>
    </div>
    <Link href="/inscriptions" class="btn btn-primary w-100">
      <i class="bi bi-table me-1"></i> Voir toutes les inscriptions
    </Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  data: {
    type: Object,
    required: true
  }
});
</script>