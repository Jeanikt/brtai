<template>
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Criar Novo Evento</h1>
            <p class="text-gray-600">Preencha as informações básicas do seu evento</p>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
            <!-- Nome e Descrição -->
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nome do Evento *
                    </label>
                    <input v-model="form.name" type="text" id="name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Ex: Resenha de Halloween">
                    <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="event_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Data e Hora *
                    </label>
                    <input v-model="form.event_date" type="datetime-local" id="event_date" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <p v-if="form.errors.event_date" class="text-red-500 text-sm mt-1">{{ form.errors.event_date }}</p>
                </div>
            </div>

            <!-- Localização -->
            <div class="mb-6">
                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                    Localização *
                </label>
                <input v-model="form.location" type="text" id="location" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="Ex: Av. Paulista, 1000 - São Paulo">
                <p v-if="form.errors.location" class="text-red-500 text-sm mt-1">{{ form.errors.location }}</p>
            </div>

            <!-- Descrição -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Descrição do Evento
                </label>
                <textarea v-model="form.description" id="description" rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="Descreva seu evento... (Open bar, DJ, etc.)"></textarea>
                <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
            </div>

            <!-- Configurações -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label for="max_participants" class="block text-sm font-medium text-gray-700 mb-2">
                        Limite de Participantes
                    </label>
                    <input v-model="form.max_participants" type="number" id="max_participants"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Deixe vazio para ilimitado">
                    <p class="text-sm text-gray-500 mt-1">
                        Plano Free: máximo 70 participantes
                    </p>
                </div>

                <div class="flex items-center space-x-3">
                    <input v-model="form.location_reveal_after_payment" type="checkbox"
                        id="location_reveal_after_payment"
                        class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                    <label for="location_reveal_after_payment" class="text-sm text-gray-700">
                        Revelar localização apenas após pagamento
                    </label>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-4">
                <Link :href="route('dashboard')"
                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                Cancelar
                </Link>
                <button type="submit" :disabled="form.processing"
                    class="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors disabled:opacity-50">
                    {{ form.processing ? 'Criando...' : 'Criar Evento' }}
                </button>
            </div>
        </form>

        <!-- Sugestão de IA -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-center mb-4">
                <span class="text-2xl mr-3">🤖</span>
                <h3 class="text-lg font-semibold text-gray-900">Precisa de ajuda com as ideias?</h3>
            </div>
            <p class="text-gray-700 mb-4">
                Use nossa IA para gerar sugestões de temas, preços e muito mais!
            </p>
            <Link :href="route('ai.suggestions')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
            <span class="mr-2">✨</span>
            Gerar Sugestões com IA
            </Link>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    description: '',
    event_date: '',
    location: '',
    location_reveal_after_payment: true,
    max_participants: null,
    theme: '',
    rules: ''
})

const submit = () => {
    form.post(route('events.store'), {
        onSuccess: () => {
            // Redirecionamento é tratado pelo controller
        }
    })
}
</script>
