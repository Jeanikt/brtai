<template>
    <div class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-md mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <button @click="goBack"
                    class="flex items-center gap-2 text-gray-700 hover:text-black transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <h1 class="text-xl font-semibold">Pagamento</h1>
                <div class="w-6"></div>
            </div>

            <!-- Event Info Card -->
            <div class="bg-white rounded-3xl shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">{{ participant.event.name }}</h2>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <span>Participante:</span>
                        <span class="font-medium text-gray-900">{{ participant.full_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Email:</span>
                        <span class="font-medium text-gray-900">{{ participant.email || 'Não informado' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Telefone:</span>
                        <span class="font-medium text-gray-900">{{ participant.phone }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-200">
                        <span>Ingresso:</span>
                        <span class="font-medium text-gray-900">{{ participant.price_tier.name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Valor:</span>
                        <span class="font-semibold text-lg text-gray-900">R$ {{ participant.price_tier.price }}</span>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <span :class="statusClass" class="inline-block px-3 py-1 rounded-full text-xs font-medium">
                        {{ statusText }}
                    </span>
                </div>
            </div>

            <!-- PIX Payment Card -->
            <div class="bg-white rounded-3xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Pague com PIX</h3>

                <!-- QR Code -->
                <div class="flex justify-center mb-6">
                    <div class="bg-white p-4 rounded-2xl border-2 border-gray-200">
                        <img :src="generateQrCode(pix_code)" alt="QR Code PIX" class="w-48 h-48">
                    </div>
                </div>

                <!-- PIX Code -->
                <div class="bg-gray-50 rounded-2xl p-4 mb-4">
                    <p class="text-xs text-gray-600 mb-2 font-medium">Código PIX Copia e Cola:</p>
                    <div class="flex gap-2">
                        <code
                            class="text-xs break-all flex-1 bg-white p-3 rounded-xl border border-gray-200 font-mono">{{ pix_code }}</code>
                    </div>
                    <button @click="copyPixCode"
                        class="w-full mt-3 bg-black text-white py-3 rounded-2xl font-semibold hover:bg-gray-800 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Copiar Código PIX
                    </button>
                </div>

                <!-- Expiration -->
                <div class="text-center mb-4">
                    <p class="text-sm text-gray-600">
                        ⏰ Este PIX expira em: <strong class="text-gray-900">{{ formatDate(pix_expires_at) }}</strong>
                    </p>
                </div>

                <!-- Instructions -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Como pagar:</h4>
                    <ol class="text-sm text-gray-700 space-y-1 list-decimal list-inside">
                        <li>Abra o app do seu banco</li>
                        <li>Vá na opção PIX</li>
                        <li>Escolha "Pix Copia e Cola"</li>
                        <li>Cole o código copiado</li>
                        <li>Confirme o pagamento</li>
                    </ol>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <button @click="checkPaymentStatus"
                    class="w-full bg-black text-white py-4 rounded-2xl font-semibold hover:bg-gray-800 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Verificar Status do Pagamento
                </button>

                <p class="text-center text-sm text-gray-600">
                    Após o pagamento, o status será atualizado automaticamente
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    participant: {
        type: Object,
        required: true
    },
    pix_code: {
        type: String,
        required: true
    },
    pix_expires_at: {
        type: String,
        required: true
    }
})

const statusClass = computed(() => {
    return props.participant.payment_status === 'paid' ? 'bg-green-100 text-green-800' :
        props.participant.payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
            'bg-red-100 text-red-800'
})

const statusText = computed(() => {
    return props.participant.payment_status === 'paid' ? 'Pago' :
        props.participant.payment_status === 'pending' ? 'Pendente' :
            'Falhou'
})

const checkPaymentStatus = async () => {
    try {
        const response = await fetch(window.route('payment.status', props.participant.id))

        if (!response.ok) {
            throw new Error('Erro na resposta do servidor')
        }

        const data = await response.json()

        if (data.paid) {
            router.visit(window.route('payment.success', props.participant.id))
        } else {
            alert('Pagamento ainda não confirmado. Tente novamente em alguns instantes.')
        }
    } catch (error) {
        console.error('Payment status check error:', error)
        alert('Erro ao verificar status do pagamento. Tente novamente.')
    }
}

const copyPixCode = () => {
    navigator.clipboard.writeText(props.pix_code)
    alert('Código PIX copiado para a área de transferência!')
}

const goBack = () => {
    window.history.back()
}

const generateQrCode = (pixCode) => {
    return `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(pixCode)}&format=png&margin=10`
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
