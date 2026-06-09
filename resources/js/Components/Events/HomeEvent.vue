<template>
    <div class="events-container" id="evenements">
      <!-- Sidebar Title -->
      <div class="events-sidebar">Événements</div>

      <!-- Events List -->
      <div class="events-list">
        <div v-for="event in events" :key="event.id" class="event-item">
          <div class="event-date">{{ event.date }}</div>
          <div class="d-flex justify-content-between align-items-start">
            <h2 class="event-title" :class="{'blue': event.highlight}">
              {{ event.title }}
            </h2>
            <Link :href="'/events/'+event.slug" class="see-more ms-3" :aria-label="'Voir plus sur l\'événement ' + event.title">
              Voir plus
            </Link>
          </div>
        </div>

        <!-- Browse All Link -->
        <div class="mt-4 text-center">
          <a href="/events" class="btn btn-outline-yellow">Voir tous les événements</a>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { ref, onMounted } from 'vue';
  import axios from 'axios';
  import { Link } from '@inertiajs/vue3';

  const events = ref([]);

  onMounted(async () => {
    try {
      const response = await axios.get('/events/latest');
      events.value = response.data.events;
    } catch (error) {
      console.error('Erreur lors du chargement des événements:', error);
    }
  });
  </script>

  <style scoped>
  /* General Container Styles */
  .events-container {
    position: relative;
    padding-left: 60px;
    width: 100%;
    margin: 1em auto;
  }

  /* Sidebar Styles */
  .events-sidebar {
    position: absolute;
    left: 0;
    top: 0;
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    font-size: 1.5rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 1rem 0;
    font-weight: bold;
  }

  /* Events List Styles */
  .event-item {
    padding: 2rem 0;
    border-bottom: 1px solid #dee2e6;
  }

  .event-item:last-child {
    border-bottom: none;
  }

  /* Event Date Styles */
  .event-date {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    font-weight: bold;
  }

  /* Event Title Styles */
  .event-title {
    margin-bottom: 0;
    font-weight: 600;
  }

  .event-title.blue {
    color: #007bff;
  }

  /* Link Styles */
  .see-more {
    color: #000;
    text-decoration: none;
    position: relative;
    font-weight: 500;
    font-size: 0.9rem;
    transition: color 0.3s ease;
  }

  .see-more::after {
    content: "";
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: #fadb17;
  }

  .see-more:hover {
    color: #007bff;
  }

  /* Browse All Link Styles */
  .btn-outline-yellow {
    border: 2px solid #f1c40f;
    color: #f1c40f;
    font-weight: bold;
    transition: background-color 0.2s, color 0.2s;
  }

  .btn-outline-yellow:hover {
    background-color: #f1c40f;
    color: #111;
  }
  </style>
