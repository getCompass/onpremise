import { SEND_UPDATED_STATS } from "./actionTypes";
import MiddlewareRegistry from "../base/redux/MiddlewareRegistry";
import { preparePayloadForCollector } from "./functions";
import { IStore } from "../app/types";
import { getLocalParticipant } from "../base/participants/functions";
import { COMMAND_QUALITY_LEVEL } from "../quality-control/constants";
import { COMMAND_PARTICIPANT_STATS } from "./constants";
import { isParticipantAudioMuted } from "../base/tracks/functions.any";

MiddlewareRegistry.register(store => next => action => {
    switch (action.type) {
    case SEND_UPDATED_STATS: {

        const { conference } = store.getState()['features/base/conference'];
        if (conference === undefined) {
            break;
        }

        const state = store.getState();
        const localParticipant = getLocalParticipant(state);
        const rawRoomId = state['features/base/conference'].room ?? "";
        const parts = rawRoomId.split('_');
        const conferenceId = parts[1] ?? "";
        const memberId = localParticipant?.jwtId ?? "";
        const ssrcMediaTypeMap = _buildSsrcMediaTypeMap(store.getState);
        const payload = preparePayloadForCollector(conferenceId, memberId, action.stats, ssrcMediaTypeMap);
        const messagePayload = {
            type: COMMAND_PARTICIPANT_STATS,
            payload
        };
        conference.sendMessage(JSON.stringify(messagePayload));
        break;
    }
    default:
        break;
    }

    return next(action);
});

/**
 * Возвращает объект, где ключ — это SSRC, а значение — videoType ("camera"/"desktop").
 *
 * @returns {Object} Пример: { 123456789: "camera", 987654321: "desktop" }
 */
function _buildSsrcMediaTypeMap(getState: IStore['getState']) {
    const ssrcMap: Record<number, string> = {};

    const tracks = getState()['features/base/tracks'];

    for (const track of tracks) {
        const jitsiTrack = track?.jitsiTrack;
        if (!jitsiTrack) {
            continue;
        }

        const ssrc: number = jitsiTrack.getSsrc();
        if (!ssrc) {
            continue;
        }

        const videoType = jitsiTrack.getVideoType();
        if (videoType) {
            ssrcMap[ssrc] = videoType;
        }
    }

    return ssrcMap;
}

