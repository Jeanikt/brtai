<template>
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Meus Ganhos</h1>
                <p class="text-gray-600 mt-2">Acompanhe seus ganhos e solicite saques</p>
            </div>

            <div v-if="tables_missing" class="bg-yellow-50 border border-yellow-200 rounded-3xl p-6 mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-yellow-500 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-yellow-800">Sistema em Manutenção</h3>
                        <p class="text-yellow-700">A funcionalidade de ganhos está temporariamente indisponível. Tente
                            novamente em alguns minutos.</p>
                    </div>
                </div>
            </div>

            <div v-else>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-3xl p-6 shadow-sm">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-green-500 rounded-2xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2m0-8c1.11 0 2.08.402 2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Ganhos Totais</p>
                                <p class="text-2xl font-bold text-gray-900">R$ {{ formatPrice(total_earnings) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-cyan-100 rounded-3xl p-6 shadow-sm">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 rounded-2xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Disponível para Saque</p>
                                <p class="text-2xl font-bold text-gray-900">R$ {{ formatPrice(available_earnings) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-violet-100 rounded-3xl p-6 shadow-sm">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-purple-500 rounded-2xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Sacado</p>
                                <p class="text-2xl font-bold text-gray-900">R$ {{ formatPrice(total_withdrawn) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Solicitar Saque</h3>

                            <div v-if="operator_account" class="space-y-4">
                                <div class="bg-gray-50 rounded-2xl p-4">
                                    <p class="text-sm text-gray-600 mb-2">Conta cadastrada</p>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ operator_account.account_type }}
                                            </p>
                                            <p class="text-sm text-gray-600">{{ operator_account.document_formatted }}
                                            </p>
                                            <p class="text-sm text-gray-600">PIX: {{ operator_account.pix_key }}</p>
                                        </div>
                                        <button @click="showEditAccountModal = true"
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Alterar
                                        </button>
                                    </div>
                                </div>

                                <form @submit.prevent="requestWithdrawal" class="space-y-4">
                                    <div>
                                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                            Valor do Saque
                                        </label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500">R$</span>
                                            <input v-model="withdrawalForm.amount" type="number" step="0.01" min="1"
                                                :max="available_earnings"
                                                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-black focus:border-black"
                                                placeholder="0,00" required>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-2">
                                            Disponível: R$ {{ formatPrice(available_earnings) }}
                                        </p>
                                    </div>

                                    <button type="submit"
                                        :disabled="withdrawalForm.processing || !withdrawalForm.amount || withdrawalForm.amount > available_earnings"
                                        class="w-full bg-black text-white py-3 px-4 rounded-2xl font-semibold hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        {{ withdrawalForm.processing ? 'Processando...' : 'Solicitar Saque' }}
                                    </button>
                                </form>
                            </div>

                            <div v-else class="text-center py-8">
                                <div
                                    class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Cadastre sua conta</h4>
                                <p class="text-gray-600 mb-4">Para solicitar saques, você precisa cadastrar seus dados
                                    bancários.</p>
                                <button @click="showAccountModal = true"
                                    class="bg-black text-white px-6 py-3 rounded-2xl font-semibold hover:bg-gray-800 transition-colors">
                                    Cadastrar Conta
                                </button>
                            </div>
                        </div>

                        <div class="bg-white rounded-3xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ganhos por Evento</h3>
                            <div class="space-y-3">
                                <div v-for="earning in earnings_by_event" :key="earning.event_id"
                                    class="flex items-center justify-between p-4 border border-gray-200 rounded-2xl">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ earning.event.name }}</p>
                                        <p class="text-sm text-gray-600">{{ formatDate(earning.event.event_date) }}</p>
                                    </div>
                                    <p class="text-lg font-bold text-green-600">R$ {{
                                        formatPrice(earning.total_earnings) }}</p>
                                </div>

                                <div v-if="earnings_by_event.length === 0" class="text-center py-8">
                                    <p class="text-gray-500">Nenhum ganho registrado ainda</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Histórico de Saques</h3>
                            <div class="space-y-4">
                                <div v-for="withdrawal in withdrawals" :key="withdrawal.id"
                                    class="border border-gray-200 rounded-2xl p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="font-semibold text-gray-900">R$ {{ formatPrice(withdrawal.amount) }}
                                        </p>
                                        <span :class="getStatusClass(withdrawal.status)"
                                            class="px-2 py-1 rounded-full text-xs font-medium">
                                            {{ getStatusText(withdrawal.status) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ formatDate(withdrawal.created_at) }}</p>
                                    <p v-if="withdrawal.failure_reason" class="text-xs text-red-600 mt-2">
                                        {{ withdrawal.failure_reason }}
                                    </p>
                                </div>

                                <div v-if="withdrawals.length === 0" class="text-center py-8">
                                    <p class="text-gray-500">Nenhum saque realizado</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-gradient-to-br from-yellow-50 to-orange-100 rounded-3xl p-6 border border-yellow-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Upgrade para Pro</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Taxas reduzidas e limite de saques maiores!
                            </p>
                            <ul class="text-sm text-gray-700 space-y-2 mb-4">
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Taxa de saque: 1% (Free: 3%)
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Saque mínimo: R$ 10 (Free: R$ 50)
                                </li>
                            </ul>
                            <Link :href="route('settings.billing')"
                                class="w-full bg-black text-white py-3 px-4 rounded-2xl font-semibold hover:bg-gray-800 transition-colors text-center block">
                            Fazer Upgrade
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showAccountModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Cadastrar Conta</h3>
                    <form @submit.prevent="storeAccount">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Conta</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button type="button" @click="accountForm.account_type = 'CPF'" :class="accountForm.account_type === 'CPF'
                                        ? 'bg-black text-white border-black'
                                        : 'bg-white text-gray-700 border-gray-300'"
                                        class="p-3 border rounded-2xl font-medium transition-colors">
                                        CPF
                                    </button>
                                    <button type="button" @click="accountForm.account_type = 'CNPJ'" :class="accountForm.account_type === 'CNPJ'
                                        ? 'bg-black text-white border-black'
                                        : 'bg-white text-gray-700 border-gray-300'"
                                        class="p-3 border rounded-2xl font-medium transition-colors">
                                        CNPJ
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label for="document" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ accountForm.account_type === 'CPF' ? 'CPF' : 'CNPJ' }}
                                </label>
                                <input v-model="accountForm.document" type="text" id="document" required
                                    :placeholder="accountForm.account_type === 'CPF' ? '000.000.000-00' : '00.000.000/0000-00'"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-black focus:border-black">
                            </div>

                            <div>
                                <label for="pix_key" class="block text-sm font-medium text-gray-700 mb-2">
                                    Chave PIX
                                </label>
                                <input v-model="accountForm.pix_key" type="text" id="pix_key" required
                                    placeholder="Chave PIX (CPF, CNPJ, Email, Telefone ou Chave Aleatória)"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-black focus:border-black">
                            </div>
                        </div>

                        <div class="flex gap-3 mt-6">
                            <button type="submit" :disabled="accountForm.processing"
                                class="flex-1 bg-black text-white px-4 py-3 rounded-2xl font-semibold hover:bg-gray-800 transition-colors disabled:opacity-50">
                                {{ accountForm.processing ? 'Validando...' : 'Cadastrar' }}
                            </button>
                            <button type="button" @click="showAccountModal = false"
                                class="flex-1 bg-gray-200 text-gray-700 px-4 py-3 rounded-2xl font-semibold hover:bg-gray-300 transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="showEditAccountModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Alterar Chave PIX</h3>
                    <form @submit.prevent="updateAccount">
                        <div class="space-y-4">
                            <div>
                                <label for="edit_pix_key" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nova Chave PIX
                                </label>
                                <input v-model="editAccountForm.pix_key" type="text" id="edit_pix_key" required
                                    placeholder="Nova chave PIX"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-black focus:border-black">
                            </div>
                        </div>

                        <div class="flex gap-3 mt-6">
                            <button type="submit" :disabled="editAccountForm.processing"
                                class="flex-1 bg-black text-white px-4 py-3 rounded-2xl font-semibold hover:bg-gray-800 transition-colors disabled:opacity-50">
                                {{ editAccountForm.processing ? 'Salvando...' : 'Salvar' }}
                            </button>
                            <button type="button" @click="showEditAccountModal = false"
                                class="flex-1 bg-gray-200 text-gray-700 px-4 py-3 rounded-2xl font-semibold hover:bg-gray-300 transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    total_earnings: Number,
    available_earnings: Number,
    total_withdrawn: Number,
    operator_account: Object,
    withdrawals: Array,
    earnings_by_event: Array,
    tables_missing: Boolean,
});

const showAccountModal = ref(false);
const showEditAccountModal = ref(false);

const accountForm = useForm({
    account_type: 'CPF',
    document: '',
    pix_key: '',
});

const editAccountForm = useForm({
    pix_key: props.operator_account?.pix_key || '',
});

const withdrawalForm = useForm({
    amount: null,
});

const storeAccount = () => {
    accountForm.post(route('earnings.account.store'), {
        onSuccess: () => {
            showAccountModal.value = false;
            accountForm.reset();
        },
    });
};

const updateAccount = () => {
    editAccountForm.put(route('earnings.account.update'), {
        onSuccess: () => {
            showEditAccountModal.value = false;
        },
    });
};

const requestWithdrawal = () => {
    withdrawalForm.post(route('earnings.withdrawal.store'), {
        onSuccess: () => {
            withdrawalForm.reset();
        },
    });
};

const formatPrice = (price) => {
    const numericPrice = Number(price) || 0;
    return numericPrice.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'processing': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
    const texts = {
        'pending': 'Pendente',
        'processing': 'Processando',
        'completed': 'Concluído',
        'failed': 'Falhou',
    };
    return texts[status] || status;
};
</script>
