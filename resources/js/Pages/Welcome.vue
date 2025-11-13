<script setup>
import { Link } from '@inertiajs/vue3'
import NavGlass from '@/Components/Nav/NavGlass.vue'

// Importe os SVGs como módulos
import setaSvg from '@/images/svg-icons/seta.svg'
import cardPaymentSvg from '@/images/svg-icons/card-payment.svg'
import retanguloSvg from '@/images/svg-icons/retangulo-centro.svg'
import seloSvg from '@/images/svg-icons/icon-selo.svg'

const data = new Date().getFullYear()
</script>

<template>
    <div class="min-h-screen bg-white">
        <!-- Cabeçalho Hero -->
        <header class="relative bg-cover bg-center py-10 mb-16 flex flex-col items-center justify-center text-center"
            style="background-image: url('/img/home-background.png');">
            <div class="absolute inset-0 bg-black opacity-40"></div>

            <div class="relative z-10 flex flex-col items-center px-4 sm:px-8 max-w-2xl w-full">
                <div class="flex justify-center mb-6">
                    <NavGlass />
                </div>

                <h1 class="text-white text-center font-prompt font-black leading-[1.1] tracking-[-0.01em] mb-6"
                    style="font-size: clamp(2rem, 5vw, 4.5rem); text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.05);">
                    Organize suas resenhas <br />
                    com facilidade.
                </h1>

                <p class="text-lg sm:text-xl text-white/90 leading-relaxed max-w-2xl mx-auto mb-8 sm:mb-10 px-2">
                    Crie eventos, receba pagamentos via Pix e gerencie sua lista de
                    convidados em um só lugar. Perfeito para churrascos, festas e encontros.
                </p>

                <!-- Botões melhorados para responsividade -->
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 w-full max-w-md items-center justify-center">
                    <!-- Botão principal -->
                    <Link :href="route('register')"
                        class="flex items-center justify-center gap-2 sm:gap-3 bg-[#FFF35F] text-gray-900 px-6 sm:px-10 py-4 sm:py-5 rounded-full font-bold text-base sm:text-lg shadow-lg hover:bg-[#fff684] hover:scale-105 transition-all duration-300 w-full sm:w-auto min-h-[52px] sm:min-h-[60px]">
                    <span class="text-sm sm:text-base whitespace-nowrap">Criar meu primeiro evento</span>
                    <img :src="setaSvg" alt="seta" class="h-4 sm:h-5 w-auto flex-shrink-0" />
                    </Link>

                    <!-- Botão para ver eventos públicos -->
                    <Link :href="route('events.public.index')"
                        class="text-white font-prompt font-semibold bg-[#00A859] px-6 sm:px-2 py-3 sm:py-2 rounded-full text-base sm:text-lg hover:bg-[#008c4a] hover:scale-105 transition-all duration-300 w-full sm:w-auto text-center min-h-[44px] flex items-center justify-center">
                    <span class="text-sm sm:text-base">Ver eventos públicos</span>
                    </Link>
                </div>
            </div>
        </header>

        <!-- Seção: Facilite sua resenha -->
        <section class="container mx-auto px-4 sm:px-6 mb-16 lg:mb-24">
            <div class="grid lg:grid-cols-2 gap-6 sm:gap-8 lg:gap-12 xl:gap-16 items-center max-w-7xl mx-auto">
                <div class="space-y-4 lg:space-y-6 order-2 lg:order-1">
                    <h2 class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-bold text-gray-900 leading-tight">
                        Facilite sua próxima<br>
                        <span class="text-[#00A859]">resenha em poucos minutos.</span>
                    </h2>

                    <p class="text-gray-700 text-sm sm:text-base lg:text-lg leading-relaxed">
                        Crie eventos de forma rápida e simples. Configure data, local e ingresso, receba pagamentos via
                        Pix com confirmações automáticas e acompanhe quem confirmou presença em tempo real.
                    </p>

                    <div class="space-y-3 lg:space-y-4">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 bg-[#00A859] rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                                <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 lg:w-3.5 lg:h-3.5 text-white" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 text-xs sm:text-sm lg:text-base">Configuração rápida em menos de
                                5 minutos</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div
                                class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 bg-[#00A859] rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                                <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 lg:w-3.5 lg:h-3.5 text-white" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 text-xs sm:text-sm lg:text-base">Pagamentos Pix
                                automáticos</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div
                                class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 bg-[#00A859] rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                                <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 lg:w-3.5 lg:h-3.5 text-white" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 text-xs sm:text-sm lg:text-base">Gestão de convidados em tempo
                                real</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center order-1 lg:order-2">
                    <div class="relative w-full max-w-xs sm:max-w-md lg:max-w-lg xl:max-w-xl">
                        <img :src="cardPaymentSvg" alt="Pagamento fácil com Pix"
                            class="w-full transform hover:scale-105 transition-transform duration-300 drop-shadow-xl" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Seção: Guarde suas histórias -->
        <section class="py-8 sm:py-12 lg:py-20 mb-16 lg:mb-24">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="grid lg:grid-cols-2 gap-6 sm:gap-8 lg:gap-12 xl:gap-16 items-center max-w-7xl mx-auto">
                    <div class="flex justify-center lg:justify-start">
                        <div class="relative w-full max-w-xs sm:max-w-md lg:max-w-lg xl:max-w-xl">
                            <img :src="retanguloSvg" alt="Memórias dos eventos"
                                class="w-full transform hover:scale-105 transition-transform duration-300" />
                            <div
                                class="absolute min-w-[140px] sm:min-w-[160px] lg:min-w-[190px] h-[28px] sm:h-[32px] left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-black/50 backdrop-blur-md rounded-full flex items-center justify-center px-3 sm:px-4 py-1 sm:py-1.5 shadow-lg">
                                <div class="flex items-center gap-2 sm:gap-2.5">
                                    <img :src="seloSvg" alt="Selo" class="w-4 h-4 sm:w-5 sm:h-5" />
                                    <span class="text-white text-sm sm:text-base font-semibold whitespace-nowrap">
                                        Brenda Oliveira
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 lg:space-y-6">
                        <h2 class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-bold text-gray-900 leading-tight">
                            Guarde suas histórias<br>
                            <span class="text-[#00A859]">mais inesquecíveis.</span>
                        </h2>

                        <p class="text-gray-700 text-sm sm:text-base lg:text-lg leading-relaxed">
                            Cada evento criado na plataforma gera um registro visual único. Adicione fotos,
                            compartilhe momentos especiais e reviva as melhores lembranças com seus amigos.
                        </p>

                        <div class="space-y-3 lg:space-y-4">
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 bg-[#00A859] rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 lg:w-3.5 lg:h-3.5 text-white" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700 text-xs sm:text-sm lg:text-base">Galeria de fotos
                                    compartilhada</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 bg-[#00A859] rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 lg:w-3.5 lg:h-3.5 text-white" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700 text-xs sm:text-sm lg:text-base">Timeline interativa dos
                                    eventos</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 bg-[#00A859] rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 lg:w-3.5 lg:h-3.5 text-white" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700 text-xs sm:text-sm lg:text-base">Download de memórias em alta
                                    qualidade</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-100 text-white py-6 sm:py-8 lg:py-12">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="text-center">
                    <p class="text-gray-600 text-xs sm:text-sm lg:text-base">
                        &copy; {{ data }}
                        <a href="#" class="text-emerald-500">BrotaAI.</a> Todos os direitos reservados.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>