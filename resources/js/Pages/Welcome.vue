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
        <header class="hero-header" style="background-image: url('/img/home-background.png');">
            <div class="hero-overlay"></div>

            <div class="hero-content">
                <div class="hero-nav-wrapper">
                    <NavGlass />
                </div>

                <h1 class="hero-title"
                    style="font-size: clamp(2rem, 5vw, 4.5rem); text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.05);">
                    Organize suas resenhas <br />
                    com facilidade.
                </h1>

                <p class="hero-description">
                    Crie eventos, receba pagamentos via Pix e gerencie sua lista de
                    convidados em um só lugar. Perfeito para churrascos, festas e encontros.
                </p>

                <div class="hero-buttons-wrapper">
                    <Link :href="route('register')" class="btn-hero-primary">
                    <span class="btn-hero-primary-text">Criar meu primeiro evento</span>
                    <img :src="setaSvg" alt="seta" class="btn-hero-primary-icon" />
                    </Link>

                    <Link :href="route('events.public.index')" class="btn-hero-secondary">
                    <span class="btn-hero-secondary-text">Ver eventos públicos</span>
                    </Link>
                </div>
            </div>
        </header>

        <section class="content-section">
            <div class="content-grid">
                <div class="content-text-column order-left">
                    <h2 class="content-title">
                        Facilite sua próxima<br>
                        <span class="content-title-highlight">resenha em poucos minutos.</span>
                    </h2>

                    <p class="content-description">
                        Crie eventos de forma rápida e simples. Configure data, local e ingresso, receba pagamentos via
                        Pix com confirmações automáticas e acompanhe quem confirmou presença em tempo real.
                    </p>

                    <div class="checklist">
                        <div class="checklist-item">
                            <div class="checklist-icon-wrapper">
                                <svg class="checklist-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="checklist-text">Configuração rápida em menos de 5 minutos</span>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-icon-wrapper">
                                <svg class="checklist-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="checklist-text">Pagamentos Pix automáticos</span>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-icon-wrapper">
                                <svg class="checklist-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="checklist-text">Gestão de convidados em tempo real</span>
                        </div>
                    </div>
                </div>

                <div class="content-image-column order-right">
                    <div class="content-image-wrapper">
                        <img :src="cardPaymentSvg" alt="Pagamento fácil com Pix"
                            class="content-image content-image-shadow" />
                    </div>
                </div>
            </div>
        </section>

        <section class="content-section">
            <div class="content-grid">
                <div class="content-image-column align-left">
                    <div class="content-image-wrapper">
                        <img :src="retanguloSvg" alt="Memórias dos eventos" class="content-image" />
                        <div class="image-tag-overlay">
                            <div class="image-tag-inner">
                                <img :src="seloSvg" alt="Selo" class="image-tag-icon" />
                                <span class="image-tag-text">
                                    Brenda Oliveira
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-text-column">
                    <h2 class="content-title">
                        Guarde suas histórias<br>
                        <span class="content-title-highlight">mais inesquecíveis.</span>
                    </h2>

                    <p class="content-description">
                        Cada evento criado na plataforma gera um registro visual único. Adicione fotos,
                        compartilhe momentos especiais e reviva as melhores lembranças com seus amigos.
                    </p>

                    <div class="checklist">
                        <div class="checklist-item">
                            <div class="checklist-icon-wrapper">
                                <svg class="checklist-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="checklist-text">Galeria de fotos compartilhada</span>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-icon-wrapper">
                                <svg class="checklist-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="checklist-text">Timeline interativa dos eventos</span>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-icon-wrapper">
                                <svg class="checklist-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="checklist-text">Download de memórias em alta qualidade</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="footer">
            <div class="footer-container">
                <div class="footer-content">
                    <p class="footer-text">
                        &copy; {{ data }}
                        <a href="#" class="footer-link">BrotaAI.</a> Todos os direitos reservados.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>