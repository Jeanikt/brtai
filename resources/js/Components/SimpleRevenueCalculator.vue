<template>
    <!-- Para usuários PRO: Mostra apenas o faturamento bruto -->
    <div v-if="user_plan === 'pro' && participants > 0 && ticketPrice > 0"
        class="bg-gradient-to-br from-green-50 to-emerald-100 border border-green-200 rounded-2xl p-5 mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 text-lg">💰 Projeção de Faturamento</h3>
                <p class="text-sm text-gray-600">Estimativa baseada nos valores informados</p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 text-center">
            <div class="bg-white rounded-xl p-4 border border-green-200">
                <div class="text-2xl font-bold text-green-600 mb-1">
                    {{ participants }}
                </div>
                <div class="text-xs text-gray-600 font-medium">Participantes</div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-green-200">
                <div class="text-2xl font-bold text-green-600 mb-1">
                    R$ {{ formatCurrency(ticketPrice) }}
                </div>
                <div class="text-xs text-gray-600 font-medium">Por ingresso</div>
            </div>

            <div class="bg-green-500 rounded-xl p-4 border border-green-600">
                <div class="text-2xl font-bold text-white mb-1">
                    R$ {{ formatCurrency(totalRevenue) }}
                </div>
                <div class="text-xs text-green-100 font-medium">Faturamento total</div>
            </div>
        </div>

        <div class="mt-4 p-3 bg-green-500 bg-opacity-10 rounded-xl border border-green-300">
            <p class="text-sm text-green-800 text-center">
                🎉 <strong>Potencial máximo:</strong> Se todos os {{ participants }} ingressos forem vendidos
            </p>
        </div>
    </div>

    <!-- Para usuários FREE: Mostra o cálculo comparativo -->

</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

interface Props {
    user_plan: string
    participants: number
    ticketPrice: number
}

const props = defineProps<Props>()

// Cálculo simplificado - apenas faturamento bruto para PRO
const totalRevenue = computed(() => {
    return props.participants * props.ticketPrice
})

// Cálculos para FREE (com taxas)
const freeRevenue = computed(() => {
    const maxParticipants = Math.min(props.participants, 70)
    const gross = maxParticipants * props.ticketPrice
    const feePerTicket = (props.ticketPrice * 0.065) + 0.80
    const fees = maxParticipants * feePerTicket
    return gross - fees
})

// Cálculos para PRO (com taxas reduzidas)
const proRevenue = computed(() => {
    const gross = props.participants * props.ticketPrice
    const feePerTicket = (props.ticketPrice * 0.055) + 0.80
    const fees = props.participants * feePerTicket
    const subscription = 19.00
    return gross - fees - subscription
})

const formatCurrency = (value: number): string => {
    return value.toFixed(2).replace('.', ',')
}
</script>
