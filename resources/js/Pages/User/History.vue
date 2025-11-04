<template>

    <Head title="Meu Histórico" />

    <AuthenticatedLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Meu Histórico de Eventos
            </h1>
            <p class="text-gray-600">
                Todos os eventos que você participou e pagou
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div v-if="participations.data.length === 0" class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">📊</div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">
                    Nenhum evento no histórico
                </h3>
                <p class="text-gray-500 mb-4">
                    Você ainda não participou de nenhum evento.
                </p>
                <Link :href="route('events.public.index')"
                    class="bg-[#FFFF00] text-black px-6 py-3 rounded-full font-semibold hover:bg-[#FFFF33] transition-colors">
                Explorar Eventos
                </Link>
            </div>

            <div v-else class="divide-y divide-gray-200">
                <div v-for="participation in participations.data" :key="participation.id"
                    class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                {{ participation.event_name }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <span class="text-lg mr-2">📅</span>
                                    {{ formatDate(participation.event_date) }}
                                </div>
                                <div class="flex items-center">
                                    <span class="text-lg mr-2">📍</span>
                                    {{ participation.event_location }}
                                </div>
                                <div class="flex items-center">
                                    <span class="text-lg mr-2">👤</span>
                                    {{ participation.organizer_name }}
                                </div>
                                <div class="flex items-center">
                                    <span class="text-lg mr-2">🎫</span>
                                    {{ participation.ticket_type }}
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-4">
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm font-semibold text-green-600">
                                        {{ formatPrice(participation.paid_amount) }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        Confirmado em {{ formatDateTime(participation.confirmed_at) }}
                                    </span>
                                </div>

                                <div v-if="participation.checked_in_at"
                                    class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    ✅ Check-in realizado
                                </div>
                                <div v-else
                                    class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    ⏳ Aguardando check-in
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <Pagination :links="participations.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineProps<{
    participations: {
        data: any[]
        links: any[]
    }
}>()

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
}

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatPrice = (price: number) => {
    if (price === 0) return 'Grátis'
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(price)
}
</script>
