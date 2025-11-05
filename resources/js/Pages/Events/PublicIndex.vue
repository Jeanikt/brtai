<template>
    <AuthenticatedLayout>
        <div class="min-h-screen font-prompt">
            <!-- Main Content -->
            <main class="py-2">
                <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-2">
                    <!-- Header Section -->
                    <div class="text-center mb-12">
                        <h1 class="text-4xl font-bold text-gray-900 mb-4">
                            Descubra Eventos Incríveis
                        </h1>
                        <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-6">
                            Encontre experiências únicas perto de você
                        </p>

                        <!-- Location Status -->
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm"
                            :class="hasLocation ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
                            <span v-if="hasLocation">📍</span>
                            <span v-else>📍</span>
                            {{ hasLocation ? 'Visualizando eventos por proximidade' : 'Ative a localização para ver eventos próximos' }}
                        </div>
                    </div>

                    <!-- Filters & Sorting -->
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-8">
                        <div class="flex flex-wrap gap-2">
                            <button @click="setFilter('all')" :class="getFilterClass('all')">
                                Todos
                            </button>
                            <button @click="setFilter('free')" :class="getFilterClass('free')">
                                🆓 Gratuitos
                            </button>
                            <button @click="setFilter('paid')" :class="getFilterClass('paid')">
                                💰 Pagos
                            </button>
                            <button @click="setFilter('available')" :class="getFilterClass('available')">
                                ✅ Com Vagas
                            </button>
                        </div>

                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <span>Ordenar por:</span>
                            <select v-model="sortBy" @change="applySorting"
                                class="border-0 bg-transparent font-semibold text-gray-900 focus:ring-0">
                                <option value="distance">📍 Mais Próximos</option>
                                <option value="date">📅 Próximas Datas</option>
                                <option value="participants">👥 Mais Populares</option>
                            </select>
                        </div>
                    </div>

                    <!-- Events Grid -->
                    <div v-if="events.data.length > 0"
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                        <div v-for="event in events.data" :key="event.id"
                            class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-300">

                            <!-- Event Image -->
                            <div class="h-48 relative overflow-hidden">
                                <img v-if="event.header_image_url" :src="event.header_image_url" :alt="event.name"
                                    class="w-full h-full object-cover transition-transform hover:scale-105 duration-300" />
                                <div v-else
                                    class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                    <span class="text-white text-lg font-semibold">🎉 {{ event.name }}</span>
                                </div>

                                <!-- Event Badges -->
                                <div class="absolute top-3 left-3 flex flex-col gap-1">
                                    <span v-if="event.is_free"
                                        class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                        🆓 Grátis
                                    </span>
                                    <span v-else
                                        class="bg-blue-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                        💰 Pago
                                    </span>
                                </div>

                                <div class="absolute top-3 right-3 flex flex-col gap-1">
                                    <span v-if="event.distance !== null && event.distance !== undefined"
                                        class="bg-black text-white px-2 py-1 rounded-full text-xs font-semibold">
                                        {{ event.distance?.toFixed(1) }} km
                                    </span>
                                    <span v-if="isEventSoldOut(event)"
                                        class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                        Esgotado
                                    </span>
                                    <span v-else-if="isEventAlmostSoldOut(event)"
                                        class="bg-amber-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                        Últimas vagas
                                    </span>
                                </div>
                            </div>

                            <!-- Event Content -->
                            <div class="p-5">
                                <!-- Event Title & Description -->
                                <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 leading-tight">
                                    {{ event.name }}
                                </h3>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed">
                                    {{ event.description || 'Uma experiência incrível te aguarda!' }}
                                </p>

                                <!-- Event Details -->
                                <div class="space-y-3 mb-4">
                                    <div class="flex items-center gap-3 text-sm">
                                        <div
                                            class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <span class="text-gray-600">📅</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ formatDate(event.event_date) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 text-sm">
                                        <div
                                            class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <span class="text-gray-600">📍</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ event.location }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 text-sm">
                                        <div
                                            class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <span class="text-gray-600">👥</span>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900">{{ event.confirmed_count }}
                                                confirmados</p>
                                            <p class="text-xs text-gray-500">
                                                {{ getAvailabilityText(event) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Price Tiers -->
                                <div class="mb-4 space-y-2">
                                    <div v-for="tier in event.price_tiers" :key="tier.id"
                                        class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ tier.name }}</p>
                                            <p v-if="tier.description" class="text-xs text-gray-500">{{ tier.description
                                                }}</p>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">
                                            {{ formatPrice(tier.price) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <Link :href="route('events.public.show', event.slug)"
                                    class="w-full bg-black text-white text-center py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors block">
                                {{ getButtonText(event) }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-16">
                        <div class="text-gray-400 text-6xl mb-4">🎭</div>
                        <h3 class="text-2xl font-semibold text-gray-600 mb-2">
                            Nenhum evento encontrado
                        </h3>
                        <p class="text-gray-500 max-w-md mx-auto mb-6">
                            {{
                                currentFilter === 'all'
                                    ? 'Não há eventos públicos disponíveis no momento.'
                                    : `Não há eventos ${getFilterLabel()} disponíveis.`
                            }}
                        </p>
                        <Link v-if="$page.props.auth.user" :href="route('events.create')"
                            class="bg-black text-white px-6 py-3 rounded-full font-semibold hover:bg-gray-800 transition-colors inline-flex items-center gap-2">
                        <span>+</span>
                        Criar Primeiro Evento
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="events.data.length > 0" class="mt-12 flex justify-center">
                        <Pagination :links="events.links" />
                    </div>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Pagination from '@/Components/Pagination.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps<{
    events: {
        data: any[]
        links: any[]
    }
    hasLocation: boolean
}>()

const sortBy = ref('distance')
const currentFilter = ref('all')

// Computed properties for filtering
const filteredEvents = computed(() => {
    let filtered = [...props.events.data]

    switch (currentFilter.value) {
        case 'free':
            filtered = filtered.filter(event => event.is_free)
            break
        case 'paid':
            filtered = filtered.filter(event => !event.is_free)
            break
        case 'available':
            filtered = filtered.filter(event => !isEventSoldOut(event))
            break
    }

    return filtered
})

// Event availability helpers
const isEventSoldOut = (event: any) => {
    if (event.max_participants && event.confirmed_count >= event.max_participants) {
        return true
    }

    // Check if all active price tiers are sold out
    const activeTiers = event.price_tiers.filter((tier: any) => tier.is_active)
    if (activeTiers.length === 0) return true

    return activeTiers.every((tier: any) =>
        tier.max_quantity && tier.current_quantity >= tier.max_quantity
    )
}

const isEventAlmostSoldOut = (event: any) => {
    if (!event.max_participants || isEventSoldOut(event)) return false

    const remaining = event.max_participants - event.confirmed_count
    return remaining > 0 && remaining <= 10
}

const getAvailabilityText = (event: any) => {
    if (isEventSoldOut(event)) {
        return 'Evento esgotado'
    }

    if (event.max_participants) {
        const remaining = event.max_participants - event.confirmed_count
        if (remaining <= 10) {
            return `Apenas ${remaining} vaga${remaining > 1 ? 's' : ''} restante${remaining > 1 ? 's' : ''}`
        }
        return `${remaining} vagas disponíveis`
    }

    return 'Vagas ilimitadas'
}

const getButtonText = (event: any) => {
    if (isEventSoldOut(event)) {
        return 'Esgotado'
    }
    return 'Ver Detalhes e Participar'
}

const getFilterClass = (filter: string) => {
    const baseClasses = 'px-4 py-2 rounded-full text-sm font-semibold transition-colors'
    return currentFilter.value === filter
        ? `${baseClasses} bg-black text-white`
        : `${baseClasses} bg-white text-gray-700 border border-gray-300 hover:border-gray-400`
}

const getFilterLabel = () => {
    const labels: any = {
        all: 'todos',
        free: 'gratuitos',
        paid: 'pagos',
        available: 'com vagas disponíveis'
    }
    return labels[currentFilter.value] || 'todos'
}

// Methods
const setFilter = (filter: string) => {
    currentFilter.value = filter
}

const applySorting = () => {
    router.get(route('events.public.index'), { sort: sortBy.value }, {
        preserveState: true,
        replace: true,
    })
}

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
        weekday: 'short',
        day: '2-digit',
        month: 'short',
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
}
</style>
