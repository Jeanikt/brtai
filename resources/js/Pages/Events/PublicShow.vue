<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-2xl mx-auto px-4 py-8">
            <!-- Event Card -->
            <div class="bg-white rounded-3xl shadow-sm overflow-hidden mb-6">
                <!-- Header Image -->
                <img v-if="event.header_image_url" :src="event.header_image_url" alt="Banner do evento"
                    class="w-full h-64 object-cover">
                <div v-else class="w-full h-64 bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-400">Sem imagem</span>
                </div>

                <div class="p-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ event.name }}</h1>

                    <!-- Event Info -->
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Data</p>
                                <p class="text-sm font-medium text-gray-900">{{ formatDate(event.event_date) }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Local</p>
                                <p class="text-sm font-medium text-gray-900"
                                    v-if="event.location_reveal_after_payment && !hasPaid">
                                    Será revelado após pagamento
                                </p>
                                <p class="text-sm font-medium text-gray-900" v-else>
                                    {{ event.location }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Organizador</p>
                                <p class="text-sm font-medium text-gray-900">{{ event.organizer.full_name }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Confirmados</p>
                                <p class="text-sm font-medium text-gray-900">{{ confirmed_count }} / {{
                                    event.max_participants || 'Ilimitado' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="event.description" class="mb-6 pb-6 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Sobre o Evento</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ event.description }}</p>
                    </div>

                    <!-- Price Tiers -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ingressos Disponíveis</h3>
                        <div class="space-y-3">
                            <div v-for="tier in event.price_tiers" :key="tier.id"
                                class="border-2 rounded-2xl p-4 transition-all"
                                :class="selectedTier?.id === tier.id ? 'border-black bg-gray-50' : 'border-gray-200 hover:border-gray-300'">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-900">{{ tier.name }}</h4>
                                    <span class="text-lg font-bold text-gray-900">
                                        R$ {{ tier.price }}
                                    </span>
                                </div>
                                <p v-if="tier.description" class="text-sm text-gray-600 mb-3">{{ tier.description }}</p>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">
                                        {{ tier.current_quantity }} / {{ tier.max_quantity || '∞' }} vendidos
                                    </span>
                                    <button @click="selectTicket(tier)"
                                        :disabled="!tier.is_active || (tier.max_quantity && tier.current_quantity >= tier.max_quantity)"
                                        class="px-4 py-2 rounded-xl font-semibold transition-colors"
                                        :class="selectedTier?.id === tier.id ? 'bg-black text-white' : 'bg-gray-100 text-gray-900 hover:bg-gray-200 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed'">
                                        {{ selectedTier?.id === tier.id ? 'Selecionado' : 'Selecionar' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Form -->
                    <div v-if="selectedTier" class="bg-gray-50 rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Finalizar Inscrição</h3>
                        <form @submit.prevent="submitParticipation" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo</label>
                                <input v-model="form.full_name" type="text" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:border-black">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input v-model="form.email" type="email" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:border-black">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                                <input v-model="form.phone" type="tel" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:border-black"
                                    placeholder="(11) 99999-9999">
                            </div>
                            <button type="submit"
                                class="w-full bg-black text-white py-4 px-4 rounded-2xl font-semibold hover:bg-gray-800 transition-colors">
                                Confirmar Inscrição - R$ {{ selectedTier.price }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    event: Object,
    confirmed_count: Number,
    available_slots: Number
})

const selectedTier = ref(null)
const hasPaid = ref(false)

const form = useForm({
    full_name: '',
    email: '',
    phone: '',
    price_tier_id: null
})

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const selectTicket = (tier) => {
    selectedTier.value = tier
    form.price_tier_id = tier.id
}

const submitParticipation = () => {
    form.post(window.route('events.public.participate', { event: props.event.slug }))
}
</script>
