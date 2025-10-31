<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-8 px-4">
        <div class="max-w-md mx-auto w-full">
            <div class="bg-white rounded-3xl shadow-sm p-8 text-center">
                <!-- Success Icon -->
                <div class="w-20 h-20 bg-black rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-2">Pagamento Confirmado!</h1>
                <p class="text-gray-600 mb-8">Sua inscrição no evento foi confirmada com sucesso.</p>

                <!-- Event Details -->
                <div class="bg-gray-50 rounded-2xl p-6 mb-6 text-left">
                    <h2 class="font-semibold text-gray-900 mb-4">Detalhes da Inscrição</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Evento:</span>
                            <span class="font-medium text-gray-900">{{ event.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Participante:</span>
                            <span class="font-medium text-gray-900">{{ participant.full_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ingresso:</span>
                            <span class="font-medium text-gray-900">{{ participant.price_tier.name }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200">
                            <span class="text-gray-600">Valor:</span>
                            <span class="font-semibold text-gray-900">R$ {{ participant.price_tier.price }}</span>
                        </div>
                    </div>

                    <div v-if="location" class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Local do Evento:</p>
                                <p class="text-sm font-medium text-gray-900">{{ location }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <a :href="`https://wa.me/?text=Confirmei minha inscrição no evento ${event.name}!`" target="_blank"
                        class="w-full bg-green-600 text-white py-4 px-4 rounded-2xl font-semibold hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                        </svg>
                        Compartilhar no WhatsApp
                    </a>

                    <button @click="downloadTicket"
                        class="w-full bg-black text-white py-4 px-4 rounded-2xl font-semibold hover:bg-gray-800 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Baixar Ingresso
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    participant: Object,
    event: Object,
    location: String
})

const downloadTicket = () => {
    alert('Funcionalidade de download do ingresso em desenvolvimento')
}
</script>
