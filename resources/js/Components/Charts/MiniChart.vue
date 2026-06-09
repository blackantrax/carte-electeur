<template>
  <div class="mini-chart" :style="{ height: '60px' }">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  data: Object,
  color: {
    type: String,
    default: 'primary'
  }
});

const chartColors = {
  primary: 'rgba(13, 110, 253, 0.2)',
  success: 'rgba(25, 135, 84, 0.2)',
  info: 'rgba(13, 202, 240, 0.2)',
  warning: 'rgba(255, 193, 7, 0.2)'
};

const chartCanvas = ref(null);
let chartInstance = null;

onMounted(() => {
  renderChart();
});

watch(() => props.data, () => {
  renderChart();
}, { deep: true });

const renderChart = () => {
  if (chartInstance) {
    chartInstance.destroy();
  }

  if (chartCanvas.value && props.data) {
    chartInstance = new Chart(chartCanvas.value, {
      type: 'line',
      data: {
        labels: props.data.labels || [],
        datasets: [{
          data: props.data.values || [],
          backgroundColor: chartColors[props.color] || chartColors.primary,
          borderColor: chartColors[props.color] || chartColors.primary,
          borderWidth: 1,
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { enabled: false }
        },
        scales: {
          x: { display: false },
          y: { display: false }
        }
      }
    });
  }
};
</script>