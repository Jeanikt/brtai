<template>
    <div class="max-w-md mx-auto bg-white shadow-lg rounded-lg overflow-hidden mt-10">
        <div class="px-6 py-4 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-2xl font-bold mb-2">Pagamento Confirmado!</h1>
            <p class="text-gray-600 mb-6">Sua inscrição para <strong>{{ event.name }}</strong> foi confirmada com
                sucesso!</p>

            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                <h2 class="font-semibold mb-2">Detalhes do Evento</h2>
                <p class="text-sm text-gray-700"><strong>Data:</strong> {{ event.event_date }}</p>
                <p v-if="location" class="text-sm text-gray-700"><strong>Local:</strong> {{ location }}</p>
                <p v-if="whatsapp_group" class="text-sm text-gray-700"><strong>Grupo WhatsApp:</strong> {{
                    whatsapp_group }}</p>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                <p class="text-sm text-green-800">
                    Você receberá um lembrete no WhatsApp um dia antes do evento. Te veemos lá! 🎉
                </p>
            </div>

            <Link :href="route('events.public.show', event.slug)"
                class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
            Voltar para a Página do Evento
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    event: Object,
    location: String,
    whatsapp_group: String
})
</script>
