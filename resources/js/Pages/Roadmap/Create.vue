<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const form = useForm({
    title: '',
    description: ''
})

const submit = () => {
    form.post(route('roadmap.store'))
}
</script>

<template>
    <AuthenticatedLayout title="Nova sugestão">
        <div class="max-w-xl mx-auto mt-8 p-6 bg-white  rounded-2xl shadow">
            <h1 class="text-2xl font-bold mb-4">Nova sugestão</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block mb-1 font-medium">Título</label>
                    <input v-model="form.title" type="text"
                        class="w-full border-gray-300 dark:border-gray-700 rounded-lg"
                        placeholder="Ex: Melhorar sistema de eventos" />
                    <div v-if="form.errors.title" class="text-red-500 text-sm">{{ form.errors.title }}</div>
                </div>

                <div>
                    <label class="block mb-1 font-medium">Descrição</label>
                    <textarea v-model="form.description" rows="4"
                        class="w-full border-gray-300 dark:border-gray-700 rounded-lg"
                        placeholder="Explique brevemente sua sugestão..."></textarea>
                    <div v-if="form.errors.description" class="text-red-500 text-sm">{{ form.errors.description }}</div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
                        :disabled="form.processing">
                        Enviar sugestão
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
