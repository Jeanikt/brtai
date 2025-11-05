<template>

    <Head title="Meu Histórico" />

    <AuthenticatedLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Meu Histórico de Eventos
            </h1>
            <p class="text-gray-600">
                Todos os eventos que você participou e organizou
            </p>
        </div>

        <!-- Filtros -->
        <div class="mb-6 flex gap-2">
            <button @click="filter = 'all'" :class="filter === 'all'
                ? 'bg-black text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                class="px-4 py-2 rounded-full font-semibold text-sm transition-colors">
                Todos
            </button>
            <button @click="filter = 'participation'" :class="filter === 'participation'
                ? 'bg-black text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                class="px-4 py-2 rounded-full font-semibold text-sm transition-colors">
                Como Participante
            </button>
            <button @click="filter = 'organized'" :class="filter === 'organized'
                ? 'bg-black text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                class="px-4 py-2 rounded-full font-semibold text-sm transition-colors">
                Como Organizador
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div v-if="filteredParticipations.length === 0" class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">📊</div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">
                    Nenhum evento no histórico
                </h3>
                <p class="text-gray-500 mb-4">
                    {{ getEmptyMessage() }}
                </p>
                <Link :href="route('events.public.index')"
                    class="bg-[#FFFF00] text-black px-6 py-3 rounded-full font-semibold hover:bg-[#FFFF33] transition-colors">
                Explorar Eventos
                </Link>
            </div>

            <div v-else class="divide-y divide-gray-200">
                <div v-for="participation in filteredParticipations" :key="participation.id"
                    class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <!-- Cabeçalho com badge de tipo -->
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ participation.event_name }}
                                </h3>
                                <span v-if="participation.type === 'organized'"
                                    class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    🎯 Organizador
                                </span>
                                <span v-else
                                    class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    👤 Participante
                                </span>
                                <span v-if="participation.event_status === 'cancelled'"
                                    class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    ❌ Cancelado
                                </span>
                                <span v-else-if="participation.event_status === 'completed'"
                                    class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    ✅ Finalizado
                                </span>
                            </div>

                            <!-- Informações do evento -->
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-600 mb-4">
                                <div class="flex items-center">
                                    <span class="text-lg mr-2">📅</span>
                                    {{ formatDate(participation.event_date) }}
                                </div>
                                <div class="flex items-center">
                                    <span class="text-lg mr-2">📍</span>
                                    {{ participation.event_location }}
                                </div>
                                <div class="flex items-center">
                                    <span class="text-lg mr-2">👤</span>
                                    {{ participation.organizer_name }}
                                </div>
                                <div class="flex items-center">
                                    <span class="text-lg mr-2">🎫</span>
                                    {{ participation.ticket_type }}
                                </div>
                            </div>

                            <!-- Informações específicas para organizador -->
                            <div v-if="participation.type === 'organized'" class="bg-blue-50 rounded-xl p-4 mb-4">
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-blue-700">
                                            {{ participation.confirmed_participants_count || 0 }}
                                        </p>
                                        <p class="text-blue-600">Confirmados</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-green-700">
                                            {{ formatPrice(participation.total_revenue || 0) }}
                                        </p>
                                        <p class="text-green-600">Arrecadado</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-gray-700">
                                            {{ formatDate(participation.event_date) }}
                                        </p>
                                        <p class="text-gray-600">Data do Evento</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Rodapé -->
                            <div class="flex items-center justify-between mt-4">
                                <div class="flex items-center space-x-4">
                                    <span v-if="participation.type === 'participation'"
                                        class="text-sm font-semibold text-green-600">
                                        {{ formatPrice(participation.paid_amount) }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        {{ participation.type === 'organized' ? 'Criado em' : 'Confirmado em' }}
                                        {{ formatDateTime(participation.confirmed_at) }}
                                    </span>
                                </div>

                                <!-- Status do check-in (apenas para participantes) -->
                                <div v-if="participation.type === 'participation'">
                                    <div v-if="participation.checked_in_at"
                                        class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        ✅ Check-in realizado
                                    </div>
                                    <div v-else
                                        class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        ⏳ Aguardando check-in
                                    </div>
                                </div>
                                <div v-else-if="participation.type === 'organized'"
                                    class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    🎯 Você organizou este evento
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="filteredParticipations.length > 0" class="px-6 py-4 border-t border-gray-200">
                <Pagination :links="participations.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { ref, computed } from 'vue'

interface Participation {
    id: string
    event_name: string
    event_date: string
    event_location: string
    organizer_name: string
    ticket_type: string
    paid_amount: number
    confirmed_at: string
    checked_in_at: string | null
    type: 'participation' | 'organized'
    event_status: string
    confirmed_participants_count?: number
    total_revenue?: number
}

const props = defineProps<{
    participations: {
        data: Participation[]
        links: any[]
    }
}>()

const filter = ref<'all' | 'participation' | 'organized'>('all')

// Computed property para filtrar as participações
const filteredParticipations = computed(() => {
    if (filter.value === 'all') {
        return props.participations.data
    }
    return props.participations.data.filter(p => p.type === filter.value)
})

const getEmptyMessage = () => {
    switch (filter.value) {
        case 'participation':
            return 'Você ainda não participou de nenhum evento como participante.'
        case 'organized':
            return 'Você ainda não organizou nenhum evento.'
        default:
            return 'Você ainda não participou nem organizou nenhum evento.'
    }
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
}

const formatDateTime = (dateString: string) => {
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
