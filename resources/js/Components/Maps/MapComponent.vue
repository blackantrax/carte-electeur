<template>
    <div class="map-container">
        <h2 class="main-title">🗺️ Répartition des électeurs par département</h2>
        <div v-if="loading" class="loading-overlay">Chargement des données...</div>
        <div class="map-instructions">🖱️ Survolez un département pour voir ses statistiques.</div>
        <div id="map"></div>
        <div class="mt-4 text-center">
            <Link :href="route('map')" class="btn btn-outline-yellow">Voir les Data</Link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import L from "leaflet";
import { Link } from '@inertiajs/vue3';
import "leaflet/dist/leaflet.css";

// Variables réactives
const loading = ref(true);
const map = ref(null);
const stats = ref({});

// ✅ Fonction utilitaire pour récupérer les JSON avec gestion d'erreur
const fetchJSON = async (url) => {
    try {
        console.log(`🔍 Fetching: ${url}`); // Vérifie si l'URL est correcte
        const response = await fetch(url);

        if (!response.ok) throw new Error(`Erreur HTTP ${response.status} - ${response.statusText}`);

        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error(`Réponse invalide : le serveur a renvoyé ${contentType}`);
        }

        return await response.json();
    } catch (error) {
        console.error(`❌ Erreur lors du chargement de ${url} :`, error);
        return null;
    }
};

// Normalisation des noms de département
const normalizeName = (name) => {
    if (typeof name !== "string") return "";
    return name.trim().toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
};

// ✅ Chargement et rendu de la carte
const renderMap = async () => {
    try {
        map.value = L.map("map").setView([7.3697, 12.3547], 7);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map.value);

        const geojson = await fetchJSON("/geo/cameroun-departements.json");
        if (!geojson || !geojson.features) throw new Error("Le fichier GeoJSON est invalide ou vide.");

        const statsResponse = await fetchJSON("/stats");
        if (!statsResponse || !statsResponse.data) throw new Error("Les statistiques sont invalides.");

        let statsNormalized = {};
        statsResponse.data.forEach(dept => {
            statsNormalized[normalizeName(dept.departement)] = dept.count;
        });

        const totalElecteurs = Object.values(statsNormalized).reduce((sum, count) => sum + count, 0);

        L.geoJSON(geojson, {
            style: () => ({
                color: "#003366",
                weight: 1,
                fillColor: "#cce5ff",
                fillOpacity: 0.7,
            }),
            onEachFeature: (feature, layer) => {
                const deptName = normalizeName(feature.properties?.shapeName || "");

                layer.on("mouseover", (e) => {
                    const deptStats = statsNormalized[deptName] || 0;
                    const percentage = totalElecteurs ? ((deptStats / totalElecteurs) * 100).toFixed(1) : 0;

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
        }).addTo(map.value);

        // ✅ Ajout de la légende
        const legend = L.control({ position: "bottomright" });

        legend.onAdd = function () {
            const div = L.DomUtil.create("div", "legend");
            div.innerHTML = `<strong>Total électeurs :</strong> ${totalElecteurs}`;
            return div;
        };

        legend.addTo(map.value);
    } catch (error) {
        console.error("Erreur de chargement de la carte :", error);
    } finally {
        loading.value = false;
    }
};

// ✅ Chargement de la carte au montage du composant
onMounted(() => {
    renderMap();
});
</script>


<style>

.main-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #141414;
        margin-bottom: 1rem;
    }

.map-container {
    position: relative;
    width: 100%;
    height: 500px;
    margin-bottom: 8em;
}

#map {
    width: 100%;
    height: 100%;
}

.loading-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.8);
    padding: 15px;
    border-radius: 5px;
    font-weight: bold;
    color: #333;
    z-index: 1000;
}

/* Message d'instructions */
.map-instructions {
    text-align: center;
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 10px;
    background: #f0f8ff;
    padding: 8px;
    border-radius: 5px;
}

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

/* Style de la légende */
.legend {
    background: white;
    padding: 10px;
    border-radius: 5px;
    font-size: 14px;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
}

/* Curseur pointeur sur les départements */
.leaflet-interactive {
    cursor: pointer;
}
</style>
