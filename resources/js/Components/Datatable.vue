<template>
  <div class="datatable-container">
    <!-- Tableau -->
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <!-- En-tête -->
        <thead class="table-light">
          <tr>     
            <th 
              v-for="column in columns" 
              :key="column.key"
              :class="{
                'text-end': column.align === 'right',
                'text-center': column.align === 'center',
                'cursor-pointer': column.sortable
              }"
              @click="column.sortable ? sort(column.key) : null"
            >
              <div class="d-flex align-items-center">
                <span>{{ column.label }}</span>
                <i 
                  v-if="column.sortable" 
                  class="bi ms-2"
                  :class="{
                    'bi-arrow-down': sortColumn === column.key && sortDirection === 'desc',
                    'bi-arrow-up': sortColumn === column.key && sortDirection === 'asc',
                    'bi-filter': sortColumn !== column.key
                  }"
                ></i>
              </div>
            </th>
          </tr>
        </thead>

        <!-- Corps -->
        <tbody>
          <!-- Squelette pendant le chargement -->
          <template v-if="loading">
            <tr v-for="i in perPage" :key="`skeleton-${i}`">
              <td v-for="column in columns" :key="`${column.key}-${i}`" :colspan="column.colspan || 1">
                <div class="placeholder-glow">
                  <span class="placeholder col-8"></span>
                </div>
              </td>
            </tr>
          </template>

          <!-- Données -->
          <template v-else>
            <tr 
              v-for="(item, index) in paginatedItems" 
              :key="item.id || index"
              class="hover-row"
              @click="emit('row-clicked', item)"
            >
              <td 
                v-for="column in columns" 
                :key="`${column.key}-${item.id}`"
                :class="{
                  'text-end': column.align === 'right',
                  'text-center': column.align === 'center',
                  'text-truncate': column.truncate
                }"
                :style="column.width ? `max-width: ${column.width}` : ''"
              >
                <!-- Contenu personnalisé -->
                <slot v-if="column.slot" :name="`cell(${column.key})`" :item="item" :value="item[column.key]"></slot>
                
                <!-- Contenu par défaut -->
                <template v-else>
                  <!-- Formatage des dates -->
                  <span v-if="column.type === 'date' && item[column.key]">
                    {{ formatDate(item[column.key], column.format) }}
                  </span>
                  
                  <!-- Badges pour les statuts -->
                  <span v-else-if="column.type === 'status'">
                    <span class="badge" :class="statusBadgeClass(item[column.key])">
                      {{ statusText(item[column.key]) }}
                    </span>
                  </span>
                  
                  <!-- Contenu brut -->
                  <template v-else>
                    {{ item[column.key] }}
                  </template>
                </template>
              </td>
            </tr>

            <!-- Aucune donnée -->
            <tr v-if="!items.length">
              <td :colspan="columns.length" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-1 opacity-50 d-block mb-2"></i>
                Aucune donnée disponible
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Pagination (optionnelle) -->
    <div v-if="showPagination && pagination.total > pagination.perPage" class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">
        Affichage de {{ pagination.from }} à {{ pagination.to }} sur {{ pagination.total }} éléments
      </div>
      <nav>
        <ul class="pagination mb-0">
          <li class="page-item" :class="{ disabled: pagination.currentPage === 1 }">
            <button class="page-link" @click="changePage(pagination.currentPage - 1)">
              &laquo;
            </button>
          </li>
          
          <li 
            v-for="page in pages" 
            :key="page" 
            class="page-item" 
            :class="{ active: page === pagination.currentPage }"
          >
            <button class="page-link" @click="changePage(page)">
              {{ page }}
            </button>
          </li>
          
          <li class="page-item" :class="{ disabled: pagination.currentPage === pagination.lastPage }">
            <button class="page-link" @click="changePage(pagination.currentPage + 1)">
              &raquo;
            </button>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  items: {
    type: Array,
    required: true,
    default: () => []
  },
  columns: {
    type: Array,
    required: true,
    validator: (cols) => cols.every(c => c.key && c.label)
  },
  loading: {
    type: Boolean,
    default: false
  },
  perPage: {
    type: Number,
    default: 10
  },
  showPagination: {
    type: Boolean,
    default: true
  },
  initialSort: {
    type: Object,
    default: () => ({ key: 'id', direction: 'asc' })
  }
});

const emit = defineEmits(['sort-changed', 'page-changed', 'row-clicked']);

// Tri
const sortColumn = ref(props.initialSort.key);
const sortDirection = ref(props.initialSort.direction);

const sortedItems = computed(() => {
  if (!sortColumn.value) return props.items;
  
  return [...props.items].sort((a, b) => {
    let valA = a[sortColumn.value];
    let valB = b[sortColumn.value];
    
    // Pour le tri numérique
    if (typeof valA === 'number' && typeof valB === 'number') {
      return sortDirection.value === 'asc' ? valA - valB : valB - valA;
    }
    
    // Pour le tri des dates
    if (valA instanceof Date && valB instanceof Date) {
      return sortDirection.value === 'asc' ? valA - valB : valB - valA;
    }
    
    // Pour le tri des chaînes
    if (typeof valA === 'string' && typeof valB === 'string') {
      return sortDirection.value === 'asc' 
        ? valA.localeCompare(valB) 
        : valB.localeCompare(valA);
    }
    
    return 0;
  });
});

// Pagination
const pagination = computed(() => {
  const total = props.items.length;
  const lastPage = Math.ceil(total / props.perPage);
  const from = (currentPage.value - 1) * props.perPage + 1;
  const to = Math.min(currentPage.value * props.perPage, total);
  
  return {
    total,
    perPage: props.perPage,
    currentPage: currentPage.value,
    lastPage,
    from,
    to
  };
});

const currentPage = ref(1);
const pages = computed(() => {
  const range = [];
  const maxVisible = 5;
  let start = 1;
  let end = pagination.value.lastPage;
  
  if (pagination.value.lastPage > maxVisible) {
    start = Math.max(1, pagination.value.currentPage - Math.floor(maxVisible / 2));
    end = start + maxVisible - 1;
    
    if (end > pagination.value.lastPage) {
      end = pagination.value.lastPage;
      start = end - maxVisible + 1;
    }
  }
  
  for (let i = start; i <= end; i++) {
    range.push(i);
  }
  
  return range;
});

const paginatedItems = computed(() => {
  if (!props.showPagination) return sortedItems.value;
  
  const start = (currentPage.value - 1) * props.perPage;
  const end = start + props.perPage;
  return sortedItems.value.slice(start, end);
});

// Méthodes
const sort = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
  
  currentPage.value = 1;
  emit('sort-changed', { column: sortColumn.value, direction: sortDirection.value });
};

const changePage = (page) => {
  if (page < 1 || page > pagination.value.lastPage) return;
  
  currentPage.value = page;
  emit('page-changed', page);
};

const formatDate = (date, format = 'short') => {
  if (!date) return '';
  
  const dateObj = date instanceof Date ? date : new Date(date);
  if (isNaN(dateObj)) return date;
  
  const options = format === 'long' 
    ? { day: 'numeric', month: 'long', year: 'numeric' } 
    : { day: 'numeric', month: 'short', year: 'numeric' };
  
  return dateObj.toLocaleDateString('fr-FR', options);
};

const statusBadgeClass = (status) => {
  const classes = {
    published: 'bg-success',
    active: 'bg-success',
    completed: 'bg-success',
    draft: 'bg-secondary',
    pending: 'bg-warning',
    cancelled: 'bg-danger',
    deleted: 'bg-danger'
  };
  
  return classes[status] || 'bg-light text-dark';
};

const statusText = (status) => {
  const texts = {
    published: 'Publié',
    draft: 'Brouillon',
    pending: 'En attente',
    cancelled: 'Annulé',
    deleted: 'Supprimé'
  };
  
  return texts[status] || status;
};
</script>

<style scoped>
.hover-row {
  transition: background-color 0.15s ease;
}

.hover-row:hover {
  background-color: rgba(0, 0, 0, 0.02);
  cursor: pointer;
}

.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.datatable-container {
  position: relative;
}

.placeholder {
  min-height: 1em;
  background-color: rgba(0, 0, 0, 0.1);
}

.badge {
  font-weight: 500;
  letter-spacing: 0.5px;
  font-size: 0.75em;
}
</style>