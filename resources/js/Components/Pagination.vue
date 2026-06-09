<template>
  <nav v-if="shouldShow" aria-label="Pagination">
    <ul class="pagination justify-content-center mb-0">
      <!-- Premier page -->
      <li class="page-item" :class="{ disabled: current === 1 }">
        <button 
          class="page-link" 
          @click="changePage(1)"
          aria-label="Première page"
        >
          <i class="bi bi-chevron-bar-left"></i>
        </button>
      </li>

      <!-- Page précédente -->
      <li class="page-item" :class="{ disabled: current === 1 }">
        <button 
          class="page-link" 
          @click="changePage(current - 1)"
          aria-label="Page précédente"
        >
          <i class="bi bi-chevron-left"></i>
        </button>
      </li>

      <!-- Pages avant la current si nécessaire -->
      <li 
        v-if="startPage > 1" 
        class="page-item disabled"
      >
        <span class="page-link">...</span>
      </li>

      <!-- Pages visibles -->
      <li 
        v-for="page in visiblePages" 
        :key="page"
        class="page-item" 
        :class="{ active: page === current }"
      >
        <button 
          class="page-link"
          @click="changePage(page)"
          :aria-current="page === current ? 'page' : null"
        >
          {{ page }}
        </button>
      </li>

      <!-- Pages après la current si nécessaire -->
      <li 
        v-if="endPage < totalPages" 
        class="page-item disabled"
      >
        <span class="page-link">...</span>
      </li>

      <!-- Page suivante -->
      <li class="page-item" :class="{ disabled: current === totalPages }">
        <button 
          class="page-link" 
          @click="changePage(current + 1)"
          aria-label="Page suivante"
        >
          <i class="bi bi-chevron-right"></i>
        </button>
      </li>

      <!-- Dernière page -->
      <li class="page-item" :class="{ disabled: current === totalPages }">
        <button 
          class="page-link" 
          @click="changePage(totalPages)"
          aria-label="Dernière page"
        >
          <i class="bi bi-chevron-bar-right"></i>
        </button>
      </li>
    </ul>

    <!-- Informations sur la pagination -->
    <div v-if="showInfo" class="text-center text-muted small mt-2">
      Affichage de {{ rangeStart }} à {{ rangeEnd }} sur {{ total }} éléments
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  current: {
    type: Number,
    required: true,
    validator: value => value >= 1
  },
  total: {
    type: Number,
    required: true,
    validator: value => value >= 0
  },
  perPage: {
    type: Number,
    default: 10,
    validator: value => value >= 1
  },
  maxVisible: {
    type: Number,
    default: 5,
    validator: value => value >= 3
  },
  showInfo: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['page-changed']);

// Calcul des valeurs dérivées
const totalPages = computed(() => Math.ceil(props.total / props.perPage));
const shouldShow = computed(() => totalPages.value > 1);

const rangeStart = computed(() => {
  return props.total === 0 ? 0 : (props.current - 1) * props.perPage + 1;
});

const rangeEnd = computed(() => {
  return Math.min(props.current * props.perPage, props.total);
});

const startPage = computed(() => {
  // Quand on est au début
  if (props.current <= Math.floor(props.maxVisible / 2) + 1) {
    return 1;
  }
  // Quand on est à la fin
  if (props.current >= totalPages.value - Math.floor(props.maxVisible / 2)) {
    return Math.max(1, totalPages.value - props.maxVisible + 1);
  }
  // Au milieu
  return props.current - Math.floor(props.maxVisible / 2);
});

const endPage = computed(() => {
  return Math.min(startPage.value + props.maxVisible - 1, totalPages.value);
});

const visiblePages = computed(() => {
  const pages = [];
  for (let i = startPage.value; i <= endPage.value; i++) {
    pages.push(i);
  }
  return pages;
});

// Méthodes
const changePage = (page) => {
  if (page < 1 || page > totalPages.value || page === props.current) return;
  emit('page-changed', page);
};
</script>

<style scoped>
.pagination {
  flex-wrap: wrap;
}

.page-link {
  min-width: 42px;
  text-align: center;
  margin: 0 2px;
  border-radius: 4px !important;
  border: 1px solid #dee2e6;
  color: #495057;
  transition: all 0.2s ease;
}

.page-item.active .page-link {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.page-item:not(.active):not(.disabled) .page-link:hover {
  background-color: #e9ecef;
  border-color: #dee2e6;
}

.page-item.disabled .page-link {
  color: #6c757d;
  pointer-events: none;
}

@media (max-width: 576px) {
  .page-link {
    min-width: 36px;
    padding: 0.375rem 0.5rem;
    font-size: 0.875rem;
  }
}
</style>