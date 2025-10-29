<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Pagamento do Ingresso</h1>
                    <div class="text-sm text-gray-500">
                        Status:
                        <span :class="statusClass" class="px-2 py-1 rounded-full text-xs font-medium">
                            {{ statusText }}
                        </span>
                    </div>
                </div>

                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h2 class="text-lg font-semibold text-blue-900 mb-2">{{ participant.event.name }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-blue-700">
                        <div>
                            <strong>Participante:</strong> {{ participant.full_name }}
                        </div>
                        <div>
                            <strong>Email:</strong> {{ participant.email || 'Não informado' }}
                        </div>
                        <div>
                            <strong>Telefone:</strong> {{ participant.phone }}
                        </div>
                        <div>
                            <strong>Ingresso:</strong> {{ participant.price_tier.name }} - R$ {{
                            participant.price_tier.price }}
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-green-800 mb-4">Pague com PIX</h3>

                    <div class="text-center mb-6">
                        <div class="bg-white p-4 rounded-lg inline-block border-2 border-green-300">
                            <img :src="generateQrCode(pix_code)" alt="QR Code PIX" class="w-48 h-48 mx-auto">
                        </div>
                    </div>

                    <div class="bg-gray-100 p-4 rounded-lg mb-4">
                        <p class="text-sm text-gray-600 mb-2 font-medium">Código PIX (copie e cole no seu app bancário):
                        </p>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <code
                                class="text-sm break-all flex-1 bg-white p-3 rounded border font-mono">{{ pix_code }}</code>
                            <button @click="copyPixCode"
                                class="bg-green-600 text-white px-4 py-3 sm:py-2 rounded-lg font-medium hover:bg-green-700 transition-colors whitespace-nowrap flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Copiar
                            </button>
                        </div>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-green-700 font-medium">
                            ⏰ Este PIX expira em: <strong>{{ formatDate(pix_expires_at) }}</strong>
                        </p>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <h4 class="text-sm font-semibold text-yellow-800 mb-2">Como pagar:</h4>
                    <ol class="text-sm text-yellow-700 list-decimal list-inside space-y-1">
                        <li>Abra o app do seu banco</li>
                        <li>Vá na opção PIX</li>
                        <li>Escolha "Pix Copia e Cola"</li>
                        <li>Cole o código copiado</li>
                        <li>Confirme o pagamento</li>
                    </ol>
                </div>

                <div class="text-center space-y-4">
                    <p class="text-gray-600">Após o pagamento, o status será atualizado automaticamente</p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button @click="checkPaymentStatus"
                            class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Verificar Status
                        </button>

                        <button @click="goBack"
                            class="bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-700 transition-colors">
                            Voltar ao Evento
                        </button>
                    </div>
                </div>
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
        const response = await fetch(route('payment.status', props.participant.id))

        if (!response.ok) {
            throw new Error('Erro na resposta do servidor')
        }

        const data = await response.json()

        if (data.paid) {
            router.visit(route('payment.success', props.participant.id))
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
