<template>
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span class="flex-shrink-0 h-5 w-5 text-blue-400">📊</span>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        Plano {{ planName }}
                    </h3>
                    <p class="text-sm text-blue-700 mt-1">
                        {{ planDescription }}
                    </p>
                </div>
            </div>
            <Link v-if="showUpgrade && user_plan === 'freemium'" :href="route('settings.billing')"
                class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full text-blue-700 bg-blue-100 hover:bg-blue-200">
            Upgrade
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    user_plan: {
        type: String,
        required: true
    },
    active_events_count: {
        type: Number,
        required: true
    },
    showUpgrade: {
        type: Boolean,
        default: true
    }
})

const planName = computed(() => {
    const names = {
        'freemium': 'Free',
        'pro': 'Pro',
        'enterprise': 'Enterprise'
    }
    return names[props.user_plan] || props.user_plan
})

const planDescription = computed(() => {
    if (props.user_plan === 'freemium') {
        return `Você tem ${props.active_events_count}/1 evento ativo`
    } else if (props.user_plan === 'pro') {
        return `Você tem ${props.active_events_count}/10 eventos ativos`
    } else {
        return `Eventos ilimitados ativos`
    }
})
</script>
