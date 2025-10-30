<template>
    <div
        class="flex items-center justify-center w-[430px] h-[63px] rounded-[44px] bg-white/20 backdrop-blur-[12.5px] px-4 overflow-hidden">
        <div class="flex items-center gap-4">
            <img src="/svg-icons/logo.svg" alt="Logo" class="h-4 w-auto" />

            <Link :href="route('login')">
            <img src="/svg-icons/entrar-button.svg" alt="entrar"
                class="h-12 w-auto cursor-pointer hover:scale-105 transition-transform" />
            </Link>

            <Link :href="route('register')">
            <img src="/svg-icons/register-button.svg" alt="register"
                class="h-12 w-auto cursor-pointer hover:scale-105 transition-transform" />
            </Link>

            <img src="/svg-icons/list-button.svg" alt="list"
                class="h-5 w-auto cursor-pointer hover:scale-105 transition-transform" />
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<style scoped></style>
