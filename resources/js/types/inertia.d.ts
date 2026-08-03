import '@inertiajs/core';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            appName: string;
            locale: string;
            flash: {
                status: string | null;
                success: string | null;
            };
            impersonation: {
                impersonatorName: string;
                targetName: string;
            } | null;
            footer: {
                text: string | null;
                supportEmail: string | null;
                termsUrl: string | null;
                privacyUrl: string | null;
            };
        };
    }
}
