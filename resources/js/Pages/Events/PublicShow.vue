<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <img v-if="event.header_image_url" :src="event.header_image_url" alt="Banner do evento"
                    class="w-full h-64 object-cover">

                <div class="p-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ event.name }}</h1>

                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-gray-600 mb-2">
                                <strong>Data:</strong> {{ formatDate(event.event_date) }}
                            </p>
                            <p class="text-gray-600 mb-2">
                                <strong>Local:</strong>
                                <span v-if="event.location_reveal_after_payment && !hasPaid">
                                    Será revelado após confirmação do pagamento
                                </span>
                                <span v-else>
                                    {{ event.location }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-600 mb-2">
                                <strong>Organizador:</strong> {{ event.organizer.full_name }}
                            </p>
                            <p class="text-gray-600">
                                <strong>Confirmados:</strong> {{ confirmed_count }} / {{ event.max_participants ||
                                'Ilimitado' }}
                            </p>
                        </div>
                    </div>

                    <div v-if="event.description" class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Descrição</h3>
                        <p class="text-gray-700">{{ event.description }}</p>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Ingressos Disponíveis</h3>
                        <div class="grid gap-4">
                            <div v-for="tier in event.price_tiers" :key="tier.id"
                                class="border border-gray-200 rounded-lg p-4 hover:border-green-500 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-900">{{ tier.name }}</h4>
                                    <span class="text-lg font-bold text-green-600">
                                        R$ {{ tier.price }}
                                    </span>
                                </div>
                                <p v-if="tier.description" class="text-gray-600 text-sm mb-3">{{ tier.description }}</p>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">
                                        {{ tier.current_quantity }} / {{ tier.max_quantity || '∞' }} vendidos
                                    </span>
                                    <button @click="selectTicket(tier)"
                                        :disabled="!tier.is_active || (tier.max_quantity && tier.current_quantity >= tier.max_quantity)"
                                        class="bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                        Selecionar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedTier" class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Finalizar Inscrição</h3>
                        <form @submit.prevent="submitParticipation">
                            <div class="grid md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                                    <input v-model="form.full_name" type="text" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input v-model="form.email" type="email" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                <input v-model="form.phone" type="tel" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>
                            <button type="submit"
                                class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors">
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
    return new Date(dateString).toLocaleString('pt-BR')
}

const selectTicket = (tier) => {
    selectedTier.value = tier
    form.price_tier_id = tier.id
}

const submitParticipation = () => {
    form.post(route('events.public.participate', { event: props.event.slug }))
}
</script>
