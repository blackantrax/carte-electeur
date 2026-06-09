<template>
  <div class="relative w-full h-full">
    <div v-if="level === 'regions'" class="absolute inset-0 flex items-center justify-center">
      <img 
        src="/images/maps/regions-map.png" 
        alt="Carte des régions" 
        class="max-h-full max-w-full object-contain"
      />
    </div>
    <div v-else-if="level === 'departments'" class="absolute inset-0 flex items-center justify-center">
      <img 
        src="/images/maps/departments-map.png" 
        alt="Carte des départements" 
        class="max-h-full max-w-full object-contain"
      />
    </div>
    <div v-else class="absolute inset-0 flex items-center justify-center">
      <img 
        src="/images/maps/communes-map.png" 
        alt="Carte des communes" 
        class="max-h-full max-w-full object-contain"
      />
    </div>
  </div>
</template>

<script setup>
defineProps({
  level: {
    type: String,
    default: 'regions'
  }
})
</script>