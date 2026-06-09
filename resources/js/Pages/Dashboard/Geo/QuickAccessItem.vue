<template>
  <Link
    :href="module.link"
    class="group flex flex-col items-center text-center p-4 rounded-lg border border-gray-200 hover:border-primary-300 hover:shadow-xs transition-all"
  >
    <span 
      class="flex items-center justify-center h-12 w-12 rounded-xl mb-3"
      :class="`bg-${module.color}-50 text-${module.color}-600 group-hover:bg-${module.color}-100`"
    >
      <i :class="`bi ${module.icon} text-xl`"></i>
    </span>
    <h3 class="font-medium text-gray-900 group-hover:text-primary-600">
      {{ module.title }}
    </h3>
    <p class="text-xs text-gray-500 mt-1">
      {{ module.count }} éléments
    </p>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  module: {
    type: Object,
    required: true
  }
})
</script>