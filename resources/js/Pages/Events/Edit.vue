<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Back button and header -->
            <div class="flex items-center gap-4 mb-6">
                <button @click="goBack"
                    class="w-10 h-10 bg-black rounded-full flex items-center justify-center hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <div>
                <h1 class="text-2xl font-bold text-gray-900">Editar Evento</h1>
                <p class="text-sm text-gray-600 mt-1">Edite os detalhes da sua resenha.</p>
            </div>

            <!-- Form -->
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
                        <input v-model="form.event_date" type="date" required
                            class="w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900" />
                    </div>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <input v-model="form.event_time" type="time" required
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
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                            class="w-full pl-12 pr-4 py-4 bg-gray-100 rounded-2xl border-0 focus:ring-2 focus:ring-gray-300 text-gray-900 placeholder-gray-400" />
                    </div>
                </div>

                <!-- Image Upload/Preview Area -->
                <div class="relative bg-gray-100 rounded-2xl overflow-hidden">
                    <div v-if="imagePreview || event.header_image_url" class="relative h-64">
                        <img :src="imagePreview || event.header_image_url" alt="Event image"
                            class="w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                            <button type="button" @click="$refs.fileInput.click()"
                                class="bg-white text-black px-6 py-3 rounded-full font-semibold hover:bg-gray-100 transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <span>Aperte para carregar outra foto.</span>
                            </button>
                        </div>
                        <p class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white text-sm">PNG, JPG ou WEBP (MAX.
                            5MB)</p>
                    </div>
                    <div v-else class="p-12 text-center border-2 border-dashed border-gray-300 cursor-pointer"
                        @click="$refs.fileInput.click()">
                        <div class="space-y-3">
                            <div class="flex justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-600 font-medium">Aperte para fazer upload ou arraste</p>
                                <p class="text-sm text-gray-500 mt-1">PNG, JPG ou WEBP (MAX. 5MB)</p>
                            </div>
                        </div>
                    </div>
                    <input ref="fileInput" type="file" accept="image/*" @change="handleFileUpload" class="hidden" />
                </div>

                <!-- Submit Button -->
                <button type="submit" :disabled="form.processing"
                    class="w-full bg-black text-white py-4 rounded-full font-bold text-base hover:bg-gray-800 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                    <span>Finalizar Edição</span>
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    event: Object
})

const form = useForm({
    name: props.event.name,
    event_date: props.event.event_date,
    event_time: props.event.event_time,
    location: props.event.location,
    price: props.event.price || '',
    max_participants: props.event.max_participants || '',
    header_image: null
})

const imagePreview = ref(null)

const goBack = () => {
    router.visit(`/events/${props.event.id}`)
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

const submit = () => {
    form.post(`/events/${props.event.id}`, {
        forceFormData: true
    })
}
</script>
