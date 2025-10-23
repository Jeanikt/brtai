<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Meus Eventos</h1>
            <Link :href="route('events.create')"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
            Criar Evento
            </Link>
        </div>

        <div v-if="events.length > 0" class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                <li v-for="event in events" :key="event.id">
                    <Link :href="route('events.show', event.id)" class="block hover:bg-gray-50">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img v-if="event.header_image_url" class="h-12 w-12 rounded-lg object-cover"
                                        :src="event.header_image_url" alt="">
                                    <div v-else
                                        class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-400 text-sm">Sem imagem</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-indigo-600 truncate">{{ event.name }}</div>
                                    <div class="text-sm text-gray-500">{{ event.event_date }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-900">{{ event.confirmed_count }} confirmados</div>
                                <div class="text-sm text-gray-500">R$ {{ event.total_revenue }}</div>
                            </div>
                        </div>
                    </div>
                    </Link>
                </li>
            </ul>
        </div>
        <div v-else class="bg-white shadow rounded-lg p-6 text-center">
            <p class="text-gray-500">Nenhum evento criado ainda.</p>
            <Link :href="route('events.create')"
                class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
            Criar Primeiro Evento
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    events: Array
})
</script>
