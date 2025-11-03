<template>
    <AuthenticatedLayout>
        <div class="space-y-6 max-w-4xl mx-auto px-4">
            <!-- Header Compacto -->
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold text-gray-900">Meus Eventos</h1>
                    <p class="text-sm text-gray-600">{{ events.length }} evento{{ events.length !== 1 ? 's' : '' }}
                        criado{{ events.length !== 1 ? 's' : '' }}</p>
                </div>

                <!-- Criar Evento Button - Condicional baseado no plano -->
                <Link v-if="can_create_event" :href="route('events.create')"
                    class="flex items-center gap-2 bg-black text-white px-6 py-3 rounded-full font-semibold hover:bg-gray-800 transition-colors text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Criar Evento
                </Link>

                <!-- Botão desabilitado para free users no limite -->
                <button v-else disabled
                    class="flex items-center gap-2 bg-gray-300 text-gray-500 px-6 py-3 rounded-full font-semibold text-sm cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Limite Atingido
                </button>
            </div>

            <!-- Banner de Upgrade para Free Users -->
            <UpgradeProBanner v-if="user_plan === 'freemium'" title="Desbloqueie todo o potencial!"
                :estimated-participants="100" :ticket-price="30" :show-savings="true" :dismissible="true"
                @dismiss="dismissUpgradeBanner" />

            <!-- Alerta de Limite para Free Users -->
            <div v-if="user_plan === 'freemium' && !can_create_event"
                class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-2xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-red-800">
                            <strong>Limite do plano Free atingido!</strong> Você tem {{ active_events_count }} evento
                            ativo.
                            <Link :href="route('settings.billing')" class="underline font-semibold hover:text-red-900">
                            Faça upgrade para criar mais eventos
                            </Link>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Barra de Filtros Compacta -->
            <div class="flex items-center gap-3 bg-white rounded-2xl p-3 border border-gray-200">
                <div class="relative flex-1 max-w-xs">
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" placeholder="Buscar eventos..."
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 rounded-xl border-0 text-sm focus:ring-2 focus:ring-gray-200" />
                </div>

                <div class="relative">
                    <button @click="showFilterDropdown = !showFilterDropdown"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors text-sm">
                        <span class="text-gray-700">Filtrar</span>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown de Filtros -->
                    <div v-if="showFilterDropdown"
                        class="absolute top-full right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-10">
                        <button v-for="filter in filters" :key="filter.value" @click="filterEvents(filter.value)"
                            class="w-full text-left px-4 py-2 hover:bg-gray-50 text-sm text-gray-700">
                            {{ filter.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Grid de Eventos -->
            <div v-if="events.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Cards de Evento Existentes -->
                <div v-for="event in events" :key="event.id"
                    class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                    <!-- Imagem do Evento -->
                    <div class="h-32 bg-cover bg-center relative"
                        :style="event.header_image_url ? `background-image: url('${event.header_image_url}')` : 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)'">
                        <!-- Badge de Status -->
                        <div class="absolute top-3 left-3">
                            <span :class="{
                                'bg-green-500': event.status === 'active',
                                'bg-yellow-500': event.status === 'draft',
                                'bg-gray-500': event.status === 'finished',
                                'bg-red-500': event.status === 'cancelled'
                            }" class="px-2 py-1 rounded-full text-xs text-white font-medium capitalize">
                                {{ event.status === 'active' ? 'Ativo' :
                                    event.status === 'draft' ? 'Rascunho' :
                                        event.status === 'finished' ? 'Finalizado' : 'Cancelado' }}
                            </span>
                        </div>

                        <!-- Ações Rápidas -->
                        <div class="absolute top-3 right-3 flex gap-1">
                            <button @click="openShareModal(event)"
                                class="w-8 h-8 bg-white/90 rounded-full flex items-center justify-center hover:bg-white transition-colors backdrop-blur-sm">
                                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                            </button>
                            <button @click="openDeleteModal(event)"
                                class="w-8 h-8 bg-white/90 rounded-full flex items-center justify-center hover:bg-white transition-colors backdrop-blur-sm">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Conteúdo do Card -->
                    <div class="p-4 space-y-3">
                        <!-- Título e Data -->
                        <div>
                            <h3 class="font-semibold text-gray-900 text-sm line-clamp-2 mb-1">
                                {{ event.name }}
                            </h3>
                            <p class="text-xs text-gray-500">
                                {{ formatEventDate(event.event_date) }}
                            </p>
                        </div>

                        <!-- Local e Preço -->
                        <div class="space-y-1">
                            <div class="flex items-center gap-1 text-xs text-gray-600">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="truncate">{{ event.location }}</span>
                            </div>
                            <div class="flex items-center gap-1 text-xs text-gray-600">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>R$ {{ formatPrice(event.price) }}</span>
                            </div>
                        </div>

                        <!-- Estatísticas -->
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">
                                {{ event.confirmed_count || 0 }} confirmados
                            </span>
                            <span :class="{
                                'text-green-600': (event.confirmed_count || 0) > 0,
                                'text-gray-400': (event.confirmed_count || 0) === 0
                            }">
                                {{ event.max_participants ? `${Math.round(((event.confirmed_count || 0) /
                                    event.max_participants) * 100)}%` : '0%' }}
                            </span>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="flex gap-2 pt-2">
                            <Link :href="route('events.show', event.id)"
                                class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded-lg text-xs font-medium hover:bg-gray-200 transition-colors text-center">
                            Ver
                            </Link>
                            <Link :href="route('events.edit', event.id)"
                                class="flex-1 bg-black text-white px-3 py-2 rounded-lg text-xs font-medium hover:bg-gray-800 transition-colors text-center">
                            Editar
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Card para Criar Novo Evento (apenas se permitido) -->
                <div v-if="can_create_event" @click="route.visit(route('events.create'))"
                    class="border-2 border-dashed border-gray-300 rounded-2xl flex flex-col items-center justify-center p-6 hover:border-gray-400 hover:bg-gray-50 transition-colors cursor-pointer min-h-[200px]">
                    <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-600 text-center">Criar novo evento</p>
                </div>
            </div>

            <!-- Estado Vazio -->
            <div v-else class="text-center py-12 bg-white rounded-2xl border border-gray-200">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl">🎪</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum evento criado</h3>
                <p class="text-gray-600 mb-6 max-w-sm mx-auto">
                    Comece criando seu primeiro evento e compartilhe com seus amigos!
                </p>
                <Link v-if="can_create_event" :href="route('events.create')"
                    class="inline-flex items-center gap-2 bg-black text-white px-6 py-3 rounded-full font-semibold hover:bg-gray-800 transition-colors text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Criar Primeiro Evento
                </Link>

                <!-- Mensagem para free users no limite -->
                <div v-else class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 max-w-sm mx-auto">
                    <p class="text-sm text-yellow-800">
                        Você atingiu o limite do plano Free.
                        <Link :href="route('settings.billing')" class="font-semibold underline hover:text-yellow-900">
                        Faça upgrade
                        </Link> para criar mais eventos.
                    </p>
                </div>
            </div>
        </div>

        <!-- Modais (mantidos do código original) -->
        <Teleport to="body">
            <!-- Modal de Exclusão -->
            <div v-if="showDeleteModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="showDeleteModal = false">
                <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-xl">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Excluir evento?</h3>
                    <p class="text-gray-600 text-sm mb-4">Esta ação não pode ser desfeita.</p>
                    <div class="flex gap-3">
                        <button @click="deleteEvent"
                            class="flex-1 bg-red-600 text-white px-4 py-2.5 rounded-xl font-semibold hover:bg-red-700 transition-colors text-sm">
                            Excluir
                        </button>
                        <button @click="showDeleteModal = false"
                            class="flex-1 bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl font-semibold hover:bg-gray-300 transition-colors text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal de Compartilhamento -->
            <div v-if="showShareModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="showShareModal = false">
                <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Compartilhar</h3>
                        <button @click="showShareModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex justify-center gap-4">
                        <button @click="shareToWhatsApp"
                            class="flex flex-col items-center gap-2 hover:opacity-80 transition-opacity">
                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-600">WhatsApp</span>
                        </button>
                        <button @click="shareToInstagram"
                            class="flex flex-col items-center gap-2 hover:opacity-80 transition-opacity">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-purple-600 via-pink-600 to-orange-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-600">Instagram</span>
                        </button>
                        <button @click="copyEventLink"
                            class="flex flex-col items-center gap-2 hover:opacity-80 transition-opacity">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-600">Copiar</span>
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

const showFilterDropdown = ref(false)
const showDeleteModal = ref(false)
const showShareModal = ref(false)
const selectedEvent = ref(null)

const filters = [
    { label: 'Todos os Eventos', value: 'all' },
    { label: 'Eventos Ativos', value: 'active' },
    { label: 'Rascunhos', value: 'draft' },
    { label: 'Finalizados', value: 'finished' },
    { label: 'Cancelados', value: 'cancelled' }
]

function filterEvents(status) {
    router.get(route('dashboard'), { filter: status }, { preserveState: true })
    showFilterDropdown.value = false
}

function openDeleteModal(event) {
    selectedEvent.value = event
    showDeleteModal.value = true
}

function deleteEvent() {
    if (!selectedEvent.value) return
    router.delete(route('events.destroy', selectedEvent.value.id), {
        onSuccess: () => (showDeleteModal.value = false)
    })
}

function openShareModal(event) {
    selectedEvent.value = event
    showShareModal.value = true
}

function formatEventDate(date) {
    const d = new Date(date)
    return d.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    }).replace('.', '')
}

function formatPrice(price) {
    // Garantir que o preço seja tratado como número
    const numericPrice = Number(price) || 0
    return numericPrice.toFixed(2)
}

function shareToInstagram() {
    alert('Compartilhar no Instagram (em breve!)')
}

function shareToWhatsApp() {
    if (!selectedEvent.value) return
    const url = `${window.location.origin}/e/${selectedEvent.value.slug}`
    const text = `Confira este evento: ${selectedEvent.value.name} - ${url}`
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank')
}

function copyEventLink() {
    if (!selectedEvent.value) return
    const url = `${window.location.origin}/e/${selectedEvent.value.slug}`
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copiado para a área de transferência!')
        showShareModal.value = false
    })
}

function dismissUpgradeBanner() {
    localStorage.setItem('hideUpgradeBanner', 'true')
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
