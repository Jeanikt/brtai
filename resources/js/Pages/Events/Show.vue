<template>
    <div class="space-y-6">
        <!-- Cabeçalho -->
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ event.name }}</h1>
                <p class="text-gray-600">
                    {{ new Date(event.event_date).toLocaleDateString('pt-BR', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                    minute: '2-digit'
                    }) }}
                </p>
            </div>
            <div class="flex space-x-3">
                <Link :href="route('events.edit', event.id)"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                Editar
                </Link>
                <button v-if="event.status === 'draft'" @click="publishEvent"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Publicar Evento
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ stats.confirmed_count }}</p>
                <p class="text-sm text-gray-600">Confirmados</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 text-center">
                <p class="text-2xl font-bold text-gray-900">R$ {{ stats.total_revenue }}</p>
                <p class="text-sm text-gray-600">Arrecadado</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ event.max_participants || '∞' }}</p>
                <p class="text-sm text-gray-600">Capacidade</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ stats.conversion_rate }}%</p>
                <p class="text-sm text-gray-600">Conversão</p>
            </div>
        </div>

        <!-- Grid Principal -->
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Informações do Evento -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Detalhes -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Detalhes do Evento</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Localização</p>
                            <p class="text-gray-900">{{ event.location }}</p>
                        </div>
                        <div v-if="event.description">
                            <p class="text-sm font-medium text-gray-600">Descrição</p>
                            <p class="text-gray-900">{{ event.description }}</p>
                        </div>
                        <div v-if="event.theme">
                            <p class="text-sm font-medium text-gray-600">Tema</p>
                            <p class="text-gray-900">{{ event.theme }}</p>
                        </div>
                    </div>
                </div>

                <!-- Participantes -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Participantes</h2>
                        <Link :href="route('events.export.participants', event.id)"
                            class="text-green-600 hover:text-green-700 text-sm font-medium">
                        Exportar Lista
                        </Link>
                    </div>

                    <div v-if="participants.length > 0" class="space-y-3">
                        <div v-for="participant in participants" :key="participant.id"
                            class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">{{ participant.full_name }}</p>
                                <p class="text-sm text-gray-600">{{ participant.phone }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 text-xs rounded-full" :class="{
                                    'bg-green-100 text-green-800': participant.payment_status === 'paid',
                                    'bg-yellow-100 text-yellow-800': participant.payment_status === 'pending',
                                    'bg-red-100 text-red-800': participant.payment_status === 'failed'
                                }">
                                    {{ participant.payment_status === 'paid' ? 'Pago' :
                                        participant.payment_status === 'pending' ? 'Pendente' : 'Falhou' }}
                                </span>
                                <p class="text-sm text-gray-600 mt-1">R$ {{ participant.payment_amount }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        Nenhum participante confirmado ainda
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Ações Rápidas -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h2>
                    <div class="space-y-3">
                        <Link :href="route('events.public.show', event.slug)" target="_blank"
                            class="w-full flex items-center justify-center px-4 py-3 border border-green-600 text-green-600 rounded-lg font-semibold hover:bg-green-50 transition-colors">
                        <span class="mr-2">🔗</span>
                        Ver Página Pública
                        </Link>
                        <Link :href="route('events.analytics', event.id)"
                            class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                        <span class="mr-2">📊</span>
                        Ver Analytics
                        </Link>
                        <button @click="copyEventLink"
                            class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                            <span class="mr-2">📋</span>
                            Copiar Link
                        </button>
                    </div>
                </div>

                <!-- Informações de Pagamento -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informações Financeiras</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Arrecadado</span>
                            <span class="font-semibold">R$ {{ stats.total_revenue }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Taxas BrotaAI</span>
                            <span class="font-semibold">R$ {{ calculateFees(stats.total_revenue) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-3">
                            <span class="text-gray-900 font-semibold">Valor Líquido</span>
                            <span class="font-semibold">R$ {{ calculateNetAmount(stats.total_revenue) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
    event: {
        type: Object,
        required: true
    },
    participants: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total_revenue: 0,
            confirmed_count: 0,
            conversion_rate: 0
        })
    }
})

const publishEvent = () => {
    router.post(route('events.publish', props.event.id))
}

const copyEventLink = () => {
    const url = route('events.public.show', props.event.slug)
    navigator.clipboard.writeText(url)
    alert('Link copiado para a área de transferência!')
}

const calculateFees = (revenue) => {
    // Taxa de 6.5% + R$0,80 por transação (simplificado)
    return (revenue * 0.065).toFixed(2)
}

const calculateNetAmount = (revenue) => {
    return (revenue - calculateFees(revenue)).toFixed(2)
}
</script>
