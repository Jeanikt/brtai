import { ref, Ref } from "vue";

interface LoadingState {
    isLoading: Ref<boolean>;
    loadingMessage: Ref<string>;
    showLoading: (message?: string) => void;
    hideLoading: () => void;
    withLoading: <T>(fn: () => Promise<T>, message?: string) => Promise<T>;
}

export const useLoading = (): LoadingState => {
    const isLoading: Ref<boolean> = ref(false);
    const loadingMessage: Ref<string> = ref("Carregando...");

    const showLoading = (message: string = "Carregando...") => {
        isLoading.value = true;
        loadingMessage.value = message;
    };

    const hideLoading = () => {
        isLoading.value = false;
    };

    const withLoading = async <T>(
        fn: () => Promise<T>,
        message: string = "Carregando..."
    ): Promise<T> => {
        showLoading(message);
        try {
            const result = await fn();
            return result;
        } finally {
            hideLoading();
        }
    };

    return {
        isLoading,
        loadingMessage,
        showLoading,
        hideLoading,
        withLoading,
    };
};
