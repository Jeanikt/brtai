<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Back button and header matching Figma "Mais Informações" -->
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('dashboard')"
                    class="w-10 h-10 bg-black rounded-full flex items-center justify-center hover:bg-gray-800 transition-colors">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                </Link>
            </div>

            <div class="flex justify-between items-start">
                <h1 class="text-2xl font-bold text-gray-900">Mais Informações</h1>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">Restam {{ timeRemaining }}</span>

                    <!-- Botão de Publicar/Despublicar -->
                    <button v-if="event.status === 'draft'" @click="publishEvent"
                        class="bg-green-600 text-white px-4 py-2 rounded-full font-semibold text-sm hover:bg-green-700 transition-colors">
                        Publicar Evento
                    </button>
                    <button v-else-if="event.status === 'active'" @click="unpublishEvent"
                        class="bg-yellow-600 text-white px-4 py-2 rounded-full font-semibold text-sm hover:bg-yellow-700 transition-colors">
                        Despublicar Evento
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Event Name with URL -->
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <p class="text-sm text-gray-600 mb-2">{{ event.name }}</p>
                    <div class="flex items-center gap-2 bg-black text-white px-4 py-3 rounded-full">
                        <span class="text-sm flex-1 truncate">{{ eventUrl }}</span>
                        <button @click="copyUrl" class="flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                    <div v-if="event.header_image_url" class="mt-3 rounded-xl overflow-hidden">
                        <img :src="event.header_image_url" :alt="event.name" class="w-full h-32 object-cover" />
                        <p class="text-center text-sm text-gray-600 mt-2">
                            Status:
                            <span class="font-semibold" :class="statusClass">
                                {{ statusText }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Revenue Card -->
                <div class="bg-white rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-gray-900">Receita total</h3>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-3xl font-bold text-gray-900">R$ {{ stats.total_revenue }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ stats.pending_payments }} pagamentos pendentes</p>
                        </div>
                    </div>
                </div>

                <!-- Participants Card -->
                <div class="bg-white rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h-5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-gray-900">Total de Convidados</h3>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.confirmed_count }} / {{
                                event.max_participants }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ stats.pending_participants }} convidados estão
                                pendentes</p>
                        </div>
                    </div>
                </div>

                <!-- Participants List -->
                <div class="bg-white rounded-2xl p-5 shadow-sm">
                    <button @click="showParticipants = !showParticipants"
                        class="w-full flex items-center justify-between text-left">
                        <h3 class="font-semibold text-gray-900">Lista Completa de Convidados</h3>
                        <svg class="w-5 h-5 text-gray-400 transition-transform"
                            :class="{ 'rotate-180': showParticipants }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-if="showParticipants" class="mt-4 space-y-3">
                        <div v-for="participant in participants" :key="participant.id"
                            class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-600">
                                        {{ getInitials(participant.full_name) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-700 block">{{ participant.full_name }}</span>
                                    <span class="text-xs text-gray-500">{{ participant.email }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs px-2 py-1 rounded-full"
                                    :class="paymentStatusClass(participant.payment_status)">
                                    {{ paymentStatusText(participant.payment_status) }}
                                </span>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div v-if="participants.length === 0" class="text-center py-8 text-gray-500">
                            Nenhum convidado confirmado ainda
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    event: Object,
    participants: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total_revenue: '0,00',
            confirmed_count: 0,
            pending_payments: 0,
            pending_participants: 0
        })
    }
})

const showParticipants = ref(false)

const eventUrl = computed(() => {
    return `${window.location.origin}/e/${props.event.slug}`
})

const timeRemaining = computed(() => {
    if (!props.event.event_date) return 'Data não definida'

    const eventDate = new Date(props.event.event_date)
    const now = new Date()

    if (eventDate < now) return 'Evento finalizado'

    const diff = eventDate - now
    const days = Math.floor(diff / (1000 * 60 * 60 * 24))
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))

    if (days > 0) {
        return `${days}d ${hours}h`
    }
    return `${hours}h ${minutes}m`
})

const statusText = computed(() => {
    const statusMap = {
        'draft': 'Rascunho',
        'active': 'Ativo',
        'cancelled': 'Cancelado',
        'completed': 'Finalizado'
    }
    return statusMap[props.event.status] || 'Desconhecido'
})

const statusClass = computed(() => {
    const classMap = {
        'draft': 'text-yellow-600',
        'active': 'text-green-600',
        'cancelled': 'text-red-600',
        'completed': 'text-gray-600'
    }
    return classMap[props.event.status] || 'text-gray-600'
})

const copyUrl = () => {
    navigator.clipboard.writeText(eventUrl.value)
    alert('Link copiado para a área de transferência!')
}

const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2)
}

const paymentStatusText = (status) => {
    const statusMap = {
        'paid': 'Pago',
        'pending': 'Pendente',
        'failed': 'Falhou'
    }
    return statusMap[status] || status
}

const paymentStatusClass = (status) => {
    const classMap = {
        'paid': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'failed': 'bg-red-100 text-red-800'
    }
    return classMap[status] || 'bg-gray-100 text-gray-800'
}

// Funções para publicar/despublicar evento
const publishEvent = () => {
    if (confirm('Tem certeza que deseja publicar este evento?')) {
        router.patch(route('events.publish', props.event.id), {}, {
            onSuccess: () => {
                // Opcional: mostrar mensagem de sucesso
            }
        })
    }
}

const unpublishEvent = () => {
    if (confirm('Tem certeza que deseja despublicar este evento?')) {
        router.patch(route('events.unpublish', props.event.id), {}, {
            onSuccess: () => {
                // Opcional: mostrar mensagem de sucesso
            }
        })
    }
}
</script>
