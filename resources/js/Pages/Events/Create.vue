<template>
    <AuthenticatedLayout>
        <div class="space-y-6 max-w-2xl mx-auto px-4">
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

            <UpgradeProBanner v-if="user_plan === 'freemium'" title="Maximize Seus Ganhos com o Plano Pro!"
                :estimated-participants="participantsNumber" :ticket-price="ticketPriceNumber" :show-savings="true"
                class="mb-6" />

            <SimpleRevenueCalculator :user_plan="user_plan" :participants="participantsNumber"
                :ticket-price="ticketPriceNumber" />

            <form @submit.prevent="submit" class="space-y-4">
                <div class="relative">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                    </div>
                    <input v-model="form.name" type="text" placeholder="Título do Evento" required :class="[
                        'w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900 placeholder-gray-400',
                        form.errors.name ? 'ring-2 ring-red-500' : ''
                    ]" />
                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1 ml-12">{{ form.errors.name }}</div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input v-model="form.event_date" type="date" required :class="[
                            'w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900',
                            form.errors.event_date ? 'ring-2 ring-red-500' : ''
                        ]" />
                        <div v-if="form.errors.event_date" class="text-red-500 text-xs mt-1 ml-12">{{
                            form.errors.event_date }}</div>
                    </div>

                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <input v-model="form.event_time" type="time" required :class="[
                            'w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900',
                            form.errors.event_time ? 'ring-2 ring-red-500' : ''
                        ]" />
                        <div v-if="form.errors.event_time" class="text-red-500 text-xs mt-1 ml-12">{{
                            form.errors.event_time }}</div>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <input v-model="form.location" type="text" placeholder="Local" required :class="[
                        'w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900 placeholder-gray-400',
                        form.errors.location ? 'ring-2 ring-red-500' : ''
                    ]" />
                    <div v-if="form.errors.location" class="text-red-500 text-xs mt-1 ml-12">{{ form.errors.location }}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <input v-model="form.price" type="number" step="0.01" placeholder="Valor por Pessoa" required
                            @input="updateCalculations" :class="[
                                'w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900 placeholder-gray-400',
                                form.errors.price ? 'ring-2 ring-red-500' : ''
                            ]" />
                        <div v-if="form.errors.price" class="text-red-500 text-xs mt-1 ml-12">{{ form.errors.price }}
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <input v-model="form.max_participants" type="number" placeholder="Convidados"
                            :max="user_plan === 'freemium' ? 70 : 500" @input="updateCalculations" :class="[
                                'w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900 placeholder-gray-400',
                                form.errors.max_participants ? 'ring-2 ring-red-500' : ''
                            ]" />
                        <div v-if="user_plan === 'freemium' && participantsNumber > 70"
                            class="absolute right-4 top-1/2 -translate-y-1/2">
                            <ProBadge />
                        </div>
                    </div>
                </div>

                <ParticipantLimitWarning v-if="user_plan === 'freemium' && form.max_participants"
                    :current-participants="participantsNumber" :max-participants="70" :ticket-price="ticketPriceNumber"
                    :user-plan="user_plan" />

                <div class="relative bg-gray-100 rounded-2xl p-10 text-center border-2 border-dashed border-gray-300 hover:border-gray-400 transition-colors cursor-pointer"
                    @click="fileInput?.click()">
                    <input ref="fileInput" type="file" accept="image/*" @change="handleFileUpload" class="hidden" />
                    <div v-if="!imagePreview" class="space-y-3">
                        <svg class="w-12 h-12 text-gray-400 mx-auto" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
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

                <button type="submit" :disabled="form.processing"
                    class="w-full bg-black text-white py-4 rounded-full font-bold text-base hover:bg-gray-800 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                    <span>{{ form.processing ? 'Criando...' : 'Criar Evento' }}</span>
                    <svg v-if="!form.processing" class="w-5 h-5 text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else class="w-5 h-5 text-green-400 animate-spin" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 2v4m0 12v4m8-10h-4M6 12H2" />
                    </svg>
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import UpgradeProBanner from '@/Components/UpgradeProBanner.vue'
import ParticipantLimitWarning from '@/Components/ParticipantLimitWarning.vue'
import ProBadge from '@/Components/ProBadge.vue'
import SimpleRevenueCalculator from '@/Components/SimpleRevenueCalculator.vue'

interface Props {
    user_plan: string
}

const props = defineProps<Props>()

interface EventForm {
    name: string
    event_date: string
    event_time: string
    location: string
    price: string
    max_participants: string
    header_image: File | null
}

const form = useForm<EventForm>({
    name: '',
    event_date: '',
    event_time: '',
    location: '',
    price: '',
    max_participants: '',
    header_image: null
})

const imagePreview = ref<string | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const participantsNumber = computed(() => {
    return parseInt(form.max_participants) || 0
})

const ticketPriceNumber = computed(() => {
    return parseFloat(form.price) || 0
})

const goBack = () => router.visit('/dashboard')

const handleFileUpload = (e: Event) => {
    const target = e.target as HTMLInputElement
    const file = target.files?.[0]
    if (!file) return

    if (file.size > 5 * 1024 * 1024) {
        alert('Arquivo muito grande. O tamanho máximo é 5MB.')
        return
    }

    form.header_image = file
    const reader = new FileReader()
    reader.onload = (ev) => (imagePreview.value = ev.target?.result as string)
    reader.readAsDataURL(file)
}

const removeImage = () => {
    form.header_image = null
    imagePreview.value = null
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

const updateCalculations = () => {
}

const submit = () => {
    if (!form.name || !form.event_date || !form.event_time || !form.location || !form.price) {
        alert('Por favor, preencha todos os campos obrigatórios.')
        return
    }

    form.post('/events', {
        forceFormData: true,
        onSuccess: () => {
            router.visit('/dashboard', { preserveScroll: true })
        },
        onError: (errors) => {
            console.error('Erros do formulário:', errors)
        }
    })
}
</script>
