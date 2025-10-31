<template>
    <div class="min-h-screen bg-white font-prompt">
        <!-- Nav no padrão Figma -->
        <nav
            class="fixed top-6 left-1/2 transform -translate-x-1/2 w-[375px] h-[99px] bg-white rounded-[44px] shadow-lg z-50">
            <div class="flex justify-between items-center h-full px-8">
                <div class="flex items-center">
                    <Link :href="route('dashboard')">
                    <ApplicationLogo class="block h-8 w-auto" fill="black" />
                    </Link>
                </div>
                <div class="flex items-center">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <span class="inline-flex rounded-md">
                                <button type="button"
                                    class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 transition duration-150 ease-in-out hover:text-gray-900 focus:outline-none">
                                    {{ $page.props.auth.user.name }}
                                    <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </span>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')">
                                Perfil
                            </DropdownLink>
                            <DropdownLink :href="route('settings.index')">
                                Configurações
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Sair
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>

                <div class="flex items-center space-x-6">
                    <NavLink :href="route('events.index')" :active="route().current('events.index')"
                        class="text-gray-600 hover:text-gray-900 font-medium text-sm">
                        Meus Eventos
                    </NavLink>
                </div>


            </div>
        </nav>

        <!-- Conteúdo principal com padding para o nav fixo -->
        <main class="pt-32 pb-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>
