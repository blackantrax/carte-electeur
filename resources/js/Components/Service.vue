<template>
    <div class="py-5">
        <!-- Services Cards -->
        <div class="row g-4">
            <!-- Card 1 -->
            <div class="col-md-4">
                <h1 class="main-title">À votre service</h1>
                <p class="subtitle">En quelques clics, je retrouve <strong>mes principales démarches et informations</strong> relatives aux élections.</p>
                <p class="civic-duty">Voter est un droit,<br>c'est aussi un devoir civique.</p>
            </div>

            <!-- Card 2 -->
            <div class="col-md-4">
                <Link href="/services/subcription">
                    <div class="service-card">
                        <img class="img-fluid rounded-start w-50 mx-auto" src="images/svg/inscription.svg" />
                        <h2 class="service-title">Je m'inscris sur les listes électorales</h2>
                        <Link href="/services/subcription" class="external-link">
                            <svg width="24" height="24" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z"/>
                            </svg>
                        </Link>
                    </div>
                </Link>
            </div>

            <!-- Card 3 -->
            <div class="col-md-4">
                <Link href="/services/registered">
                    <div class="service-card items-center">
                        <img class="img-fluid rounded-start w-50 mx-auto" src="images/svg/picto-je-donne-procuration.svg" />
                        <h2 class="service-title">Je participe au recensement</h2>
                        <Link href="/services/registered" class="external-link">
                            <svg width="24" height="24" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z"/>
                            </svg>
                        </Link>
                    </div>
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped>
.service-card {
    background: white;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    transition: transform 0.3s ease;
    height: 100%;
    position: relative;
    padding: 2rem;
    text-align: center;
}

.service-card:hover {
    transform: translateY(-5px);
}

.service-icon {
    width: 60px;
    height: 60px;
    margin-bottom: 1.5rem;
}

.external-link {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    color: #1d1d1b;
    text-decoration: none;
}

.service-title {
    color: #1d1d1b;
    font-weight: 600;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.main-title {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 1rem;
}

.subtitle {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.civic-duty {
    font-style: italic;
    color: #666;
}
</style>
