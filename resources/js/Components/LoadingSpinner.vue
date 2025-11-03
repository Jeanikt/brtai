<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 shadow-xl flex flex-col items-center gap-4">
            <div class="relative">
                <div class="w-12 h-12 border-4 border-gray-200 border-t-black rounded-full animate-spin"></div>
            </div>
            <p class="text-gray-700 font-medium">{{ message }}</p>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    show: boolean
    message?: string
}

withDefaults(defineProps < Props > (), {
    message: 'Carregando...'
})
</script>
