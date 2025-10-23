<template>
    <div class="min-h-screen bg-gradient-to-br from-green-50 to-orange-50">
        <div class="container mx-auto px-4 py-16">
            <!-- Cabeçalho -->
            <header class="text-center mb-16">
                <h1 class="text-5xl font-bold text-green-600 mb-4">
                    BrotaAI 🎉
                </h1>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Plataforma inteligente para organizar suas resenhas e festas.
                    Crie eventos, gerencie participantes e receba pagamentos via Pix automaticamente.
                </p>
            </header>

            <!-- Cards de Features -->
            <div class="grid md:grid-cols-3 gap-8 mb-16">
                <div class="bg-white rounded-lg p-6 shadow-md border border-green-100">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <span class="text-2xl">🎪</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Crie Eventos em Minutos</h3>
                    <p class="text-gray-600">Formulário simples com sugestões de IA para criar eventos incríveis</p>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-md border border-orange-100">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                        <span class="text-2xl">💸</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Pagamento Pix Automático</h3>
                    <p class="text-gray-600">Sistema integrado com AbacatePay para receber pagamentos automaticamente
                    </p>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-md border border-blue-100">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <span class="text-2xl">📊</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Dashboard Inteligente</h3>
                    <p class="text-gray-600">Acompanhe métricas em tempo real e sugestões de IA</p>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="text-center bg-white rounded-2xl p-8 shadow-lg border border-green-200 max-w-2xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Pronto para organizar sua primeira resenha?
                </h2>
                <p class="text-gray-600 mb-6">
                    Comece gratuitamente - até 70 participantes no plano Free
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link :href="route('register')"
                        class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Criar Conta Grátis
                    </Link>
                    <Link :href="route('login')"
                        class="border border-green-600 text-green-600 px-8 py-3 rounded-lg font-semibold hover:bg-green-50 transition-colors">
                    Fazer Login
                    </Link>
                </div>
            </div>

            <!-- Informações de Planos -->
            <div class="mt-16 text-center">
                <h3 class="text-2xl font-bold text-gray-800 mb-8">Planos Simples e Transparentes</h3>
                <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <div class="bg-white rounded-xl p-6 border-2 border-green-200">
                        <h4 class="text-xl font-bold text-green-600 mb-2">Plano Free</h4>
                        <p class="text-3xl font-bold text-gray-800 mb-4">Grátis</p>
                        <ul class="text-gray-600 space-y-2 mb-6">
                            <li>✓ 1 evento ativo por vez</li>
                            <li>✓ Até 70 participantes</li>
                            <li>✓ Taxa de 6.5% + R$0,80 por Pix</li>
                            <li>✓ Dashboard básico</li>
                        </ul>
                    </div>

                    <div class="bg-green-600 rounded-xl p-6 text-white">
                        <h4 class="text-xl font-bold mb-2">Plano Pro</h4>
                        <p class="text-3xl font-bold mb-4">R$ 19/mês</p>
                        <ul class="space-y-2 mb-6">
                            <li>✓ Eventos ilimitados</li>
                            <li>✓ Participantes ilimitados</li>
                            <li>✓ Taxa reduzida: 5.5% + R$0,80</li>
                            <li>✓ IA completa + Analytics</li>
                            <li>✓ Suporte prioritário</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
