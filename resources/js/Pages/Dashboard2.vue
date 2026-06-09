<template>
    <AppLayout>
        <h1 class="text-2xl font-bold">Tableau de Bord</h1>

        <nav class="mt-4">
            <ul class="space-y-2">
                <li>
                    <Link href="/articles" class="px-4 py-2 bg-blue-500 text-white rounded">
                        Gérer les Articles
                    </Link>
                </li>
                <li>
                    <Link href="/events" class="px-4 py-2 bg-green-500 text-white rounded">
                        Gérer les Événements
                    </Link>
                </li>
                <li>
                    <Link href="/gallery" class="px-4 py-2 bg-purple-500 text-white rounded">
                        Gérer la Galerie
                    </Link>
                </li>
            </ul>
        </nav>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>
