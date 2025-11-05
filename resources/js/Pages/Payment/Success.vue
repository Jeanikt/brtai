<template>
    <div class="min-h-screen bg-white">
        <header class="border-b border-gray-200 bg-white">
            <div class="max-w-4xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center">
                            <ApplicationLogo fill="black" class="w-10 h-10" />
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900">{{ event.name }}</h1>
                            <p class="text-xs text-gray-600">Inscrição Confirmada</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-600">Organizador</p>
                        <p class="text-sm font-semibold text-gray-900 truncate max-w-[120px]">
                            {{ event.organizer.full_name }}
                        </p>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-2xl mx-auto px-4 py-8">
            <!-- Card de Sucesso -->
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
                <!-- Header com gradiente -->
                <div
                    class="relative h-32 bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center">
                    <div class="text-center text-white">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold">Inscrição Confirmada!</h2>
                        <p class="text-green-100 mt-1">Você está na lista do evento</p>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Mensagem de Parabéns -->
                    <div class="text-center">
                        <p class="text-gray-700 text-lg">
                            🎉 Parabéns <strong>{{ participant.full_name }}</strong>!
                            Sua inscrição foi confirmada com sucesso.
                        </p>
                        <p class="text-gray-600 mt-2">
                            Prepare-se para uma experiência incrível!
                        </p>
                    </div>

                    <!-- Detalhes do Evento -->
                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex items-center gap-3 p-4 bg-gray-100 rounded-2xl">
                            <div class="w-8 h-8 bg-black rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-gray-600 mb-1">Data e Hora</p>
                                <p class="text-sm font-semibold text-gray-900">{{ formatDate(event.event_date) }}</p>
                            </div>
                        </div>

                        <!-- Local Revelado -->
                        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
                            <div
                                class="w-8 h-8 bg-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-emerald-600 mb-1 font-semibold">📍 Local do Evento</p>
                                <p class="text-sm font-bold text-gray-900">{{ event.location }}</p>
                                <p class="text-xs text-emerald-600 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Agora revelado após sua confirmação
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-4 bg-gray-100 rounded-2xl">
                            <div class="w-8 h-8 bg-black rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-gray-600 mb-1">Seu Ingresso</p>
                                <p class="text-sm font-semibold text-gray-900">{{ participant.full_name }}</p>
                                <p class="text-xs text-gray-500 mt-1">Confirme sua identidade no local</p>
                            </div>
                        </div>
                    </div>

                    <!-- Informações Adicionais -->
                    <div class="bg-blue-50 rounded-2xl p-4 border border-blue-200">
                        <h3 class="text-sm font-semibold text-blue-900 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Informações Importantes
                        </h3>
                        <ul class="text-blue-800 text-sm space-y-1">
                            <li>• Chegue com 30 minutos de antecedência</li>
                            <li>• Apresente seu documento com foto</li>
                            <li>• Este ingresso é pessoal e intransferível</li>
                            <li v-if="event.rules">• {{ event.rules }}</li>
                        </ul>
                    </div>

                    <!-- Compartilhar -->
                    <div class="text-center pt-4 border-t border-gray-200">
                        <h4 class="text-base font-semibold text-gray-900 mb-3">📣 Compartilhe este evento</h4>
                        <div class="flex justify-center gap-3">
                            <button @click="shareWhatsApp"
                                class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center hover:bg-gray-200 transition-colors">
                                <span class="text-gray-700 text-lg">📱</span>
                            </button>
                            <button @click="shareFacebook"
                                class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center hover:bg-gray-200 transition-colors">
                                <span class="text-gray-700 font-semibold">f</span>
                            </button>
                            <button @click="copyLink"
                                class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center hover:bg-gray-200 transition-colors">
                                <span class="text-gray-700">🔗</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botão Voltar para o Evento -->
            <div class="text-center">
                <a :href="eventUrl"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-black text-white rounded-full font-semibold hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Voltar para a página do evento
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'

const props = defineProps({
    participant: {
        type: Object,
        required: true
    },
    event: {
        type: Object,
        required: true
    }
})

onMounted(() => {
    console.log('Payment Success component mounted')
    console.log('Participant:', props.participant)
    console.log('Event:', props.event)
})

const formatDate = (dateString) => {
    if (!dateString) return 'Data não definida'
    const date = new Date(dateString)
    return date.toLocaleString('pt-BR', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const eventUrl = computed(() => {
    return `/e/${props.event.slug}`
})

const shareWhatsApp = () => {
    const text = `Acabei de me inscrever no evento: ${props.event.name} - ${window.location.origin}${eventUrl.value}`
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank')
}

const shareFacebook = () => {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.origin + eventUrl.value)}`, '_blank')
}

const copyLink = () => {
    navigator.clipboard.writeText(window.location.origin + eventUrl.value)
    alert('Link copiado para a área de transferência!')
}
</script>
