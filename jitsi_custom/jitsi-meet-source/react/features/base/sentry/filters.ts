import { ErrorEvent } from "@sentry/react";
import { Breadcrumb, TransactionEvent } from "@sentry/types";

export const clearURL = (url: string): string => {
    return url ? new URL(url).origin : '';
}

export const clearErrorEvent = (event: ErrorEvent): void => {
    if (event.request?.url) {
        event.request.url = clearURL(event.request.url);
    }

    if (event.transaction) {
        event.transaction = window.location.host;
    }
}

export const clearBreadcrumb = (breadcrumb: Breadcrumb): void => {
    if (breadcrumb.data?.url) {
        breadcrumb.data.url = clearURL(breadcrumb.data.url);
    }
}

export const clearTransaction = (event: TransactionEvent): void => {
    if (event.request?.url) {
        event.request.url = clearURL(event.request.url);
    }

    if (event.transaction) {
        event.transaction = window.location.host;
    }
}