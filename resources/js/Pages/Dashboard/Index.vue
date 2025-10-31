<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Meus Eventos</h1>
                    <p class="text-gray-600 mt-1">Bem-vindo de volta! Aqui está o resumo dos seus eventos.</p>
                </div>
                <Link :href="route('events.create')"
                    class="inline-flex items-center justify-center bg-black text-white px-6 py-3 rounded-2xl font-semibold hover:bg-gray-800 transition-colors shadow-sm">
                <span class="text-lg mr-2">+</span>
                Criar Evento
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total de Eventos</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_events }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">🎪</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Receita Total</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">R$ {{ stats.total_revenue }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">💸</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Participantes</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_participants }}</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">👥</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Próximos Eventos</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.upcoming_events }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center">
                            <span class="text-xl">📅</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Eventos Recentes</h2>
                            <Link :href="route('events.index')"
                                class="text-gray-600 hover:text-gray-900 text-sm font-medium flex items-center">
                            Ver todos
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            </Link>
                        </div>

                        <div v-if="events.length > 0" class="space-y-4">
                            <div v-for="event in events" :key="event.id"
                                class="border border-gray-200 rounded-xl p-4 hover:border-gray-300 transition-colors group">
                                <div v-if="event.header_image_url"
                                    class="w-full h-32 bg-gray-200 rounded-lg mb-3 overflow-hidden">
                                    <img :src="event.header_image_url" :alt="event.name"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                </div>
                                <div v-else
                                    class="w-full h-32 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg mb-3 flex items-center justify-center">
                                    <span class="text-gray-400 text-lg">🎪</span>
                                </div>

                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-semibold text-gray-900 text-sm leading-tight">{{ event.name }}</h3>
                                    <span class="px-2 py-1 text-xs rounded-full font-medium shrink-0 ml-2" :class="{
                                        'bg-green-100 text-green-800': event.status === 'active',
                                        'bg-gray-100 text-gray-800': event.status === 'draft',
                                        'bg-red-100 text-red-800': event.status === 'cancelled'
                                    }">
                                        {{ event.status === 'active' ? 'Ativo' : event.status === 'draft' ? 'Rascunho' :
                                        'Cancelado' }}
                                    </span>
                                </div>

                                <p class="text-xs text-gray-600 mb-1">
                                    {{ new Date(event.event_date).toLocaleDateString('pt-BR') }}
                                </p>
                                <p class="text-xs text-gray-600 mb-3">
                                    {{ event.location }} • {{ event.confirmed_count }} confirmados
                                </p>

                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-semibold text-gray-900">
                                        R$ {{ event.total_revenue || '0' }}
                                    </span>
                                    <Link :href="route('events.show', event.id)"
                                        class="text-gray-600 hover:text-gray-900 text-sm font-medium flex items-center">
                                    Ver detalhes
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-8">
                            <div
                                class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl text-gray-400">🎪</span>
                            </div>
                            <p class="text-gray-600 mb-4">Nenhum evento criado ainda</p>
                            <Link :href="route('events.create')"
                                class="inline-flex items-center bg-black text-white px-6 py-2 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                            <span class="text-lg mr-2">+</span>
                            Criar Primeiro Evento
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Seu Plano</h2>

                        <div class="bg-gradient-to-r from-gray-900 to-gray-700 rounded-xl p-4 text-white mb-4">
                            <h3 class="text-lg font-bold mb-1">Plano {{ plan.type === 'freemium' ? 'Free' : plan.type }}
                            </h3>
                            <p class="text-gray-300 text-sm">
                                {{ plan.type === 'freemium' ? 'Até 70 participantes por evento' : 'Participantes ilimitados' }}
                            </p>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-600">Eventos ativos</span>
                                <span class="font-semibold text-sm">
                                    {{ plan.event_limit === null ? 'Ilimitados' :
                                        `${plan.active_events_count}/${plan.event_limit}` }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-t border-gray-100">
                                <span class="text-sm text-gray-600">Participantes por evento</span>
                                <span class="font-semibold text-sm">
                                    {{ plan.participant_limit === null ? 'Ilimitados' : plan.participant_limit }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-t border-gray-100">
                                <span class="text-sm text-gray-600">Taxa por transação</span>
                                <span class="font-semibold text-sm">
                                    {{ plan.type === 'freemium' ? '6.5% + R$0,80' : '5.5% + R$0,80' }}
                                </span>
                            </div>
                        </div>

                        <div v-if="plan.type === 'freemium'"
                            class="mt-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                            <p class="text-xs text-orange-800 mb-2">
                                <strong>Upgrade para Pro:</strong> Eventos ilimitados e taxa reduzida!
                            </p>
                            <Link :href="route('settings.upgrade-pro')"
                                class="w-full bg-orange-600 text-white py-2 rounded-lg font-semibold hover:bg-orange-700 transition-colors text-center block text-sm">
                            Fazer Upgrade
                            </Link>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h2>
                        <div class="space-y-3">
                            <Link :href="route('events.create')"
                                class="flex items-center p-3 border border-gray-200 rounded-xl hover:border-gray-300 hover:bg-gray-50 transition-colors group">
                            <div
                                class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center mr-3 group-hover:bg-green-100 transition-colors">
                                <span class="text-lg">➕</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 text-sm">Criar Evento</p>
                                <p class="text-xs text-gray-600">Iniciar nova resenha</p>
                            </div>
                            </Link>

                            <Link :href="route('ai.suggestions')"
                                class="flex items-center p-3 border border-gray-200 rounded-xl hover:border-gray-300 hover:bg-gray-50 transition-colors group">
                            <div
                                class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-100 transition-colors">
                                <span class="text-lg">🤖</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 text-sm">Sugestões de IA</p>
                                <p class="text-xs text-gray-600">Ideias para seu evento</p>
                            </div>
                            </Link>

                            <Link :href="route('settings.index')"
                                class="flex items-center p-3 border border-gray-200 rounded-xl hover:border-gray-300 hover:bg-gray-50 transition-colors group">
                            <div
                                class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center mr-3 group-hover:bg-purple-100 transition-colors">
                                <span class="text-lg">⚙️</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 text-sm">Configurações</p>
                                <p class="text-xs text-gray-600">Gerenciar conta</p>
                            </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
            participant_limit: 70,
            active_events_count: 0
        })
    }
})
</script>
