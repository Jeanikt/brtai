<template>
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Configurações</h1>
                <p class="text-gray-600">Gerencie sua conta e preferências</p>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                <!-- Sidebar Menu -->
                <div class="md:col-span-1">
                    <nav class="space-y-2">
                        <Link :href="route('settings.index')"
                            class="block px-4 py-3 bg-yellow-400 text-black rounded-2xl font-semibold">
                        Perfil
                        </Link>
                        <Link :href="route('settings.billing')"
                            class="block px-4 py-3 text-gray-700 rounded-2xl font-semibold hover:bg-gray-100 transition-colors">
                        Plano e Cobrança
                        </Link>
                    </nav>
                </div>

                <!-- Content -->
                <div class="md:col-span-3 space-y-6">
                    <!-- Profile Form -->
                    <div class="bg-white rounded-3xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">Informações do Perfil</h2>
                        <form @submit.prevent="updateProfile">
                            <div class="grid md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nome Completo *
                                    </label>
                                    <input v-model="profileForm.full_name" type="text" id="full_name" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-black focus:border-black">
                                    <p v-if="profileForm.errors.full_name" class="text-red-500 text-sm mt-1">
                                        {{ profileForm.errors.full_name }}
                                    </p>
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        Telefone *
                                    </label>
                                    <input v-model="profileForm.phone" type="tel" id="phone" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-black focus:border-black"
                                        placeholder="(11) 99999-9999">
                                    <p v-if="profileForm.errors.phone" class="text-red-500 text-sm mt-1">
                                        {{ profileForm.errors.phone }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" :disabled="profileForm.processing"
                                    class="px-6 py-3 bg-black text-white rounded-2xl font-semibold hover:bg-gray-800 transition-colors disabled:opacity-50">
                                    {{ profileForm.processing ? 'Salvando...' : 'Salvar Alterações' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Current Plan -->
                    <div class="bg-white rounded-3xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">Plano Atual</h2>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <p class="text-2xl font-bold text-gray-900">Plano {{ plan.type }}</p>
                                <p class="text-gray-600">
                                    {{ plan.type === 'freemium' ? 'Até 70 participantes por evento' : 'Participantes ilimitados' }}
                                </p>
                            </div>
                            <div class="px-4 py-2 rounded-full text-sm font-semibold" :class="{
                                'bg-green-100 text-green-800': plan.type === 'pro',
                                'bg-gray-100 text-gray-800': plan.type === 'freemium'
                            }">
                                {{ plan.type === 'pro' ? 'Pro' : 'Free' }}
                            </div>
                        </div>

                        <div v-if="plan.type === 'freemium'"
                            class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
                            <p class="text-sm text-gray-900 font-semibold mb-3">
                                Faça upgrade para Pro e desbloqueie todos os recursos!
                            </p>
                            <ul class="text-sm text-gray-700 space-y-2 mb-4">
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Eventos ilimitados
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Participantes ilimitados
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Taxa reduzida (5.5% + R$0,80)
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Sugestões de IA
                                </li>
                            </ul>
                            <Link :href="route('settings.upgrade-pro')" method="post" as="button"
                                class="w-full bg-black text-white py-3 rounded-2xl font-semibold hover:bg-gray-800 transition-colors text-center">
                            Fazer Upgrade para Pro - R$ 19/mês
                            </Link>
                        </div>

                        <div v-else class="bg-green-50 border border-green-200 rounded-2xl p-4">
                            <p class="text-sm text-green-800 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                Você está no plano {{ plan.type }}! Obrigado pela confiança.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
    profile: {
        type: Object,
        required: true
    },
    plan: {
        type: Object,
        default: () => ({
            type: 'free'
        })
    }
})

const profileForm = useForm({
    full_name: props.profile.full_name,
    phone: props.profile.phone
})

const updateProfile = () => {
    profileForm.put(window.route('settings.profile.update'))
}
</script>
