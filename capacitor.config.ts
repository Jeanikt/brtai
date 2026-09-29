import type { CapacitorConfig } from "@capacitor/cli";

const config: CapacitorConfig = {
    appId: "com.brotaai.app",
    appName: "BrotaAI",
    webDir: "public",
    server: {
        url: "http://192.168.0.150:8000",
        cleartext: true,
    },
    plugins: {
        SplashScreen: {
            launchShowDuration: 2000,
            backgroundColor: "#82ef00",
            showSpinner: false,
        },
    },
};

export default config;
