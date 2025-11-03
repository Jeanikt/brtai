import { ref, Ref } from "vue";

interface NotificationItem {
    id: number;
    type: "success" | "error" | "warning" | "info";
    title: string;
    message: string;
    duration: number;
}

interface NotificationContainer {
    addNotification: (notification: Omit<NotificationItem, "id">) => void;
    removeNotification: (id: number) => void;
}

const notificationContainer: Ref<NotificationContainer | null> = ref(null);

export const useNotifications = () => {
    const showNotification = (notification: Omit<NotificationItem, "id">) => {
        if (notificationContainer.value) {
            notificationContainer.value.addNotification(notification);
        } else {
            console.warn("Notification container not mounted");
        }
    };

    const success = (message: string, title: string = "Sucesso!") => {
        showNotification({ type: "success", title, message, duration: 5000 });
    };

    const error = (message: string, title: string = "Erro!") => {
        showNotification({ type: "error", title, message, duration: 5000 });
    };

    const warning = (message: string, title: string = "Atenção!") => {
        showNotification({ type: "warning", title, message, duration: 5000 });
    };

    const info = (message: string, title: string = "Informação") => {
        showNotification({ type: "info", title, message, duration: 3000 });
    };

    const setContainer = (container: NotificationContainer) => {
        notificationContainer.value = container;
    };

    return {
        success,
        error,
        warning,
        info,
        setContainer,
    };
};
