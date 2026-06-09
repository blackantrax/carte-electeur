<template>
    <FrontLayout>
        <Head :title="title" />
        <PageTitle title="Je m'inscris sur les listes électorales" :breadcrumbs="breadcrumbs" />
        <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Colonne de gauche -->
            <div class="space-y-6">
            <h1 class="text-3xl font-bold text-gray-900">
                Je m'inscris sur les listes électorales
            </h1>

            <p class="text-gray-600">
                Pour pouvoir voter aux prochaines élections, il est nécessaire de vous inscrire sur les listes électorales de votre commune. L'inscription sur les listes électorales peut être réalisée en ligne jusqu'au sixième mercredi précédant le scrutin.
            </p>

            <div class="space-y-4">
                <p class="font-medium">Pour demander une inscription sur les listes électorales, vous devez remplir <span class="font-semibold">deux conditions cumulatives</span> :</p>

                <div class="space-y-4">
                <div>
                    <h2 class="font-semibold">Avoir le droit de vote, ce qui implique de :</h2>
                    <ul class="list-disc pl-5 mt-2 space-y-2">
                    <li>posséder la <span class="font-medium">nationalité française</span> ;</li>
                    <li>être <span class="font-medium">majeur</span> (18 ans) au plus tard la veille du scrutin ou, en cas de second tour, la veille du second tour ;</li>
                    <li>jouir de ses <span class="font-medium">droits civils et politiques</span>.</li>
                    </ul>
                </div>

                <div>
                    <h2 class="font-semibold">Avoir une attache avec la commune au titre de :</h2>
                    <ul class="list-disc pl-5 mt-2 space-y-2">
                    <li>son domicile principal</li>
                    <li>de sa qualité de contribuable</li>
                    <li>de sa qualité de gérant de société.</li>
                    </ul>
                </div>
                </div>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                    Attention : l'inscription sur les listes électorales n'est pas automatique mais nécessite une démarche de votre part.
                    </p>
                </div>
                </div>
            </div>

            <a href="https://elecam.cm/?q=fr/contact#" target="" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-900 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Accéder au service en ligne
                <svg class="ml-2 -mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            </a>

            <div class="space-y-4">
                <h2 class="font-semibold">À l'appui de votre demande d'inscription, vous aurez à transmettre plusieurs documents :</h2>
                <ul class="list-disc pl-5 space-y-2">
                <li>un justificatif de <span class="font-medium">votre identité</span> (par exemple votre carte nationale d'identité) ;</li>
                <li>un justificatif de <span class="font-medium">votre lien avec la commune de vote</span> (par exemple votre facture d'électricité comme justificatif de domicile).</li>
                </ul>
            </div>

            <!-- <div class="mt-8">
                <h2 class="text-lg font-semibold mb-4">Voir la page en FALC</h2>
                <a href="#" class="inline-flex items-center text-blue-600 hover:text-blue-500">
                <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" />
                </svg>
                17-06-24-je-m'inscris-sur-les-listes-electorales-version-facile-à-lire-et-à-comprendre.pdf
                <span class="text-gray-500 ml-2">(382,59 Ko)</span>
                </a>
            </div>  -->
            </div>

            <!-- Colonne de droite -->
            <div class="hidden lg:block">
            <img
                src="/images/5.png"
                alt="Illustration du processus d'inscription"
                class="w-full max-w-md mx-auto"
            />
            </div>
        </div>
        </div>
    </FrontLayout>
  </template>

  <script setup>
    import FrontLayout from "@/Layouts/FrontLayout.vue";
    import { Head } from "@inertiajs/vue3";
    import PageTitle from "@/Components/PageTitle.vue";

    const breadcrumbs = [
        { label: 'Accueil', url: '/', active: false },
        { label: 'Inscription Online', active: true }
    ];
  </script>

  <style scoped>
  /* Ajoutez ici des styles personnalisés si nécessaire */
  </style>
