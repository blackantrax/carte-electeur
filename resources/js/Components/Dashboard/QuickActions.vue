<template>
  <div class="card">
    <div class="card-header bg-white">
      <h2 class="h5 mb-0">Actions Rapides</h2>
    </div>
    <div class="card-body">
      <div class="row g-2">
        <div class="col-6">
          <Link href="/admin/blogs/create" class="btn btn-primary w-100">
            <i class="bi bi-plus-lg me-1"></i> Article
          </Link>
        </div>
        <div class="col-6">
          <Link href="/admin/events/create" class="btn btn-success w-100">
            <i class="bi bi-plus-lg me-1"></i> Événement
          </Link>
        </div>
        <div class="col-6">
          <Link href="/admin/videos/create" class="btn btn-info w-100">
            <i class="bi bi-plus-lg me-1"></i> Vidéo
          </Link>
        </div>
        <div class="col-6">
          <Link href="/admin/slideshows/create" class="btn btn-primary w-100">
            <i class="bi bi-plus-lg me-1"></i> Slideshow
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>