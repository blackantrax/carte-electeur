<template>
  <div class="image-uploader">
    <!-- Zone de dépôt -->
    <div 
      @dragover.prevent="dragOver = true"
      @dragleave="dragOver = false"
      @drop.prevent="handleDrop"
      @click="triggerFileInput"
      class="upload-area"
      :class="{
        'border-blue-400 bg-blue-50': dragOver,
        'border-gray-300': !dragOver,
        'border-dashed': !previewUrl
      }"
    >
      <!-- Aperçu de l'image -->
      <div v-if="previewUrl" class="image-preview">
        <img 
          :src="processedPreviewUrl" 
          alt="Aperçu de l'image" 
          class="preview-image"
        >
        <button 
          @click.stop="removeImage"
          class="remove-btn"
          aria-label="Supprimer l'image"
        >
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- État initial -->
      <div v-else class="upload-prompt">
        <i class="upload-icon fas fa-cloud-upload-alt"></i>
        <p class="upload-text">
          <span class="font-medium text-blue-600">Glissez-déposez</span> une image ou cliquez pour sélectionner
        </p>
        <p class="upload-hint text-xs text-gray-500">
          Formats supportés: JPG, PNG, WEBP (Max. {{ maxFileSize }}MB)
        </p>
      </div>

      <!-- Input fichier caché -->
      <input 
        ref="fileInput"
        type="file" 
        accept="image/*"
        class="hidden"
        @change="handleFileSelect"
      >
    </div>

    <!-- Contrôles optionnels -->
    <div v-if="previewUrl" class="mt-3 flex items-center justify-between">
      <button 
        @click="triggerFileInput"
        class="text-sm text-blue-600 hover:text-blue-800"
      >
        <i class="fas fa-sync-alt mr-1"></i> Changer
      </button>
      <button 
        @click="openCropper"
        v-if="enableCropper"
        class="text-sm text-purple-600 hover:text-purple-800"
      >
        <i class="fas fa-crop-alt mr-1"></i> Recadrer
      </button>
    </div>

    <!-- Erreurs -->
    <p v-if="error" class="mt-1 text-sm text-red-600">
      <i class="fas fa-exclamation-circle mr-1"></i> {{ error }}
    </p>

    <!-- Modal de recadrage -->
    <Modal 
      v-if="showCropper" 
      :show="showCropper" 
      @close="showCropper = false"
      max-width="3xl"
    >
      <div class="p-4">
        <h3 class="text-lg font-medium mb-4">Recadrer l'image</h3>
        <div class="cropper-container">
          <VueCropper
            v-if="cropperImage"
            ref="cropper"
            :src="cropperImage"
            :aspect-ratio="aspectRatio"
            :auto-crop-area="0.8"
            :view-mode="2"
            guides
          />
        </div>
        <div class="flex justify-end space-x-3 mt-4">
          <button 
            @click="showCropper = false" 
            class="btn btn-secondary"
          >
            Annuler
          </button>
          <button 
            @click="applyCrop" 
            class="btn btn-primary"
          >
            Appliquer
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { VueCropper } from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  initialImage: {
    type: [String, File, null],
    default: null
  },
  enableCropper: {
    type: Boolean,
    default: true
  },
  aspectRatio: {
    type: Number,
    default: 16/9
  },
  maxFileSize: {
    type: Number,
    default: 5 // MB
  },
  storagePath: {
    type: String,
    default: '/'
  }
});

const emit = defineEmits(['image-changed', 'clear-image']);

const fileInput = ref(null);
const previewUrl = ref(null);
const dragOver = ref(false);
const error = ref(null);
const showCropper = ref(false);
const cropper = ref(null);
const cropperImage = ref(null);
const selectedFile = ref(null);

// Calculer l'URL de prévisualisation traitée
const processedPreviewUrl = computed(() => {
  if (!previewUrl.value) return null;
  
  // Si c'est une data URL (fichier sélectionné)
  if (previewUrl.value.startsWith('data:')) {
    return previewUrl.value;
  }
  
  // Si c'est déjà une URL complète
  if (previewUrl.value.startsWith('http')) {
    return previewUrl.value;
  }
  
  
  // Pour les chemins relatifs venant de la base de données
  return `${previewUrl.value}`;
});

// Initialiser avec l'image de départ
const initImage = () => {
  if (props.initialImage) {
    if (props.initialImage instanceof File) {
      // Si c'est un File, créer une preview
      const reader = new FileReader();
      reader.onload = (e) => {
        previewUrl.value = e.target.result;
        cropperImage.value = e.target.result;
      };
      reader.readAsDataURL(props.initialImage);
    } else if (typeof props.initialImage === 'string') {
      // Si c'est une string (URL ou chemin)
      previewUrl.value = props.initialImage;
      cropperImage.value = props.initialImage;
    }
  }
};

// Fonctions principales
const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileSelect = (e) => {
  const file = e.target.files[0];
  if (file) validateAndProcessFile(file);
};

const handleDrop = (e) => {
  dragOver.value = false;
  const file = e.dataTransfer.files[0];
  if (file) validateAndProcessFile(file);
};

const validateAndProcessFile = (file) => {
  error.value = null;

  // Validation du type
  const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
  if (!validTypes.includes(file.type)) {
    error.value = 'Format d\'image non supporté';
    return;
  }

  // Validation de la taille
  const maxSize = props.maxFileSize * 1024 * 1024;
  if (file.size > maxSize) {
    error.value = `L'image ne doit pas dépasser ${props.maxFileSize}MB`;
    return;
  }

  selectedFile.value = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    previewUrl.value = e.target.result;
    cropperImage.value = e.target.result;
    emit('image-changed', file, e.target.result);
  };
  reader.readAsDataURL(file);
};

const removeImage = () => {
  // Libérer l'URL d'objet si elle existe
  if (previewUrl.value && previewUrl.value.startsWith('data:')) {
    URL.revokeObjectURL(previewUrl.value);
  }
  previewUrl.value = null;
  cropperImage.value = null;
  selectedFile.value = null;
  fileInput.value.value = '';
  emit('clear-image');
};

// Fonctions de recadrage
const openCropper = () => {
  if (previewUrl.value) {
    showCropper.value = true;
  }
};

const applyCrop = () => {
  if (cropper.value) {
    cropper.value.getCroppedCanvas().toBlob((blob) => {
      const file = new File([blob], selectedFile.value.name, {
        type: selectedFile.value.type,
        lastModified: Date.now()
      });

      const reader = new FileReader();
      reader.onload = (e) => {
        previewUrl.value = e.target.result;
        emit('image-changed', file, e.target.result);
        showCropper.value = false;
      };
      reader.readAsDataURL(blob);
    }, selectedFile.value.type);
  }
};

// Watch pour les changements de l'image initiale
watch(() => props.initialImage, (newVal) => {
  if (newVal !== previewUrl.value) {
    if (newVal instanceof File) {
      const reader = new FileReader();
      reader.onload = (e) => {
        previewUrl.value = e.target.result;
        cropperImage.value = e.target.result;
      };
      reader.readAsDataURL(newVal);
    } else if (typeof newVal === 'string') {
      previewUrl.value = newVal;
      cropperImage.value = newVal;
    } else if (newVal === null) {
      previewUrl.value = null;
      cropperImage.value = null;
    }
  }
}, { immediate: true });

// Initialisation
initImage();
</script>

<style scoped>
.image-uploader {
  @apply w-full;
}

.upload-area {
  @apply relative w-full h-48 rounded-lg border-2 flex items-center justify-center cursor-pointer transition-all overflow-hidden;
}

.upload-prompt {
  @apply text-center p-4;
}

.upload-icon {
  @apply text-4xl text-gray-400 mb-2;
}

.upload-text {
  @apply text-sm text-gray-600 mb-1;
}

.image-preview {
  @apply absolute inset-0 w-full h-full;
}

.preview-image {
  @apply w-full h-full object-cover;
}

.remove-btn {
  @apply absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors;
}

.cropper-container {
  @apply w-full h-96 bg-gray-100;
}

.btn {
  @apply px-4 py-2 rounded-md font-medium transition-colors;
}

.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700;
}

.btn-secondary {
  @apply bg-gray-200 text-gray-800 hover:bg-gray-300;
}
</style>