<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <Link :href="route('dashboard')"
                            class="group w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center hover:shadow-md transition-all duration-300">
                        <svg class="w-5 h-5 text-gray-600 group-hover:text-black transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        </Link>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Detalhes do Evento</h1>
                            <p class="text-xs text-gray-600 mt-1 truncate max-w-[200px]">{{ event?.name ||
                                'Carregando...' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-end gap-2">
                            <p class="text-xs text-gray-600">Status:</p>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                :class="statusClass">
                                {{ statusText }}
                            </span>
                            <span v-if="event?.is_public"
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Público
                            </span>
                            <span v-else
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Privado
                            </span>
                        </div>

                        <div class="flex gap-2">
                            <button v-if="event?.status === 'draft'" @click="publishEvent"
                                class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-2 rounded-xl font-semibold hover:shadow-lg transition-all duration-300 text-sm">
                                Publicar
                            </button>
                            <button v-else-if="event?.status === 'active'" @click="unpublishEvent"
                                class="bg-gradient-to-r from-amber-500 to-orange-600 text-white px-4 py-2 rounded-xl font-semibold hover:shadow-lg transition-all duration-300 text-sm">
                                Despublicar
                            </button>

                            <Link :href="route('events.edit', event?.id)"
                                class="bg-white text-gray-700 px-4 py-2 rounded-xl font-semibold border border-gray-200 hover:border-gray-300 hover:shadow-lg transition-all duration-300 text-sm">
                            Editar
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="$page.props.flash?.success"
                    class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-xl">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error"
                    class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-xl">
                    {{ $page.props.flash.error }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white rounded-2xl shadow-sm p-4">
                            <h3 class="text-base font-semibold text-gray-900 mb-3">Link do Evento</h3>
                            <div class="flex gap-2">
                                <input :value="eventUrl" readonly
                                    class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-600 text-xs font-medium" />
                                <button @click="copyUrl"
                                    class="bg-black text-white px-4 py-2 rounded-xl font-semibold hover:bg-gray-800 transition-colors flex items-center gap-2 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Copiar
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-2xl p-4 shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="w-8 h-8 bg-green-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-green-600 text-xs font-semibold">+{{ growthRate }}%</span>
                                </div>
                                <h3 class="text-gray-600 text-xs font-medium mb-1">Receita Total</h3>
                                <p class="text-xl font-bold text-gray-900 mb-1">R$ {{ stats?.total_revenue || '0,00' }}
                                </p>
                                <p class="text-xs text-gray-500">{{ stats?.pending_payments || 0 }} pendentes</p>
                            </div>

                            <div class="bg-gradient-to-br from-blue-50 to-cyan-100 rounded-2xl p-4 shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="w-8 h-8 bg-blue-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-blue-600 text-xs font-semibold">{{ occupancyRate }}%</span>
                                </div>
                                <h3 class="text-gray-600 text-xs font-medium mb-1">Convidados</h3>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ stats?.confirmed_count || 0 }} / {{
                                    event?.max_participants || '∞' }}</p>
                                <p class="text-xs text-gray-500">{{ stats?.pending_participants || 0 }} aguardando</p>
                            </div>

                            <div class="bg-gradient-to-br from-purple-50 to-violet-100 rounded-2xl p-4 shadow-sm">
                                <div class="w-8 h-8 bg-purple-500 rounded-xl flex items-center justify-center mb-3">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <h3 class="text-gray-600 text-xs font-medium mb-1">Taxa de Conversão</h3>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ conversionRate }}%</p>
                                <p class="text-xs text-gray-500">Eficiência</p>
                            </div>

                            <div class="bg-gradient-to-br from-orange-50 to-amber-100 rounded-2xl p-4 shadow-sm">
                                <div class="w-8 h-8 bg-orange-500 rounded-xl flex items-center justify-center mb-3">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-gray-600 text-xs font-medium mb-1">Tempo Restante</h3>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ timeRemaining }}</p>
                                <p class="text-xs text-gray-500">Para início</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Lista de Convidados</h3>
                                <button @click="exportParticipants"
                                    class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-xl font-medium hover:bg-gray-200 transition-colors flex items-center gap-2 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Exportar
                                </button>
                            </div>

                            <div class="space-y-2">
                                <div v-for="participant in participants" :key="participant.id"
                                    class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:border-gray-200 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-semibold text-xs">
                                            {{ getInitials(participant.full_name) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-semibold text-gray-900 text-sm truncate">{{
                                                participant.full_name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ participant.email }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-medium text-gray-900">
                                            R$ {{ participant.payment_amount || '0.00' }}
                                        </span>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium"
                                            :class="paymentStatusClass(participant.payment_status)">
                                            {{ paymentStatusText(participant.payment_status) }}
                                        </span>
                                    </div>
                                </div>

                                <div v-if="participants.length === 0" class="text-center py-8">
                                    <div
                                        class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 text-sm">Nenhum convidado confirmado ainda</p>
                                    <p class="text-gray-400 text-xs mt-1">Compartilhe o link do evento</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                            <img v-if="event?.header_image_url" :src="event.header_image_url" :alt="event?.name"
                                class="w-full h-32 object-cover" />
                            <div v-else
                                class="w-full h-32 bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>

                            <div class="p-4">
                                <h3 class="text-base font-semibold text-gray-900 mb-3">Informações</h3>

                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs text-gray-600">Data e Hora</p>
                                            <p class="font-medium text-gray-900 text-sm truncate">{{
                                                formatEventDate(event?.event_date) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs text-gray-600">Local</p>
                                            <p class="font-medium text-gray-900 text-sm truncate">{{ event?.location ||
                                                'Não definido' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs text-gray-600">Tipo</p>
                                            <p class="font-medium text-gray-900 text-sm">{{ event?.is_free ? 'Gratuito'
                                                : 'Pago' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm p-4">
                            <h3 class="text-base font-semibold text-gray-900 mb-3">Ações Rápidas</h3>

                            <div class="space-y-2">
                                <button @click="shareEvent"
                                    class="w-full flex items-center gap-2 p-3 rounded-xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-300 text-sm">
                                    <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-gray-900">Compartilhar</span>
                                </button>

                                <Link :href="route('events.analytics', event?.id)"
                                    class="w-full flex items-center gap-2 p-3 rounded-xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-300 text-sm">
                                <div class="w-8 h-8 bg-green-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-900">Analytics</span>
                                </Link>

                                <button @click="sendReminders"
                                    class="w-full flex items-center gap-2 p-3 rounded-xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-300 text-sm">
                                    <div class="w-8 h-8 bg-orange-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-gray-900">Lembretes</span>
                                </button>
                            </div>
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
    event: {
        type: Object,
        default: () => ({})
    },
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

const eventUrl = computed(() => {
    if (!props.event?.slug) return ''
    return `${window.location.origin}/e/${props.event.slug}`
})

const timeRemaining = computed(() => {
    if (!props.event?.event_date) return 'Data não definida'

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

const occupancyRate = computed(() => {
    if (!props.event?.max_participants) return 0
    return Math.round(((props.stats?.confirmed_count || 0) / props.event.max_participants) * 100)
})

const conversionRate = computed(() => {
    const totalParticipants = props.participants.length
    if (totalParticipants === 0) return 0
    const confirmed = props.participants.filter(p => p.payment_status === 'paid').length
    return Math.round((confirmed / totalParticipants) * 100)
})

const growthRate = computed(() => {
    return Math.round(Math.random() * 20) + 5
})

const statusText = computed(() => {
    if (!props.event?.status) return 'Desconhecido'

    const statusMap = {
        'draft': 'Rascunho',
        'active': 'Ativo',
        'cancelled': 'Cancelado',
        'completed': 'Finalizado'
    }
    return statusMap[props.event.status] || 'Desconhecido'
})

const statusClass = computed(() => {
    if (!props.event?.status) return 'bg-gray-100 text-gray-800'

    const classMap = {
        'draft': 'bg-yellow-100 text-yellow-800',
        'active': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800',
        'completed': 'bg-gray-100 text-gray-800'
    }
    return classMap[props.event.status] || 'bg-gray-100 text-gray-800'
})

const formatEventDate = (dateString) => {
    if (!dateString) return 'Data não definida'
    const date = new Date(dateString)
    return date.toLocaleString('pt-BR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const copyUrl = () => {
    navigator.clipboard.writeText(eventUrl.value)
    alert('Link copiado para a área de transferência!')
}

const getInitials = (name) => {
    if (!name) return '??'
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

const publishEvent = () => {
    if (!props.event?.id) return

    if (confirm('Tem certeza que deseja publicar este evento? Ele ficará visível publicamente.')) {
        router.post(route('events.publish', props.event.id), {}, {
            onSuccess: () => {
            },
            onError: (errors) => {
                console.error('Erro ao publicar evento:', errors)
            }
        })
    }
}

const unpublishEvent = () => {
    if (!props.event?.id) return

    if (confirm('Tem certeza que deseja despublicar este evento? Ele não ficará mais visível publicamente.')) {
        router.post(route('events.unpublish', props.event.id), {}, {
            onSuccess: () => {
            },
            onError: (errors) => {
                console.error('Erro ao despublicar evento:', errors)
            }
        })
    }
}

const shareEvent = () => {
    if (navigator.share) {
        navigator.share({
            title: props.event?.name || 'Evento',
            text: 'Confira este evento incrível!',
            url: eventUrl.value
        })
    } else {
        copyUrl()
    }
}

const exportParticipants = () => {
    if (!props.event?.id) return
    router.visit(route('events.participants.export', props.event.id))
}

const sendReminders = () => {
    alert('Funcionalidade de lembretes em desenvolvimento!')
}
</script>
