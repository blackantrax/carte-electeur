<template>
  <div class="flex justify-center items-center">
    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2" :class="`border-${color}-500`"></div>
  </div>
</template>

<script setup>
defineProps({
  color: {
    type: String,
    default: 'blue' // Couleur par défaut
  },
  size: {
    type: String,
    default: 'md' // Taille par défaut (sm, md, lg)
  }
});
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>