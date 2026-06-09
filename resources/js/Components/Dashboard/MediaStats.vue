<template>
  <div>
    <h3 class="h5 mb-3">Statistiques des médias</h3>
    <div class="row mb-3">
      <div class="col-6">
        <div class="card bg-light">
          <div class="card-body text-center">
            <h4 class="text-info">{{ data.videos }}</h4>
            <p class="small mb-0">
              <i class="bi bi-play-btn"></i> Vidéos
            </p>
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="card bg-light">
          <div class="card-body text-center">
            <h4 class="text-primary">{{ data.slides }}</h4>
            <p class="small mb-0">
              <i class="bi bi-images"></i> Slides
            </p>
          </div>
        </div>
      </div>
    </div>
    <div class="d-grid gap-2">
      <Link href="/videos" class="btn btn-outline-info">
        <i class="bi bi-play-btn me-1"></i> Gérer vidéos
      </Link>
      <Link href="/slideshows" class="btn btn-outline-primary">
        <i class="bi bi-images me-1"></i> Gérer slides
      </Link>
    </div>
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