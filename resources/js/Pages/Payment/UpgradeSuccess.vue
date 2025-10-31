<!-- resources/js/Pages/Settings/UpgradeSuccess.vue -->
<template>
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto px-4 py-8">
            <div class="text-center">
                <!-- Success Icon -->
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">Upgrade Concluído!</h1>
                <p class="text-lg text-gray-600 mb-8">Agora você faz parte do <span
                        class="font-semibold text-yellow-600">Plano Pro</span></p>

                <!-- Benefits Card -->
                <div class="bg-white rounded-3xl shadow-sm p-8 mb-8 text-left">
                    <h3 class="text-xl font-semibold text-gray-900 mb-6">Recursos desbloqueados:</h3>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Eventos Ilimitados</h4>
                                <p class="text-sm text-gray-600">Crie quantos eventos quiser</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Participantes Ilimitados</h4>
                                <p class="text-sm text-gray-600">Sem limite de convidados</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Taxa Reduzida</h4>
                                <p class="text-sm text-gray-600">Apenas 5.5% + R$0,80 por ingresso</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Analytics Avançados</h4>
                                <p class="text-sm text-gray-600">Métricas detalhadas dos eventos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-8">
                    <h4 class="font-semibold text-blue-900 mb-3">Próximos passos</h4>
                    <ul class="space-y-2 text-sm text-blue-800">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Explore os novos recursos no dashboard</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Crie novos eventos sem limitações</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Aproveite as taxas reduzidas nos pagamentos</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 justify-center">
                    <Link :href="route('dashboard')"
                        class="bg-black text-white px-8 py-3 rounded-2xl font-semibold hover:bg-gray-800 transition-colors">
                    Ir para Dashboard
                    </Link>
                    <Link :href="route('events.create')"
                        class="bg-gray-100 text-gray-700 px-8 py-3 rounded-2xl font-semibold hover:bg-gray-200 transition-colors">
                    Criar Evento
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    plan_type: String,
    user: Object
})
</script>
