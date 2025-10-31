<template>
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <button @click="goBack"
                    class="flex items-center gap-2 text-gray-700 hover:text-black transition-colors mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="font-medium">Voltar</span>
                </button>
                <h1 class="text-3xl font-bold text-gray-900">Análises do Evento</h1>
                <p class="mt-2 text-gray-600">Acompanhe o desempenho e as métricas do seu evento</p>
            </div>

            <!-- Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-3xl shadow-sm p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total de Participantes</p>
                            <p class="text-2xl font-bold text-gray-900">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Confirmados</p>
                            <p class="text-2xl font-bold text-gray-900">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Taxa de Conversão</p>
                            <p class="text-2xl font-bold text-gray-900">0%</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Receita Total</p>
                            <p class="text-2xl font-bold text-gray-900">R$ 0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-3xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Participação ao Longo do Tempo</h2>
                    <div
                        class="h-64 flex items-center justify-center border-2 border-dashed border-gray-200 rounded-2xl">
                        <p class="text-gray-500">Gráfico de participação será exibido aqui</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Métricas de Engajamento</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span>Visualizações da Página</span>
                                <span class="font-medium">0</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span>Taxa de Cliques</span>
                                <span class="font-medium">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: 0%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span>Tempo Médio na Página</span>
                                <span class="font-medium">0s</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-purple-600 h-2 rounded-full" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Participants List -->
            <div class="bg-white rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Participantes Recentes</h2>
                </div>
                <div class="p-6">
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Nenhum participante</h3>
                        <p class="text-sm text-gray-500">Os participantes aparecerão aqui quando começarem a se
                            inscrever.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'

const goBack = () => {
    router.visit(window.route('dashboard'))
}
</script>
