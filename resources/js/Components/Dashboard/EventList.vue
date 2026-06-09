<template>
  <div>
    <div v-if="events.length" class="list-group list-group-flush">
      <Link 
        v-for="event in events" 
        :key="event.id" 
        :href="`/events/${event.id}/edit`"
        class="list-group-item list-group-item-action border-0 px-0 py-2"
      >
        <div class="d-flex align-items-center">
          <i class="bi me-2" :class="eventIcon(event.date)"></i>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between">
              <strong>{{ event.title }}</strong>
              <small class="text-muted ms-2">{{ formatDate(event.date) }}</small>
            </div>
            <small class="text-muted">{{ eventLocation(event) }}</small>
          </div>
        </div>
      </Link>
    </div>
    <div v-else class="text-center py-3 text-muted">
      Aucun événement à venir
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  events: {
    type: Array,
    required: true
  }
});

const eventIcon = (date) => {
  const eventDate = new Date(date);
  const now = new Date();
  return eventDate > now 
    ? 'bi-calendar-check text-success' 
    : 'bi-calendar-x text-secondary';
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short'
  });
};

const eventLocation = (event) => {
  return event.location || 'Lieu non spécifié';
};
</script>