<template>
  <Link
    :href="item.route"
    class="group flex items-center w-full px-3 py-2.5 text-sm rounded-md transition-colors duration-200"
    :class="{
      'bg-gray-800 text-yellow-400 font-medium': isActive,
      'text-gray-300 hover:bg-gray-800 hover:text-white': !isActive
    }"
  >
    <span 
      class="flex items-center justify-center w-8 h-8 rounded-lg mr-3 transition-colors duration-200"
      :class="{
        'bg-yellow-400/20 text-yellow-400': isActive,
        'bg-gray-700 text-gray-400 group-hover:bg-yellow-400/10': !isActive
      }"
    >
      <i :class="`fas fa-${item.icon}`"></i>
    </span>
    <span class="truncate">{{ item.name }}</span>
    <span 
      v-if="isActive" 
      class="ml-auto w-1.5 h-6 bg-yellow-400 rounded-full"
    />
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  item: {
    type: Object,
    required: true
  },
  isActive: {
    type: Boolean,
    default: false
  }
})
</script>