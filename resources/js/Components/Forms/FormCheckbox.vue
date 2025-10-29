<template>
    <div class="flex items-center space-x-3">
        <input :id="id" :checked="modelValue" type="checkbox" :class="checkboxClasses"
            @change="$emit('update:modelValue', $event.target.checked)">
        <label :for="id" class="text-sm text-gray-700">
            {{ label }}
        </label>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    label: String,
    modelValue: Boolean,
    id: String
})

defineEmits(['update:modelValue'])

const checkboxClasses = computed(() => [
    'w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500',
    props.disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
])
</script>
