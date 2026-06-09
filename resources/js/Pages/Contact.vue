<template>
  <FrontLayout>
    <Head :title="title" />
    <PageTitle title="Contactez-nous" :breadcrumbs="breadcrumbs" />
    
    <div class="contact-container bg-gradient-to-b from-gray-50 to-white py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success Message -->
        <div v-if="$page.props.flash && $page.props.flash.success" class="mb-8 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
          <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <p>{{ $page.props.flash.success }}</p>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="Object.keys($page.props.errors).length > 0" class="mb-8 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
          <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <p>Veuillez corriger les erreurs dans le formulaire.</p>
          </div>
          <ul class="mt-2 list-disc list-inside">
            <li v-for="(error, field) in $page.props.errors" :key="field">
              {{ error }}
            </li>
          </ul>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <!-- Contact Form Section -->
          <div class="contact-form bg-white p-10 rounded-2xl shadow-2xl relative overflow-hidden">
            <!-- Decorative elements -->
            <div class="absolute -top-20 -right-20 w-64 h-64 rounded-full bg-yellow-100 opacity-20"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 rounded-full bg-yellow-100 opacity-20"></div>
            
            <h1 class="text-4xl font-extrabold text-gray-900 mb-6 relative">
              <span class="relative z-10">Parlons ensemble</span>
              <span class="absolute bottom-0 left-0 w-20 h-1.5 bg-yellow-500 rounded-full"></span>
            </h1>
            
            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
              Notre équipe est à votre disposition pour répondre à toutes vos questions sur les cartes d'électeurs.
            </p>

            <form @submit.prevent="submit" class="space-y-6">
              <!-- Name Field -->
              <div class="form-group relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Votre nom complet <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input 
                    type="text" 
                    v-model="form.name"
                    :class="{'border-red-500': $page.props.errors.name}"
                    class="w-full px-5 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all pl-12"
                    placeholder="Saturin PENLAP"
                    required
                  >
                  <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                    <i class="fas fa-user text-lg"></i>
                  </div>
                </div>
                <p v-if="$page.props.errors.name" class="mt-1 text-sm text-red-500">
                  {{ $page.props.errors.name }}
                </p>
              </div>

              <!-- Email Field -->
              <div class="form-group relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Adresse e-mail <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input 
                    type="email" 
                    v-model="form.email"
                    :class="{'border-red-500': $page.props.errors.email}"
                    class="w-full px-5 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all pl-12"
                    placeholder="saturin.penlap@example.com"
                    required
                  >
                  <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                    <i class="fas fa-envelope text-lg"></i>
                  </div>
                </div>
                <p v-if="$page.props.errors.email" class="mt-1 text-sm text-red-500">
                  {{ $page.props.errors.email }}
                </p>
              </div>

              <!-- Subject Field -->
              <div class="form-group relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Objet du message <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <select 
                    v-model="form.subject"
                    :class="{'border-red-500': $page.props.errors.subject}"
                    class="w-full px-5 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all appearance-none pl-12 pr-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIGNsYXNzPSJsdWNpZGUgbHVjaWRlLWNoZXZyb24tZG93biI+PHBhdGggZD0ibTYgOSA2IDYgNi02Ii8+PC9zdmc+')] bg-no-repeat bg-[center_right_1rem]"
                    required
                  >
                    <option value="" disabled selected>Sélectionnez un sujet</option>
                    <option value="Inscription électorale">Inscription électorale</option>
                    <option value="Problème de carte">Problème de carte</option>
                    <option value="Demande d'information">Demande d'information</option>
                    <option value="Autre demande">Autre demande</option>
                  </select>
                  <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                    <i class="fas fa-tag text-lg"></i>
                  </div>
                </div>
                <p v-if="$page.props.errors.subject" class="mt-1 text-sm text-red-500">
                  {{ $page.props.errors.subject }}
                </p>
              </div>

              <!-- Message Field -->
              <div class="form-group relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Votre message <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <textarea 
                    v-model="form.message"
                    :class="{'border-red-500': $page.props.errors.message}"
                    class="w-full px-5 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all pl-12 min-h-[180px]"
                    placeholder="Décrivez votre demande en détail..."
                    required
                  ></textarea>
                  <div class="absolute left-3 top-4 text-gray-400">
                    <i class="fas fa-comment-alt text-lg"></i>
                  </div>
                </div>
                <p v-if="$page.props.errors.message" class="mt-1 text-sm text-red-500">
                  {{ $page.props.errors.message }}
                </p>
              </div>

              <!-- Submit Button -->
              <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center space-x-3 relative overflow-hidden group"
                :disabled="form.processing"
              >
                <span class="relative z-10">
                  <span v-if="form.processing">
                    <i class="fas fa-circle-notch fa-spin mr-2"></i> Envoi en cours...
                  </span>
                  <span v-else>
                    <i class="fas fa-paper-plane mr-2"></i> Envoyer mon message
                  </span>
                </span>
              </button>
            </form>
          </div>

          <!-- Contact Info Section -->
          <div class="contact-info bg-gradient-to-br from-gray-900 to-gray-800 text-white p-10 rounded-2xl shadow-2xl relative overflow-hidden">
            <!-- Decorative elements -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-500 opacity-10 rounded-full transform translate-x-16 -translate-y-16"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-yellow-500 opacity-10 rounded-full transform -translate-x-16 translate-y-16"></div>
            
            <h2 class="text-3xl font-extrabold mb-8 relative">
              <span class="text-yellow-400">Nos coordonnées</span>
              <span class="absolute bottom-0 left-0 w-16 h-1 bg-yellow-400 rounded-full"></span>
            </h2>
            
            <div class="space-y-8">
              <!-- Location -->
              <div class="flex items-start group">
                <div class="bg-yellow-500 text-gray-900 p-3 rounded-xl mr-5 transform transition-transform group-hover:rotate-6">
                  <i class="fas fa-map-marker-alt text-xl"></i>
                </div>
                <div>
                  <h3 class="font-bold text-xl mb-2">Localisation</h3>
                  <div class="space-y-1 text-gray-300">
                    <p>Cameroun (LITTORAL)</p>
                    <p>Douala, New Bell</p>
                    <p>Centre 10158</p>
                  </div>
                  <a 
                    href="https://maps.google.com?q=Douala+New+Bell+Centre+10158" 
                    target="_blank"
                    class="inline-flex items-center mt-3 text-yellow-400 hover:text-yellow-300 transition-colors"
                  >
                    <span>Voir sur la carte</span>
                    <i class="fas fa-external-link-alt ml-2 text-sm"></i>
                  </a>
                </div>
              </div>
              
              <!-- Email -->
              <div class="flex items-start group">
                <div class="bg-yellow-500 text-gray-900 p-3 rounded-xl mr-5 transform transition-transform group-hover:rotate-6">
                  <i class="fas fa-envelope text-xl"></i>
                </div>
                <div>
                  <h3 class="font-bold text-xl mb-2">Email</h3>
                  <p class="text-gray-300 mb-3">contact@carte-electeur.com</p>
                  <a 
                    href="mailto:contact@carte-electeur.com" 
                    class="inline-flex items-center text-yellow-400 hover:text-yellow-300 transition-colors"
                  >
                    <span>Nous écrire</span>
                    <i class="fas fa-arrow-right ml-2 text-sm"></i>
                  </a>
                </div>
              </div>
              
              <!-- Phone -->
              <div class="flex items-start group">
                <div class="bg-yellow-500 text-gray-900 p-3 rounded-xl mr-5 transform transition-transform group-hover:rotate-6">
                  <i class="fas fa-phone-alt text-xl"></i>
                </div>
                <div>
                  <h3 class="font-bold text-xl mb-2">Téléphone</h3>
                  <p class="text-gray-300">+237 659 215 496</p>
                  <p class="text-gray-300 text-sm mt-1">Lundi - Vendredi, 8h - 17h</p>
                  <a 
                    href="tel:+237659215496" 
                    class="inline-flex items-center mt-3 text-yellow-400 hover:text-yellow-300 transition-colors"
                  >
                    <span>Appeler maintenant</span>
                    <i class="fas fa-phone-volume ml-2 text-sm"></i>
                  </a>
                </div>
              </div>
            </div>
            
            <!-- Social Media -->
            <div class="mt-12">
              <h3 class="font-bold text-xl mb-5">Suivez notre actualité</h3>
              <div class="flex space-x-5">
                <a 
                  v-for="social in socialMedia" 
                  :key="social.name"
                  :href="social.url" 
                  target="_blank"
                  class="social-icon bg-gray-700 hover:bg-yellow-500 text-white p-3 rounded-full transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg"
                  :aria-label="social.name"
                >
                  <i :class="social.icon"></i>
                </a>
              </div>
            </div>

            <!-- Emergency Contact -->
            <div class="mt-10 bg-gray-700 bg-opacity-50 rounded-xl p-5 border-l-4 border-yellow-500">
              <div class="flex items-start">
                <div class="text-yellow-500 mr-3">
                  <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                  <h4 class="font-bold text-lg mb-1">Urgence électorale</h4>
                  <p class="text-gray-300 text-sm">Pour les problèmes urgents concernant votre carte d'électeur</p>
                  <a href="tel:+237659215496" class="inline-flex items-center mt-2 text-yellow-400 hover:text-yellow-300 transition-colors">
                    <span>+237 680 000 000</span>
                    <i class="fas fa-phone ml-2 text-sm"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </FrontLayout>
</template>

<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';
import PageTitle from '@/Components/PageTitle.vue';

const props = defineProps({
  title: {
    type: String,
    default: 'Contactez-nous - Cartes d\'Électeurs'
  },
  errors: Object
});

const breadcrumbs = [
  { label: 'Accueil', url: '/', active: false },
  { label: 'Contactez-nous', active: true }
];

const form = useForm({
  name: '',
  email: '',
  subject: '',
  message: ''
});

const socialMedia = [
  { name: 'Facebook', icon: 'fab fa-facebook-f', url: 'https://web.facebook.com/profile.php?id=61565828483750' },
  { name: 'YouTube', icon: 'fab fa-youtube', url: 'https://www.youtube.com/@cartesdelecteurs' },
  { name: 'Instagram', icon: 'fab fa-instagram', url: 'https://www.instagram.com/cartesdelecteur/' },
  { name: 'TikTok', icon: 'fab fa-tiktok', url: 'https://www.tiktok.com/@carteselecteurs' }
];

const submit = () => {
  form.post(route('contact.submit'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
    }
  });
};
</script>

<style scoped>
.contact-container {
  background-image: 
    radial-gradient(circle at 20% 30%, rgba(245, 158, 11, 0.05) 0%, transparent 50%),
    radial-gradient(circle at 80% 70%, rgba(245, 158, 11, 0.05) 0%, transparent 50%);
}

.contact-form {
  border-left: 4px solid #f59e0b;
}

.social-icon {
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.social-icon:hover {
  transform: translateY(-3px) scale(1.1);
}

input:focus, textarea:focus, select:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3);
}

/* Animation for form submission button */
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

button[disabled] {
  animation: pulse 1.5s infinite;
  opacity: 0.8;
}
</style>