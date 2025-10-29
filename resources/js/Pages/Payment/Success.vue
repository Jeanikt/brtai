<template>
    <div class="min-h-screen bg-green-50 flex items-center justify-center py-8">
        <div class="max-w-md mx-auto px-4">
            <div class="bg-white rounded-xl shadow-md p-8 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-2">Pagamento Confirmado!</h1>
                <p class="text-gray-600 mb-6">Sua inscrição no evento foi confirmada com sucesso.</p>

                <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                    <h2 class="font-semibold text-gray-900 mb-2">Detalhes da Inscrição</h2>
                    <p><strong>Evento:</strong> {{ event.name }}</p>
                    <p><strong>Participante:</strong> {{ participant.full_name }}</p>
                    <p><strong>Ingresso:</strong> {{ participant.price_tier.name }}</p>
                    <p><strong>Valor:</strong> R$ {{ participant.price_tier.price }}</p>

                    <div v-if="location" class="mt-3 pt-3 border-t border-gray-200">
                        <p><strong>Local do Evento:</strong> {{ location }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <a :href="`https://wa.me/?text=Confirmado minha inscrição no evento ${event.name}!`" target="_blank"
                        class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors block">
                        Compartilhar no WhatsApp
                    </a>

                    <button @click="downloadTicket"
                        class="w-full bg-gray-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-gray-700 transition-colors">
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
