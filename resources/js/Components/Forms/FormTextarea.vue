<template>
    <div>
        <label :for="id" class="block text-sm font-medium text-gray-700 mb-2">
            {{ label }}
        </label>
        <textarea :id="id" :value="modelValue" :rows="parseInt(rows)" :placeholder="placeholder"
            :class="textareaClasses" @input="$emit('update:modelValue', $event.target.value)"></textarea>
        <p v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</p>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    modelValue: String,
    rows: {
        type: [String, Number],
        default: 3
    },
    placeholder: String,
    error: String,
    id: String
})

defineEmits(['update:modelValue'])

const textareaClasses = 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500'
</script>