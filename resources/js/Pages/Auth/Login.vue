<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Se connecter" />

    <div class="flex items-center justify-center min-h-screen bg-[#1d1d1b] relative overflow-hidden">
        <!-- Effet de flou en arrière-plan -->
        <div class="absolute inset-0 bg-cover bg-center opacity-20 blur-lg" style="background-image: url('/images/background.jpg');"></div>

        <div class="relative p-8 w-full max-w-md">
            <!-- Logo au centre -->
            <div class="flex justify-center mb-6">
                <AuthenticationCardLogo />
            </div>

            <div class="text-center">
                <h1 class="text-[#fadb17] text-3xl font-extrabold">Connexion</h1>
                <p class="text-gray-400 mt-1">Accédez à votre tableau de bord</p>
            </div>

            <div v-if="status" class="mb-4 text-sm font-medium text-green-400">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="mt-6 space-y-4">
                <div>
                    <InputLabel for="email" value="Email" class="text-[#fadb17]" />
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-[#fadb17]">
                            📧
                        </span>
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full pl-10 bg-gray-900 text-white border-[#fadb17] rounded-lg"
                            required
                            autofocus
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="Mot de passe" class="text-[#fadb17]" />
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-[#fadb17]">
                            🔒
                        </span>
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full pl-10 bg-gray-900 text-white border-[#fadb17] rounded-lg"
                            required
                            autocomplete="current-password"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between text-gray-400">
                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.remember" name="remember" class="text-[#fadb17]" />
                        <span class="ms-2 text-sm">Se souvenir de moi</span>
                    </label>

                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-[#fadb17] text-sm hover:text-yellow-300">
                        Mot de passe oublié ?
                    </Link>
                </div>
                <div v-if="form.hasErrors" class="text-red-500 text-sm text-center mt-2">
                    Vérifiez vos identifiants et réessayez.
                </div>

                <div class="mt-4">
                    <PrimaryButton
                        :disabled="form.processing"
                        class="w-50 mx-auto bg-[#fadb17] hover:bg-yellow-600 text-black font-bold py-2 px-4 rounded-lg transition duration-300 transform hover:scale-105 shadow-md">
                        🔑 {{ form.processing ? 'Connexion...' : 'Se Connecter' }}
                    </PrimaryButton>

                </div>
            </form>
        </div>
    </div>
</template>
