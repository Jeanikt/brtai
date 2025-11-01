<template>
  <div class="flex items-center justify-center w-[430px] h-[63px] rounded-[44px] bg-white/20 backdrop-blur-[12.5px] px-4 overflow-hidden">
    <div class="flex items-center gap-4">
      <img :src="logoSvg" alt="Logo" class="h-4 w-auto" />

      <Link :href="route('login')">
        <img :src="entrarSvg" alt="Entrar"
            class="h-12 w-auto cursor-pointer hover:scale-105 transition-transform" />
      </Link>

      <Link :href="route('register')">
        <img :src="registerSvg" alt="Cadastrar"
            class="h-12 w-auto cursor-pointer hover:scale-105 transition-transform" />
      </Link>

      <img :src="listSvg" alt="Lista"
          class="h-5 w-auto cursor-pointer hover:scale-105 transition-transform" />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

// Importe os SVGs como módulos
import logoSvg from '@/images/svg-icons/logo.svg'
import entrarSvg from '@/images/svg-icons/entrar-button.svg'
import registerSvg from '@/images/svg-icons/register-button.svg'
import listSvg from '@/images/svg-icons/list-button.svg'
</script>
