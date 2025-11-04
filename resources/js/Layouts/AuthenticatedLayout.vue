<template>
    <div class="min-h-screen bg-gray-100 font-prompt">
        <NotificationContainer ref="notificationContainer" />
        <LoadingSpinner :show="globalLoading.isLoading.value" :message="globalLoading.loadingMessage.value" />

        <!-- NAVBAR -->
        <nav class="fixed top-0 left-0 right-0 bg-white shadow-sm z-40">
            <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-10">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <Link :href="route('dashboard')" class="flex items-center">
                    <ApplicationLogo fill="green" class="w-10 h-10 sm:w-20 sm:h-20" />
                    </Link>

                    <!-- DESKTOP -->
                    <div class="hidden md:flex items-center gap-6">
                        <!-- Nome -->
                        <span class="text-sm font-medium text-gray-800">
                            {{ $page.props.auth.user.name }}
                        </span>

                        <!-- Criar evento -->
                        <Link :href="route('dashboard')"
                            class="px-5 py-2 rounded-full text-sm font-semibold text-black bg-gradient-to-r from-yellow-300 to-yellow-400 hover:from-yellow-400 hover:to-yellow-500 transition-all shadow-md hover:shadow-lg">
                        Criar evento
                        </Link>

                        <!-- Meu histórico -->
                        <Link :href="route('user.history')"
                            class="px-5 py-2 rounded-full text-sm font-semibold bg-white border border-gray-300 text-gray-800 hover:bg-gray-50 hover:shadow-md transition-all">
                        Meu histórico
                        </Link>

                        <!-- Eventos públicos -->
                        <Link :href="route('events.public.index')"
                            class="px-5 py-2 rounded-full text-sm font-semibold bg-white border border-gray-300 text-gray-800 hover:bg-gray-50 hover:shadow-md transition-all">
                        Eventos públicos
                        </Link>

                        <!-- Roadmap -->
                        <Link :href="route('roadmap.index')"
                            class="px-5 py-2 rounded-full text-sm font-semibold bg-white border border-gray-300 text-gray-800 hover:bg-gray-50 hover:shadow-md transition-all">
                        Roadmap
                        </Link>

                        <!-- Dropdown -->
                        <div class="relative">
                            <button @click="toggleMenu"
                                class="p-2 rounded-full text-gray-700 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path v-if="!showingNavigationDropdown" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <transition enter-active-class="transition duration-200 ease-out"
                                enter-from-class="transform opacity-0 -translate-y-2"
                                enter-to-class="transform opacity-100 translate-y-0"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="transform opacity-100 translate-y-0"
                                leave-to-class="transform opacity-0 -translate-y-2">
                                <div v-if="showingNavigationDropdown"
                                    class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-lg border border-gray-200 py-2 z-50">
                                    <Link :href="route('dashboard')"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</Link>
                                    <Link :href="route('user.history')"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Meu Histórico
                                    </Link>
                                    <Link :href="route('events.public.index')"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Eventos públicos
                                    </Link>
                                    <Link :href="route('roadmap.index')"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Roadmap</Link>
                                    <Link :href="route('settings.index')"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</Link>
                                    <Link :href="route('logout')" method="post" as="button"
                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    Sair</Link>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <!-- MOBILE -->
                    <div class="flex md:hidden items-center gap-2">
                        <Link :href="route('dashboard')"
                            class="bg-gradient-to-r from-yellow-300 to-yellow-400 text-black px-4 py-2 rounded-full text-sm font-semibold hover:from-yellow-400 hover:to-yellow-500 transition-all shadow-sm">
                        Criar
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
        </nav>

        <!-- CONTEÚDO -->
        <main class="pt-20 pb-8">
            <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, provide } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import NotificationContainer from '@/Components/NotificationContainer.vue'
import LoadingSpinner from '@/Components/LoadingSpinner.vue'
import { useNotifications } from '@/Composables/useNotifications'
import { useLoading } from '@/Composables/useLoading'
import axios from 'axios'

interface NotificationContainerInstance {
    addNotification: (notification: any) => void
    removeNotification: (id: number) => void
}

const showingNavigationDropdown = ref(false)
const notificationContainer = ref<NotificationContainerInstance | null>(null)
const { setContainer, success, error, warning, info } = useNotifications()
const globalLoading = useLoading()

const toggleMenu = () => {
    showingNavigationDropdown.value = !showingNavigationDropdown.value
}

onMounted(async () => {
    try {
        if (notificationContainer.value) setContainer(notificationContainer.value)

        const page = usePage()
        const flash = (page.props as any).flash

        if (flash?.success) success(flash.success)
        if (flash?.error) error(flash.error)
        if (flash?.warning) warning(flash.warning)
        if (flash?.info) info(flash.info)

        if ((window as any).Laravel?.page?.props?.auth?.user) {
            try {
                const getLocation = () =>
                    new Promise<{ latitude: number; longitude: number } | null>((resolve) => {
                        if (!navigator.geolocation) return resolve(null)
                        navigator.geolocation.getCurrentPosition(
                            (pos) => resolve({ latitude: pos.coords.latitude, longitude: pos.coords.longitude }),
                            () => resolve(null),
                            { enableHighAccuracy: true, timeout: 5000 }
                        )
                    })

                const coords = await getLocation()
                await axios.post('/api/session/location', {
                    latitude: coords?.latitude,
                    longitude: coords?.longitude,
                    user_agent: navigator.userAgent
                })
            } catch (err) {
                console.warn('Falha ao enviar localização:', err)
            }
        }
    } catch (error) {
        console.error('Erro no mounted hook:', error)
    }
})

provide('notifications', { success, error, warning, info })
provide('globalLoading', globalLoading)
</script>
