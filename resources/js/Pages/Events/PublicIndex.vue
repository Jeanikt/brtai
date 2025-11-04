<template>
    <div class="min-h-screen bg-gray-100 font-prompt">
        <nav class="bg-white shadow-sm">
            <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-10">
                <div class="flex justify-between items-center h-16">
                    <Link :href="route('events.public.index')" class="flex items-center">
                    <ApplicationLogo fill="black" class="w-10 h-10 sm:w-10 sm:h-10" />
                    </Link>

                    <div class="flex items-center gap-4">
                        <button @click="requestLocation"
                            class="bg-[#82ef00] text-gray-900 px-4 py-2 rounded-full text-sm font-semibold hover:bg-[#72df00] transition-colors">
                            📍 Atualizar Localização
                        </button>

                        <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                            class="bg-[#FFFF00] text-black px-4 py-2 rounded-full text-sm font-semibold hover:bg-[#FFFF33] transition-colors">
                        Meus Eventos
                        </Link>

                        <Link v-else :href="route('login')"
                            class="bg-[#FFFF00] text-black px-4 py-2 rounded-full text-sm font-semibold hover:bg-[#FFFF33] transition-colors">
                        Entrar
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <main class="pt-8 pb-8">
            <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">
                        Eventos Públicos
                    </h1>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Descubra eventos incríveis perto de você.
                        <span v-if="hasLocation" class="text-green-600 font-semibold">
                            Ordenados por proximidade
                        </span>
                        <span v-else class="text-yellow-600 font-semibold">
                            Ative a localização para ver eventos próximos
                        </span>
                    </p>
                </div>

                <div class="flex flex-wrap gap-4 mb-8 justify-center">
                    <button @click="sortBy = 'distance'" :class="[
                        'px-6 py-2 rounded-full text-sm font-semibold transition-colors',
                        sortBy === 'distance'
                            ? 'bg-[#82ef00] text-gray-900'
                            : 'bg-white text-gray-700 border border-gray-300'
                    ]">
                        📍 Mais Próximos
                    </button>
                    <button @click="sortBy = 'date'" :class="[
                        'px-6 py-2 rounded-full text-sm font-semibold transition-colors',
                        sortBy === 'date'
                            ? 'bg-[#FFFF00] text-black'
                            : 'bg-white text-gray-700 border border-gray-300'
                    ]">
                        📅 Próximas Datas
                    </button>
                    <button @click="sortBy = 'participants'" :class="[
                        'px-6 py-2 rounded-full text-sm font-semibold transition-colors',
                        sortBy === 'participants'
                            ? 'bg-blue-500 text-white'
                            : 'bg-white text-gray-700 border border-gray-300'
                    ]">
                        👥 Mais Populares
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div v-for="event in events.data" :key="event.id"
                        class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <div class="h-48 bg-gray-200 relative">
                            <img v-if="event.header_image_url" :src="event.header_image_url" :alt="event.name"
                                class="w-full h-full object-cover" />
                            <div v-else
                                class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                <span class="text-white text-lg font-semibold">🎉 Evento</span>
                            </div>

                            <div v-if="event.distance !== null && event.distance !== undefined"
                                class="absolute top-3 right-3 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                {{ event.distance?.toFixed(1) }} km
                            </div>
                        </div>

                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 line-clamp-2">
                                {{ event.name }}
                            </h3>

                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {{ event.description }}
                            </p>

                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <span class="text-lg mr-2">📅</span>
                                    {{ formatDate(event.event_date) }}
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <span class="text-lg mr-2">📍</span>
                                    {{ event.location }}
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <span class="text-lg mr-2">👥</span>
                                    {{ event.confirmed_count }} confirmados
                                </div>
                            </div>

                            <div class="mb-4">
                                <div v-for="tier in event.price_tiers" :key="tier.id"
                                    class="flex justify-between items-center py-1">
                                    <span class="text-sm text-gray-700">{{ tier.name }}</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ formatPrice(tier.price) }}
                                    </span>
                                </div>
                            </div>

                            <Link :href="route('events.public.show', event.slug)"
                                class="w-full bg-[#FFFF00] text-black text-center py-3 rounded-full font-semibold hover:bg-[#FFFF33] transition-colors block">
                            Ver Detalhes
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-center">
                    <Pagination :links="events.links" />
                </div>

                <div v-if="events.data.length === 0" class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">🎭</div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">
                        Nenhum evento público encontrado
                    </h3>
                    <p class="text-gray-500">
                        Não há eventos públicos disponíveis no momento.
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps<{
    events: {
        data: any[]
        links: any[]
    }
    hasLocation: boolean
}>()

const sortBy = ref('distance')

const requestLocation = () => {
    if (!navigator.geolocation) {
        alert('Geolocalização não é suportada pelo seu navegador')
        return
    }

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            try {
                await router.post('/events-public/location', {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                })

                router.reload()
            } catch (error) {
                console.error('Erro ao salvar localização:', error)
                alert('Erro ao salvar localização')
            }
        },
        (error) => {
            console.warn('Erro ao obter localização:', error)
            alert('Não foi possível obter sua localização. Verifique as permissões do navegador.')
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 600000
        }
    )
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatPrice = (price: number) => {
    if (price === 0) return 'Grátis'
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(price)
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-clamp: 2;
}
</style>
