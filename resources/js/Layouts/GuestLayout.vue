<template>
    <div class="min-h-screen bg-cover bg-center bg-no-repeat flex items-center justify-center p-4"
        style="background-image: url('/img/home-background.png');">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="bg-white rounded-3xl p-8 shadow-2xl">
            <slot />
        </div>
    </div>
</template>

<script setup lang="ts">
import NavGlass from '@/Components/Nav/NavGlass.vue';

</script>
