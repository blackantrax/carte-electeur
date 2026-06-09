<template>
  <div class="flex items-start p-3 hover:bg-gray-50 rounded-lg transition-colors">
    <div class="flex-shrink-0 mt-1">
      <span 
        class="flex items-center justify-center h-9 w-9 rounded-lg"
        :class="`bg-${activityColor}-50 text-${activityColor}-600`"
      >
        <i :class="`bi ${activityIcon}`"></i>
      </span>
    </div>
    <div class="ml-3 flex-1 min-w-0">
      <div class="flex items-baseline justify-between">
        <p class="text-sm font-medium text-gray-900">
          {{ activity.name }}
        </p>
        <span class="text-xs text-gray-500">
          {{ formatDate(activity.date) }}
        </span>
      </div>
      <p class="text-xs text-gray-500 mt-1">
        {{ activity.action }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  activity: {
    type: Object,
    required: true
  }
})

const activityColor = computed(() => {
  const colors = {
    'Pays': 'primary',
    'Région': 'success',
    'Département': 'info',
    'Commune': 'warning',
    'Bureau': 'danger'
  }
  return colors[props.activity.type] || 'secondary'
})

const activityIcon = computed(() => {
  const icons = {
    'Pays': 'bi-flag-fill',
    'Région': 'bi-pin-map-fill',
    'Département': 'bi-map-fill',
    'Commune': 'bi-geo-alt-fill',
    'Bureau': 'bi-building-fill'
  }
  return icons[props.activity.type] || 'bi-activity'
})

const formatDate = (dateString) => {
  const options = { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }
  return new Date(dateString).toLocaleDateString('fr-FR', options)
}
</script>