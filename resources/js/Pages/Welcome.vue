<template>
    <div class="min-h-screen bg-white">
        <!-- Cabeçalho Hero -->
        <header class="relative bg-cover bg-center py-10 mb-16 flex flex-col items-center justify-center text-center"
            style="background-image: url('/img/home-background.png');">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-black opacity-40"></div>

            <!-- Conteúdo principal -->
            <div class="relative z-10 flex flex-col items-center px-8 max-w-2xl">
                <!-- Logo com blur -->
                <div class="flex justify-center mb-6">
                    <NavGlass />
                </div>

                <!-- Título -->
                <h1 class="text-white text-center font-prompt font-black leading-[1.1] tracking-[-0.01em] mb-6"
                    style="font-size: clamp(2.5rem, 5vw, 4.5rem); text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.05);">
                    Organize suas resenhas <br />
                    com facilidade.
                </h1>

                <!-- Descrição -->
                <p class="text-xl text-white/90 leading-relaxed max-w-2xl mx-auto mb-10">
                    Crie eventos, receba pagamentos via Pix e gerencie sua lista de
                    convidados em um só lugar. Perfeito para churrascos, festas e encontros.
                </p>

                <!-- Botão CTA -->
                <Link :href="route('register')"
                    class="flex items-center justify-center gap-3 bg-[#FFF35F] text-gray-900 px-10 py-5 rounded-full font-bold text-lg shadow-lg hover:bg-[#fff684] hover:scale-105 transition-all duration-300">
                <span>Criar meu primeiro evento</span>
                <img src="/svg-icons/seta.svg" alt="seta" class="h-5 w-auto" />
                </Link>
            </div>
        </header>

        <!-- Seção: Facilite sua resenha -->
        <section class="container mx-auto px-4 lg:px-6 mb-16 lg:mb-24">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 xl:gap-16 items-center max-w-7xl mx-auto">
                <!-- Conteúdo textual -->
                <div class="space-y-4 lg:space-y-6 order-2 lg:order-1">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-bold text-gray-900 leading-tight">
                        Facilite sua próxima<br>
                        <span class="text-[#00A859]">resenha em poucos minutos.</span>
                    </h2>

                    <p class="text-gray-700 text-base lg:text-lg leading-relaxed">
                        Crie eventos de forma rápida e simples. Configure data, local e ingresso, receba pagamentos via
                        Pix com confirmações automáticas e acompanhe quem confirmou presença em tempo
                        real.
                    </p>

                    <!-- Features list -->
                    <div class="space-y-3 lg:space-y-4">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-5 h-5 lg:w-6 lg:h-6 bg-[#00A859] rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                                <span class="text-white text-xs lg:text-sm">✓</span>
                            </div>
                            <span class="text-gray-700 text-sm lg:text-base">Configuração rápida em menos de 5
                                minutos</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div
                                class="w-5 h-5 lg:w-6 lg:h-6 bg-[#00A859] rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                                <span class="text-white text-xs lg:text-sm">✓</span>
                            </div>
                            <span class="text-gray-700 text-sm lg:text-base">Pagamentos Pix automáticos</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div
                                class="w-5 h-5 lg:w-6 lg:h-6 bg-[#00A859] rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                                <span class="text-white text-xs lg:text-sm">✓</span>
                            </div>
                            <span class="text-gray-700 text-sm lg:text-base">Gestão de convidados em tempo real</span>
                        </div>
                    </div>
                </div>

                <!-- Imagem -->
                <div class="flex justify-center order-1 lg:order-2">
                    <div class="relative w-full max-w-md lg:max-w-lg xl:max-w-xl">
                        <img src="/svg-icons/card-payment.svg" alt="Pagamento fácil com Pix"
                            class="w-full transform hover:scale-105 transition-transform duration-300 drop-shadow-xl" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Seção: Guarde suas histórias -->
        <section class="py-12 lg:py-20 mb-16 lg:mb-24">
            <div class="container mx-auto px-4 lg:px-6">
                <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 xl:gap-16 items-center max-w-7xl mx-auto">
                    <!-- Imagem com overlay -->
                    <div class="flex justify-center lg:justify-start">
                        <div class="relative w-full max-w-md lg:max-w-lg xl:max-w-xl">
                            <img src="/svg-icons/retangulo-centro.svg" alt="Memórias dos eventos"
                                class="w-full transform hover:scale-105 transition-transform duration-300" />
                            <!-- Retângulo com usuário -->
                            <div
                                class="absolute min-w-[160px] lg:min-w-[190px] h-[32px] left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-black/50 backdrop-blur-md rounded-full flex items-center justify-center px-4 py-1.5 shadow-lg">
                                <div class="flex items-center gap-2.5">
                                    <img src="/svg-icons/icon-selo.svg" alt="Selo" class="w-5 h-5" />
                                    <span class="text-white text-base font-semibold whitespace-nowrap">
                                        Brenda Oliveira
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Conteúdo textual -->
                    <div class="space-y-4 lg:space-y-6">
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-bold text-gray-900 leading-tight">
                            Guarde suas histórias<br>
                            <span class="text-[#00A859]">mais inesquecíveis.</span>
                        </h2>

                        <p class="text-gray-700 text-base lg:text-lg leading-relaxed">
                            Cada evento criado na plataforma gera um registro visual único. Adicione fotos,
                            compartilhe momentos especiais e reviva as melhores lembranças com seus amigos.
                        </p>

                        <!-- Features list -->
                        <div class="space-y-3 lg:space-y-4">
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-5 h-5 lg:w-6 lg:h-6 bg-[#00A859] rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                                    <span class="text-white text-xs lg:text-sm">✓</span>
                                </div>
                                <span class="text-gray-700 text-sm lg:text-base">Galeria de fotos compartilhada</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-5 h-5 lg:w-6 lg:h-6 bg-[#00A859] rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                                    <span class="text-white text-xs lg:text-sm">✓</span>
                                </div>
                                <span class="text-gray-700 text-sm lg:text-base">Timeline interativa dos eventos</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-5 h-5 lg:w-6 lg:h-6 bg-[#00A859] rounded-full flex items-center justify-center mt-1 flex-shrink-0">
                                    <span class="text-white text-xs lg:text-sm">✓</span>
                                </div>
                                <span class="text-gray-700 text-sm lg:text-base">Download de memórias em alta
                                    qualidade</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Seção CTA Final -->
        <!-- <section class="container mx-auto px-4 lg:px-6 mb-16 lg:mb-24">
            <div
                class="bg-[#00A859] rounded-2xl lg:rounded-3xl p-6 lg:p-8 xl:p-12 text-center max-w-4xl mx-auto shadow-xl">
                <h2 class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-bold text-white mb-4 lg:mb-6">
                    Pronto para transformar suas resenhas?
                </h2>
                <p class="text-white/90 text-sm lg:text-base xl:text-lg mb-6 lg:mb-8 max-w-2xl mx-auto">
                    Junte-se a milhares de cariocas que já estão organizando seus eventos
                    de forma simples e descomplicada.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 lg:gap-4 justify-center items-center">
                    <Link :href="route('register')"
                        class="bg-white text-[#00A859] px-6 lg:px-10 py-3 lg:py-4 rounded-full font-bold text-sm lg:text-base hover:bg-gray-50 hover:scale-105 active:scale-95 transition-all duration-300 shadow-lg w-full sm:w-auto text-center">
                    Começar Agora Gratuitamente
                    </Link>
                    <Link :href="route('login')"
                        class="border-2 border-white text-white px-6 lg:px-10 py-3 lg:py-4 rounded-full font-bold text-sm lg:text-base hover:bg-white/10 active:bg-white/20 transition-all duration-300 w-full sm:w-auto text-center">
                    Já tenho uma conta
                    </Link>
                </div>
                <p class="text-white/70 text-xs lg:text-sm mt-4 lg:mt-6">
                    ✓ Sem custo inicial • ✓ Até 50 participantes • ✓ Suporte 24/7
                </p>
            </div>
        </section> -->

        <!-- Footer -->
        <footer class="bg-gray-100 text-white py-8 lg:py-12">
            <div class="container mx-auto px-4 lg:px-6">
                <div class="text-center">
                    <p class="text-gray-600 text-sm lg:text-base">
                        &copy; {{ data }}
                    <a class="text-emerald-500">BrotaAI.</a> Todos os direitos reservados.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import NavGlass from '@/Components/Nav/NavGlass.vue'
import { Link } from '@inertiajs/vue3'

const data = new Date().getFullYear();
</script>
