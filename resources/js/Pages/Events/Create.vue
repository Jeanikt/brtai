<template>
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Criar Novo Evento</h1>
            <p class="text-gray-600">Preencha as informações básicas do seu evento</p>
        </div>

        <PlanLimitAlert v-if="!can_create_event" :message="planLimitMessage" :show="true" />

        <PlanInfoCard :user_plan="user_plan" :active_events_count="active_events_count" />

        <form @submit.prevent="submit" class="bg-white rounded-xl shadow-md border border-gray-100 p-6"
            :class="{ 'opacity-50 pointer-events-none': !can_create_event }">

            <ImageUpload v-model="form.header_image" label="Banner do Evento" class="mb-6"
                :error="form.errors.header_image" />

            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <FormInput label="Nome do Evento *" v-model="form.name" type="text" required
                    placeholder="Ex: Resenha de Halloween" :error="form.errors.name" />

                <FormInput label="Data e Hora *" v-model="form.event_date" type="datetime-local" required
                    :error="form.errors.event_date" />
            </div>

            <FormInput label="Localização *" v-model="form.location" type="text" required
                placeholder="Ex: Av. Paulista, 1000 - São Paulo" :error="form.errors.location" class="mb-6" />

            <FormTextarea label="Descrição do Evento" v-model="form.description" rows="3"
                placeholder="Descreva seu evento... (Open bar, DJ, etc.)" :error="form.errors.description"
                class="mb-6" />

            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <FormInput label="Limite de Participantes" v-model="form.max_participants" type="number"
                    :placeholder="maxParticipantsPlaceholder" :error="form.errors.max_participants" />

                <FormCheckbox label="Revelar localização apenas após pagamento"
                    v-model="form.location_reveal_after_payment" />
            </div>

            <div class="flex justify-end space-x-4">
                <Link :href="route('dashboard')"
                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                Cancelar
                </Link>
                <button type="submit" :disabled="form.processing || !can_create_event"
                    class="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors disabled:opacity-50">
                    {{ form.processing ? 'Criando...' : 'Criar Evento' }}
                </button>
            </div>
        </form>

        <AISuggestionCard v-if="can_create_event" />
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import PlanLimitAlert from '@/Components/Plain/PlanLimitAlert.vue'
import PlanInfoCard from '@/Components/Plain/PlanInfoCard.vue'
import FormInput from '@/Components/Forms/FormInput.vue'
import FormTextarea from '@/Components/Forms/FormTextarea.vue'
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue'
import AISuggestionCard from '@/Components/AI/AISuggestionCard.vue'
import ImageUpload from '@/Components/Image/ImageUpload.vue'

const props = defineProps({
    user_plan: String,
    active_events_count: Number,
    can_create_event: Boolean
})

const form = useForm({
    name: '',
    description: '',
    event_date: '',
    location: '',
    location_reveal_after_payment: true,
    max_participants: null,
    theme: '',
    rules: '',
    header_image: null
})

const planLimitMessage = computed(() => {
    const messages = {
        'freemium': 'Plano Free permite apenas 1 evento ativo por vez. Faça upgrade para criar mais eventos.',
        'pro': 'Plano Pro permite até 10 eventos ativos simultaneamente.',
        'enterprise': 'Plano Enterprise permite eventos ilimitados.',
    }
    return messages[props.user_plan] || 'Limite de eventos atingido para seu plano.'
})

const maxParticipantsPlaceholder = computed(() => {
    const limits = {
        'freemium': 'Máximo 70 participantes',
        'pro': 'Máximo 500 participantes',
        'enterprise': 'Ilimitado'
    }
    return limits[props.user_plan] || 'Deixe vazio para ilimitado'
})

const submit = () => {
    if (!props.can_create_event) return

    form.post(route('events.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset()
        }
    })
}
</script>
