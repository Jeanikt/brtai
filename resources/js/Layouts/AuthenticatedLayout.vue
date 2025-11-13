<template>
    <div class="min-h-screen bg-gray-100 font-prompt">
        <NotificationContainer ref="notificationContainer" />
        <LoadingSpinner :show="globalLoading.isLoading.value" :message="globalLoading.loadingMessage.value" />

        <nav class="fixed top-0 left-0 right-0 bg-white shadow-sm z-40">
            <div class="max-w-[1440px] mx-auto px-3 sm:px-4 lg:px-10">
                <div class="flex justify-between items-center h-14 sm:h-16">
                    <Link :href="route('dashboard')" class="flex items-center">
                        <ApplicationLogo fill="green" class="w-8 h-8 sm:w-10 sm:h-10 lg:w-20 lg:h-20" />
                    </Link>

                    <div class="hidden md:flex items-center gap-3 lg:gap-4">
                        <span class="text-sm font-medium text-gray-800 truncate max-w-[100px] lg:max-w-[120px] xl:max-w-none">
                            {{ $page.props.auth.user.name }}
                        </span>

                        <Link :href="route('earnings.index')"
                            class="flex items-center gap-2 px-3 lg:px-4 py-2 rounded-full text-xs lg:text-sm font-semibold bg-white border border-gray-300 text-gray-800 hover:bg-gray-50 hover:shadow-md transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Earnings
                        </Link>

                        <Link :href="route('events.create')"
                            class="px-3 lg:px-4 py-2 rounded-full text-xs lg:text-sm font-semibold text-black bg-gradient-to-r from-yellow-300 to-yellow-400 hover:from-yellow-400 hover:to-yellow-500 transition-all shadow-md hover:shadow-lg whitespace-nowrap">
                            Criar evento
                        </Link>

                        <Link :href="route('user.history')"
                            class="px-3 lg:px-4 py-2 rounded-full text-xs lg:text-sm font-semibold bg-white border border-gray-300 text-gray-800 hover:bg-gray-50 hover:shadow-md transition-all whitespace-nowrap">
                            Histórico
                        </Link>

                        <Link :href="route('events.public.index')"
                            class="px-3 lg:px-4 py-2 rounded-full text-xs lg:text-sm font-semibold bg-white border border-gray-300 text-gray-800 hover:bg-gray-50 hover:shadow-md transition-all whitespace-nowrap">
                            Eventos
                        </Link>

                        <Link :href="route('roadmap.index')"
                            class="px-3 lg:px-4 py-2 rounded-full text-xs lg:text-sm font-semibold bg-white border border-gray-300 text-gray-800 hover:bg-gray-50 hover:shadow-md transition-all whitespace-nowrap">
                            Roadmap
                        </Link>

                        <div class="relative">
                            <button @click="toggleMenu"
                                class="p-2 rounded-full text-gray-700 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                                <svg class="h-5 w-5 lg:h-6 lg:w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
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
                                    class="absolute right-0 mt-2 w-48 lg:w-56 rounded-xl bg-white shadow-lg border border-gray-200 py-2 z-50">
                                    <Link :href="route('dashboard')"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        Dashboard
                                    </Link>
                                    <Link :href="route('earnings.index')"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Earnings
                                    </Link>
                                    <Link :href="route('user.history')"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Meu Histórico
                                    </Link>
                                    <Link :href="route('events.public.index')"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                        </svg>
                                        Eventos públicos
                                    </Link>
                                    <Link :href="route('roadmap.index')"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        Roadmap
                                    </Link>
                                    <Link :href="route('settings.index')"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Perfil
                                    </Link>
                                    <Link :href="route('logout')" method="post" as="button"
                                        class="flex items-center gap-3 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Sair
                                    </Link>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <div class="flex md:hidden items-center gap-2">
                        <Link :href="route('events.create')"
                            class="bg-gradient-to-r from-yellow-300 to-yellow-400 text-black px-3 py-1.5 rounded-full text-xs font-semibold hover:from-yellow-400 hover:to-yellow-500 transition-all shadow-sm whitespace-nowrap">
                            Criar
                        </Link>

                        <button @click="toggleMenu"
                            class="inline-flex items-center justify-center p-1.5 rounded-md text-gray-900 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path v-if="!showingNavigationDropdown" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <transition enter-active-class="transition duration-200 ease-out"
                    enter-from-class="transform opacity-0 -translate-y-2"
                    enter-to-class="transform opacity-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="transform opacity-100 translate-y-0"
                    leave-to-class="transform opacity-0 -translate-y-2">
                    <div v-if="showingNavigationDropdown && $page.props.auth.user"
                        class="md:hidden bg-white border-t border-gray-200 py-3 space-y-1">
                        <div class="px-3 py-2 text-sm font-medium text-gray-800 border-b border-gray-100">
                            {{ $page.props.auth.user.name }}
                        </div>

                        <Link :href="route('dashboard')"
                            class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </Link>
                        <Link :href="route('earnings.index')"
                            class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Earnings
                        </Link>
                        <Link :href="route('user.history')"
                            class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Meu Histórico
                        </Link>
                        <Link :href="route('events.public.index')"
                            class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            Eventos públicos
                        </Link>
                        <Link :href="route('roadmap.index')"
                            class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Roadmap
                        </Link>
                        <Link :href="route('settings.index')"
                            class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Perfil
                        </Link>
                        <Link :href="route('logout')" method="post" as="button"
                            class="flex items-center gap-3 w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-gray-100 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Sair
                        </Link>
                    </div>
                </transition>
            </div>
        </nav>

        <main class="pt-14 sm:pt-16 pb-6 sm:pb-8">
            <div class="mx-auto max-w-[1440px] px-3 sm:px-4 lg:px-10">
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
