<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Back button and header matching Figma -->
            <div class="flex items-center gap-4 mb-6">
                <button @click="goBack"
                    class="w-10 h-10 bg-black rounded-full flex items-center justify-center hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Criar Novo Evento</h1>
                    <p class="text-sm text-gray-600 mt-1">Preencha os detalhes da sua resenha.</p>
                </div>
                <button class="text-sm text-gray-500 hover:text-gray-700">Salvar como rascunho</button>
            </div>

            <!-- Added upgrade banner for free users before form -->
            <UpgradeProBanner v-if="user_plan === 'freemium'" title="Maximize Seus Ganhos com o Plano Pro!"
                :estimated-participants="parseInt(form.max_participants) || 100"
                :ticket-price="parseFloat(form.price) || 30" :show-savings="true" class="mb-6" />

            <!-- Form with Figma styling -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Title Input -->
                <div class="relative">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                    </div>
                    <input v-model="form.name" type="text" placeholder="Título do Evento" required
                        class="w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900 placeholder-gray-400" />
                </div>

                <!-- Date and Time Row -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input v-model="form.event_date" type="date" placeholder="Data" required
                            class="w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900" />
                    </div>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <input v-model="form.event_time" type="time" placeholder="Horário" required
                            class="w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900" />
                    </div>
                </div>

                <!-- Location Input -->
                <div class="relative">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <input v-model="form.location" type="text" placeholder="Local" required
                        class="w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900 placeholder-gray-400" />
                </div>

                <!-- Price and Guests Row -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <input v-model="form.price" type="number" step="0.01" placeholder="Valor por Pessoa" required
                            class="w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900 placeholder-gray-400" />
                    </div>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <input v-model="form.max_participants" type="number" placeholder="Convidados"
                            :max="user_plan === 'freemium' ? 70 : 500" @input="checkParticipantLimit"
                            class="w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900 placeholder-gray-400" />
                        <!-- Added PRO badge for free users -->
                        <div v-if="user_plan === 'freemium' && form.max_participants > 70"
                            class="absolute right-4 top-1/2 -translate-y-1/2">
                            <ProBadge />
                        </div>
                    </div>
                </div>

                <!-- Added participant limit warning -->
                <ParticipantLimitWarning v-if="form.max_participants"
                    :current-participants="parseInt(form.max_participants)" :max-participants="70"
                    :ticket-price="parseFloat(form.price) || 30" :user-plan="user_plan" />

                <!-- Image Upload Area -->
                <div class="relative bg-gray-100 rounded-2xl p-12 text-center border-2 border-dashed border-gray-300 hover:border-gray-400 transition-colors cursor-pointer"
                    @click="$refs.fileInput.click()">
                    <input ref="fileInput" type="file" accept="image/*" @change="handleFileUpload" class="hidden" />
                    <div v-if="!imagePreview" class="space-y-3">
                        <div class="flex justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-600 font-medium">Aperte para fazer upload ou arraste</p>
                            <p class="text-sm text-gray-500 mt-1">PNG, JPG ou WEBP (MAX. 5MB)</p>
                        </div>
                    </div>
                    <div v-else class="relative">
                        <img :src="imagePreview" alt="Preview" class="max-h-48 mx-auto rounded-lg" />
                        <button type="button" @click.stop="removeImage"
                            class="absolute top-2 right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" :disabled="form.processing"
                    class="w-full bg-black text-white py-4 rounded-full font-bold text-base hover:bg-gray-800 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                    <span>Criar Evento</span>
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Success Modal -->
        <Teleport to="body">
            <div v-if="showSuccessModal"
                class="fixed inset-0 bg-white flex flex-col items-center justify-center z-50 p-4">
                <div class="text-center space-y-6 max-w-sm">
                    <div class="w-24 h-24 bg-black rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-12 h-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Seu Evento foi criado com sucesso!</h2>
                        <p class="text-gray-600">Você será redirecionado para seus Eventos Ativos em instantes.</p>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Added upgrade modal when trying to exceed limits -->
        <Teleport to="body">
            <div v-if="showUpgradeModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="showUpgradeModal = false">
                <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-xl">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Upgrade para Desbloquear!</h3>
                        <p class="text-gray-600 text-sm">
                            Você atingiu o limite de <strong>70 participantes</strong> do plano Free.
                            Faça upgrade para Pro e tenha até <strong>500 participantes</strong> por evento!
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-2xl p-4 mb-6">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Plano Free (70 pax):</span>
                                <span class="font-semibold">R$ {{ calculateEarnings(70) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="text-gray-600">Plano Pro ({{ form.max_participants }} pax):</span>
                                <span class="font-bold text-green-600">R$ {{
                                    calculateEarnings(parseInt(form.max_participants)) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2 font-bold text-green-800">
                                <span>Você ganha a mais:</span>
                                <span>+R$ {{ calculateDifference() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Link :href="route('settings.billing')"
                            class="flex-1 bg-black text-yellow-400 py-3 px-4 rounded-full font-bold text-center hover:bg-gray-800 transition-colors">
                        Fazer Upgrade
                        </Link>
                        <button @click="showUpgradeModal = false"
                            class="flex-1 bg-gray-200 text-gray-700 py-3 px-4 rounded-full font-semibold hover:bg-gray-300 transition-colors">
                            Continuar Free
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import UpgradeProBanner from '@/Components/UpgradeProBanner.vue'
import ParticipantLimitWarning from '@/Components/ParticipantLimitWarning.vue'
import ProBadge from '@/Components/ProBadge.vue'

const form = useForm({
    name: '',
    event_date: '',
    event_time: '',
    location: '',
    price: '',
    max_participants: '',
    header_image: null
})

const imagePreview = ref(null)
const showSuccessModal = ref(false)
const showUpgradeModal = ref(false)

const goBack = () => {
    router.visit('/dashboard')
}

const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.header_image = file
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const removeImage = () => {
    form.header_image = null
    imagePreview.value = null
}

const submit = () => {
    form.post('/events', {
        forceFormData: true,
        onSuccess: () => {
            showSuccessModal.value = true
            setTimeout(() => {
                router.visit('/dashboard')
            }, 2000)
        }
    })
}

const checkParticipantLimit = () => {
    const participants = parseInt(form.max_participants)
    if (participants > 70 && !showUpgradeModal.value) {
        showUpgradeModal.value = true
    }
}

const calculateEarnings = (participants) => {
    const price = parseFloat(form.price) || 30
    const grossRevenue = participants * price
    const feePerTicket = (price * 0.065) + 0.80
    const totalFees = feePerTicket * participants
    return (grossRevenue - totalFees).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })
}

const calculateDifference = () => {
    const price = parseFloat(form.price) || 30
    const participants = parseInt(form.max_participants) || 100

    const freeEarnings = 70 * price - ((price * 0.065 + 0.80) * 70)
    const proEarnings = participants * price - ((price * 0.055 + 0.80) * participants) - 19

    return (proEarnings - freeEarnings).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })
}

const user_plan = ref('freemium')
const active_events_count = ref(0)
const can_create_event = ref(true)

defineProps({
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
</script>
