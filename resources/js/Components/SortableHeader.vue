<template>
  <button 
    type="button"
    class="flex items-center space-x-1 focus:outline-none group"
    @click="toggleSort"
  >
    <span>{{ label }}</span>
    <span class="flex flex-col items-center justify-center">
      <!-- Flèche ascendante -->
      <svg 
        class="w-3 h-3"
        :class="{
          'text-yellow-400': currentSort === sortKey && currentDirection === 'asc',
          'text-gray-400 group-hover:text-gray-300': currentSort !== sortKey || currentDirection !== 'asc'
        }"
        fill="none" 
        viewBox="0 0 24 24" 
        stroke="currentColor"
      >
        <path 
          stroke-linecap="round" 
          stroke-linejoin="round" 
          stroke-width="2" 
          d="M5 15l7-7 7 7"
        />
      </svg>
      
      <!-- Flèche descendante -->
      <svg 
        class="w-3 h-3"
        :class="{
          'text-yellow-400': currentSort === sortKey && currentDirection === 'desc',
          'text-gray-400 group-hover:text-gray-300': currentSort !== sortKey || currentDirection !== 'desc'
        }"
        fill="none" 
        viewBox="0 0 24 24" 
        stroke="currentColor"
      >
        <path 
          stroke-linecap="round" 
          stroke-linejoin="round" 
          stroke-width="2" 
          d="M19 9l-7 7-7-7"
        />
      </svg>
    </span>
  </button>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  sortKey: {
    type: String,
    required: true
  },
  currentSort: {
    type: String,
    default: ''
  },
  currentDirection: {
    type: String,
    default: 'asc'
  }
});

const emit = defineEmits(['sort']);

const toggleSort = () => {
  let direction = 'asc';
  
  if (props.currentSort === props.sortKey) {
    direction = props.currentDirection === 'asc' ? 'desc' : 'asc';
  }

  emit('sort', {
    key: props.sortKey,
    direction: direction
  });
};
</script>

<style scoped>
button {
  transition: color 0.2s ease;
}

button:hover {
  @apply text-yellow-400;
}

svg {
  transition: all 0.2s ease;
}
</style>