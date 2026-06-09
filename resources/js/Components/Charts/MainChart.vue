<template>
  <div v-if="loading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Chargement...</span>
    </div>
  </div>
  <div v-else class="main-chart" :style="{ height: '400px' }">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  data: Array,
  type: {
    type: String,
    default: 'line'
  },
  loading: Boolean
});

const chartCanvas = ref(null);
let chartInstance = null;

onMounted(() => {
  renderChart();
});

watch(() => [props.data, props.type], () => {
  renderChart();
}, { deep: true });

const renderChart = () => {
  if (chartInstance) {
    chartInstance.destroy();
  }

  if (chartCanvas.value && props.data) {
    const config = {
      type: props.type,
      data: {
        labels: props.data.map(item => item.label),
        datasets: [
          {
            label: 'Articles',
            data: props.data.map(item => item.articles),
            backgroundColor: 'rgba(13, 110, 253, 0.5)',
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 2
          },
          {
            label: 'Événements',
            data: props.data.map(item => item.events),
            backgroundColor: 'rgba(25, 135, 84, 0.5)',
            borderColor: 'rgba(25, 135, 84, 1)',
            borderWidth: 2
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          title: {
            display: true,
            text: 'Activité par période'
          },
          tooltip: {
            mode: 'index',
            intersect: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    };

    chartInstance = new Chart(chartCanvas.value, config);
  }
};
</script>