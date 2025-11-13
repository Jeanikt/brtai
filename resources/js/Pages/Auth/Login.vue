<template>
    <div class="min-h-screen bg-cover bg-center bg-no-repeat flex flex-col items-center justify-start p-4 relative"
        style="background-image: url('/img/home-background.png');">

        <Head title="Login" />

        <div class="absolute inset-0 bg-black/40"></div>

        <div class="relative z-10 flex flex-col items-center w-full max-w-md mt-8">
            <div class="w-full mb-10 flex justify-center">
                <NavGlass />
            </div>

            <!-- Main Login Card -->
            <div
                class="bg-white/20 backdrop-blur-[12.5px] rounded-[44px] shadow-2xl mb-8 w-full p-8 border border-white/30">
                <div class="text-center mb-6">
                    <h1 class="text-white text-2xl font-bold mb-2">Boas-vindas de volta!</h1>
                    <p class="text-white/90 text-lg">
                        Teste gratuitamente entrando com a sua conta Google<br>para começar a aproveitar nossa
                        plataforma.
                    </p>
                </div>

                <!-- Google Login Button -->
                <div class="mb-6">
                    <a :href="route('google.redirect')"
                        class="w-full h-[60px] flex items-center justify-center gap-4 rounded-[44px] font-medium transition-all duration-200 bg-white/90 backdrop-blur-[12.5px] text-gray-900 border border-white/30 hover:bg-white hover:scale-105 hover:shadow-lg">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M18.1712 8.36791H17.9998V8.33325H9.99984V11.6666H14.7098C14.0223 13.6074 12.1762 14.9999 9.99984 14.9999C7.23859 14.9999 4.99984 12.7612 4.99984 9.99992C4.99984 7.23867 7.23859 4.99992 9.99984 4.99992C11.2748 4.99992 12.434 5.48075 13.3173 6.26625L15.674 3.90959C14.1857 2.52125 12.1948 1.66658 9.99984 1.66658C5.39734 1.66658 1.6665 5.39741 1.6665 9.99992C1.6665 14.6024 5.39734 18.3333 9.99984 18.3333C14.6023 18.3333 18.3332 14.6024 18.3332 9.99992C18.3332 9.44125 18.2757 8.89575 18.1712 8.36791Z"
                                fill="#FFC107" />
                            <path
                                d="M2.62744 6.12125L5.36536 8.12917C6.10619 6.29417 7.90036 4.99992 9.99994 4.99992C11.2749 4.99992 12.4341 5.48075 13.3174 6.26625L15.6741 3.90959C14.1858 2.52125 12.1949 1.66658 9.99994 1.66658C6.79911 1.66658 4.02327 3.47375 2.62744 6.12125Z"
                                fill="#FF3D00" />
                            <path
                                d="M10 18.3333C12.1525 18.3333 14.1084 17.5096 15.5871 16.17L13.0079 13.9875C12.1432 14.6452 11.0865 15.0009 10 15C7.83253 15 5.99211 13.6179 5.29878 11.6892L2.5813 13.7829C3.96047 16.4817 6.7613 18.3333 10 18.3333Z"
                                fill="#4CAF50" />
                            <path
                                d="M18.1712 8.36791H17.9998V8.33325H9.99984V11.6666H14.7098C14.3807 12.5902 13.7589 13.3971 12.9407 13.9871L12.9398 13.9862L15.519 16.1687C15.3657 16.3062 18.3332 14.1666 18.3332 9.99992C18.3332 9.44125 18.2757 8.89575 18.1712 8.36791Z"
                                fill="#1976D2" />
                        </svg>
                        <span class="text-base font-semibold">Continuar com Google</span>
                    </a>
                </div>

                <!-- Terms Notice -->
                <div class="text-center">
                    <p class="text-white/70 text-xs">
                        Criando uma conta, você concorda com todos os nossos<br>termos e condições.
                    </p>
                </div>
            </div>

            <!-- Help Link -->
            <div class="text-center">
                <Link :href="route('password.request')"
                    class="text-sm text-gray-200 hover:text-gray-300 transition-colors duration-200">
                Obter ajuda para fazer <span class="underline text-white font-semibold">login</span>
                </Link>
            </div>

            <!-- Status Message -->
            <div v-if="status" class="mt-4 bg-green-50 border border-green-200 rounded-2xl p-4 text-green-700 text-sm">
                {{ status }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import NavGlass from '@/Components/Nav/NavGlass.vue'

defineProps<{
    status?: string
}>()
</script>