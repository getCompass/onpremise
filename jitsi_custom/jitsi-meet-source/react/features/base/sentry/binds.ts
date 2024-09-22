import { setUser } from "@sentry/react";
import { ErrorEvent } from "@sentry/react";

export const bindDataForSend = (event: ErrorEvent) => {
    bindUser(event);
    bindConference(event);
    bindConnectStats(event);
}

const bindUser = (event: ErrorEvent) => {

    if (!APP.store || !event.extra) {
        return;
    }

    const userId = APP.conference.getMyUserId();
    const { user } = APP.store.getState()['features/base/jwt'] ?? {};

    if (user) {
        event.extra['jwt-user'] = { id: user.id,  type: user.type }
        setUser({ id: user.id })
    }

    if (userId) {
        event.extra['user'] = { id: userId }
    }
}

const bindConference = (event: ErrorEvent) => {

    if (!APP.store || !event.extra) {
        return;
    }

    const { conference, error } = APP.store.getState()['features/base/conference'] ?? {};

    event.extra['conference'] = {
        sessionId: conference?.sessionId,
        error,
    }
}

const bindConnectStats = (event: ErrorEvent) => {

    try {

        // иногда падает, ибо модуль не инициализирован
        const stats = APP.conference.getStats();
        if (!stats || !event.extra) {
            return;
        }

        event.extra['connect-stats'] = {
            bitrate: stats.bitrate,
            packetLoss: stats.packetLoss,
            connectionQuality: stats.connectionQuality,
            connectionState: APP.conference.getConnectionState()
        }
    } catch (e) {
        // nope
    }
}

