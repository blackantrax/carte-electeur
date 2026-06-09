<template>
  <footer class="footer bg-black text-white">
    <div class="container mx-auto px-4 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <!-- Logo et description -->
        <div class="text-center md:text-left">
          <img src="/images/logo.png" alt="Logo CARTES D'ELECTEURS" class="logo-carte mx-auto md:mx-0 mb-4">
          <p class="text-gray-300 mb-4">Plateforme officielle de cartes d'électeurs</p>
          
          <!-- Informations de contact -->
          <div class="contact-info">
            <div class="flex items-center mb-2">
              <i class="fas fa-envelope text-yellow-400 mr-2"></i>
              <a href="mailto:cartedelecteur8@gmail.com" class="hover:text-yellow-400 transition-colors">cartedelecteur8@gmail.com</a>
            </div>
            <div class="flex items-center">
              <i class="fas fa-phone text-yellow-400 mr-2"></i>
              <span>6 89 47 38 60 / 6 78 85 44 20</span>
            </div>
          </div>
        </div>

        <!-- Liens rapides -->
        <div>
          <h5 class="footer-title mb-4">Liens Rapides</h5>
          <ul class="space-y-2">
            <li><a href="#" class="footer-link">Portail de Transparence</a></li>
            <li><a href="#" class="footer-link">Plan du site</a></li>
            <li><a href="#" class="footer-link">Politique de Confidentialité</a></li>
            <li><a href="#" class="footer-link">Monde entier</a></li>
          </ul>
        </div>

        <!-- Ressources -->
        <div>
          <h5 class="footer-title mb-4">Ressources</h5>
          <ul class="space-y-2">
            <li><a href="#" class="footer-link">Signaler une faute</a></li>
            <li><a href="#" class="footer-link">Conditions d'utilisation</a></li>
            <li><a href="#" class="footer-link">Transparence</a></li>
            <li><a href="#" class="footer-link">FAQ</a></li>
          </ul>
        </div>

        <!-- Newsletter et boutons -->
        <div class="text-center md:text-left">
          <h5 class="footer-title mb-4">Restez informé</h5>
          <form class="mb-4">
            <div class="flex">
              <input type="email" placeholder="Votre email" class="flex-grow px-4 py-2 rounded-l focus:outline-none text-gray-900">
              <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold px-4 py-2 rounded-r transition-colors">
                <i class="fas fa-paper-plane"></i>
              </button>
            </div>
          </form>
          
          <div class="flex flex-col space-y-3">
            <a href="#" class="btn-donate">
              FAIRE UN DON <i class="fas fa-heart ml-2"></i>
            </a>
            <a href="contact.html" class="btn-contact">
              NOUS CONTACTER <i class="fas fa-envelope ml-2"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Ligne inférieure -->
      <div class="border-t border-gray-700 pt-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <p class="mb-4 md:mb-0">© {{ currentYear }} CARTES D'ELECTEURS. Tous droits réservés.</p>
          
          <div class="social-icons flex space-x-6">
            <a href="https://web.facebook.com/profile.php?id=61565828483750" aria-label="Facebook">
              <i class="fab fa-facebook-f hover:text-yellow-400 transition-colors"></i>
            </a>
            <a href="https://www.youtube.com/@cartesdelecteurs" aria-label="YouTube">
              <i class="fab fa-youtube hover:text-yellow-400 transition-colors"></i>
            </a>
            <a href="https://www.instagram.com/cartesdelecteur/" aria-label="Instagram">
              <i class="fab fa-instagram hover:text-yellow-400 transition-colors"></i>
            </a>
            <a href="https://www.tiktok.com/@carteselecteurs" aria-label="TikTok">
              <i class="fab fa-tiktok hover:text-yellow-400 transition-colors"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
const currentYear = new Date().getFullYear()
</script>

<style scoped>
.footer-title {
  @apply text-xl font-bold text-yellow-400 uppercase tracking-wider;
}

.footer-link {
  @apply text-gray-300 hover:text-yellow-400 transition-colors;
}

.logo-carte {
  @apply max-w-[180px] h-auto transition-transform duration-300 hover:scale-105;
}

.btn-donate {
  @apply bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-2 px-4 rounded flex items-center justify-center transition-colors duration-300;
}

.btn-contact {
  @apply bg-transparent border-2 border-yellow-500 hover:bg-yellow-500 hover:text-gray-900 text-yellow-500 font-bold py-2 px-4 rounded flex items-center justify-center transition-colors duration-300;
}

.social-icons a {
  @apply text-2xl text-white hover:text-yellow-400 transition-colors duration-300;
}

.contact-info a {
  @apply hover:text-yellow-400 transition-colors;
}
</style>