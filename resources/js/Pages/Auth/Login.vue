<template>
    <div class="min-h-screen bg-cover bg-center bg-no-repeat flex flex-col items-center justify-start p-4 relative"
        style="background-image: url('/img/home-background.png');">

        <Head title="Login" />

        <div class="absolute inset-0 bg-black/40"></div>

        <div class="relative z-10 flex flex-col items-center w-full max-w-md mt-8">
            <div class="w-full mb-10 flex justify-center">
                <NavGlass />
            </div>

            <div class="text-center mb-6">
                <h1 class="text-white text-2xl font-bold mb-2">Seja bem-vindo(a)</h1>
            </div>

            <div class="bg-white rounded-[44px] shadow-2xl mb-8 w-full p-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <div class="text-center mb-6">
                        <p class="text-lg">
                            Junte-se à plataforma e aproveite <br> todos os recursos de graça.
                        </p>
                    </div>

                    <div>
                        <input id="email" type="email" v-model="form.email" required autofocus autocomplete="email"
                            class="w-full h-[48px] bg-[#E9E9EA] rounded-[44px] px-5 text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-[#00A859] focus:outline-none transition-all"
                            placeholder="Email" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <input id="password" type="password" v-model="form.password" required
                            autocomplete="current-password"
                            class="w-full h-[48px] bg-[#E9E9EA] rounded-[44px] px-5 text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-[#00A859] focus:outline-none transition-all"
                            placeholder="Senha" />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" :disabled="form.processing"
                            class="w-12 h-12 rounded-full flex items-center justify-center transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <img v-if="!form.processing" src="/svg-icons/seta.svg" alt="seta" class="w-10 h-10" />
                        </button>
                    </div>
                </form>
            </div>

            <div class="w-full mb-8 flex justify-between gap-4">
                <a :href="route('google.redirect')"
                    class="w-full h-[43px] flex items-center justify-center gap-3 rounded-[44px] font-medium transition-all duration-200 bg-black/70 backdrop-blur-[12.5px] text-white border border-white/30 hover:bg-black/80">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M18.1712 8.36791H17.9998V8.33325H9.99984V11.6666H14.7098C14.0223 13.6074 12.1762 14.9999 9.99984 14.9999C7.23859 14.9999 4.99984 12.7612 4.99984 9.99992C4.99984 7.23867 7.23859 4.99992 9.99984 4.99992C11.2748 4.99992 12.434 5.48075 13.3173 6.26625L15.674 3.90959C14.1857 2.52125 12.1948 1.66658 9.99984 1.66658C5.39734 1.66658 1.6665 5.39741 1.6665 9.99992C1.6665 14.6024 5.39734 18.3333 9.99984 18.3333C14.6023 18.3333 18.3332 14.6024 18.3332 9.99992C18.3332 9.44125 18.2757 8.89575 18.1712 8.36791Z"
                            fill="#FFC107" />
                        <path
                            d="M2.62744 6.12125L5.36536 8.12917C6.10619 6.29417 7.90036 4.99992 9.99994 4.99992C11.2749 4.99992 12.4341 5.48075 13.3174 6.26625L15.6741 3.90959C14.1858 2.52125 12.1949 1.66658 9.99994 1.66658C6.79911 1.66658 4.02327 3.47375 2.62744 6.12125Z"
                            fill="#FF3D00" />
                        <path
                            d="M10 18.3333C12.1525 18.3333 14.1084 17.5096 15.5871 16.17L13.0079 13.9875C12.1432 14.6452 11.0865 15.0009 10 15C7.83253 15 5.99211 13.6179 5.29878 11.6892L2.5813 13.7829C3.96047 16.4817 6.7613 18.3333 10 18.3333Z"
                            fill="#4CAF50" />
                        <path
                            d="M18.1712 8.36791H17.9998V8.33325H9.99984V11.6666H14.7098C14.3807 12.5902 13.7589 13.3971 12.9407 13.9871L12.9398 13.9862L15.519 16.1687C15.3657 16.3062 18.3332 14.1666 18.3332 9.99992C18.3332 9.44125 18.2757 8.89575 18.1712 8.36791Z"
                            fill="#1976D2" />
                    </svg>
                    <span class="text-sm">Continuar com Google</span>
                </a>

                <button type="button"
                    class="w-full h-[43px] flex items-center justify-center gap-3 rounded-[44px] font-medium transition-all duration-200 bg-transparent text-white border border-white/30 hover:bg-white/10">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M15.7956 10.5555C15.7833 8.88883 16.5167 7.77772 18.0056 6.99994C17.25 5.87494 16.0833 5.27772 14.5167 5.20827C12.9944 5.12494 11.3333 6.20827 10.6889 6.20827C10.0111 6.20827 8.57222 5.24994 7.35556 5.24994C4.87778 5.27772 2.5 7.08327 2.5 10.8749C2.5 11.9999 2.71111 13.1527 3.13333 14.3333C3.73889 15.9583 5.87222 19.4999 7.85556 19.4444C8.95 19.4166 9.61111 18.7083 11.1722 18.7083C12.7056 18.7083 13.3222 19.4444 14.5167 19.4444C16.5167 19.4166 18.4778 16.2499 19.0444 14.6111C16.2556 13.4583 15.7956 10.6388 15.7956 10.5555ZM13.7889 3.49994C14.8444 2.24994 14.7444 1.24994 14.7056 0.833275C13.8444 0.888831 12.8056 1.45827 12.1833 2.12494C11.4833 2.87494 11.0333 3.7916 11.1333 4.83327C12.0611 4.9166 13.0056 4.37494 13.7889 3.49994Z"
                            fill="currentColor" />
                    </svg>
                    <span class="text-sm">Continuar com Apple</span>
                </button>
            </div>

            <div class="text-center">
                <Link :href="route('password.request')"
                    class="text-sm text-gray-200 hover:text-gray-300 transition-colors duration-200">
                Obter ajuda para fazer <span class="underline text-white font-semibold">logon</span>
                </Link>
            </div>

            <div v-if="status" class="mt-4 bg-green-50 border border-green-200 rounded-2xl p-4 text-green-700 text-sm">
                {{ status }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import NavGlass from '@/Components/Nav/NavGlass.vue'

defineProps<{
    canResetPassword?: boolean
    status?: string
}>()

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password')
        },
    })
}
</script>
