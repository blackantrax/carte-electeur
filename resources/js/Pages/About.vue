<template>
  <FrontLayout>
    <Head :title="title" />
    <PageTitle title="A Propos de Nous" :breadcrumbs="breadcrumbs" />
    <!-- Hero Section -->
    <div class="bg-black text-white py-12 md:py-20">
      <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center">
          <div class="md:w-1/2 mb-8 md:mb-0 md:pr-8">
            <h1 class="text-4xl md:text-5xl font-bold uppercase mb-4">CARTES D'ÉLECTEURS</h1>
            <p class="text-xl text-yellow-400 mb-6">VALORISE TES DEVOIRS</p>
            <div class="w-20 h-1 bg-yellow-400 mb-6"></div>
            <p class="text-lg mb-8">Notre mission est de renforcer la démocratie par l'éducation électorale et la formation des jeunes scrutateurs.</p>
            <a href="#actions" class="inline-block bg-yellow-400 hover:bg-yellow-500 text-black px-6 py-3 rounded-lg font-semibold transition duration-300">
              Découvrez nos actions
            </a>
          </div>
          <div class="md:w-1/2">
            <img src="/images/equipe.jpg" alt="Équipe de sensibilisation" class="w-full rounded-lg shadow-xl border-4 border-gray-800 hover:scale-105 transition duration-300">
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="text-white">
      <div class="container mx-auto px-4 py-12">

        <!-- Introduction Section -->
        <section id="introduction" class="bg-gray-800 rounded-xl shadow-lg p-6 md:p-8 mb-12">
          <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-8 md:mb-0 md:pr-8">
              <div class="flex items-center mb-6">
                <div class="bg-yellow-400 text-black w-12 h-12 rounded-full flex items-center justify-center mr-4">
                  <i class="fas fa-flag text-xl"></i>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold">Notre constat</h2>
              </div>
              <p class="mb-6">Lors de nos descentes sur le terrain, nous avons constaté :</p>
              <ul class="mb-6 space-y-3">
                <li class="flex items-start">
                  <i class="fas fa-circle text-yellow-400 text-xs mt-2 mr-3"></i>
                  <span>Méconnaissance des lois électorales</span>
                </li>
                <li class="flex items-start">
                  <i class="fas fa-circle text-yellow-400 text-xs mt-2 mr-3"></i>
                  <span>Manque de formation des scrutateurs</span>
                </li>
                <li class="flex items-start">
                  <i class="fas fa-circle text-yellow-400 text-xs mt-2 mr-3"></i>
                  <span>Jeunes peu informés sur leurs droits</span>
                </li>
              </ul>
              <div class="border-l-4 border-yellow-400 italic pl-4 py-2 bg-gray-700">
                "Surveiller le vote est crucial, connaître le processus du décompte est essentiel pour des élections transparentes."
              </div>
            </div>
            <div class="md:w-1/2">
              <img src="/images/descente.jpg" alt="Sensibilisation électorale" class="w-full rounded-lg shadow-md">
            </div>
          </div>
        </section>

        <!-- Formation Section -->
        <section id="formation" class="mb-12">
          <div class="text-center mb-12">
            <div class="inline-flex items-center mb-4">
              <div class="bg-yellow-400 text-black w-12 h-12 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-graduation-cap text-xl"></i>
              </div>
              <h2 class="text-2xl text-black md:text-3xl font-bold">Notre programme de formation</h2>
            </div>
            <p class="text-gray-400 max-w-2xl mx-auto">Nous formons les jeunes sur les aspects clés du processus électoral</p>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gray-800 rounded-lg shadow-md p-6 border-t-4 border-yellow-400 transition duration-300 hover:shadow-lg hover:-translate-y-2">
              <div class="bg-yellow-400 text-black w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                <i class="fas fa-list text-2xl"></i>
              </div>
              <h3 class="text-xl font-semibold text-center mb-3">Liste électorale</h3>
              <p class="text-gray-400 text-center">Comment s'inscrire et importance de la publication</p>
              <div class="absolute top-4 right-4 text-4xl font-bold text-gray-700">01</div>
            </div>
            
            <div class="bg-gray-800 rounded-lg shadow-md p-6 border-t-4 border-yellow-400 transition duration-300 hover:shadow-lg hover:-translate-y-2">
              <div class="bg-yellow-400 text-black w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                <i class="fas fa-user-shield text-2xl"></i>
              </div>
              <h3 class="text-xl font-semibold text-center mb-3">Rôle du scrutateur</h3>
              <p class="text-gray-400 text-center">Avant, pendant et après le vote</p>
              <div class="absolute top-4 right-4 text-4xl font-bold text-gray-700">02</div>
            </div>
            
            <div class="bg-gray-800 rounded-lg shadow-md p-6 border-t-4 border-yellow-400 transition duration-300 hover:shadow-lg hover:-translate-y-2">
              <div class="bg-yellow-400 text-black w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                <i class="fas fa-vote-yea text-2xl"></i>
              </div>
              <h3 class="text-xl font-semibold text-center mb-3">Processus de vote</h3>
              <p class="text-gray-400 text-center">De l'arrivée au bureau jusqu'au décompte</p>
              <div class="absolute top-4 right-4 text-4xl font-bold text-gray-700">03</div>
            </div>
            
            <div class="bg-gray-800 rounded-lg shadow-md p-6 border-t-4 border-yellow-400 transition duration-300 hover:shadow-lg hover:-translate-y-2">
              <div class="bg-yellow-400 text-black w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                <i class="fas fa-gavel text-2xl"></i>
              </div>
              <h3 class="text-xl font-semibold text-center mb-3">Lois électorales</h3>
              <p class="text-gray-400 text-center">Droits et obligations des électeurs</p>
              <div class="absolute top-4 right-4 text-4xl font-bold text-gray-700">04</div>
            </div>
          </div>
        </section>

        <!-- Actions Section -->
        <section id="actions" class="bg-gray-800 rounded-xl shadow-lg p-8 mb-12">
          <div class="flex flex-col items-center mb-12">
            <div class="bg-yellow-400 text-black w-12 h-12 rounded-full flex items-center justify-center mr-4 mb-4">
              <i class="fas fa-tasks text-xl"></i>
            </div>
            <h2 class="text-2xl md:text-3xl font-bold text-center">Nos actions sur le terrain</h2>
          </div>
          
          <div class="relative pl-8 border-l-2 border-gray-700 space-y-10">
            <!-- Item 1 -->
            <div class="relative">
              <div class="absolute -left-11 top-0 w-6 h-6 rounded-full border-4 border-gray-900 bg-yellow-400"></div>
              <div class="bg-gray-700 rounded-lg shadow-sm p-6">
                <div class="text-yellow-400 font-semibold mb-2">Juillet 2024</div>
                <h3 class="text-xl font-semibold mb-3">Lancement officiel</h3>
                <p class="text-gray-400 mb-4">Sensibilisation lors d'un championnat de football à New Bell, Douala</p>
                <img src="/images/lancement.jpg" alt="Lancement" class="w-full rounded-lg shadow">
              </div>
            </div>
            
            <!-- Item 2 -->
            <div class="relative">
              <div class="absolute -left-11 top-0 w-6 h-6 rounded-full border-4 border-gray-900 bg-yellow-400"></div>
              <div class="bg-gray-700 rounded-lg shadow-sm p-6">
                <div class="text-yellow-400 font-semibold mb-2">Novembre 2024</div>
                <h3 class="text-xl font-semibold mb-3">Foire de Ngondo</h3>
                <p class="text-gray-400">Stand d'information et sessions de formation intensive</p>
              </div>
            </div>
            
            <!-- Item 3 -->
            <div class="relative">
              <div class="absolute -left-11 top-0 w-6 h-6 rounded-full border-4 border-gray-900 bg-yellow-400"></div>
              <div class="bg-gray-700 rounded-lg shadow-sm p-6">
                <div class="text-yellow-400 font-semibold mb-2">Décembre 2024</div>
                <h3 class="text-xl font-semibold mb-3">Festival Fomaric</h3>
                <p class="text-gray-400 mb-4">Ateliers interactifs de sensibilisation électorale</p>
                <img src="/images/fomaric.jpg" alt="Festival" class="w-full rounded-lg shadow">
              </div>
            </div>
            
            <!-- Item 4 -->
            <div class="relative">
              <div class="absolute -left-11 top-0 w-6 h-6 rounded-full border-4 border-gray-900 bg-yellow-400"></div>
              <div class="bg-gray-700 rounded-lg shadow-sm p-6">
                <div class="text-yellow-400 font-semibold mb-2">Janvier 2025</div>
                <h3 class="text-xl font-semibold mb-3">Campagne terrain</h3>
                <p class="text-gray-400">Activitités quotidiennes avec soutien d'acteurs politiques</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Promoteur Section -->
        <section id="promoteur" class="bg-gray-800 rounded-xl shadow-lg p-8 mb-12">
          <div class="flex flex-col lg:flex-row items-center">
            <div class="lg:w-1/3 mb-8 lg:mb-0 flex flex-col items-center">
              <div class="relative">
                <img src="/images/3.jpg" alt="Alain Boris" class="w-64 h-64 rounded-full object-cover border-4 border-gray-900 shadow-lg">
                <div class="absolute -bottom-2 -right-2 bg-yellow-400 text-black w-16 h-16 rounded-full flex items-center justify-center border-4 border-gray-900">
                  <i class="fas fa-lightbulb text-2xl"></i>
                </div>
              </div>
              <h3 class="text-xl font-semibold mt-6">Alain Boris</h3>
              <p class="text-gray-400">Fondateur & coordinateur</p>
            </div>
            <div class="lg:w-2/3 lg:pl-12">
              <div class="flex items-center mb-6">
                <div class="bg-yellow-400 text-black w-12 h-12 rounded-full flex items-center justify-center mr-4">
                  <i class="fas fa-user-tie text-xl"></i>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold">Le coordinateur</h2>
              </div>
              <blockquote class="border-l-4 border-yellow-400 pl-6 italic text-xl mb-8">
                "Chacun doit à sa façon apporter une pierre à l'édifice. Nous devons tous travailler selon nos moyens pour appuyer un changement démocratique."
              </blockquote>
            </div>
          </div>
        </section>

        <!-- Récompenses Section -->
        <section id="recompenses" class="bg-gray-800 rounded-xl shadow-lg p-8">
          <div class="flex flex-col lg:flex-row items-center">
            <div class="lg:w-1/2 mb-8 lg:mb-0 lg:pr-8">
              <div class="flex items-center mb-6">
                <div class="bg-yellow-400 text-black w-12 h-12 rounded-full flex items-center justify-center mr-4">
                  <i class="fas fa-trophy text-xl"></i>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold">Notre programme de récompenses</h2>
              </div>
              <p class="mb-6">
                Pour renforcer l'engagement citoyen à travers le sport, nous récompensons mensuellement :
              </p>
              <div class="bg-gray-700 rounded-lg p-6 flex items-start mb-8">
                <div class="bg-yellow-400 text-black w-14 h-14 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                  <i class="fas fa-crown text-2xl"></i>
                </div>
                <div>
                  <h3 class="text-xl font-semibold mb-2">CFOOT PLAYER OF THE MONTH</h3>
                  <p class="text-gray-400">Le meilleur joueur du championnat reçoit cette distinction honorifique pour son excellence et son fair-play.</p>
                </div>
              </div>
              <p>
                Cette initiative crée un pont entre sport et éducation civique, touchant un public jeune et dynamique.
              </p>
            </div>
            <div class="lg:w-1/2 relative">
              <img src="/images/month.jpg" alt="Trophée CFOOT" class="w-full rounded-lg shadow-xl">
              <div class="absolute top-4 right-4 bg-yellow-400 text-black px-4 py-2 rounded-full font-bold">
                CFOOT
              </div>
            </div>
          </div>
        </section>

        <!-- CTA Section -->
        <div class="text-center mt-16">
          <Link :href="route('registered')" class="inline-block bg-yellow-400 hover:bg-yellow-500 text-black px-8 py-4 rounded-lg font-bold text-lg transition duration-300">
            <i class="fas fa-file-alt mr-2"></i> Enregistrez-vous maintenant
          </Link>
        </div>
      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

defineProps({
  title: String,
  description: String
});

const breadcrumbs = [
    { label: 'Accueil', url: '/', active: false },
    { label: 'A Propos de Nous', active: true }
  ];
</script>

<style scoped>
/* Smooth scrolling for anchor links */
html {
  scroll-behavior: smooth;
}

/* Custom scrollbar for better UX */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}
::-webkit-scrollbar-track {
  background: #1a202c;
}
::-webkit-scrollbar-thumb {
  background: #f6e05e;
  border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
  background: #ecc94b;
}
</style>