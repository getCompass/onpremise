import { IReduxState, IStore } from '../app/types';
import { IStateful } from '../base/app/types';
import { MEDIA_TYPE } from '../base/media/constants';
import {
    getDominantSpeakerParticipant,
    getLocalParticipant,
    getLocalScreenShareParticipant,
    getParticipantById,
    getPinnedParticipant,
    getRemoteParticipants,
    getVirtualScreenshareParticipantByOwnerId
} from '../base/participants/functions';
import { toState } from '../base/redux/functions';
import { isStageFilmstripAvailable } from '../filmstrip/functions';
import { getAutoPinSetting } from '../video-layout/functions';

import {
    SELECT_MINIMIZED_VIDEO_PARTICIPANT,
    SET_MINIMIZED_VIDEO_DIMENSIONS,
    UPDATE_KNOWN_MINIMIZED_VIDEO_RESOLUTION
} from './actionTypes';

/**
 * Action to select the participant to be displayed in MinimizedVideo based on the
 * participant id provided. If a participant id is not provided, the MinimizedVideo
 * participant will be selected based on a variety of factors: If there is a
 * dominant or pinned speaker, or if there are remote tracks, etc.
 *
 * @param {string} participant - The participant id of the user that needs to be
 * displayed on the minimized video.
 * @returns {Function}
 */
export function selectParticipantInMinimizedVideo(participant?: string) {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const state = getState();

        const participantId = participant ?? getLocalParticipant(state)?.id;
        const minimizedVideo = state['features/minimized-video'];
        const remoteScreenShares = state['features/video-layout'].remoteScreenShares;
        let latestScreenshareParticipantId;

        if (remoteScreenShares?.length) {
            latestScreenshareParticipantId = remoteScreenShares[remoteScreenShares.length - 1];
        }

        // When trying to auto pin screenshare, always select the endpoint even though it happens to be
        // the minimized video participant in redux (for the reasons listed above in the minimized video selection
        // logic above). The auto pin screenshare logic kicks in after the track is added
        // (which updates the minimized video participant and selects all endpoints because of the auto tile
        // view mode). If the screenshare endpoint is not among the forwarded endpoints from the bridge,
        // it needs to be selected again at this point.
        if (participantId !== minimizedVideo.participantId || participantId === latestScreenshareParticipantId) {
            dispatch({
                type: SELECT_MINIMIZED_VIDEO_PARTICIPANT,
                participantId
            });
        }
    };
}

/**
 * Updates the currently seen resolution of the video displayed on minimized video.
 *
 * @param {number} resolution - The current resolution (height) of the video.
 * @returns {{
 *     type: UPDATE_KNOWN_MINIMIZED_VIDEO_RESOLUTION,
 *     resolution: number
 * }}
 */
export function updateKnownMinimizedVideoResolution(resolution: number) {
    return {
        type: UPDATE_KNOWN_MINIMIZED_VIDEO_RESOLUTION,
        resolution
    };
}

/**
 * Sets the dimenstions of the minimized video in redux.
 *
 * @param {number} height - The height of the minimized video.
 * @param {number} width - The width of the minimized video.
 * @returns {{
 *     type: SET_MINIMIZED_VIDEO_DIMENSIONS,
 *     height: number,
 *     width: number
 * }}
 */
export function setMinimizedVideoDimensions(height: number, width: number) {
    return {
        type: SET_MINIMIZED_VIDEO_DIMENSIONS,
        height,
        width
    };
}

/**
 * Returns the most recent existing remote video track.
 *
 * @param {Function|Object} stateful - The redux store or {@code getState} function.
 * @private
 * @returns {(Track|undefined)}
 */
function _electLastVisibleRemoteParticipant(stateful: IStateful) {
    const state = toState(stateful);
    const tracks = state['features/base/tracks'];

    // First we try to get most recent remote video track.
    for (let i = tracks.length - 1; i >= 0; --i) {
        const track = tracks[i];

        if (!track.local && track.mediaType === MEDIA_TYPE.VIDEO && track.participantId) {
            const participant = getParticipantById(state, track.participantId);

            if (participant) {
                return participant;
            }
        }
    }
}
