<template>
    <component :is="layout">
        <div class="flex justify-center mb-8 mt-4">
            <div class="relative w-full max-w-2xl">
                <div
                    class="flex items-center bg-white rounded-full shadow-sm border border-gray-300 px-4 py-3 transition-all duration-200 focus-within:border-[#00A859] focus-within:ring-2 focus-within:ring-[#00A859]/20">
                    <svg class="w-5 h-5 text-gray-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input v-model="searchQuery" @input="handleSearchInput" type="text"
                        placeholder="Buscar eventos por nome, local, descrição..."
                        class="bg-transparent border-0 text-gray-700 placeholder-gray-500 focus:outline-none focus:ring-0 w-full">
                    <button v-if="searchQuery" @click="clearSearch"
                        class="ml-2 p-1 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="text-center mb-12">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                    Descubra experiências
                    <span class="text-[#00A859]">únicas</span> perto de você
                </h1>
                <p class="text-lg sm:text-xl text-gray-600 mb-8 leading-relaxed">
                    Encontre eventos incríveis, desde encontros casuais até experiências memoráveis.
                    Tudo em um só lugar.
                </p>
            </div>
        </div>

        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 p-6 bg-white rounded-2xl shadow-sm border border-gray-200/60">
            <div class="flex flex-wrap gap-2">
                <button @click="setFilter('all')" :class="getFilterClass('all')">
                    🌟 Todos
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

            <div class="flex items-center gap-3 text-sm">
                <span class="text-gray-600 font-medium">Ordenar por:</span>
                <select v-model="sortBy" @change="applySorting"
                    class="border-0 bg-gray-100 rounded-full px-4 py-2 text-gray-900 font-semibold focus:ring-2 focus:ring-[#00A859]/20 focus:outline-none transition-all">
                    <option value="distance">📍 Mais Próximos</option>
                    <option value="date">📅 Próximas Datas</option>
                    <option value="participants">👥 Mais Populares</option>
                </select>
            </div>
        </div>

        <div v-if="filteredEvents.length > 0"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            <div v-for="event in filteredEvents" :key="event.id"
                class="bg-white rounded-2xl shadow-sm border border-gray-200/60 overflow-hidden hover:shadow-lg transition-all duration-300 hover:translate-y-[-2px] group">

                <div class="h-48 relative overflow-hidden">
                    <img v-if="event.header_image_url" :src="event.header_image_url" :alt="event.name"
                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
                    <div v-else
                        class="w-full h-full bg-gradient-to-br from-[#00A859] to-[#00A859]/80 flex items-center justify-center">
                        <span class="text-white text-lg font-semibold">🎉 {{ event.name }}</span>
                    </div>

                    <div class="absolute top-3 left-3 flex flex-col gap-1">
                        <span v-if="event.is_free"
                            class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                            🆓 Grátis
                        </span>
                        <span v-else
                            class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                            💰 Pago
                        </span>
                    </div>

                    <div class="absolute top-3 right-3 flex flex-col gap-1">
                        <span v-if="event.distance !== null && event.distance !== undefined"
                            class="bg-gray-900/90 text-white px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-sm">
                            {{ event.distance?.toFixed(1) }} km
                        </span>
                        <span v-if="isEventSoldOut(event)"
                            class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                            Esgotado
                        </span>
                        <span v-else-if="isEventAlmostSoldOut(event)"
                            class="bg-amber-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                            Últimas vagas
                        </span>
                    </div>
                </div>

                <div class="p-5">
                    <h3
                        class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 leading-tight group-hover:text-[#00A859] transition-colors">
                        {{ event.name }}
                    </h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed">
                        {{ event.description || 'Uma experiência incrível te aguarda!' }}
                    </p>

                    <div class="space-y-3 mb-4">
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-gray-600">📅</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ formatDate(event.event_date) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-gray-600">📍</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ event.location }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-gray-600">👥</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">{{ event.confirmed_count }} confirmados</p>
                                <p class="text-xs text-gray-500">
                                    {{ getAvailabilityText(event) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 space-y-2">
                        <div v-for="tier in event.price_tiers" :key="tier.id"
                            class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg border border-gray-200/60">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ tier.name }}</p>
                                <p v-if="tier.description" class="text-xs text-gray-500">{{ tier.description }}</p>
                            </div>
                            <span class="text-sm font-bold text-gray-900">
                                {{ formatPrice(tier.price) }}
                            </span>
                        </div>
                    </div>

                    <Link :href="route('events.public.show', event.slug)"
                        class="w-full bg-gradient-to-r from-gray-900 to-gray-800 text-white text-center py-3 rounded-xl font-semibold hover:from-[#00A859] hover:to-[#00A859]/90 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-[1.02] block">
                    {{ getButtonText(event) }}
                    </Link>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-200/60">
            <div class="text-gray-400 text-6xl mb-4">🔍</div>
            <h3 class="text-2xl font-semibold text-gray-600 mb-2">
                {{ searchQuery ? 'Nenhum evento encontrado' : 'Nenhum evento encontrado' }}
            </h3>
            <p class="text-gray-500 max-w-md mx-auto mb-6">
                {{
                    searchQuery
                        ? `Não encontramos resultados para "${searchQuery}". Tente outros termos.`
                        : currentFilter === 'all'
                            ? 'Não há eventos públicos disponíveis no momento.'
                            : `Não há eventos ${getFilterLabel()} disponíveis.`
                }}
            </p>
            <button v-if="searchQuery" @click="clearSearch"
                class="bg-gradient-to-r from-[#00A859] to-[#00A859]/90 text-white px-6 py-3 rounded-full font-semibold hover:shadow-md transition-all duration-200 inline-flex items-center gap-2 hover:scale-105">
                Limpar busca
            </button>
            <Link v-else :href="route('register')"
                class="bg-gradient-to-r from-[#00A859] to-[#00A859]/90 text-white px-6 py-3 rounded-full font-semibold hover:shadow-md transition-all duration-200 inline-flex items-center gap-2 hover:scale-105">
            <span>+</span>
            Criar Conta para Organizar Eventos
            </Link>
        </div>

        <div v-if="events.data.length > 0" class="mt-12 flex justify-center">
            <Pagination :links="events.links" />
        </div>
    </component>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import axios from 'axios'

const page = usePage()

const props = defineProps<{
    events: {
        data: any[]
        links: any[]
    }
    hasLocation: boolean
    searchQuery?: string
    filters?: {
        sort?: string
    }
}>()

const layout = computed(() => {
    return page.props.auth.user ? AuthenticatedLayout : GuestLayout
})

const sortBy = ref(props.filters?.sort || 'distance')
const currentFilter = ref('all')
const locationRequested = ref(false)
const searchQuery = ref(props.searchQuery || '')
const searchTimeout = ref<number | null>(null)

onMounted(() => {
    if (!props.hasLocation && !locationRequested.value) {
        requestLocationAutomatically()
    }
})

const requestLocationAutomatically = async () => {
    if (!navigator.geolocation) {
        return
    }

    locationRequested.value = true

    try {
        const position = await new Promise<GeolocationPosition>((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 600000
            })
        })

        try {
            const response = await axios.post(route('events.public.storeLocation'), {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            })

            if (response.data.success) {
                router.reload({ only: ['events', 'hasLocation'] })
            }
        } catch (error) {
        }
    } catch (error) {
    }
}

const filteredEvents = computed(() => {
    let filtered = [...props.events.data]

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(event =>
            event.name.toLowerCase().includes(query) ||
            event.description?.toLowerCase().includes(query) ||
            event.location.toLowerCase().includes(query)
        )
    }

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

const handleSearchInput = () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value)
    }

    searchTimeout.value = setTimeout(() => {
        router.get(route('events.public.index'),
            {
                search: searchQuery.value,
                sort: sortBy.value
            },
            {
                preserveState: true,
                replace: true,
            }
        )
    }, 500)
}

const clearSearch = () => {
    searchQuery.value = ''
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value)
    }
    router.get(route('events.public.index'),
        {
            sort: sortBy.value
        },
        {
            preserveState: true,
            replace: true,
        }
    )
}

const isEventSoldOut = (event: any) => {
    if (event.max_participants && event.confirmed_count >= event.max_participants) {
        return true
    }

    const activeTiers = event.price_tiers?.filter((tier: any) => tier.is_active) || []
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
    const baseClasses = 'px-4 py-2 rounded-full text-sm font-semibold transition-all duration-200'
    return currentFilter.value === filter
        ? `${baseClasses} bg-[#00A859] text-white shadow-sm`
        : `${baseClasses} bg-white text-gray-700 border border-gray-300 hover:border-[#00A859]/50 hover:text-[#00A859]`
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

const setFilter = (filter: string) => {
    currentFilter.value = filter
}

const applySorting = () => {
    router.get(route('events.public.index'), { sort: sortBy.value }, {
        preserveState: true,
        replace: true,
    })
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
    line-clamp: 2;
}
</style>