import { useForm } from "@inertiajs/vue3";
import { useLoading } from "./useLoading";

interface FormWithLoadingOptions {
    loadingMessage?: string;
    onFinish?: () => void;
    onError?: (errors: any) => void;
    onSuccess?: (data: any) => void;
    [key: string]: any;
}

export const useFormWithLoading = <T extends Record<string, any>>(data: T) => {
    const form = useForm(data);
    const loadingState = useLoading();

    const submitWithLoading = (
        method: "post" | "put" | "patch" | "delete",
        url: string,
        options: FormWithLoadingOptions = {}
    ) => {
        loadingState.showLoading(options.loadingMessage || "Salvando...");

        const result = form[method](url, {
            ...options,
            onFinish: () => {
                loadingState.hideLoading();
                options.onFinish?.();
            },
            onError: (errors) => {
                loadingState.hideLoading();
                // CORREÇÃO: Garanta que onError seja chamado mesmo se não for fornecido
                if (options.onError) {
                    options.onError(errors);
                }
            },
            onSuccess: (data) => {
                loadingState.hideLoading();
                options.onSuccess?.(data);
            },
        });

        return result;
    };

    return {
        ...form,
        isLoading: loadingState.isLoading,
        loadingMessage: loadingState.loadingMessage,
        showLoading: loadingState.showLoading,
        hideLoading: loadingState.hideLoading,
        submitWithLoading,
        post: (url: string, options?: FormWithLoadingOptions) =>
            submitWithLoading("post", url, options),
        put: (url: string, options?: FormWithLoadingOptions) =>
            submitWithLoading("put", url, options),
        patch: (url: string, options?: FormWithLoadingOptions) =>
            submitWithLoading("patch", url, options),
        delete: (url: string, options?: FormWithLoadingOptions) =>
            submitWithLoading("delete", url, options),
    };
};
