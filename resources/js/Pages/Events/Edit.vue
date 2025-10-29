<template>
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Editar Evento</h1>
            <p class="text-gray-600">Atualize as informações do seu evento</p>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
            <ImageUpload
                v-model="form.header_image"
                label="Banner do Evento"
                class="mb-6"
                :error="form.errors.header_image"
                :existing-image="event.header_image_url"
            />

            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <FormInput
                    label="Nome do Evento *"
                    v-model="form.name"
                    type="text"
                    required
                    placeholder="Ex: Resenha de Halloween"
                    :error="form.errors.name"
                />

                <FormInput
                    label="Data e Hora *"
                    v-model="form.event_date"
                    type="datetime-local"
                    required
                    :error="form.errors.event_date"
                />
            </div>

            <FormInput
                label="Localização *"
                v-model="form.location"
                type="text"
                required
                placeholder="Ex: Av. Paulista, 1000 - São Paulo"
                :error="form.errors.location"
                class="mb-6"
            />

            <FormTextarea
                label="Descrição do Evento"
                v-model="form.description"
                rows="3"
                placeholder="Descreva seu evento... (Open bar, DJ, etc.)"
                :error="form.errors.description"
                class="mb-6"
            />

            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <FormInput
                    label="Limite de Participantes"
                    v-model="form.max_participants"
                    type="number"
                    :placeholder="maxParticipantsPlaceholder"
                    :error="form.errors.max_participants"
                />

                <FormCheckbox
                    label="Revelar localização apenas após pagamento"
                    v-model="form.location_reveal_after_payment"
                />
            </div>

            <div class="flex justify-end space-x-4">
                <Link :href="route('events.show', event.id)"
                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                    Cancelar
                </Link>
                <button type="submit"
                        :disabled="form.processing"
                        class="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors disabled:opacity-50">
                    {{ form.processing ? 'Atualizando...' : 'Atualizar Evento' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import FormInput from '@/Components/FormInput.vue'
import FormTextarea from '@/Components/FormTextarea.vue'
import FormCheckbox from '@/Components/FormCheckbox.vue'
import ImageUpload from '@/Components/ImageUpload.vue'

const props = defineProps({
    event: Object,
    user_plan: String
})

const form = useForm({
    name: props.event.name,
    description: props.event.description,
    event_date: props.event.event_date.substring(0, 16),
    location: props.event.location,
    location_reveal_after_payment: props.event.location_reveal_after_payment,
    max_participants: props.event.max_participants,
    theme: props.event.theme,
    rules: props.event.rules,
    header_image: null
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
    form.post(route('events.update', props.event.id), {
        forceFormData: true,
        onSuccess: () => {
            form.reset()
        }
    })
}
</script>
