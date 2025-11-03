<template>
    <div class="min-h-screen bg-gray-100 font-prompt">
        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 bg-white shadow-sm z-50">
            <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-10">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <Link :href="route('dashboard')" class="flex items-center">
                    <ApplicationLogo fill="black" class="w-8 h-8 sm:w-10 sm:h-10" />
                    </Link>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center gap-5">
                        <span class="text-sm font-medium text-gray-900">
                            {{ $page.props.auth.user.name }}
                        </span>

                        <Link :href="route('dashboard')"
                            class="bg-[#FFFF00] text-black px-6 py-2 rounded-full text-sm font-semibold hover:bg-[#FFFF33] transition-colors">
                        Meus eventos
                        </Link>

                        <Link :href="route('roadmap.index')"
                            class="bg-[#82ef00] text-gray-900 border border-gray-200 px-6 py-2 rounded-full text-sm font-semibold hover:bg-gray-50 transition-colors">
                        Roadmap
                        </Link>

                        <button @click="toggleMenu"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-900 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path v-if="!showingNavigationDropdown" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile Navigation -->
                    <div class="flex md:hidden items-center gap-3">
                        <Link :href="route('dashboard')"
                            class="bg-[#FFFF00] text-black px-4 py-2 rounded-full text-sm font-semibold hover:bg-[#FFFF33] transition-colors">
                        Eventos
                        </Link>

                        <Link :href="route('roadmap.index')"
                            class="bg-white border border-gray-300 text-gray-900 px-4 py-2 rounded-full text-sm font-semibold hover:bg-gray-50 transition-colors">
                        Roadmap
                        </Link>

                        <button @click="toggleMenu"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-900 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path v-if="!showingNavigationDropdown" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dropdown Menu -->
            <transition enter-active-class="transition duration-200 ease-out"
                enter-from-class="transform opacity-0 -translate-y-2"
                enter-to-class="transform opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="transform opacity-100 translate-y-0"
                leave-to-class="transform opacity-0 -translate-y-2">
                <div v-if="showingNavigationDropdown"
                    class="border-t border-gray-200 md:absolute md:right-4 md:top-16 md:w-64 md:bg-white md:rounded-2xl md:shadow-lg md:border md:border-gray-200">
                    <div class="space-y-1 px-4 py-3">
                        <Link :href="route('dashboard')"
                            class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-100">
                        Dashboard
                        </Link>
                        <Link :href="route('roadmap.index')"
                            class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-100">
                        Roadmap
                        </Link>
                        <Link :href="route('settings.index')"
                            class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-100">
                        Perfil
                        </Link>
                        <Link :href="route('logout')" method="post" as="button"
                            class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-gray-100">
                        Sair
                        </Link>
                    </div>
                </div>
            </transition>
        </nav>

        <!-- Main content -->
        <main class="pt-20 pb-8">
            <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'

const showingNavigationDropdown = ref(false)
const toggleMenu = () => {
    showingNavigationDropdown.value = !showingNavigationDropdown.value
}
</script>
