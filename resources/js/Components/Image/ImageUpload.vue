<template>
    <div class="space-y-4">
        <label class="block text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div v-if="!previewUrl" class="flex items-center justify-center w-full">
            <label
                class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <svg class="w-8 h-8 mb-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 20 16">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                    </svg>
                    <p class="mb-2 text-sm text-gray-500">
                        <span class="font-semibold">Clique para upload</span>
                    </p>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF (MAX. 5MB)</p>
                </div>
                <input type="file" class="hidden" @change="handleFileSelect" accept="image/*" />
            </label>
        </div>

        <div v-else class="relative">
            <img :src="previewUrl" alt="Preview" class="w-full h-48 object-cover rounded-lg shadow-md">
            <button type="button" @click="removeImage"
                class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <p v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    label: {
        type: String,
        default: 'Imagem de Banner'
    },
    required: {
        type: Boolean,
        default: false
    },
    error: String,
    existingImage: String
})

const emit = defineEmits(['update:modelValue'])

const previewUrl = ref(props.existingImage || '')
const selectedFile = ref(null)

watch(() => props.existingImage, (newVal) => {
    previewUrl.value = newVal || ''
})

const handleFileSelect = (event) => {
    const file = event.target.files[0]

    if (file) {
        if (!file.type.startsWith('image/')) {
            emit('update:modelValue', null)
            previewUrl.value = ''
            return
        }

        if (file.size > 5 * 1024 * 1024) {
            emit('update:modelValue', null)
            previewUrl.value = ''
            return
        }

        selectedFile.value = file
        previewUrl.value = URL.createObjectURL(file)
        emit('update:modelValue', file)
    }
}

const removeImage = () => {
    selectedFile.value = null
    previewUrl.value = ''
    emit('update:modelValue', null)

    if (props.existingImage) {
        emit('update:modelValue', 'remove')
    }
}
</script>
