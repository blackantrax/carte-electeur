<template>
  <div>
    <div v-if="articles.length" class="list-group list-group-flush">
      <Link 
        v-for="article in articles" 
        :key="article.id" 
        :href="`/articles/${article.id}/edit`"
        class="list-group-item list-group-item-action border-0 px-0 py-2"
      >
        <div class="d-flex align-items-center">
          <span class="badge me-2" :class="statusBadgeClass(article.status)">
            {{ statusText(article.status) }}
          </span>
          <span class="text-truncate flex-grow-1">{{ article.title }}</span>
          <small class="text-muted ms-2">{{ formatDate(article.created_at) }}</small>
        </div>
      </Link>
    </div>
    <div v-else class="text-center py-3 text-muted">
      Aucun article récent
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  articles: {
    type: Array,
    required: true
  }
});

const statusBadgeClass = (status) => {
  return {
    published: 'bg-success',
    draft: 'bg-secondary',
    pending: 'bg-warning'
  }[status] || 'bg-light text-dark';
};

const statusText = (status) => {
  return {
    published: 'Publié',
    draft: 'Brouillon',
    pending: 'En attente'
  }[status] || status;
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short'
  });
};
</script>