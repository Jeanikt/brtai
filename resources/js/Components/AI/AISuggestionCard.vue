<template>
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-center mb-4">
            <span class="text-2xl mr-3">🤖</span>
            <h3 class="text-lg font-semibold text-gray-900">Precisa de ajuda com as ideias?</h3>
        </div>
        <p class="text-gray-700 mb-4">
            Use nossa IA para gerar sugestões de temas, preços e muito mais!
        </p>
        <Link :href="route('ai.suggestions')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
        <span class="mr-2">✨</span>
        Gerar Sugestões com IA
        </Link>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
