<template>
    <div v-if="show" class="fixed top-4 right-4 z-50 max-w-sm w-full">
        <div :class="notificationClasses" class="rounded-lg shadow-lg p-4 transition-all duration-300 transform">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg v-if="type === 'success'" class="w-6 h-6 text-green-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else-if="type === 'error'" class="w-6 h-6 text-red-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else-if="type === 'warning'" class="w-6 h-6 text-yellow-500" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <svg v-else class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ title }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ message }}</p>
                </div>
                <button @click="close" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const props = defineProps({
    type: {
        type: String,
        default: 'info',
        validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    title: String,
    message: String,
    duration: {
        type: Number,
        default: 5000
    }
})

const emit = defineEmits(['close'])
const show = ref(false)

const notificationClasses = computed(() => {
    const baseClasses = 'bg-white border-l-4'
    const typeClasses = {
        success: 'border-green-500',
        error: 'border-red-500',
        warning: 'border-yellow-500',
        info: 'border-blue-500'
    }
    return `${baseClasses} ${typeClasses[props.type]}`
})

onMounted(() => {
    show.value = true
    if (props.duration > 0) {
        setTimeout(() => {
            close()
        }, props.duration)
    }
})

const close = () => {
    show.value = false
    setTimeout(() => {
        emit('close')
    }, 300)
}
</script>
