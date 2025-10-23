<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-md mx-auto bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="bg-green-600 p-6 text-white text-center">
                <h1 class="text-2xl font-bold mb-2">Finalizar Inscrição</h1>
                <p class="text-green-100">{{ participant.event.name }}</p>
            </div>

            <div class="p-6">
                <!-- Informações do Participante -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Seus Dados</h2>
                    <div class="space-y-2 text-sm">
                        <p><span class="font-medium">Nome:</span> {{ participant.full_name }}</p>
                        <p><span class="font-medium">Telefone:</span> {{ participant.phone }}</p>
                        <p><span class="font-medium">Valor:</span> R$ {{ participant.payment_amount }}</p>
                    </div>
                </div>

                <!-- QR Code PIX -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Pagamento via PIX</h2>
                    <div class="bg-gray-100 rounded-lg p-4 text-center">
                        <div
                            class="w-48 h-48 bg-white mx-auto mb-4 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-lg">
                            <span class="text-gray-500 text-sm">QR Code apareceria aqui</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">Escaneie o QR Code com seu app bancário</p>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Código PIX (Copie e
                                Cole)</label>
                            <div class="flex">
                                <input type="text" :value="pix_code" readonly
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg text-sm">
                                <button @click="copyPixCode"
                                    class="bg-green-600 text-white px-4 py-2 rounded-r-lg hover:bg-green-700 transition-colors">
                                    Copiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instruções -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-yellow-800 mb-2">Instruções Importantes</h3>
                    <ul class="text-sm text-yellow-700 space-y-1">
                        <li>• O pagamento é processado automaticamente</li>
                        <li>• Você receberá uma confirmação por WhatsApp</li>
                        <li>• O local será revelado após a confirmação do pagamento</li>
                        <li>• Este PIX expira em 24 horas</li>
                    </ul>
                </div>

                <!-- Botões -->
                <div class="flex justify-between">
                    <Link :href="route('events.public.show', participant.event.slug)"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                    Voltar
                    </Link>
                    <button @click="checkPaymentStatus"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors">
                        Já Paguei
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
    participant: {
        type: Object,
        required: true
    },
    pix_code: {
        type: String,
        default: '00020126580014br.gov.bcb.pix0136mock123520400005303986540510.005802BR5913BROTAAI PLAT6008BRASILIA62070503***6304A1B2'
    },
    pix_expires_at: {
        type: String,
        default: '24/12/2024 20:00'
    }
})

const copyPixCode = () => {
    navigator.clipboard.writeText(props.pix_code)
    alert('Código PIX copiado! Cole no seu app bancário.')
}

const checkPaymentStatus = () => {
    router.get(route('payment.status', props.participant.id))
}
</script>
