<template>
    <div class="min-h-screen bg-white">
        <!-- Cabeçalho com imagem de fundo -->
        <header class="relative bg-cover bg-center py-16 mb-12 flex flex-col items-center justify-center text-center"
            style="background-image: url('/img/home-background.png');">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-black opacity-40"></div>

            <!-- Conteúdo principal -->
            <div class="relative z-10 flex flex-col items-center px-6">
                <!-- Logo com blur -->
                <div class="flex justify-center mb-8">
                    <div class="flex justify-center mb-8">
                        <NavGlass />
                    </div>

                </div>

                <!-- Título -->
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white mb-4 leading-tight max-w-2xl">
                    Organize suas resenhas com facilidade.
                </h1>

                <!-- Descrição -->
                <p class="text-lg text-white/90 leading-relaxed max-w-md mx-auto mb-8">
                    Crie eventos, receba pagamentos via Pix e gerencie sua lista de convidados em um só lugar.
                    Perfeito para churrascos, festas e encontros no Rio de Janeiro.
                </p>

                <!-- Botão CTA -->
                <Link :href="route('register')"
                    class="bg-[#FFF35F] text-gray-900 px-8 py-4 rounded-full font-bold text-lg shadow-md hover:bg-[#fff684] transition">
                Criar meu primeiro evento →
                </Link>
            </div>
        </header>

        <div class="container mx-auto px-6 py-8">
            <!-- Cards de Features -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                    <div class="w-14 h-14 bg-[#E8F5E9] rounded-2xl flex items-center justify-center mb-4">
                        <span class="text-2xl">🎪</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Crie seu primeiro evento</h3>
                    <p class="text-gray-700 mb-4">Facilite sua próxima resenha em poucos minutos.</p>
                    <div class="space-y-2 text-gray-700">
                        <div class="flex items-center">
                            <span class="font-medium">Valor:</span>
                            <span class="ml-2">R$ 320,45</span>
                        </div>
                        <div>Que acontece ao Cristo</div>
                        <div>Não há nada que é possível</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                    <div class="w-14 h-14 bg-[#FFF3E0] rounded-2xl flex items-center justify-center mb-4">
                        <span class="text-2xl">💸</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Pagamento Pix Automático</h3>
                    <p class="text-gray-700">
                        Sistema integrado para receber pagamentos automaticamente.
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                    <div class="w-14 h-14 bg-[#E3F2FD] rounded-2xl flex items-center justify-center mb-4">
                        <span class="text-2xl">📊</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Memórias dos Eventos</h3>
                    <p class="text-gray-700 leading-relaxed">
                        Cada evento criado na plataforma gera um registro visual, onde é possível adicionar fotos e
                        relembrar os melhores momentos.
                    </p>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="text-center bg-[#00A859] rounded-2xl p-8 shadow-lg max-w-md mx-auto mb-16">
                <h2 class="text-2xl font-bold text-white mb-4">
                    Pronto para organizar sua primeira resenha?
                </h2>
                <p class="text-white/90 mb-6">
                    Comece gratuitamente - até 70 participantes no plano Free.
                </p>
                <div class="flex flex-col gap-4">
                    <Link :href="route('register')"
                        class="bg-white text-[#00A859] px-8 py-4 rounded-xl font-bold text-lg hover:bg-gray-50 transition-colors">
                    Criar Conta Grátis
                    </Link>
                    <Link :href="route('login')"
                        class="border-2 border-white text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-white/10 transition-colors">
                    Fazer Login
                    </Link>
                </div>
            </div>

            <!-- Planos -->
            <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-900 mb-8">Planos Simples e Transparentes</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto">
                    <div class="bg-white rounded-2xl p-6 border-2 border-[#00A859]">
                        <h4 class="text-xl font-bold text-[#00A859] mb-2">Plano Free</h4>
                        <p class="text-3xl font-bold text-gray-900 mb-4">Grátis</p>
                        <ul class="text-gray-700 space-y-3 mb-2 text-left">
                            <li class="flex items-center">
                                <span class="text-[#00A859] mr-3">✓</span>
                                1 evento ativo por vez
                            </li>
                            <li class="flex items-center">
                                <span class="text-[#00A859] mr-3">✓</span>
                                Até 70 participantes
                            </li>
                            <li class="flex items-center">
                                <span class="text-[#00A859] mr-3">✓</span>
                                Taxa de 6.5% + R$0,80 por Pix
                            </li>
                            <li class="flex items-center">
                                <span class="text-[#00A859] mr-3">✓</span>
                                Dashboard básico
                            </li>
                        </ul>
                    </div>

                    <div class="bg-[#00A859] rounded-2xl p-6 text-white">
                        <h4 class="text-xl font-bold mb-2">Plano Pro</h4>
                        <p class="text-3xl font-bold mb-4">R$ 19/mês</p>
                        <ul class="space-y-3 mb-2 text-left">
                            <li class="flex items-center">
                                <span class="text-white mr-3">✓</span>
                                Eventos ilimitados
                            </li>
                            <li class="flex items-center">
                                <span class="text-white mr-3">✓</span>
                                Participantes ilimitados
                            </li>
                            <li class="flex items-center">
                                <span class="text-white mr-3">✓</span>
                                Taxa reduzida: 5.5% + R$0,80
                            </li>
                            <li class="flex items-center">
                                <span class="text-white mr-3">✓</span>
                                IA completa + Analytics
                            </li>
                            <li class="flex items-center">
                                <span class="text-white mr-3">✓</span>
                                Suporte prioritário
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import NavGlass from '@/Components/Nav/NavGlass.vue';
import { Link } from '@inertiajs/vue3'
</script>
