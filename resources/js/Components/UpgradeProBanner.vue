<template>
    <div
        class="bg-gradient-to-br from-yellow-400 via-yellow-300 to-yellow-500 rounded-[32px] p-6 shadow-lg relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-black opacity-5 rounded-full -ml-12 -mb-12"></div>

        <div class="relative z-10">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-black rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="bg-black text-yellow-400 text-xs font-bold px-3 py-1 rounded-full">UPGRADE PRO</span>
                </div>
                <button v-if="dismissible" @click="$emit('dismiss')" class="text-black hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <h3 class="text-xl font-bold text-black mb-2">
                {{ title || 'Desbloqueie Todo o Potencial dos Seus Eventos!' }}
            </h3>

            <p class="text-black text-sm mb-4 opacity-90">
                {{ description || 'Faça upgrade para Pro e economize nas taxas enquanto cria eventos ilimitados.' }}
            </p>

            <!-- Savings Calculator -->
            <div v-if="showSavings && calculatedSavings > 0" class="bg-black bg-opacity-10 rounded-2xl p-4 mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-black">Você está perdendo:</span>
                    <span class="text-2xl font-bold text-black">R$ {{ formatPrice(calculatedSavings) }}</span>
                </div>
                <p class="text-xs text-black opacity-80">
                    Por evento com {{ estimatedParticipants }} participantes pagando R$ {{ ticketPrice }}
                </p>
            </div>

            <!-- Benefits List -->
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium text-black">Eventos ilimitados</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium text-black">Até 500 convidados</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium text-black">Taxa reduzida 5.5%</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium text-black">Sugestões de IA</span>
                </div>
            </div>

            <Link :href="route('settings.billing')"
                class="block w-full bg-black text-yellow-400 py-3 px-6 rounded-full font-bold text-center hover:bg-gray-800 transition-colors">
            Fazer Upgrade por R$ 19/mês
            </Link>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    title: String,
    description: String,
    dismissible: {
        type: Boolean,
        default: false
    },
    showSavings: {
        type: Boolean,
        default: true
    },
    estimatedParticipants: {
        type: Number,
        default: 100
    },
    ticketPrice: {
        type: Number,
        default: 30
    }
})

defineEmits(['dismiss'])

const calculatedSavings = computed(() => {
    const freeFee = (props.ticketPrice * 0.065) + 0.80
    const proFee = (props.ticketPrice * 0.055) + 0.80
    const savingsPerTicket = freeFee - proFee
    return savingsPerTicket * props.estimatedParticipants
})

const formatPrice = (value) => {
    return value.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })
}
</script>
