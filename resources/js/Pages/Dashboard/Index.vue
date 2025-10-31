<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="space-y-2">
                <h1 class="text-2xl font-bold text-gray-900">Meus eventos</h1>
                <p class="text-sm text-gray-600">Gerenciar suas resenhas por aqui.</p>
            </div>

            <!-- Search and Actions Bar -->
            <div class="flex items-center gap-3">
                <!-- Search Icon -->
                <button
                    class="flex items-center justify-center w-10 h-10 rounded-full bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>

                <!-- Criar Evento Button -->
                <Link :href="route('events.create')"
                    class="flex-1 flex items-center justify-center bg-black text-white px-6 py-2.5 rounded-full font-semibold hover:bg-gray-800 transition-colors text-sm">
                Criar Evento
                </Link>

                <!-- Filtrar Button -->
                <button @click="showFilterDropdown = !showFilterDropdown"
                    class="relative flex items-center gap-2 px-4 py-2.5 rounded-full bg-white border border-gray-900 hover:bg-gray-50 transition-colors">
                    <span class="text-sm font-medium text-gray-900">Filtrar Por</span>
                    <svg class="w-4 h-4 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>

                    <!-- Filter Dropdown -->
                    <div v-if="showFilterDropdown"
                        class="absolute top-full right-0 mt-2 w-56 bg-white rounded-2xl shadow-lg border border-gray-200 py-2 z-10">
                        <button @click="filterEvents('all')"
                            class="w-full text-left px-4 py-2.5 hover:bg-gray-50 text-sm text-gray-900">
                            Todos os Eventos
                        </button>
                        <button @click="filterEvents('active')"
                            class="w-full text-left px-4 py-2.5 hover:bg-gray-50 text-sm text-gray-900">
                            Eventos em Andamento
                        </button>
                        <button @click="filterEvents('finished')"
                            class="w-full text-left px-4 py-2.5 hover:bg-gray-50 text-sm text-gray-900">
                            Eventos Finalizados
                        </button>
                        <button @click="filterEvents('cancelled')"
                            class="w-full text-left px-4 py-2.5 hover:bg-gray-50 text-sm text-gray-900">
                            Eventos Cancelados
                        </button>
                    </div>
                </button>
            </div>

            <!-- Added upgrade banner for free users -->
            <UpgradeProBanner v-if="user_plan === 'freemium' && events.length > 0" :estimated-participants="100"
                :ticket-price="30" :dismissible="true" @dismiss="dismissUpgradeBanner" class="mb-6" />

            <!-- Added plan limit warning when approaching limit -->
            <div v-if="user_plan === 'freemium' && active_events_count >= 1"
                class="bg-red-50 border-2 border-red-400 rounded-2xl p-4 mb-6">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-red-400 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-red-900 mb-1">Limite de Eventos Atingido!</h4>
                        <p class="text-sm text-red-800 mb-3">
                            Você já tem <strong>{{ active_events_count }} evento ativo</strong> no plano Free.
                            Para criar mais eventos, faça upgrade para o plano Pro e tenha até <strong>10 eventos
                                simultâneos</strong>!
                        </p>
                        <Link :href="route('settings.billing')"
                            class="inline-block bg-black text-yellow-400 py-2 px-4 rounded-full font-bold text-sm hover:bg-gray-800 transition-colors">
                        Fazer Upgrade Agora
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Events List -->
            <div v-if="events.length > 0" class="space-y-4">
                <div v-for="event in events" :key="event.id"
                    class="relative bg-white rounded-[32px] overflow-hidden shadow-sm">
                    <!-- Event Card with Background Image -->
                    <div class="relative h-[280px] p-6 flex flex-col justify-between"
                        :style="event.header_image_url ? `background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6)), url('${event.header_image_url}'); background-size: cover; background-position: center;` : 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);'">

                        <!-- Event Title and Status -->
                        <div>
                            <h3 class="text-white text-xl font-bold mb-1">
                                {{ event.name }}
                                <span v-if="event.status === 'draft'" class="text-sm font-normal">(Em breve)</span>
                            </h3>

                            <!-- Event Details -->
                            <div class="space-y-1 text-white text-sm">
                                <p>{{ formatEventDate(event.event_date) }}</p>
                                <p>{{ event.location }}</p>
                                <p>R$ {{ event.price || '10,00' }} por pessoa</p>
                                <p>+{{ event.confirmed_count || 0}} Convidados</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col gap-2">
                            <Link :href="route('events.show', event.id)"
                                class="bg-white text-black px-4 py-2.5 rounded-full font-semibold text-sm hover:bg-gray-100 transition-colors text-center">
                            Mais Informações
                            </Link>
                            <Link :href="route('events.edit', event.id)"
                                class="bg-white text-black px-4 py-2.5 rounded-full font-semibold text-sm hover:bg-gray-100 transition-colors text-center">
                            Editar Evento
                            </Link>
                        </div>

                        <!-- Icon Actions (Top Right) -->
                        <div class="absolute top-6 right-6 flex flex-col gap-3">
                            <button @click="openShareModal(event)"
                                class="w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors shadow-md">
                                <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                            </button>
                            <button @click="openDeleteModal(event)"
                                class="w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors shadow-md">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add New Event Card -->
                <div class="bg-gray-100 rounded-[32px] h-[200px] flex items-center justify-center hover:bg-gray-200 transition-colors cursor-pointer"
                    @click="route.visit(route('events.create'))">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="space-y-4">
                <!-- Added upgrade banner in empty state for free users -->
                <UpgradeProBanner v-if="user_plan === 'freemium'" title="Comece com o Pé Direito!"
                    description="Crie seu primeiro evento e veja como é fácil. Com o plano Pro, você pode criar eventos ilimitados!"
                    :show-savings="false" class="mb-6" />

                <div class="text-center py-12 bg-white rounded-[32px]">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">🎪</span>
                    </div>
                    <p class="text-gray-600 mb-4">Nenhum evento criado ainda</p>
                    <Link :href="route('events.create')"
                        class="inline-flex items-center bg-black text-white px-6 py-3 rounded-full font-semibold hover:bg-gray-800 transition-colors">
                    <span class="text-lg mr-2">+</span>
                    Criar Primeiro Evento
                    </Link>
                </div>

                <!-- Add New Event Card -->
                <div class="bg-gray-100 rounded-[32px] h-[200px] flex items-center justify-center hover:bg-gray-200 transition-colors cursor-pointer"
                    @click="route.visit(route('events.create'))">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Teleport to="body">
            <div v-if="showDeleteModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="showDeleteModal = false">
                <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-xl">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Deseja Excluir este evento?</h3>
                    <div class="flex gap-3 mt-6">
                        <button @click="deleteEvent"
                            class="flex-1 bg-red-600 text-white px-6 py-3 rounded-full font-semibold hover:bg-red-700 transition-colors">
                            Excluir
                        </button>
                        <button @click="showDeleteModal = false"
                            class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-full font-semibold hover:bg-gray-300 transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Share Modal -->
        <Teleport to="body">
            <div v-if="showShareModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="showShareModal = false">
                <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-xl relative">
                    <button @click="showShareModal = false"
                        class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Compartilhe seu evento</h3>
                    <div class="flex justify-center gap-6">
                        <button @click="shareToInstagram"
                            class="flex flex-col items-center gap-2 hover:opacity-80 transition-opacity">
                            <div
                                class="w-14 h-14 rounded-full bg-gradient-to-br from-purple-600 via-pink-600 to-orange-500 flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg>
                            </div>
                        </button>
                        <button @click="shareToWhatsApp"
                            class="flex flex-col items-center gap-2 hover:opacity-80 transition-opacity">
                            <div class="w-14 h-14 rounded-full bg-green-500 flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                </svg>
                            </div>
                        </button>
                        <button @click="shareToTikTok"
                            class="flex flex-col items-center gap-2 hover:opacity-80 transition-opacity">
                            <div class="w-14 h-14 rounded-full bg-black flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import UpgradeProBanner from '@/Components/UpgradeProBanner.vue'

defineProps({
    events: {
        type: Array,
        default: () => []
    },
    user_plan: {
        type: String,
        default: 'freemium'
    },
    active_events_count: {
        type: Number,
        default: 0
    },
    can_create_event: {
        type: Boolean,
        default: true
    }
})

const showDeleteModal = ref(false)
const showShareModal = ref(false)
const showFilterDropdown = ref(false)
const selectedEvent = ref(null)

const formatEventDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: 'long'
    }) + ' às ' + date.toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit'})
}

const openDeleteModal = (event) => {
    selectedEvent.value = event
    showDeleteModal.value = true
}

const openShareModal = (event) => {
    selectedEvent.value = event
    showShareModal.value = true
}

const deleteEvent = () => {
    if (selectedEvent.value) {
        router.delete(route('events.destroy', selectedEvent.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false
                selectedEvent.value = null
                router.reload({ only: ['dashboard'] })
            }
        })
    }
}

const filterEvents = (filter) => {
    showFilterDropdown.value = false
    route.get(route('dashboard', { filter }))
}

const shareToInstagram = () => {
    window.open(`https://www.instagram.com/`, '_blank')
}

const shareToWhatsApp = () => {
    if (selectedEvent.value) {
        const url = window.location.origin + '/eventos/' + selectedEvent.value.slug
        const text = `Confira este evento: ${selectedEvent.value.name}`
        window.open(`https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`, '_blank')
    }
}

const shareToTikTok = () => {
    window.open(`https://www.tiktok.com/`, '_blank')
}

const dismissUpgradeBanner = () => {
    // Could store in localStorage to persist dismissal
    localStorage.setItem('upgrade_banner_dismissed', 'true')
}
</script>
