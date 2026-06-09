<template>
    <div class="container mx-auto">
        <!-- Formulaire de recherche -->
        <div class="row g-3 align-items-end">
            <!-- Champ de recherche -->
            <div class="col-md-3">
                <label for="search" class="form-label">Rechercher:</label>
                <input
                    type="text"
                    id="rechercher"
                    v-model="filters.search"
                    class="form-control"
                    placeholder="Enter titre"
                />
            </div>

            <!-- Sélection thématique -->
            <div class="col-md-3">
                <label for="thematicArea" class="form-label">Catégorie:</label>
                <select id="thematicArea" v-model="filters.category" class="form-select">
                    <option value="">- Toutes -</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
            </div>

            <!-- Sélection année -->
            <div class="col-md-3">
                <label for="year" class="form-label">Année :</label>
                <select id="year" v-model="filters.year" class="form-select">
                    <option value="">- année -</option>
                    <option v-for="year in availableYears" :key="year" :value="year">
                        {{ year }}
                    </option>
                </select>
            </div>

            <!-- Bouton GO -->
            <div class="col-md-3">
                <button class="btn btn-primary w-100" @click="$emit('applyFilters')">
                    GO
                </button>
            </div>
        </div>

        <!-- Sélecteur de mode d'affichage -->
        <div class="mt-4 d-flex align-items-center">
            <span class="me-3">Affichage :</span>
            <div class="btn-group" role="group">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :class="{ 'active': viewMode === 'list' }"
                    @click="$emit('update:viewMode', 'list')"
                >
                    <i class="bi bi-list"></i> Liste
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :class="{ 'active': viewMode === 'grid' }"
                    @click="$emit('update:viewMode', 'grid')"
                >
                    <i class="bi bi-grid"></i> Grille
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

defineProps({
    categories: Array,
    filters: Object, // 🔥 Synchronisé avec le parent via `v-model`
    viewMode: String // 🔥 Synchronisé avec le parent via `v-model`
});

defineEmits(['update:filters', 'update:viewMode', 'applyFilters']); // 🔥 Envoie au parent

const availableYears = computed(() => {
    const currentYear = new Date().getFullYear();
    return Array.from({ length: currentYear - 1999 }, (_, i) => 2000 + i);
});
</script>

<style scoped>
.btn-primary {
    background-color: #f1c40f;
    border-color: #f1c40f;
}

.btn-primary:hover {
    background-color: #f1c40f;
    border-color: #f1c40f;
}

.btn-outline-secondary.active {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}
</style>
