<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Configurações</h1>
            <p class="text-gray-600">Gerencie sua conta e preferências</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Menu Lateral -->
            <div class="md:col-span-1">
                <nav class="space-y-1">
                    <Link :href="route('settings.index')"
                        class="block px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg font-semibold">
                    Perfil
                    </Link>
                    <Link :href="route('settings.billing')"
                        class="block px-4 py-3 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                    Plano e Cobrança
                    </Link>
                </nav>
            </div>

            <!-- Conteúdo -->
            <div class="md:col-span-2 space-y-6">
                <!-- Perfil -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informações do Perfil</h2>
                    <form @submit.prevent="updateProfile">
                        <div class="grid md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nome Completo *
                                </label>
                                <input v-model="profileForm.full_name" type="text" id="full_name" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <p v-if="profileForm.errors.full_name" class="text-red-500 text-sm mt-1">
                                    {{ profileForm.errors.full_name }}
                                </p>
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Telefone *
                                </label>
                                <input v-model="profileForm.phone" type="tel" id="phone" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    placeholder="(11) 99999-9999">
                                <p v-if="profileForm.errors.phone" class="text-red-500 text-sm mt-1">
                                    {{ profileForm.errors.phone }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" :disabled="profileForm.processing"
                                class="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors disabled:opacity-50">
                                {{ profileForm.processing ? 'Salvando...' : 'Salvar Alterações' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Plano Atual -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Plano Atual</h2>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">Plano {{ plan.type }}</p>
                            <p class="text-gray-600">
                                {{ plan.type === 'free' ? 'Até 70 participantes por evento' : 'Participantes ilimitados'
                                }}
                            </p>
                        </div>
                        <div class="px-4 py-2 rounded-full text-sm font-semibold" :class="{
                            'bg-green-100 text-green-800': plan.type === 'pro',
                            'bg-gray-100 text-gray-800': plan.type === 'free'
                        }">
                            {{ plan.type === 'pro' ? 'Pro' : 'Free' }}
                        </div>
                    </div>

                    <div v-if="plan.type === 'free'" class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <p class="text-sm text-orange-800 mb-3">
                            <strong>Faça upgrade para Pro</strong> e desbloqueie todos os recursos!
                        </p>
                        <ul class="text-sm text-orange-700 space-y-1 mb-4">
                            <li>✓ Eventos ilimitados</li>
                            <li>✓ Participantes ilimitados</li>
                            <li>✓ Taxa reduzida (5.5% + R$0,80)</li>
                            <li>✓ Sugestões de IA</li>
                        </ul>
                        <Link :href="route('settings.upgrade-pro')"
                            class="w-full bg-orange-600 text-white py-2 rounded-lg font-semibold hover:bg-orange-700 transition-colors text-center block">
                        Fazer Upgrade para Pro - R$ 19/mês
                        </Link>
                    </div>

                    <div v-else class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-green-800">
                            🎉 Você está no plano Pro! Obrigado pela confiança.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
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
    profileForm.put(route('settings.profile.update'))
}
</script>
