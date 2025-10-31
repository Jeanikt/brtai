<template>
    <div class="min-h-screen bg-gray-50 font-prompt">
        <!-- Nav with responsive design matching Figma -->
        <nav class="fixed top-0 left-0 right-0 bg-white shadow-sm z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <Link :href="route('dashboard')" class="flex items-center">
                        <ApplicationLogo fill="black" />
                    </Link>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center gap-4">
                        <span class="text-sm font-medium text-gray-900">
                            {{ $page.props.auth.user.name }}
                        </span>

                        <Link :href="route('dashboard')"
                            class="bg-[#FFFF00] text-black px-6 py-2 rounded-full text-sm font-semibold hover:bg-[#FFFF33] transition-colors">
                        Meus eventos
                        </Link>

                        <!-- Desktop Menu Button -->
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-900 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                                <path
                                    :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile Navigation -->
                    <div class="flex md:hidden items-center gap-3">
                        <Link :href="route('dashboard')"
                            class="bg-[#FFFF00] text-black px-4 py-2 rounded-full text-sm font-semibold hover:bg-[#FFFF33] transition-colors">
                        Meus eventos
                        </Link>

                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-900 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                                <path
                                    :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dropdown Menu -->
            <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                class="border-t border-gray-200 md:absolute md:right-4 md:top-16 md:w-64 md:bg-white md:rounded-2xl md:shadow-lg md:border md:border-gray-200">
                <div class="space-y-1 px-4 py-3">
                    <Link :href="route('dashboard')"
                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-100">
                    Dashboard
                    </Link>
                    <Link :href="route('dashboard')"
                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-100">
                    Meus Eventos
                    </Link>
                    <Link :href="route('profile.edit')"
                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-100">
                    Perfil
                    </Link>
                    <Link :href="route('logout')" method="post" as="button"
                        class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-gray-100">
                    Sair
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Main content with responsive padding -->
        <main class="pt-20 pb-8">
            <div class="mx-auto max-w-[430px] md:max-w-2xl lg:max-w-4xl px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const showingNavigationDropdown = ref(false)
</script>
