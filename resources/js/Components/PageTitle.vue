<template>
    <div class="page-header mb-6">
        <!-- Titre principal avec options -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">
                    {{ title }}
                </h1>
                
                <!-- Breadcrumbs améliorés -->
                <nav v-if="breadcrumbs.length" class="flex mt-2">
                    <ol class="flex items-center space-x-2">
                        <li
                            v-for="(breadcrumb, index) in breadcrumbs"
                            :key="index"
                            class="flex items-center"
                        >
                            <template v-if="!breadcrumb.active">
                                <Link 
                                    :href="breadcrumb.url" 
                                    class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors flex items-center"
                                >
                                    <span v-if="index === 0" class="flex items-center">
                                        <i class="bi bi-house-door mr-1.5 text-gray-400"></i>
                                    </span>
                                    {{ breadcrumb.label }}
                                </Link>
                            </template>
                            <template v-else>
                                <span class="text-sm font-medium text-gray-700">
                                    {{ breadcrumb.label }}
                                </span>
                            </template>
                            
                            <!-- Séparateur amélioré -->
                            <span 
                                v-if="index < breadcrumbs.length - 1"
                                class="mx-2 text-gray-300"
                            >
                                <i class="bi bi-chevron-right text-xs"></i>
                            </span>
                        </li>
                    </ol>
                </nav>
            </div>
            
            <!-- Slot pour boutons d'action -->
            <div v-if="$slots.actions" class="flex-shrink-0">
                <slot name="actions"></slot>
            </div>
        </div>
        
        <!-- Slot supplémentaire sous le titre -->
        <div v-if="$slots.subtitle" class="mt-2">
            <slot name="subtitle"></slot>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        required: true,
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});
</script>

<style scoped>
.page-header {
    font-family: 'Inter', sans-serif;
}

/* Animation douce pour les liens */
a {
    transition: color 0.2s ease-in-out;
}

/* Style pour les écrans très petits */
@media (max-width: 400px) {
    .breadcrumb-item-text {
        display: none;
    }
    
    .breadcrumb-item:first-child i {
        margin-right: 0;
    }
}
</style>