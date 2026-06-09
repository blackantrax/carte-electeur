<template>
  <div 
    class="stat-card"
    :class="{
      'cursor-pointer hover:shadow-lg': clickable,
      [`border-l-4 border-${safeColor}-500`]: true
    }"
    @click="handleClick"
  >
    <div class="card-header">
      <div class="icon-container" :class="`bg-${safeColor}-50`">
        <i :class="`text-${safeColor}-600 ${icon}`"></i>
      </div>
      
      <div class="card-content">
        <p class="card-title">{{ title }}</p>
        <h3 class="card-value">
          <span v-if="loading" class="loading-placeholder"></span>
          <span v-else>{{ formattedValue }}</span>
        </h3>
      </div>
    </div>

    <div v-if="showFooter" class="card-footer">
      <div class="trend-badge" :class="trendClasses">
        <i :class="trendIcon"></i>
        <span>{{ trendText }}</span>
      </div>
      <p v-if="description" class="card-description">{{ description }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    default: 0
  },
  icon: {
    type: String,
    required: true
  },
  trend: {
    type: String,
    validator: val => ['up', 'down', 'neutral'].includes(val)
  },
  change: {
    type: [String, Number],
    default: null
  },
  color: {
    type: String,
    default: 'blue',
    validator: val => ['blue', 'green', 'red', 'purple', 'amber', 'indigo'].includes(val)
  },
  description: String,
  clickable: Boolean,
  loading: Boolean,
  format: Function
});

const emit = defineEmits(['click']);

const safeColor = computed(() => props.color || 'blue');

const formattedValue = computed(() => {
  if (props.format) return props.format(props.value);
  return typeof props.value === 'number' 
    ? props.value.toLocaleString() 
    : props.value;
});

const showFooter = computed(() => props.trend || props.description);

const trendClasses = computed(() => ({
  'bg-emerald-50 text-emerald-700': props.trend === 'up',
  'bg-rose-50 text-rose-700': props.trend === 'down',
  'bg-gray-100 text-gray-600': !props.trend || props.trend === 'neutral'
}));

const trendIcon = computed(() => ({
  'bi-arrow-up': props.trend === 'up',
  'bi-arrow-down': props.trend === 'down',
  'bi-dash': !props.trend || props.trend === 'neutral'
}));

const trendText = computed(() => {
  if (!props.change) return 'Stable';
  return typeof props.change === 'number'
    ? `${props.trend === 'up' ? '+' : ''}${props.change}%`
    : props.change;
});

const handleClick = () => {
  if (props.clickable) emit('click');
};
</script>

<style scoped>
.stat-card {
  @apply bg-white rounded-xl p-5 shadow-sm transition-all duration-200;
  border-left-width: 4px;
}

.card-header {
  @apply flex items-start gap-4;
}

.icon-container {
  @apply p-3 rounded-lg flex-shrink-0;
}

.card-content {
  @apply flex-1 min-w-0;
}

.card-title {
  @apply text-sm font-medium text-gray-500 truncate;
}

.card-value {
  @apply text-2xl font-semibold text-gray-900 mt-1;
}

.card-footer {
  @apply mt-4 flex items-center gap-2;
}

.trend-badge {
  @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
}

.card-description {
  @apply text-xs text-gray-500 truncate;
}

.loading-placeholder {
  @apply inline-block h-6 w-3/4 bg-gray-200 rounded animate-pulse;
}

.hover\:shadow-lg:hover {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}
</style>