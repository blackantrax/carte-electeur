<template>
  <div class="pie-chart-container">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue';
import { Chart, ArcElement, Tooltip, Legend } from 'chart.js';

// Enregistrer les éléments nécessaires pour le pie chart
Chart.register(ArcElement, Tooltip, Legend);

const props = defineProps({
  data: {
    type: Object,
    required: true,
    validator: value => {
      return value.labels && value.datasets;
    }
  },
  options: {
    type: Object,
    default: () => ({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const label = context.label || '';
              const value = context.raw || 0;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = Math.round((value / total) * 100);
              return `${label}: ${value} (${percentage}%)`;
            }
          }
        }
      }
    })
  },
  height: {
    type: Number,
    default: 300
  }
});

const chartCanvas = ref(null);
let chartInstance = null;

const initChart = () => {
  if (chartCanvas.value) {
    destroyChart();
    
    chartInstance = new Chart(chartCanvas.value, {
      type: 'pie',
      data: props.data,
      options: props.options
    });
  }
};

const destroyChart = () => {
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }
};

const updateChart = () => {
  if (chartInstance) {
    chartInstance.data = props.data;
    chartInstance.update();
  }
};

onMounted(initChart);
onBeforeUnmount(destroyChart);

watch(() => props.data, updateChart, { deep: true });
watch(() => props.options, updateChart, { deep: true });
</script>

<style scoped>
.pie-chart-container {
  position: relative;
  height: v-bind('props.height + "px"');
  width: 100%;
}

@media (max-width: 640px) {
  .pie-chart-container {
    height: calc(v-bind('props.height') * 0.8px);
  }
}
</style>