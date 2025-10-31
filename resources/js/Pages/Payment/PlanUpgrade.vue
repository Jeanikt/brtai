<!-- resources/js/Pages/Payment/PlanUpgrade.vue -->
<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-md mx-auto px-4">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Upgrade para Pro</h1>
                <p class="text-gray-600 mt-2">Desbloqueie todos os recursos</p>
            </div>

            <!-- Plan Details -->
            <div class="bg-white rounded-3xl shadow-sm p-6 mb-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-semibold text-gray-900">Plano Pro</h3>
                        <p class="text-sm text-gray-600">Recursos ilimitados</p>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">
                        R$ {{ amount }}
                    </span>
                </div>

                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Eventos ilimitados
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Participantes ilimitados
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Taxa reduzida (5.5% + R$0,80)
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Analytics avançados
                    </li>
                </ul>
            </div>

            <!-- PIX Payment -->
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
                <div class="bg-gray-50 rounded-2xl p-4 mb-4">
                    <div class="aspect-square max-w-xs mx-auto">
                        <img v-if="qrCodeUrl" :src="qrCodeUrl" alt="QR Code PIX" class="w-full h-full object-contain">
                        <div v-else class="w-full h-full bg-gray-200 rounded-lg flex items-center justify-center">
                            <span class="text-gray-400">Carregando QR Code...</span>
                        </div>
                    </div>
                </div>

                <!-- PIX Code -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Código PIX (Copie e Cole)</label>
                    <div class="flex gap-2">
                        <input :value="pix_code" type="text" readonly
                            class="flex-1 px-4 py-3 bg-gray-50 border border-gray-300 rounded-2xl text-sm font-mono">
                        <button @click="copyPixCode"
                            class="px-4 py-3 bg-gray-100 text-gray-700 rounded-2xl font-semibold hover:bg-gray-200 transition-colors">
                            {{ copyButtonText }}
                        </button>
                    </div>
                </div>

                <!-- Timer -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-yellow-800">Tempo restante</span>
                        <span class="text-lg font-bold text-yellow-800">{{ formatTime(remainingTime) }}</span>
                    </div>
                    <div class="w-full bg-yellow-200 rounded-full h-2 mt-2">
                        <div class="bg-yellow-600 h-2 rounded-full transition-all duration-1000"
                            :style="{ width: timerPercentage + '%' }"></div>
                    </div>
                </div>
            </div>

            <!-- Status Check -->
            <div class="text-center">
                <button @click="checkPaymentStatus" :disabled="isChecking"
                    class="w-full bg-black text-white py-4 px-4 rounded-2xl font-semibold hover:bg-gray-800 transition-colors disabled:opacity-50">
                    {{ isChecking ? 'Verificando...' : 'Já paguei, verificar status' }}
                </button>
                <p class="text-sm text-gray-500 mt-3">A confirmação é automática</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    transaction: Object,
    pix_code: String,
    pix_expires_at: String,
    plan_type: String,
    amount: Number
})

const copyButtonText = ref('Copiar')
const isChecking = ref(false)
const remainingTime = ref(0)
const checkInterval = ref(null)
const statusCheckInterval = ref(null)

// Gerar QR Code
const qrCodeUrl = computed(() => {
    if (!props.pix_code) return null
    return `https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=${encodeURIComponent(props.pix_code)}`
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
        const response = await fetch(route('settings.check-upgrade-status', props.transaction.gateway_transaction_id))
        const data = await response.json()

        if (data.paid) {
            router.visit(route('settings.upgrade-success'))
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
            const response = await fetch(route('settings.check-upgrade-status', props.transaction.gateway_transaction_id))
            const data = await response.json()

            if (data.paid) {
                clearInterval(statusCheckInterval.value)
                router.visit(route('settings.upgrade-success'))
            }
        } catch (error) {
            console.error('Erro na verificação automática:', error)
        }
    }, 5000)
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
