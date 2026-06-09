<template>
  <div class="chart-container relative h-full w-full">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

// Enregistrer les composants nécessaires
Chart.register(...registerables);

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  }
});

const chartCanvas = ref(null);
let chartInstance = null;

const initChart = () => {
  if (chartCanvas.value) {
    // Détruire l'instance précédente si elle existe
    if (chartInstance) {
      chartInstance.destroy();
    }
    
    // Créer un nouveau graphique
    chartInstance = new Chart(chartCanvas.value, {
      type: 'line',
      data: props.data,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          },
          tooltip: {
            mode: 'index',
            intersect: false
          }
        },
        scales: {
          y: {
            beginAtZero: false,
            grid: {
              drawBorder: false
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        },
        interaction: {
          intersect: false,
          mode: 'nearest'
        },
        ...props.options
      }
    });
  }
};

// Initialiser le graphique au montage
onMounted(initChart);

// Mettre à jour le graphique quand les données changent
watch(() => props.data, () => {
  initChart();
}, { deep: true });
</script>

<style scoped>
.chart-container {
  min-height: 300px;
}
</style>