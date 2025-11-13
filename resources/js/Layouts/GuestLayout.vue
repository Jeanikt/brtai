<template>
    <div class="min-h-screen bg-gray-50 font-prompt">
        <!-- Navigation Bar -->
        <nav class="fixed top-0 left-0 right-0 bg-white/95 backdrop-blur-md shadow-sm border-b border-gray-200/60 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center group">
                    <ApplicationLogo fill="#00A859" class="w-12 h-12 transition-transform group-hover:scale-105" />
                    </Link>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center space-x-1">
                        <!-- Search Bar -->
                        <div class="relative mx-4">
                            <div
                                class="flex items-center bg-gray-100/80 rounded-full px-4 py-2 w-64 transition-all duration-200 focus-within:bg-white focus-within:ring-2 focus-within:ring-[#00A859]/20 focus-within:shadow-sm">
                                <svg class="w-4 h-4 text-gray-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" placeholder="Buscar eventos..."
                                    class="bg-transparent border-0 text-sm text-gray-700 placeholder-gray-500 focus:outline-none focus:ring-0 w-full">
                            </div>
                        </div>

                        <!-- Navigation Links -->
                        <div class="flex items-center space-x-1">
                            <Link href="#" class="nav-link group">
                            <span class="nav-link-text">Todos Lugares</span>
                            </Link>
                            <Link :href="route('events.public.index')" class="nav-link group">
                            <span class="nav-link-text">Eventos</span>
                            </Link>
                            <Link :href="route('register')" class="nav-link group">
                            <span class="nav-link-text">Criar Evento</span>
                            </Link>
                            <Link :href="route('login')" class="nav-link group">
                            <span class="nav-link-text">Meus Ingressos</span>
                            </Link>
                        </div>

                        <!-- Auth Buttons -->
                        <div class="flex items-center space-x-3 ml-4">
                            <Link :href="route('login')" class="auth-btn secondary">
                            Entrar
                            </Link>
                            <Link :href="route('register')" class="auth-btn primary">
                            Criar Conta
                            </Link>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="flex md:hidden items-center space-x-2">
                        <Link :href="route('login')"
                            class="text-sm text-gray-700 hover:text-[#00A859] transition-colors px-3 py-2">
                        Entrar
                        </Link>
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="mobile-menu-btn">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!showingNavigationDropdown" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Navigation Menu -->
                <transition enter-active-class="transition duration-200 ease-out"
                    enter-from-class="transform opacity-0 -translate-y-2"
                    enter-to-class="transform opacity-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="transform opacity-100 translate-y-0"
                    leave-to-class="transform opacity-0 -translate-y-2">
                    <div v-if="showingNavigationDropdown" class="md:hidden border-t border-gray-200/60 py-4 space-y-3">
                        <!-- Mobile Search -->
                        <div class="relative">
                            <div
                                class="flex items-center bg-gray-100/80 rounded-full px-4 py-3 transition-all duration-200 focus-within:bg-white focus-within:ring-2 focus-within:ring-[#00A859]/20">
                                <svg class="w-4 h-4 text-gray-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" placeholder="Buscar eventos..."
                                    class="bg-transparent border-0 text-sm text-gray-700 placeholder-gray-500 focus:outline-none focus:ring-0 w-full">
                            </div>
                        </div>

                        <!-- Mobile Links -->
                        <div class="space-y-2">
                            <Link href="#" class="mobile-nav-link">
                            Todos Lugares
                            </Link>
                            <Link :href="route('events.public.index')" class="mobile-nav-link">
                            Eventos
                            </Link>
                            <Link :href="route('register')" class="mobile-nav-link">
                            Criar Evento
                            </Link>
                            <Link :href="route('login')" class="mobile-nav-link">
                            Meus Ingressos
                            </Link>
                        </div>

                        <!-- Mobile Auth Buttons -->
                        <div class="pt-4 border-t border-gray-200/60 space-y-2">
                            <Link :href="route('register')" class="w-full auth-btn primary text-center justify-center">
                            Criar Conta
                            </Link>
                        </div>
                    </div>
                </transition>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="pt-16 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <slot />
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200/60 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center mb-4 md:mb-0">
                        <ApplicationLogo fill="#00A859" class="w-16 h-16" />
                    </div>
                    <div class="text-sm text-gray-600">
                        &copy; {{ new Date().getFullYear() }} BrotaAI. Conectando pessoas através de experiências.
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'

const showingNavigationDropdown = ref(false)
</script>

<style scoped>
.nav-link {
    @apply px-4 py-2 rounded-full text-sm font-medium text-gray-700 transition-all duration-200 hover:text-[#00A859] hover:bg-gray-100/80 relative;
}

.nav-link-text {
    @apply relative z-10;
}

.nav-link::before {
    content: '';
    @apply absolute inset-0 rounded-full bg-gradient-to-r from-[#00A859]/10 to-[#00A859]/5 opacity-0 transition-opacity duration-200;
}

.nav-link:hover::before {
    @apply opacity-100;
}

.auth-btn {
    @apply px-4 py-2 rounded-full text-sm font-semibold transition-all duration-200 transform hover:scale-105;
}

.auth-btn.primary {
    @apply bg-gradient-to-r from-[#00A859] to-[#00A859]/90 text-white shadow-sm hover:shadow-md hover:from-[#00A859] hover:to-[#00A859];
}

.auth-btn.secondary {
    @apply text-gray-700 hover:text-[#00A859] hover:bg-gray-100/80;
}

.mobile-menu-btn {
    @apply p-2 rounded-full text-gray-700 hover:text-[#00A859] hover:bg-gray-100/80 transition-colors duration-200;
}

.mobile-nav-link {
    @apply block px-4 py-3 rounded-xl text-base font-medium text-gray-700 transition-all duration-200 hover:text-[#00A859] hover:bg-gray-100/80;
}
</style>