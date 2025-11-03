<template>
    <AuthenticatedLayout>
        <div class="space-y-6 font-prompt px-3 sm:px-6">
            <!-- Cabeçalho -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                        🚀 Roadmap
                    </h1>
                    <p class="text-sm text-gray-600">
                        Veja o que está sendo desenvolvido e envie suas sugestões!
                    </p>
                </div>

                <button @click="openModal"
                    class="bg-purple-500 text-white px-4 sm:px-5 py-2 rounded-full text-sm font-semibold shadow-sm hover:bg-purple-600 transition">
                    Nova sugestão
                </button>
            </div>

            <!-- Grade principal -->
            <div
                class="flex flex-col md:grid md:grid-cols-2 xl:grid-cols-4 bg-gray-50 rounded-2xl overflow-hidden w-full max-w-[1600px] mx-auto divide-y md:divide-y-0 md:divide-x divide-gray-200">
                <div v-for="(items, column) in groupedRoadmap" :key="column"
                    class="bg-white p-5 sm:p-6 w-full flex flex-col">
                    <h2 class="font-semibold text-base sm:text-lg text-gray-800 mb-3 flex items-center gap-2">
                        {{ columnLabel(column) }}
                    </h2>

                    <div v-if="items.length"
                        :class="['space-y-3 transition-all duration-200', items.length > 4 ? 'max-h-[420px] overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-gray-300 hover:scrollbar-thumb-gray-400' : '']">
                        <div v-for="item in items" :key="item.id"
                            class="bg-gray-50 p-3 sm:p-4 rounded-xl border border-gray-200 flex flex-col justify-between hover:shadow transition">
                            <div>
                                <h3 class="font-semibold text-gray-900 text-sm sm:text-base leading-tight">
                                    {{ item.title }}
                                </h3>
                                <p class="text-xs sm:text-sm text-gray-600 mt-1 leading-snug break-words">
                                    {{ item.description || "Sem descrição." }}
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mt-3 gap-2">
                                <button @click="like(item)"
                                    class="flex items-center justify-center gap-2 text-xs sm:text-sm text-gray-700 bg-white border border-gray-200 rounded-full px-3 py-1 hover:bg-purple-100 hover:border-purple-300 transition">
                                    ❤️
                                    <span class="font-medium ml-1">{{ item.likes_count ?? 0 }}</span>
                                </button>

                                <div v-if="isAdmin" class="w-full sm:w-auto">
                                    <select v-model="item.status" @change="updateStatus(item)"
                                        class="w-full text-xs sm:text-sm border rounded-lg px-2 py-1 focus:ring-purple-500 focus:border-purple-500">
                                        <option value="suggested">🧠 Sugestão</option>
                                        <option value="planned">🪜 Próximos Passos</option>
                                        <option value="in_progress">🍳 Cozinhando</option>
                                        <option value="completed">🎉 Tudo Pronto!</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p v-else class="text-xs sm:text-sm text-gray-500 italic">
                        Nenhum item aqui ainda.
                    </p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative">
                <button @click="closeModal"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-lg font-bold">
                    ✕
                </button>

                <h1 class="text-xl font-bold text-gray-800 mb-4">Nova Sugestão 💡</h1>

                <form @submit.prevent="submit">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Título</label>
                            <input v-model="form.title" type="text"
                                class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Ex: Suporte a login com Apple" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea v-model="form.description"
                                class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                                rows="4" placeholder="Descreva a ideia..."></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="closeModal"
                                class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                                Cancelar
                            </button>

                            <button type="submit"
                                class="px-4 py-2 rounded-lg bg-purple-600 text-white font-semibold hover:bg-purple-700 transition">
                                Enviar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Toast -->
        <transition name="toast-fade">
            <div v-if="toast.show"
                :class="['fixed right-4 bottom-6 z-50 px-4 py-3 rounded-lg shadow-lg', toast.type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white']">
                <div class="flex items-center gap-3">
                    <div v-if="toast.type === 'success'">✅</div>
                    <div v-else>⚠️</div>
                    <div class="text-sm">{{ toast.message }}</div>
                    <button @click="hideToast" class="ml-3 text-white/80 hover:text-white">✕</button>
                </div>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const { props } = usePage()
const roadmap = ref(props.roadmap || [])
const isAdmin = props.isAdmin ?? false

// Modal controls
const showModal = ref(false)
const openModal = () => (showModal.value = true)
const closeModal = () => (showModal.value = false)

// Form
const form = useForm({ title: '', description: '' })
const submit = () => {
    form.post(route('roadmap.store'), {
        onSuccess: () => {
            closeModal()
            form.reset()
            showToast('Sugestão enviada com sucesso!', 'success')
        },
        onError: (errors) => {
            showToast('Erro: ' + (errors.title?.[0] ?? 'Verifique os campos.'), 'error')
        }
    })
}

// Group roadmap items
const groupedRoadmap = computed(() => {
    const groups = { suggested: [], planned: [], in_progress: [], completed: [] };
    (Array.isArray(roadmap.value) ? roadmap.value : []).forEach((item) => {
        if (groups[item.status]) groups[item.status].push(item)
    })
    return groups
})

// Column labels
const columnLabel = (status) => ({
    suggested: '🧠 Sugestões',
    planned: '🪜 Próximos Passos',
    in_progress: '🍳 Cozinhando',
    completed: '🎉 Tudo Pronto!',
}[status] || status)

// Update status (admin)
const updateStatus = (item) => {
    if (!item.id) return showToast('Item inválido', 'error')
    router.put(route('roadmap.updateStatus', item.id), { status: item.status }, {
        preserveScroll: true,
        onSuccess: () => showToast('Status atualizado!', 'success'),
        onError: () => showToast('Erro ao atualizar status', 'error')
    })
}

// Like (with optimistic update)
const like = (item) => {
    if (!item || !item.id) return showToast('Item sem ID válido', 'error')
    const found = roadmap.value.find(i => i.id === item.id)
    if (found) found.likes_count = (found.likes_count ?? 0) + 1
    router.post(route('roadmap.like', item.id), {}, {
        preserveScroll: true,
        onError: () => showToast('Erro ao curtir', 'error'),
    })
}

// Toast
const toast = ref({ show: false, message: '', type: 'success', timeoutId: null })
const showToast = (message, type = 'success', timeout = 3000) => {
    if (toast.value.timeoutId) clearTimeout(toast.value.timeoutId)
    toast.value = { show: true, message, type, timeoutId: setTimeout(hideToast, timeout) }
}
const hideToast = () => {
    if (toast.value.timeoutId) clearTimeout(toast.value.timeoutId)
    toast.value = { show: false, message: '', type: 'success', timeoutId: null }
}
</script>

<style>
.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: #d1d5db;
    border-radius: 9999px;
}

.toast-fade-enter-active,
.toast-fade-leave-active {
    transition: opacity .25s ease, transform .2s ease;
}

.toast-fade-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.toast-fade-leave-to {
    opacity: 0;
    transform: translateY(8px);
}
</style>
