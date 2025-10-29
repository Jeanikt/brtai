<template>
    <div class="space-y-6">
        <!-- Cabeçalho -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-gray-600">Bem-vindo de volta! Aqui está o resumo dos seus eventos.</p>
            </div>
            <Link :href="route('events.create')"
                class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors">
            + Criar Evento
            </Link>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total de Eventos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total_events }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <span class="text-xl">🎪</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Receita Total</p>
                        <p class="text-2xl font-bold text-gray-900">R$ {{ stats.total_revenue }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="text-xl">💸</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Participantes</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total_participants }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <span class="text-xl">👥</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Próximos Eventos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.upcoming_events }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <span class="text-xl">📅</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de Eventos -->
        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Meus Eventos -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Meus Eventos</h2>
                    <Link :href="route('events.index')" class="text-green-600 hover:text-green-700 text-sm font-medium">
                    Ver todos
                    </Link>
                </div>

                <div v-if="events.length > 0" class="space-y-4">
                    <div v-for="event in events" :key="event.id"
                        class="border border-gray-200 rounded-lg p-4 hover:border-green-300 transition-colors">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold text-gray-900">{{ event.name }}</h3>
                            <span class="px-2 py-1 text-xs rounded-full" :class="{
                                'bg-green-100 text-green-800': event.status === 'active',
                                'bg-gray-100 text-gray-800': event.status === 'draft',
                                'bg-red-100 text-red-800': event.status === 'cancelled'
                            }">
                                {{ event.status === 'active' ? 'Ativo' : event.status === 'draft' ? 'Rascunho' :
                                'Cancelado' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">
                            {{ new Date(event.event_date).toLocaleDateString('pt-BR') }} -
                            {{ event.confirmed_count }} confirmados
                        </p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-900">
                                R$ {{ event.total_revenue || '0' }}
                            </span>
                            <Link :href="route('events.show', event.id)"
                                class="text-green-600 hover:text-green-700 text-sm font-medium">
                            Ver detalhes →
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl text-gray-400">🎪</span>
                    </div>
                    <p class="text-gray-600 mb-4">Nenhum evento criado ainda</p>
                    <Link :href="route('events.create')"
                        class="bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Criar Primeiro Evento
                    </Link>
                </div>
            </div>

            <!-- Informações do Plano -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Seu Plano</h2>

                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white mb-6">
                    <h3 class="text-xl font-bold mb-2">Plano {{ plan.type }}</h3>
                    <p class="text-green-100">
                        {{ plan.type === 'freemium' ? 'Até 70 participantes por evento' : 'Participantes ilimitados' }}
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="text-gray-600">Eventos ativos</span>
                        <span class="font-semibold">
                            {{ plan.event_limit === null ? 'Ilimitados' : plan.event_limit }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="text-gray-600">Participantes por evento</span>
                        <span class="font-semibold">
                            {{ plan.participant_limit === null ? 'Ilimitados' : plan.participant_limit }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-3">
                        <span class="text-gray-600">Taxa por transação</span>
                        <span class="font-semibold">
                            {{ plan.type === 'freemium' ? '6.5% + R$0,80' : '5.5% + R$0,80' }}
                        </span>
                    </div>
                </div>

                <div v-if="plan.type === 'freemium'" class="mt-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                    <p class="text-sm text-orange-800 mb-3">
                        <strong>Upgrade para Pro:</strong> Eventos ilimitados e taxa reduzida!
                    </p>
                    <Link :href="route('settings.upgrade-pro')"
                        class="w-full bg-orange-600 text-white py-2 rounded-lg font-semibold hover:bg-orange-700 transition-colors text-center block">
                    Fazer Upgrade para Pro
                    </Link>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link :href="route('events.create')"
                    class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition-colors">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                    <span class="text-lg">➕</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Criar Evento</p>
                    <p class="text-sm text-gray-600">Iniciar nova resenha</p>
                </div>
                </Link>

                <Link :href="route('ai.suggestions')"
                    class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <span class="text-lg">🤖</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Sugestões de IA</p>
                    <p class="text-sm text-gray-600">Ideias para seu evento</p>
                </div>
                </Link>

                <Link :href="route('settings.index')"
                    class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition-colors">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                    <span class="text-lg">⚙️</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Configurações</p>
                    <p class="text-sm text-gray-600">Gerenciar conta</p>
                </div>
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    events: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total_events: 0,
            total_revenue: 0,
            total_participants: 0,
            upcoming_events: 0
        })
    },
    plan: {
        type: Object,
        default: () => ({
            type: 'freemium',
            event_limit: 1,
            participant_limit: 70
        })
    }
})
</script>
