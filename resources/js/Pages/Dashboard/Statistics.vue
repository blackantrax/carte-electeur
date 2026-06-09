<template>
    <AppLayout>
        <!-- Section Title Dynamique -->
        <PageTitle
            title="Statistiques"
            :breadcrumbs="breadcrumbs"
        />

        <section class="section">
            <div class="row">
                <div class="card border border-gray-200 rounded-lg shadow-md">
                    <div class="card-body p-6">
                        <!-- Vertical Pills Tabs -->
                        <div class="flex flex-col md:flex-row items-start">
                            <!-- Sidebar Tabs -->
                            <div class="nav flex-col nav-pills space-y-2 md:space-y-0 md:mr-4 md:w-1/4">
                                <button class="nav-link active" id="v-pills-home-tab" data-bs-toggle="pill" data-bs-target="#v-pills-home" type="button" role="tab" aria-controls="v-pills-home" aria-selected="true">
                                    Articles
                                </button>
                                <button class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                    Videos
                                </button>
                                <button class="nav-link" id="v-pills-messages-tab" data-bs-toggle="pill" data-bs-target="#v-pills-messages" type="button" role="tab" aria-controls="v-pills-messages" aria-selected="false">
                                    Photos
                                </button>
                                <button class="nav-link" id="v-pills-electeurs-tab" data-bs-toggle="pill" data-bs-target="#v-pills-electeurs" type="button" role="tab" aria-controls="v-pills-electeurs" aria-selected="false">
                                    Cartes d'électeurs
                                </button>
                            </div>

                            <!-- Tab Content -->
                            <div class="tab-content md:w-3/4" id="v-pills-tabContent">
                                <!-- Articles -->
                                <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                    <p class="text-gray-600">
                                        Sunt est soluta temporibus accusantium neque nam maiores cumque temporibus. Tempora libero non est unde veniam est qui dolor.
                                    </p>
                                </div>

                                <!-- Videos -->
                                <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                    <p class="text-gray-600">
                                        Nesciunt totam et. Consequuntur magnam aliquid eos nulla dolor iure eos quia.
                                    </p>
                                </div>

                                <!-- Photos -->
                                <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                                    <p class="text-gray-600">
                                        Saepe animi et soluta ad odit soluta sunt. Nihil quos omnis animi debitis cumque.
                                    </p>
                                </div>

                                <!-- Cartes d'électeurs -->
                                <div class="tab-pane fade" id="v-pills-electeurs" role="tabpanel" aria-labelledby="v-pills-electeurs-tab">
                                    <h5 class="text-lg font-semibold text-gray-700 mb-4">Statistiques des Cartes d'Électeurs</h5>

                                    <!-- Tableau des statistiques -->
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm text-gray-600 bg-white border border-gray-200 shadow-sm rounded-lg">
                                            <thead class="bg-gray-100 text-gray-700 text-xs uppercase tracking-wider">
                                                <tr>
                                                    <th class="px-4 py-2 text-left">Département</th>
                                                    <th class="px-4 py-2 text-center">Nombre d'Électeurs</th>
                                                    <th class="px-4 py-2 text-right">Proportion (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="department in stats" :key="department.name" class="border-b hover:bg-gray-50">
                                                    <td class="px-4 py-2">{{ department.name }}</td>
                                                    <td class="px-4 py-2 text-center">{{ department.count }}</td>
                                                    <td class="px-4 py-2 text-right">{{ calculatePercentage(department.count) }}%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Vertical Pills Tabs -->
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import { ref } from 'vue';

// Données des statistiques
const stats = ref([
    { name: 'Littoral', count: 1500 },
    { name: 'Centre', count: 2500 },
    { name: 'Adamaoua', count: 800 },
    { name: 'Ouest', count: 1200 },
    { name: 'Nord', count: 1000 },
]);

const breadcrumbs = ref([
                { label: 'Accueil', url: 'index.html', active: false },
                { label: 'Dashboard', url: '#', active: false },
                { label: 'Statistiques', active: true }
            ]);

// Fonction pour calculer le pourcentage
const calculatePercentage = (count) => {
    const total = stats.value.reduce((sum, dept) => sum + dept.count, 0);
    return ((count / total) * 100).toFixed(2);
};
</script>

<style scoped>
.nav-link {
    font-family: 'Inter', sans-serif;
    background: #f8f9fa;
    border: 1px solid #ddd;
    color: #333;
    padding: 10px 15px;
    border-radius: 5px;
    transition: all 0.3s;
}
.nav-link:hover {
    background: #e9ecef;
    color: #0056b3;
}
.nav-link.active {
    background: #0056b3;
    color: #fff;
}
.tab-pane {
    padding: 1rem;
}
table {
    border-spacing: 0;
    border-collapse: collapse;
}
th, td {
    font-family: 'Inter', sans-serif;
}
</style>
