// @ts-expect-error
import VideoLayout from '../../../modules/UI/videolayout/VideoLayout';
import { IStore } from '../app/types';
import { getParticipantById } from '../base/participants/functions';
import { getVideoTrackByParticipant } from '../base/tracks/functions.web';

import { SET_SEE_WHAT_IS_BEING_SHARED } from './actionTypes';

export * from './actions.any';

/**
* Captures a screenshot of the video displayed on the minimized video.
*
* @returns {Function}
*/
export function captureMinimizedVideoScreenshot() {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const state = getState();
        const minimizedVideo = state['features/minimized-video'];
        const promise = Promise.resolve();

        if (!minimizedVideo?.participantId) {
            return promise;
        }

        const participant = getParticipantById(state, minimizedVideo.participantId);
        const participantTrack = getVideoTrackByParticipant(state, participant);

        // Participants that join the call video muted do not have a jitsiTrack attached.
        if (!participantTrack?.jitsiTrack) {
            return promise;
        }
        const videoStream = participantTrack.jitsiTrack.getOriginalStream();

        if (!videoStream) {
            return promise;
        }

        // Get the video element for the minimized video, cast HTMLElement to HTMLVideoElement to make flow happy.
        /* eslint-disable-next-line no-extra-parens*/
        const videoElement = (document.getElementById('minimizedVideo') as any);

        if (!videoElement) {
            return promise;
        }

        // Create a HTML canvas and draw video on to the canvas.
        const [ track ] = videoStream.getVideoTracks();
        const { height, width } = track.getSettings() ?? track.getConstraints();
        const canvasElement = document.createElement('canvas');
        const ctx = canvasElement.getContext('2d');

        canvasElement.style.display = 'none';
        canvasElement.height = parseInt(height, 10);
        canvasElement.width = parseInt(width, 10);
        ctx?.drawImage(videoElement, 0, 0);
        const dataURL = canvasElement.toDataURL('image/png', 1.0);

        // Cleanup.
        ctx?.clearRect(0, 0, canvasElement.width, canvasElement.height);
        canvasElement.remove();

        return Promise.resolve(dataURL);
    };
}

/**
 * Resizes the minimized video container based on the dimensions provided.
 *
 * @param {number} width - Width that needs to be applied on the minimized video container.
 * @param {number} height - Height that needs to be applied on the minimized video container.
 * @returns {Function}
 */
export function resizeminimizedVideo(width: number, height: number) {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const state = getState();
        const minimizedVideo = state['features/minimized-video'];

        if (minimizedVideo) {
            const minimizedVideoContainer = VideoLayout.getMinimizedVideo();

            minimizedVideoContainer.updateContainerSize(width, height);
            minimizedVideoContainer.resize();
        }
    };
}

/**
 * Updates the value used to display what is being shared.
 *
 * @param {boolean} seeWhatIsBeingShared - The current value.
 * @returns {{
 *     type: SET_SEE_WHAT_IS_BEING_SHARED,
 *     seeWhatIsBeingShared: boolean
 * }}
 */
export function setSeeWhatIsBeingShared(seeWhatIsBeingShared: boolean) {
    return {
        type: SET_SEE_WHAT_IS_BEING_SHARED,
        seeWhatIsBeingShared
    };
}
