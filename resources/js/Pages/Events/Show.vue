<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Back button and header matching Figma "Mais Informações" -->
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('dashboard')"
                    class="w-10 h-10 bg-black rounded-full flex items-center justify-center hover:bg-gray-800 transition-colors">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                </Link>
            </div>

            <div class="flex justify-between items-start">
                <h1 class="text-2xl font-bold text-gray-900">Mais Informações</h1>
                <span class="text-sm text-gray-500">Restam {{ timeRemaining }}</span>
            </div>

            <div class="space-y-4">
                <!-- Event Name with URL -->
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <p class="text-sm text-gray-600 mb-2">{{ event.name }}</p>
                    <div class="flex items-center gap-2 bg-black text-white px-4 py-3 rounded-full">
                        <span class="text-sm flex-1 truncate">{{ eventUrl }}</span>
                        <button @click="copyUrl" class="flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                    <div v-if="event.header_image_url" class="mt-3 rounded-xl overflow-hidden">
                        <img :src="event.header_image_url" alt="Event" class="w-full h-32 object-cover" />
                        <p class="text-center text-sm text-gray-600 mt-2">Status: <span class="font-semibold">Aguardando
                                pagamentos</span></p>
                    </div>
                </div>

                <!-- Revenue Card -->
                <div class="bg-white rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-gray-900">Receita total</h3>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-3xl font-bold text-gray-900">R$ {{ stats.total_revenue || '30.000' }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ stats.pending_payments || 11 }} pagamentos
                                pendentes</p>
                        </div>
                    </div>
                </div>

                <!-- Participants Card -->
                <div class="bg-white rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h-5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-gray-900">Total de Convidados</h3>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.confirmed_count || '03' }} / {{
                                event.max_participants || '14' }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ stats.pending_participants || 11 }} Convidados
                                estão pendentes</p>
                        </div>
                    </div>
                </div>

                <!-- Participants List -->
                <div class="bg-white rounded-2xl p-5 shadow-sm">
                    <button @click="showParticipants = !showParticipants"
                        class="w-full flex items-center justify-between text-left">
                        <h3 class="font-semibold text-gray-900">Lista Completa de Convidados</h3>
                        <svg class="w-5 h-5 text-gray-400 transition-transform"
                            :class="{ 'rotate-180': showParticipants }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-if="showParticipants" class="mt-4 space-y-3">
                        <div v-for="participant in participants" :key="participant.id"
                            class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                                <span class="text-gray-700">{{ participant.full_name }}</span>
                            </div>
                            <button class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                        <div v-if="participants.length === 0" class="text-center py-8 text-gray-500">
                            Nenhum convidado confirmado ainda
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    event: Object,
    participants: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total_revenue: 0,
            confirmed_count: 0,
            pending_payments: 0,
            pending_participants: 0
        })
    }
})

const showParticipants = ref(false)

const eventUrl = computed(() => {
    return `brotai.com.br/eventos/${props.event.slug || 'KD13k9ak9DAK1'}`
})

const timeRemaining = computed(() => {
    const eventDate = new Date(props.event.event_date)
    const now = new Date()
    const diff = eventDate - now
    const hours = Math.floor(diff / (1000 * 60 * 60))
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
    return `${hours}h${minutes}m`
})

const copyUrl = () => {
    navigator.clipboard.writeText(`https://${eventUrl.value}`)
    alert('Link copiado!')
}
</script>
