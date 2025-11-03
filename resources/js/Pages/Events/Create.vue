<template>
    <AuthenticatedLayout>
        <div class="max-w-xl mx-auto mt-8 bg-white shadow rounded-2xl p-6 space-y-4 font-prompt">
            <h1 class="text-xl font-bold text-gray-800">Nova Sugestão 💡</h1>

            <form @submit.prevent="submit">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Título</label>
                        <input v-model="form.title" type="text"
                            class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Ex: Suporte a login com Apple" required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea v-model="form.description"
                            class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                            rows="4" placeholder="Descreva a ideia..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <Link :href="route('roadmap.index')"
                            class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                        Cancelar
                        </Link>

                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-purple-600 text-white font-semibold hover:bg-purple-700 transition">
                            Enviar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const form = useForm({
    title: '',
    description: '',
})

const submit = () => {
    form.post(route('roadmap.store'))
}
</script>
