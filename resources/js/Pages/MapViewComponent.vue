<template>
    <FrontLayout>
      <Head :title="title" />
      <PageTitle title="MAP VIEW" :breadcrumbs="breadcrumbs" />
      <div class="row">
        <!-- Sidebar -->
        <div class="col-md-4 col-lg-3 sidebar p-3">
          <h5 class="mb-4 sidebar-title">DÉPARTEMENTS</h5>
          <!-- Barre de recherche -->
          <input
            type="text"
            v-model="searchQuery"
            class="form-control mb-3"
            placeholder="Rechercher un département..."
          />
          <nav class="nav flex-column department-list">
            <!-- Liste filtrée des départements -->
            <a
              v-for="(department, index) in filteredDepartments"
              :key="index"
              class="nav-link"
              href="#"
              @click.prevent="centerMapOnDepartment(department)"
            >
              {{ department.name }}
            </a>
          </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-8 col-lg-9 p-4">
          <h1 class="main-title">Carte Interactive des Départements du Cameroun</h1>
          <div class="map-container">
            <div id="map"></div>
          </div>
        </div>
      </div>
    </FrontLayout>
</template>

<script setup>
import FrontLayout from "@/Layouts/FrontLayout.vue";
import HomeEvent from "@/Components/Events/HomeEvent.vue";
import { Head } from "@inertiajs/vue3";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import { ref, onMounted, computed } from "vue";
import PageTitle from "@/Components/PageTitle.vue";

const departments = ref([]);
const searchQuery = ref("");
const stats = ref({});
const totalElecteurs = ref(0);
let map = null;

const breadcrumbs = [
    { label: 'Accueil', url: '/', active: false },
    { label: 'Carte Globale', active: true }
  ];

const filteredDepartments = computed(() => {
    return departments.value.filter((department) =>
        department.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

async function fetchJSON(url) {
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`Erreur HTTP ${response.status}`);
        return await response.json();
    } catch (error) {
        console.error(`Erreur lors du chargement de ${url}:`, error);
        return null;
    }
}

async function renderMap() {
    try {
        map = L.map("map").setView([7.3697, 12.3547], 6);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        const geojson = await fetchJSON("/geo/cameroun-departements.json");
        const statsData = await fetchJSON("/stats");

        if (!geojson || !geojson.features || !statsData || !statsData.data) {
            throw new Error("Données invalides");
        }

        stats.value = statsData.data.reduce((acc, dept) => {
            acc[dept.departement.toUpperCase()] = dept.count;
            return acc;
        }, {});

        totalElecteurs.value = Object.values(stats.value).reduce((sum, count) => sum + count, 0);

        departments.value = geojson.features.map((feature) => ({
            name: feature.properties.shapeName,
            bounds: L.geoJSON(feature).getBounds(),
        }));

        L.geoJSON(geojson, {
            style: () => ({
                color: "#003366",
                weight: 1,
                fillColor: "#cce5ff",
                fillOpacity: 0.7,
            }),
            onEachFeature: (feature, layer) => {
                const deptName = feature.properties.shapeName.toUpperCase();
                const deptStats = stats.value[deptName] || 0;
                const percentage = totalElecteurs.value ? ((deptStats / totalElecteurs.value) * 100).toFixed(1) : 0;

                layer.on("mouseover", (e) => {
                    e.target.setStyle({ fillColor: "#99ccff", fillOpacity: 1 });
                    layer.bindTooltip(
                        `<strong>${feature.properties.shapeName}</strong><br>
                        Inscrits : ${deptStats}<br>
                        Proportion : ${percentage}%`,
                        { permanent: false, direction: "top", offset: [0, -10] }
                    ).openTooltip();
                });

                layer.on("mouseout", (e) => {
                    e.target.setStyle({ fillColor: "#cce5ff", fillOpacity: 0.7 });
                    layer.closeTooltip();
                });
            },
        }).addTo(map);

        // Légende
        const legend = L.control({ position: "bottomright" });
        legend.onAdd = function () {
            const div = L.DomUtil.create("div", "legend");
            div.innerHTML = `<strong>Total électeurs :</strong> ${totalElecteurs.value}`;
            return div;
        };
        legend.addTo(map);
    } catch (error) {
        console.error("Erreur lors du chargement de la carte :", error);
    }
}

function centerMapOnDepartment(department) {
    if (map && department.bounds) {
        map.fitBounds(department.bounds);
    }
}

onMounted(() => {
    renderMap();
});
</script>

<style scoped>
.row {
    display: flex;
}

.main-title {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 2rem;
    text-transform: uppercase;
}

.sidebar {
    background-color: #f9fafb;
    min-height: 100vh;
    height: 700px;
    border-right: 1px solid #e3e6ea;
    position: sticky;
    top: 0;
    overflow-y: auto;
}

.sidebar-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #495057;
}

.department-list {
    max-height: calc(100% - 60px);
    overflow-y: auto;
}

.nav-link {
    color: #495057;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    border-radius: 5px;
    transition: all 0.3s ease-in-out;
}

.nav-link:hover,
.nav-link:focus {
    background-color: #eaf4ff;
    color: #007bff;
    text-decoration: none;
}

.map-container {
    position: relative;
    width: 100%;
    height: 700px;
}

#map {
    width: 100%;
    height: 100%;
}
</style>
