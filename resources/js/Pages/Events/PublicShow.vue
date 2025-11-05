<template>
    <div class="min-h-screen bg-white">
        <header class="border-b border-gray-200 bg-white">
            <div class="max-w-4xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10  rounded-xl flex items-center justify-center">
                            <ApplicationLogo fill="black" class="w-10 h-10" />
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900">{{ event.name }}</h1>
                            <p class="text-xs text-gray-600">Experiência única</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-600">Organizador</p>
                        <p class="text-sm font-semibold text-gray-900 truncate max-w-[120px]">{{
                            event.organizer.full_name }}</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-2xl mx-auto px-4 py-6">
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="relative h-48 overflow-hidden">
                    <img v-if="event.header_image_url" :src="event.header_image_url" :alt="event.name"
                        class="w-full h-full object-cover" />
                    <div v-else
                        class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                        <div class="text-center text-gray-600">
                            <span class="text-4xl mb-2 block">🎉</span>
                            <p class="text-base font-semibold px-4">{{ event.name }}</p>
                        </div>
                    </div>

                    <div class="absolute top-4 right-4">
                        <span
                            class="bg-white/90 backdrop-blur-sm text-gray-900 px-3 py-1 rounded-xl font-semibold text-xs">
                            {{ event.is_free ? 'Gratuito' : 'Ingressos' }}
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-6">
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

                        <div class="flex items-center gap-3 p-4 bg-gray-100 rounded-2xl">
                            <div class="w-8 h-8 bg-black rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-gray-600 mb-1">Local</p>
                                <p class="text-sm font-semibold text-gray-900"
                                    v-if="event.location_reveal_after_payment && !hasPaid">
                                    🔒 Revelado após confirmação
                                </p>
                                <p class="text-sm font-semibold text-gray-900" v-else>
                                    📍 {{ event.location }}
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
                                <p class="text-xs text-gray-600 mb-1">Confirmados</p>
                                <p class="text-sm font-semibold text-gray-900">{{ confirmed_count }} / {{
                                    event.max_participants || '∞' }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="event.description" class="bg-gray-50 rounded-2xl p-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">📖 Sobre o Evento</h3>
                        <p class="text-gray-700 text-sm leading-relaxed">{{ event.description }}</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">🎟️ Ingressos</h3>
                        <div class="space-y-3">
                            <div v-for="tier in event.price_tiers" :key="tier.id" @click="selectTicket(tier)"
                                class="border-2 rounded-2xl p-4 transition-all duration-200 cursor-pointer" :class="selectedTier?.id === tier.id
                                    ? 'border-black bg-gray-50'
                                    : 'border-gray-200 hover:border-gray-300'">

                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="text-base font-bold text-gray-900">{{ tier.name }}</h4>
                                        <p v-if="tier.description" class="text-gray-600 text-sm mt-1">{{
                                            tier.description }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-bold text-gray-900">
                                            {{ event.is_free ? 'Grátis' : `R$ ${tier.price}` }}
                                        </span>
                                        <p v-if="!event.is_free" class="text-xs text-gray-500">por pessoa</p>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">
                                        {{ tier.current_quantity }} / {{ tier.max_quantity || '∞' }} vendidos
                                    </span>
                                    <button
                                        :disabled="!tier.is_active || (tier.max_quantity && tier.current_quantity >= tier.max_quantity)"
                                        class="px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-200"
                                        :class="selectedTier?.id === tier.id
                                            ? 'bg-black text-white'
                                            : 'bg-gray-900 text-white hover:bg-gray-800 disabled:bg-gray-300 disabled:cursor-not-allowed'">
                                        {{ selectedTier?.id === tier.id ? '✅ Selecionado' : 'Selecionar' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedTier" class="bg-gray-50 rounded-2xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">🎯 Finalizar Inscrição</h3>
                        <p class="text-gray-600 text-sm mb-4">Preencha os dados para garantir sua vaga</p>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Quantidade de Ingressos
                            </label>
                            <select v-model="ticketQuantity" @change="updateParticipants"
                                class="w-full px-4 py-3 bg-white rounded-2xl border border-gray-300 focus:ring-2 focus:ring-black focus:border-black">
                                <option v-for="n in 10" :key="n" :value="n">{{ n }} ingresso{{ n > 1 ? 's' : '' }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                Cada ingresso deve ser associado a um CPF e telefone único
                            </p>
                        </div>

                        <form @submit.prevent="submitParticipation" class="space-y-6">
                            <div v-for="(participant, index) in form.participants" :key="index"
                                class="border border-gray-200 rounded-2xl p-4 space-y-4">
                                <h4 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <span
                                        class="w-6 h-6 bg-black text-white rounded-full text-xs flex items-center justify-center">
                                        {{ index + 1 }}
                                    </span>
                                    Participante {{ index + 1 }}
                                </h4>

                                <div class="relative">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <input v-model="participant.full_name" type="text" required
                                        class="w-full pl-12 pr-4 py-4 bg-white rounded-2xl border-0 focus:ring-2 focus:ring-black text-gray-900 placeholder-gray-400"
                                        placeholder="Nome Completo *" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="relative">
                                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <input v-model="participant.email" type="email"
                                            class="w-full pl-12 pr-4 py-4 bg-white rounded-2xl border-0 focus:ring-2 focus:ring-black text-gray-900 placeholder-gray-400"
                                            placeholder="Email" />
                                    </div>

                                    <div class="relative">
                                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                        </div>
                                        <input v-model="participant.phone" type="tel" required
                                            class="w-full pl-12 pr-4 py-4 bg-white rounded-2xl border-0 focus:ring-2 focus:ring-black text-gray-900 placeholder-gray-400"
                                            placeholder="Telefone *" @input="formatPhone(index)" maxlength="15" />
                                    </div>
                                </div>

                                <div class="relative">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <input v-model="participant.cpf" type="text" required
                                        class="w-full pl-12 pr-4 py-4 bg-white rounded-2xl border-0 focus:ring-2 focus:ring-black text-gray-900 placeholder-gray-400"
                                        placeholder="CPF (apenas números) *" @input="formatCPF(index)" maxlength="14" />
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                                <h4 class="font-semibold text-gray-900 text-sm mb-3">Resumo do Pedido</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-sm">{{ selectedTier.name }} × {{ ticketQuantity
                                        }}</span>
                                        <span class="text-base font-bold text-gray-900">
                                            {{ event.is_free ? 'Grátis' : `R$ ${(selectedTier.price *
                                                ticketQuantity).toFixed(2)}` }}
                                        </span>
                                    </div>
                                    <div v-if="!event.is_free"
                                        class="flex justify-between items-center text-xs text-gray-500">
                                        <span>Taxa de serviço</span>
                                        <span>Inclusa</span>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" :disabled="form.processing"
                                class="w-full bg-black text-white py-4 rounded-full font-bold text-base hover:bg-gray-800 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                                <span>
                                    {{ form.processing ? 'Processando...' :
                                        event.is_free ?
                                            `Confirmar ${ticketQuantity} Inscrição${ticketQuantity > 1 ? 'ões' : ''}` :
                                            `Pagar R$ ${(selectedTier.price * ticketQuantity).toFixed(2)}`
                                    }}
                                </span>
                                <svg v-if="!form.processing" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </button>

                            <p class="text-xs text-gray-500 text-center">
                                🔒 Cada ingresso é pessoal e intransferível. CPF e telefone serão validados no check-in.
                            </p>
                        </form>
                    </div>

                    <div class="text-center pt-6 border-t border-gray-200">
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
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'

const props = defineProps({
    event: Object,
    confirmed_count: Number,
    available_slots: Number
})

const selectedTier = ref(null)
const hasPaid = ref(false)
const ticketQuantity = ref(1)

const form = useForm({
    participants: [
        {
            full_name: '',
            email: '',
            phone: '',
            cpf: ''
        }
    ],
    price_tier_id: null
})

const updateParticipants = () => {
    const currentLength = form.participants.length
    if (ticketQuantity.value > currentLength) {
        for (let i = currentLength; i < ticketQuantity.value; i++) {
            form.participants.push({
                full_name: '',
                email: '',
                phone: '',
                cpf: ''
            })
        }
    } else {
        form.participants.splice(ticketQuantity.value)
    }
}

const formatCPF = (index) => {
    let cpf = form.participants[index].cpf
    cpf = cpf.replace(/\D/g, '')
    cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2')
    cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2')
    cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2')
    form.participants[index].cpf = cpf
}

const formatPhone = (index) => {
    let phone = form.participants[index].phone
    phone = phone.replace(/\D/g, '')
    phone = phone.replace(/(\d{2})(\d)/, '($1) $2')
    phone = phone.replace(/(\d{5})(\d)/, '$1-$2')
    form.participants[index].phone = phone
}

const formatDate = (dateString) => {
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

const selectTicket = (tier) => {
    if (tier.is_active && (!tier.max_quantity || tier.current_quantity < tier.max_quantity)) {
        selectedTier.value = tier
        form.price_tier_id = tier.id
    }
}

const submitParticipation = () => {
    const participantsData = form.participants.map(participant => {
        return {
            ...participant,
            cpf: participant.cpf.replace(/\D/g, ''),
            phone: participant.phone.replace(/\D/g, '')
        };
    });

    for (let i = 0; i < participantsData.length; i++) {
        const participant = participantsData[i];
        if (participant.cpf.length !== 11) {
            alert(`O CPF do participante ${i + 1} deve ter 11 dígitos.`);
            return;
        }

        const phoneDigits = participant.phone;
        if (phoneDigits.length < 10) {
            alert(`O telefone do participante ${i + 1} deve ter pelo menos 10 dígitos.`);
            return;
        }
    }

    router.post(route('events.public.participate', { event: props.event.slug }), {
        participants: participantsData,
        price_tier_id: form.price_tier_id
    }, {
        onStart: () => form.processing = true,
        onFinish: () => form.processing = false,
    });
}

const shareWhatsApp = () => {
    const text = `Confira este evento: ${props.event.name} - ${window.location.href}`
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank')
}

const shareFacebook = () => {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`, '_blank')
}

const copyLink = () => {
    navigator.clipboard.writeText(window.location.href)
    alert('Link copiado para a área de transferência!')
}
</script>
