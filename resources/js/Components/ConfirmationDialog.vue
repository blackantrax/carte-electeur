<template>
  <Modal :show="show" @close="close">
    <div 
      class="confirmation-dialog"
      role="dialog"
      aria-modal="true"
      :aria-labelledby="dialogTitleId"
      :aria-describedby="dialogDescId"
    >
      <div class="dialog-header">
        <h3 :id="dialogTitleId" class="dialog-title">
          <slot name="title">{{ title }}</slot>
        </h3>
        <button 
          @click="close" 
          class="dialog-close-btn" 
          :disabled="isLoading"
          aria-label="Fermer la boîte de dialogue"
        >
          <i class="fas fa-times" aria-hidden="true"></i>
        </button>
      </div>

      <div class="dialog-content">
        <div class="dialog-icon" v-if="icon">
          <i :class="icon" aria-hidden="true"></i>
        </div>
        <div :id="dialogDescId" class="dialog-message">
          <slot>
            <p v-if="message">{{ message }}</p>
          </slot>
        </div>
      </div>

      <div class="dialog-footer">
        <button 
          @click="close" 
          class="btn btn-secondary"
          :disabled="isLoading"
          :aria-busy="isLoading"
          aria-label="Annuler l'action"
        >
          <slot name="cancelButton">Annuler</slot>
        </button>
        <button 
          @click="confirm" 
          class="btn btn-danger"
          :class="{ 'loading': isLoading }"
          :disabled="isLoading"
          :aria-busy="isLoading"
          :aria-label="confirmButtonAriaLabel"
          autofocus
        >
          <span v-if="!isLoading">
            <slot name="confirmButton">
              <i class="fas fa-trash mr-2" aria-hidden="true"></i> 
              <span>Supprimer</span>
            </slot>
          </span>
          <span v-else>
            <i class="fas fa-circle-notch fa-spin mr-2" aria-hidden="true"></i>
            <slot name="loadingText">Suppression en cours...</slot>
          </span>
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useUniqueId } from './Composables/useUniqueId';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    default: 'Confirmer la suppression'
  },
  message: String,
  icon: {
    type: String,
    default: 'fas fa-exclamation-triangle text-yellow-500'
  },
  dangerLevel: {
    type: String,
    default: 'high', // 'high'|'medium'|'low'
    validator: (value) => ['high', 'medium', 'low'].includes(value)
  }
});

const emit = defineEmits(['close', 'confirm']);

const isLoading = ref(false);
const dialogTitleId = useUniqueId('dialog-title');
const dialogDescId = useUniqueId('dialog-desc');

const confirmButtonAriaLabel = computed(() => {
  return isLoading.value ? 
    'Action en cours...' : 
    `Confirmer ${props.title.toLowerCase()}`;
});

const dangerClasses = computed(() => {
  return {
    high: {
      icon: 'fas fa-exclamation-triangle text-red-500',
      button: 'bg-red-600 hover:bg-red-700'
    },
    medium: {
      icon: 'fas fa-exclamation-circle text-yellow-500',
      button: 'bg-orange-500 hover:bg-orange-600'
    },
    low: {
      icon: 'fas fa-info-circle text-blue-500',
      button: 'bg-blue-500 hover:bg-blue-600'
    }
  }[props.dangerLevel];
});

const close = () => {
  if (!isLoading.value) {
    emit('close');
  }
};

const confirm = async () => {
  if (isLoading.value) return;
  
  try {
    isLoading.value = true;
    await emit('confirm');
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.confirmation-dialog {
  @apply bg-white rounded-xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full mx-4;
  max-height: calc(100vh - 2rem);
}

.dialog-header {
  @apply px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white;
}

.dialog-title {
  @apply text-xl font-semibold text-gray-900;
}

.dialog-close-btn {
  @apply p-2 rounded-full hover:bg-gray-100 transition-colors text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
}
.dialog-close-btn:disabled {
  @apply opacity-50 cursor-not-allowed hover:bg-transparent;
}

.dialog-content {
  @apply px-6 py-5 flex items-start;
}

.dialog-icon {
  @apply mr-4 text-4xl flex-shrink-0;
}

.dialog-message {
  @apply text-gray-700 prose prose-p:my-2;
}

.dialog-footer {
  @apply px-6 py-4 bg-gray-50 flex justify-end space-x-3;
}

.btn {
  @apply px-5 py-2.5 rounded-lg font-medium transition-all flex items-center justify-center;
  min-width: 7rem;
  @apply focus:outline-none focus:ring-2 focus:ring-offset-2;
}

.btn-secondary {
  @apply bg-gray-100 text-gray-800 hover:bg-gray-200 focus:ring-gray-300;
}
.btn-secondary:disabled {
  @apply opacity-75 cursor-not-allowed hover:bg-gray-100;
}

.btn-danger {
  @apply text-white focus:ring-opacity-50;
}
.btn-danger:disabled {
  @apply opacity-75 cursor-not-allowed;
}

.btn-danger.high {
  @apply bg-red-600 hover:bg-red-700 focus:ring-red-500;
}
.btn-danger.medium {
  @apply bg-orange-500 hover:bg-orange-600 focus:ring-orange-400;
}
.btn-danger.low {
  @apply bg-blue-500 hover:bg-blue-600 focus:ring-blue-400;
}

.loading {
  @apply opacity-75 cursor-not-allowed;
}

.fa-spin {
  animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Animation d'entrée/sortie */
.confirmation-dialog-enter-active,
.confirmation-dialog-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}
.confirmation-dialog-enter-from,
.confirmation-dialog-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}
</style>