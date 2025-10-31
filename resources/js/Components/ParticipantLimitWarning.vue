<template>
    <div v-if="shouldShowWarning" class="bg-yellow-50 border-2 border-yellow-400 rounded-2xl p-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-black mb-1">
                    {{ currentParticipants >= maxParticipants ? 'Limite Atingido!' : 'Atenção ao Limite' }}
                </h4>
                <p class="text-sm text-gray-800 mb-3">
                    <span v-if="currentParticipants >= maxParticipants">
                        Você atingiu o limite de <strong>{{ maxParticipants }} participantes</strong> do plano Free.
                    </span>
                    <span v-else>
                        Você está próximo do limite de <strong>{{ maxParticipants }} participantes</strong> do plano
                        Free.
                    </span>
                    Com o plano Pro, você pode ter até <strong>500 participantes</strong> e ainda economiza nas taxas!
                </p>

                <!-- Comparison -->
                <div class="bg-white rounded-xl p-3 mb-3 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Plano Free ({{ maxParticipants }} pax):</span>
                        <span class="font-semibold text-gray-900">R$ {{ formatPrice(freeEarnings) }}</span>
                    </div>
                    <div class="flex justify-between text-sm border-t pt-2">
                        <span class="text-gray-600">Plano Pro ({{ proParticipants }} pax):</span>
                        <span class="font-bold text-green-600">R$ {{ formatPrice(proEarnings) }}</span>
                    </div>
                    <div class="flex justify-between text-sm border-t pt-2 bg-green-50 -mx-3 px-3 py-2 rounded-b-xl">
                        <span class="font-bold text-green-800">Você ganha a mais:</span>
                        <span class="font-bold text-green-800">+R$ {{ formatPrice(difference) }}</span>
                    </div>
                </div>

                <Link :href="route('settings.billing')"
                    class="block w-full bg-black text-yellow-400 py-2.5 px-4 rounded-full font-bold text-sm text-center hover:bg-gray-800 transition-colors">
                Upgrade para Pro - R$ 19/mês
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    currentParticipants: {
        type: Number,
        required: true
    },
    maxParticipants: {
        type: Number,
        default: 70
    },
    ticketPrice: {
        type: Number,
        default: 30
    },
    userPlan: {
        type: String,
        default: 'freemium'
    }
})

const shouldShowWarning = computed(() => {
    return props.userPlan === 'freemium' && props.currentParticipants >= 50
})

const proParticipants = computed(() => {
    return Math.min(props.currentParticipants * 1.5, 500)
})

const freeEarnings = computed(() => {
    const grossRevenue = props.maxParticipants * props.ticketPrice
    const feePerTicket = (props.ticketPrice * 0.065) + 0.80
    const totalFees = feePerTicket * props.maxParticipants
    return grossRevenue - totalFees
})

const proEarnings = computed(() => {
    const grossRevenue = proParticipants.value * props.ticketPrice
    const feePerTicket = (props.ticketPrice * 0.055) + 0.80
    const totalFees = feePerTicket * proParticipants.value
    const monthlyFee = 19
    return grossRevenue - totalFees - monthlyFee
})

const difference = computed(() => {
    return proEarnings.value - freeEarnings.value
})

const formatPrice = (value) => {
    return value.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })
}
</script>
