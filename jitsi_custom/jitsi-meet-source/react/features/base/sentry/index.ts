import {
    init,
    getCurrentScope,
    browserTracingIntegration,
    browserProfilingIntegration,
    captureConsoleIntegration
} from "@sentry/react";
import {clearBreadcrumb, clearErrorEvent, clearTransaction} from "./filters";
import { bindDataForSend } from "./binds";

export const initSentry = () => {

    init({
        dsn: __SENTRY_DSN__,
        release: __RELEASE__,
        enabled: !!__SENTRY_DSN__,
        environment: __SENTRY_ENVIRONMENT__,
        maxBreadcrumbs: 50,

        integrations: [
            browserTracingIntegration(),
            browserProfilingIntegration(),
            captureConsoleIntegration({ levels: ['error'] })
        ],

        tracesSampleRate: 1,

        beforeSendTransaction: (event) => {
            clearTransaction(event)
            return event;
        },

        beforeBreadcrumb: (breadcrumbs) => {
            clearBreadcrumb(breadcrumbs);
            return breadcrumbs
        },

        beforeSend: (event) => {
            clearErrorEvent(event);
            bindDataForSend(event);
            return event;
        },
    })

    // меняем имя транзаций
    // ибо по умолчанию в ней идет конфидициальная инфа
    getCurrentScope().setTransactionName(window.location.host)
}



