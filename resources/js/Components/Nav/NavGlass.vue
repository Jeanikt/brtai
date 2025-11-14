<template>
    <div class="nav-glass">
        <div class="nav-glass-inner">
            <ApplicationLogo fill="white" class="nav-glass-logo" />

            <Link :href="route('login')">
            <img :src="entrarSvg" alt="Entrar" class="nav-glass-button" />
            </Link>

            <Link :href="route('register')">
            <img :src="registerSvg" alt="Cadastrar" class="nav-glass-button" />
            </Link>

            <img :src="listSvg" alt="Lista" class="nav-glass-list-icon" />
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import entrarSvg from '@/images/svg-icons/entrar-button.svg'
import registerSvg from '@/images/svg-icons/register-button.svg'
import listSvg from '@/images/svg-icons/list-button.svg'
</script>