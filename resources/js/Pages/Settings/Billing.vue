<template>
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Planos e Cobrança</h1>
                <p class="text-gray-600">Gerencie seu plano atual e faça upgrade</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <!-- Free Plan -->
                <div class="bg-white border-2 border-gray-200 rounded-3xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Free</h3>
                    <p class="text-3xl font-bold text-gray-900 mb-4">R$ 0<span
                            class="text-sm font-normal text-gray-600">/mês</span></p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            1 evento ativo
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Até 70 participantes
                        </li>
                    </ul>
                    <button v-if="user_plan === 'freemium'" disabled
                        class="w-full bg-gray-100 text-gray-400 py-3 px-4 rounded-2xl font-semibold cursor-not-allowed">
                        Plano Atual
                    </button>
                </div>

                <!-- Pro Plan -->
                <div class="bg-white border-2 border-yellow-400 rounded-3xl p-6 shadow-lg relative">
                    <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                        <span
                            class="bg-yellow-400 text-black text-xs font-bold px-3 py-1 rounded-full">Recomendado</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Pro</h3>
                    <p class="text-3xl font-bold text-gray-900 mb-4">R$ 49<span
                            class="text-sm font-normal text-gray-600">/mês</span></p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Até 10 eventos ativos
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Até 500 participantes
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Analytics avançados
                        </li>
                    </ul>
                    <Link v-if="user_plan === 'freemium'" :href="route('settings.upgrade-pro')" method="post"
                        as="button"
                        class="w-full bg-black text-white py-3 px-4 rounded-2xl font-semibold hover:bg-gray-800 transition-colors">
                    Fazer Upgrade
                    </Link>
                    <button v-else-if="user_plan === 'pro'" disabled
                        class="w-full bg-gray-100 text-gray-400 py-3 px-4 rounded-2xl font-semibold cursor-not-allowed">
                        Plano Atual
                    </button>
                </div>

                <!-- Enterprise Plan -->
                <div class="bg-white border-2 border-gray-200 rounded-3xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Enterprise</h3>
                    <p class="text-3xl font-bold text-gray-900 mb-4">Personalizado</p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Eventos ilimitados
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Participantes ilimitados
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Suporte prioritário
                        </li>
                    </ul>
                    <button v-if="user_plan === 'enterprise'" disabled
                        class="w-full bg-gray-100 text-gray-400 py-3 px-4 rounded-2xl font-semibold cursor-not-allowed">
                        Plano Atual
                    </button>
                    <button v-else
                        class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-2xl font-semibold hover:bg-gray-200 transition-colors">
                        Contatar Vendas
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    user_plan: String
})
</script>
