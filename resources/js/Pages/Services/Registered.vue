<template>
  <FrontLayout>
    <Head title="Sécurisation du Vote" />
    
    <!-- Header visuel renforcé -->
    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 py-8 text-white">
      <div class="container mx-auto px-4 text-center">
        <h1 class="text-3xl md:text-4xl font-bold mb-3">
          <i class="bi bi-shield-lock mr-2"></i> Sécurisez Votre Vote
        </h1>
        <p class="text-lg opacity-90 max-w-2xl mx-auto">
          Protection des électeurs camerounais au pays et dans la diaspora
        </p>
      </div>
    </div>

    <!-- Conteneur principal avec carte interactive -->
    <div class="container mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8">
      <!-- Carte dynamique (40% largeur) -->
      <div class="lg:w-2/5 bg-white rounded-xl shadow-lg overflow-hidden sticky top-4 h-fit">
        <div class="bg-gray-800 p-4 text-white font-medium">
          <i class="bi bi-geo-alt-fill mr-2"></i> Votre position électorale
        </div>
        <div class="p-4 h-96 relative">
          <!-- Carte interactive SVG -->
          <svg viewBox="0 0 500 300" class="w-full h-full">
            <!-- Fond de carte -->
            <path d="M100,100 L400,100 L400,250 L100,250 Z" fill="#E5E7EB" />
            
            <!-- Surbrillance dynamique selon la sélection -->
            <path v-if="form.region" 
                  :d="getRegionPath(form.region)" 
                  fill="#F59E0B" 
                  class="transition-all duration-300"
                  opacity="0.7" />
            
            <!-- Points pour les bureaux de vote -->
            <circle v-for="b in bureaux" 
                    :key="b.code"
                    :cx="b.coords.x" 
                    :cy="b.coords.y"
                    r="5"
                    :fill="form.bureauVote === b.code ? '#D97706' : '#10B981'"
                    @click="form.bureauVote = b.code"
                    class="cursor-pointer hover:r-6 transition-all" />
            
            <!-- Légende -->
            <text x="20" y="280" class="text-xs fill-gray-600">Cliquez sur un point pour sélectionner votre bureau</text>
          </svg>
          
          <!-- Légende textuelle dynamique -->
          <div class="absolute bottom-4 left-0 right-0 px-4">
            <div class="bg-white bg-opacity-90 p-3 rounded-lg shadow">
              <p class="font-semibold text-yellow-700">
                <i class="bi bi-info-circle mr-1"></i> 
                {{ locationSummary || "Sélectionnez votre localisation" }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Formulaire (60% largeur) -->
      <div class="lg:w-3/5 bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Barre de progression stylisée -->
        <div class="bg-gray-50 px-6 py-4 border-b">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">
              Étape {{ step }} sur {{ totalSteps }}
            </span>
            <span class="text-sm font-semibold text-yellow-600">
              {{ Math.round((step / totalSteps) * 100) }}% complété
            </span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-yellow-500 h-2.5 rounded-full transition-all duration-500"
                 :style="{ width: progressWidth }"></div>
          </div>
        </div>

        <!-- Contenu des étapes -->
        <div class="p-6 md:p-8">
          <!-- Étape 1 - Identification -->
          <transition name="fade-slide" mode="out-in">
            <div v-if="step === 1" key="step1" class="space-y-6">
              <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                  <span class="bg-yellow-100 text-yellow-800 w-8 h-8 rounded-full flex items-center justify-center mr-3">1</span>
                  Votre identité électorale
                </h2>
                <p class="text-gray-600 mt-1">
                  Commençons par vérifier vos informations de base
                </p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Carte d'identité interactive -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Numéro CIN/ELECAM
                    <span class="text-xs text-gray-500">(Visible sur votre carte électorale)</span>
                  </label>
                  <div class="relative">
                    <input v-model.trim="form.identifiant" 
                           type="text"
                           placeholder="Ex: 1234 5678 9012"
                           class="input-style pl-12"
                           :class="{ 'input-error': errors.identifiant }"
                           @input="validateField('identifiant')">
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                      <i class="bi bi-person-badge text-lg"></i>
                    </div>
                  </div>
                  <span v-if="errors.identifiant" class="error-message">{{ errors.identifiant }}</span>
                </div>

                <!-- Type d'élection avec icônes -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Type d'élection
                  </label>
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label v-for="(opt, index) in electionOptions" 
                           :key="index"
                           class="flex flex-col items-center p-4 border rounded-lg cursor-pointer transition-all"
                           :class="{
                             'border-yellow-500 bg-yellow-50': form.typeElection === opt.value,
                             'border-gray-200 hover:border-yellow-300': form.typeElection !== opt.value
                           }"
                           @click="form.typeElection = opt.value; validateField('typeElection')">
                          <i :class="[opt.icon, 'text-2xl mb-2', form.typeElection === opt.value ? 'text-yellow-600' : 'text-gray-400']"></i>
                          <span class="text-sm font-medium text-center">{{ opt.label }}</span>
                          <input type="radio" v-model="form.typeElection" :value="opt.value" class="hidden">
                        </label>
                  </div>
                  <span v-if="errors.typeElection" class="error-message">{{ errors.typeElection }}</span>
                </div>
              </div>
            </div>
          </transition>

          <!-- Étape 2 - Localisation -->
          <transition name="fade-slide" mode="out-in">
            <div v-if="step === 2" key="step2" class="space-y-6">
              <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                  <span class="bg-yellow-100 text-yellow-800 w-8 h-8 rounded-full flex items-center justify-center mr-3">2</span>
                  Votre localisation
                </h2>
                <p class="text-gray-600 mt-1">
                  Où êtes-vous inscrit(e) pour voter ?
                </p>
              </div>

              <!-- Sélecteur Pays avec drapeau -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Pays de vote
                </label>
                <div class="relative">
                  <select v-model="form.pays"
                          class="input-style pl-12"
                          :class="{ 'input-error': errors.pays }"
                          @change="loadGeoData">
                    <option value="" disabled>Sélectionnez votre pays</option>
                    <option v-for="p in pays" :key="p.code" :value="p.code">
                      {{ p.nom }}
                    </option>
                  </select>
                  <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                    <i v-if="form.pays" class="bi" :class="'bi-flag-' + form.pays.toLowerCase()"></i>
                    <i v-else class="bi bi-globe text-gray-400"></i>
                  </div>
                </div>
                <span v-if="errors.pays" class="error-message">{{ errors.pays }}</span>
              </div>

              <!-- Affichage dynamique selon le pays -->
              <div v-if="form.pays === 'CM'" class="space-y-5">
                <!-- Sélecteurs pour le Cameroun -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Région
                    </label>
                    <div class="relative">
                      <select v-model="form.region"
                              class="input-style"
                              :class="{ 'input-error': errors.region }"
                              @change="loadDepartements">
                        <option value="" disabled>Sélectionnez votre région</option>
                        <option v-for="r in regions" :key="r.code" :value="r.code">
                          {{ r.nom }}
                        </option>
                      </select>
                    </div>
                    <span v-if="errors.region" class="error-message">{{ errors.region }}</span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Département
                    </label>
                    <select v-model="form.departement"
                            class="input-style"
                            :class="{ 'input-error': errors.departement }"
                            @change="loadCommunes">
                      <option value="" disabled>Sélectionnez votre département</option>
                      <option v-for="d in departements" :key="d.code" :value="d.code">
                        {{ d.nom }}
                      </option>
                    </select>
                    <span v-if="errors.departement" class="error-message">{{ errors.departement }}</span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Commune
                    </label>
                    <select v-model="form.commune"
                            class="input-style"
                            :class="{ 'input-error': errors.commune }"
                            @change="loadBureaux">
                      <option value="" disabled>Sélectionnez votre commune</option>
                      <option v-for="c in communes" :key="c.code" :value="c.code">
                        {{ c.nom }}
                      </option>
                    </select>
                    <span v-if="errors.commune" class="error-message">{{ errors.commune }}</span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Bureau de vote
                    </label>
                    <select v-model="form.bureauVote"
                            class="input-style"
                            :class="{ 'input-error': errors.bureauVote }">
                      <option value="" disabled>Sélectionnez votre bureau</option>
                      <option v-for="b in bureaux" :key="b.code" :value="b.code">
                        {{ b.nom }} ({{ b.adresse }})
                      </option>
                    </select>
                    <span v-if="errors.bureauVote" class="error-message">{{ errors.bureauVote }}</span>
                  </div>
                </div>
              </div>

              <!-- Affichage pour la diaspora -->
              <div v-else-if="form.pays" class="space-y-5">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Ambassade/Consulat
                  </label>
                  <select v-model="form.ambassade"
                          class="input-style"
                          :class="{ 'input-error': errors.ambassade }">
                    <option value="" disabled>Sélectionnez votre représentation</option>
                    <option v-for="a in ambassades" :key="a.code" :value="a.code">
                      {{ a.nom }} - {{ a.ville }}
                    </option>
                  </select>
                  <span v-if="errors.ambassade" class="error-message">{{ errors.ambassade }}</span>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                  <div class="flex items-start">
                    <i class="bi bi-info-circle text-blue-600 mt-0.5 mr-2 flex-shrink-0"></i>
                    <p class="text-sm text-blue-800">
                      Pour les électeurs de la diaspora, votre bureau de vote sera automatiquement assigné à la représentation diplomatique sélectionnée.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </transition>

          <!-- Étape 3 - Confirmation -->
          <transition name="fade-slide" mode="out-in">
            <div v-if="step === 3" key="step3" class="space-y-6">
              <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                  <span class="bg-yellow-100 text-yellow-800 w-8 h-8 rounded-full flex items-center justify-center mr-3">3</span>
                  Confirmation finale
                </h2>
                <p class="text-gray-600 mt-1">
                  Vérifiez attentivement vos informations avant validation
                </p>
              </div>

              <!-- Carte récapitulative -->
              <div class="bg-gray-50 rounded-xl overflow-hidden border border-gray-200">
                <div class="bg-yellow-600 px-4 py-3 text-white font-medium">
                  <i class="bi bi-check-circle mr-2"></i>
                  Vos informations électorales
                </div>
                
                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-4">
                    <div>
                      <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Identité</p>
                      <div class="mt-1 space-y-2">
                        <p><span class="font-medium">Numéro CIN/ELECAM:</span> {{ form.identifiant || '-' }}</p>
                        <p><span class="font-medium">Type d'élection:</span> {{ form.typeElection || '-' }}</p>
                      </div>
                    </div>

                    <div>
                      <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Localisation</p>
                      <div class="mt-1 space-y-2">
                        <p><span class="font-medium">Pays:</span> {{ getPaysNom(form.pays) || '-' }}</p>
                        <template v-if="form.pays === 'CM'">
                          <p><span class="font-medium">Région:</span> {{ getRegionNom(form.region) || '-' }}</p>
                          <p><span class="font-medium">Département:</span> {{ getDepartementNom(form.departement) || '-' }}</p>
                          <p><span class="font-medium">Commune:</span> {{ getCommuneNom(form.commune) || '-' }}</p>
                          <p><span class="font-medium">Bureau de vote:</span> {{ getBureauNom(form.bureauVote) || '-' }}</p>
                        </template>
                        <template v-else>
                          <p><span class="font-medium">Représentation:</span> {{ getAmbassadeNom(form.ambassade) || '-' }}</p>
                        </template>
                      </div>
                    </div>
                  </div>

                  <!-- QR Code de confirmation -->
                  <div class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 rounded-lg p-4">
                    <div class="bg-white p-2 rounded-md mb-3">
                      <!-- Placeholder pour QR Code -->
                      <div class="w-32 h-32 bg-gray-100 flex items-center justify-center text-gray-400">
                        <i class="bi bi-qr-code text-4xl"></i>
                      </div>
                    </div>
                    <p class="text-sm text-center text-gray-600 mt-2">
                      Ce QR code confirme votre inscription
                    </p>
                    <button type="button" class="text-sm text-yellow-600 font-medium mt-2 hover:underline flex items-center">
                      <i class="bi bi-download mr-1"></i>
                      Télécharger la confirmation
                    </button>
                  </div>
                </div>
              </div>

              <!-- Consentement et soumission -->
              <div class="space-y-4">
                <div class="flex items-start">
                  <input v-model="form.consentement"
                         type="checkbox"
                         id="consentement"
                         class="h-5 w-5 text-yellow-600 mt-0.5"
                         :class="{ 'border-red-500': errors.consentement }"
                         @change="validateField('consentement')">
                  <label for="consentement" class="ml-3 text-sm text-gray-700">
                    Je certifie sur l'honneur l'exactitude de ces informations et autorise leur utilisation pour sécuriser mon vote conformément à la loi électorale camerounaise.
                    <a href="#" class="text-yellow-600 hover:underline">Politique de confidentialité</a>
                  </label>
                </div>
                <span v-if="errors.consentement" class="error-message">{{ errors.consentement }}</span>

                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                  <div class="flex">
                    <i class="bi bi-exclamation-triangle text-yellow-600 mt-0.5 mr-2 flex-shrink-0"></i>
                    <p class="text-sm text-yellow-800">
                      Toute fausse déclaration est passible de sanctions pénales selon l'article 164 du code électoral camerounais.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Navigation -->
        <div class="bg-gray-50 px-6 py-4 border-t flex flex-col-reverse md:flex-row justify-between gap-3">
          <button v-if="step > 1"
                  type="button"
                  @click="previousStep"
                  class="btn btn-gray flex items-center justify-center">
            <i class="bi bi-arrow-left mr-2"></i>
            Précédent
          </button>
          <div v-else></div>

          <button v-if="step < totalSteps"
                  type="button"
                  @click="nextStep"
                  :disabled="!isStepValid"
                  class="btn btn-yellow flex items-center justify-center ml-auto">
            Suivant
            <i class="bi bi-arrow-right ml-2"></i>
          </button>

          <button v-if="step === totalSteps" 
                  @click="submitForm" 
                  :disabled="loading || !isFormValid"
                  class="btn btn-yellow w-full md:w-auto flex items-center justify-center">
            <i v-if="loading" class="bi bi-arrow-repeat animate-spin mr-2"></i>
            <i v-else class="bi bi-shield-lock mr-2"></i>
            {{ loading ? 'Enregistrement...' : 'Valider et sécuriser mon vote' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Footer informatif -->
    <div class="bg-gray-800 text-white py-6 mt-12">
      <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="mb-4 md:mb-0">
            <p class="font-medium">Sécurisation du Vote Camerounais</p>
            <p class="text-sm opacity-80">Service officiel - Ministère de l'Administration Territoriale</p>
          </div>
          <div class="flex space-x-4">
            <a href="#" class="text-sm hover:underline flex items-center">
              <i class="bi bi-file-earmark-text mr-1"></i>
              Guide de l'électeur
            </a>
            <a href="#" class="text-sm hover:underline flex items-center">
              <i class="bi bi-telephone mr-1"></i>
              Assistance
            </a>
          </div>
        </div>
      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';
import axios from 'axios';

// Configuration de base
const totalSteps = 3;
const step = ref(1);
const loading = ref(false);

// Données géographiques
const pays = ref([
  { code: 'CM', nom: 'Cameroun' },
  { code: 'FR', nom: 'France' },
  { code: 'US', nom: 'États-Unis' },
  { code: 'BE', nom: 'Belgique' },
  { code: 'DE', nom: 'Allemagne' },
]);

const regions = ref([]);
const departements = ref([]);
const communes = ref([]);
const bureaux = ref([]);
const ambassades = ref([]);

// Formulaire
const form = ref({
  identifiant: '',
  typeElection: '',
  pays: '',
  region: '',
  departement: '',
  commune: '',
  bureauVote: '',
  ambassade: '',
  consentement: false
});

const errors = ref({});

// Options pour les types d'élection avec icônes Bootstrap
const electionOptions = [
  {
    value: 'Présidentielle',
    label: 'Présidentielle',
    icon: 'bi-person-square'
  },
  {
    value: 'Législatives',
    label: 'Législatives',
    icon: 'bi-building'
  },
  {
    value: 'Municipales',
    label: 'Municipales',
    icon: 'bi-house-door'
  }
];

// Méthodes optimisées pour charger les données géographiques
const loadGeoData = async () => {
  try {
    if (form.value.pays === 'CM') {
      const [regionsRes] = await Promise.all([
        axios.get('/api/regions')
      ]);
      regions.value = regionsRes.data;
      form.value.region = '';
      form.value.departement = '';
      form.value.commune = '';
      form.value.bureauVote = '';
    } else {
      const [ambassadesRes] = await Promise.all([
        axios.get(`/api/ambassades?pays=${form.value.pays}`)
      ]);
      ambassades.value = ambassadesRes.data;
      form.value.ambassade = '';
    }
  } catch (error) {
    console.error("Erreur de chargement des données géographiques", error);
  }
};

// Méthodes optimisées pour le chargement hiérarchique
const loadDepartements = async () => {
  if (!form.value.region) return;
  try {
    const response = await axios.get(`/api/departements?region=${form.value.region}`);
    departements.value = response.data;
    form.value.departement = '';
    form.value.commune = '';
    form.value.bureauVote = '';
  } catch (error) {
    console.error("Erreur de chargement des départements", error);
  }
};

const loadCommunes = async () => {
  if (!form.value.departement) return;
  try {
    const response = await axios.get(`/api/communes?departement=${form.value.departement}`);
    communes.value = response.data;
    form.value.commune = '';
    form.value.bureauVote = '';
  } catch (error) {
    console.error("Erreur de chargement des communes", error);
  }
};

const loadBureaux = async () => {
  if (!form.value.commune) return;
  try {
    const response = await axios.get(`/api/bureaux?commune=${form.value.commune}`);
    bureaux.value = response.data;
    form.value.bureauVote = '';
  } catch (error) {
    console.error("Erreur de chargement des bureaux", error);
  }
};

const loadAmbassades = async () => {
  if (!form.value.pays) return;
  try {
    const response = await axios.get(`/api/ambassades?pays=${form.value.pays}`);
    ambassades.value = response.data;
    form.value.ambassade = '';
  } catch (error) {
    console.error("Erreur de chargement des ambassades", error);
  }
};

// Helpers optimisés pour obtenir les noms
const getRegionNom = (code) => regions.value.find(r => r.code === code)?.nom || '';
const getDepartementNom = (code) => departements.value.find(d => d.code === code)?.nom || '';
const getCommuneNom = (code) => communes.value.find(c => c.code === code)?.nom || '';
const getBureauNom = (code) => bureaux.value.find(b => b.code === code)?.nom || '';
const getAmbassadeNom = (code) => ambassades.value.find(a => a.code === code)?.nom || '';
const getPaysNom = (code) => pays.value.find(p => p.code === code)?.nom || '';

// Chemins SVG simplifiés pour les régions
const getRegionPath = (regionCode) => {
  const paths = {
    'EN': 'M150,120 L200,120 L200,170 L150,170 Z',
    'NW': 'M200,120 L250,120 L250,170 L200,170 Z',
    'AD': 'M150,170 L200,170 L200,220 L150,220 Z',
  };
  return paths[regionCode] || '';
};

// Résumé de localisation optimisé
const locationSummary = computed(() => {
  if (!form.value.pays) return '';
  
  return form.value.pays === 'CM' 
    ? `${getRegionNom(form.value.region)}, ${getDepartementNom(form.value.departement)}`
    : `${getPaysNom(form.value.pays)} - ${getAmbassadeNom(form.value.ambassade)}`;
});

// Validation optimisée
const validateField = (field) => {
  if (!form.value[field]) {
    errors.value[field] = 'Ce champ est obligatoire';
  } else {
    delete errors.value[field];
  }
};

// Validation des étapes optimisée
const isStepValid = computed(() => {
  const stepValidations = {
    1: () => form.value.identifiant && form.value.typeElection && !errors.value.identifiant,
    2: () => form.value.pays && (
      form.value.pays === 'CM' 
        ? form.value.region && form.value.departement && form.value.commune && form.value.bureauVote
        : form.value.ambassade
    ),
    3: () => true
  };
  
  return stepValidations[step.value] ? stepValidations[step.value]() : false;
});

const isFormValid = computed(() => {
  return isStepValid.value && form.value.consentement;
});

// Navigation optimisée
const nextStep = () => {
  if (isStepValid.value) {
    step.value++;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const previousStep = () => {
  step.value--;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Barre de progression
const progressWidth = computed(() => `${(step.value / totalSteps) * 100}%`);

// Soumission optimisée avec gestion d'erreur
const submitForm = async () => {
  if (!isFormValid.value) return;
  
  loading.value = true;
  try {
    const response = await axios.post('/api/secure-vote', form.value);
    // Redirection ou affichage d'un message de succès
    console.log('Formulaire soumis avec succès', response.data);
    // Ici vous pourriez utiliser Inertia pour la redirection
    // router.visit('/confirmation');
  } catch (error) {
    console.error("Erreur lors de la soumission", error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  } finally {
    loading.value = false;
  }
};

// Initialisation
onMounted(() => {
  // Charger les données initiales si nécessaire
});
</script>

<style scoped>
.input-style {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all;
}

.input-error {
  @apply border-red-500 focus:ring-red-500 focus:border-red-500;
}

.error-message {
  @apply text-sm text-red-600 mt-1 block;
}

.btn {
  @apply px-6 py-2 rounded-lg font-medium transition-all flex items-center justify-center;
}

.btn-gray {
  @apply bg-gray-200 text-gray-700 hover:bg-gray-300;
}

.btn-yellow {
  @apply bg-yellow-500 text-white hover:bg-yellow-600 disabled:bg-yellow-300;
}

/* Transitions optimisées */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

/* Animation pour le chargement */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Animation d'entrée */
.animate-fade-in {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>