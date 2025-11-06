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
                <div class="text-center mb-6">
                    <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Pague com PIX</h3>
                    <p class="text-sm text-gray-600">Escaneie o QR Code ou copie o código</p>
                </div>

                <!-- QR Code -->
                <div class="flex justify-center mb-6">
                    <div class="bg-white p-4 rounded-2xl border-2 border-gray-200">
                        <img v-if="pix_qr_code" :src="pix_qr_code" alt="QR Code PIX" class="w-48 h-48">
                        <div v-else class="w-48 h-48 bg-gray-200 rounded-lg flex items-center justify-center">
                            <span class="text-gray-400">Carregando QR Code...</span>
                        </div>
                    </div>
                </div>

                <!-- PIX Code -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Código PIX Copia e Cola:</label>
                    <div class="flex gap-2">
                        <input :value="pix_code" type="text" readonly
                            class="flex-1 px-4 py-3 bg-gray-50 border border-gray-300 rounded-2xl text-sm font-mono">
                        <button @click="copyPixCode"
                            class="px-4 py-3 bg-gray-100 text-gray-700 rounded-2xl font-semibold hover:bg-gray-200 transition-colors">
                            {{ copyButtonText }}
                        </button>
                    </div>
                </div>

                <!-- Expiration Timer -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-yellow-800">Tempo restante</span>
                        <span class="text-lg font-bold text-yellow-800">{{ formatTime(remainingTime) }}</span>
                    </div>
                    <div class="w-full bg-yellow-200 rounded-full h-2">
                        <div class="bg-yellow-600 h-2 rounded-full transition-all duration-1000"
                            :style="{ width: timerPercentage + '%' }"></div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
                    <h4 class="text-sm font-semibold text-blue-900 mb-2">Como pagar:</h4>
                    <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
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
                <button @click="checkPaymentStatus" :disabled="isChecking"
                    class="w-full bg-black text-white py-4 rounded-2xl font-semibold hover:bg-gray-800 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ isChecking ? 'Verificando...' : 'Já paguei, verificar status' }}
                </button>

                <p class="text-center text-sm text-gray-600">
                    Após o pagamento, o status será atualizado automaticamente
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
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
    pix_qr_code: {
        type: String,
        required: true
    },
    pix_expires_at: {
        type: String,
        required: true
    }
})

const copyButtonText = ref('Copiar')
const isChecking = ref(false)
const remainingTime = ref(0)
const checkInterval = ref(null)
const statusCheckInterval = ref(null)

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

const timerPercentage = computed(() => {
    const totalTime = 30 * 60 // 30 minutos em segundos
    return (remainingTime.value / totalTime) * 100
})

const formatTime = (seconds) => {
    const minutes = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

const copyPixCode = async () => {
    try {
        await navigator.clipboard.writeText(props.pix_code)
        copyButtonText.value = 'Copiado!'
        setTimeout(() => {
            copyButtonText.value = 'Copiar'
        }, 2000)
    } catch (err) {
        console.error('Falha ao copiar texto: ', err)
    }
}

const checkPaymentStatus = async () => {
    isChecking.value = true
    try {
        const response = await fetch(route('payment.status', props.participant.id))
        const data = await response.json()

        if (data.paid) {
            router.visit(route('payment.success', props.participant.id))
        } else {
            alert('Pagamento ainda não confirmado. Tente novamente em alguns instantes.')
        }
    } catch (error) {
        alert('Erro ao verificar status do pagamento.')
    } finally {
        isChecking.value = false
    }
}

const updateTimer = () => {
    const expiresAt = new Date(props.pix_expires_at)
    const now = new Date()
    const diff = Math.floor((expiresAt - now) / 1000)

    remainingTime.value = Math.max(0, diff)

    if (diff <= 0) {
        clearInterval(checkInterval.value)
        alert('O código PIX expirou. Por favor, recarregue a página para gerar um novo.')
    }
}

// Verificação automática do status
const startStatusCheck = () => {
    statusCheckInterval.value = setInterval(async () => {
        try {
            const response = await fetch(route('payment.status', props.participant.id))
            const data = await response.json()

            if (data.paid) {
                clearInterval(statusCheckInterval.value)
                router.visit(route('payment.success', props.participant.id))
            }
        } catch (error) {
            console.error('Erro na verificação automática:', error)
        }
    }, 5000) // Verificar a cada 5 segundos
}

const goBack = () => {
    window.history.back()
}

onMounted(() => {
    updateTimer()
    checkInterval.value = setInterval(updateTimer, 1000)
    startStatusCheck()
})

onUnmounted(() => {
    if (checkInterval.value) clearInterval(checkInterval.value)
    if (statusCheckInterval.value) clearInterval(statusCheckInterval.value)
})
</script>
