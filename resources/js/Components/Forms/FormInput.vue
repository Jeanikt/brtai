<template>
    <div class="space-y-2">
        <label v-if="label" class="block text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <input :type="type" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)"
            :placeholder="placeholder" :required="required" :disabled="disabled" :class="[
                'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:outline-none transition-colors',
                error
                    ? 'border-red-300 focus:border-red-500 focus:ring-red-200'
                    : 'border-gray-300 focus:border-green-500 focus:ring-green-200',
                disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
            ]" />

        <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
        <p v-if="help" class="text-gray-500 text-sm">{{ help }}</p>
    </div>
</template>

<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    label: {
        type: String,
        default: ''
    },
    type: {
        type: String,
        default: 'text'
    },
    placeholder: {
        type: String,
        default: ''
    },
    required: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    error: {
        type: String,
        default: ''
    },
    help: {
        type: String,
        default: ''
    }
})

defineEmits(['update:modelValue'])
</script>
