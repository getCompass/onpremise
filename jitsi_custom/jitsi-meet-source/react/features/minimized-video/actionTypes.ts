/**
 * Action to select the participant to be displayed in MinimizedVideo.
 *
 * {
 *     type: SELECT_MINIMIZED_VIDEO_PARTICIPANT,
 *     participantId: (string|undefined)
 * }
 */
export const SELECT_MINIMIZED_VIDEO_PARTICIPANT
    = 'SELECT_MINIMIZED_VIDEO_PARTICIPANT';

/**
 * Action to set the dimensions of the minimized video.
 *
 * {
 *     type: SET_MINIMIZED_VIDEO_DIMENSIONS,
 *     height: number,
 *     width: number
 * }
 */
export const SET_MINIMIZED_VIDEO_DIMENSIONS = 'SET_MINIMIZED_VIDEO_DIMENSIONS';

/**
 * Action to update the redux store with the current resolution of minimized video.
 *
 * @returns {{
 *     type: UPDATE_KNOWN_MINIMIZED_VIDEO_RESOLUTION,
 *     resolution: number
 * }}
 */
export const UPDATE_KNOWN_MINIMIZED_VIDEO_RESOLUTION
    = 'UPDATE_KNOWN_MINIMIZED_VIDEO_RESOLUTION';

/**
 * Action to set the redux store of the current show me what I'm sharing flag value.
 *
 * @returns {{
 *     type: SET_SEE_WHAT_IS_BEING_SHARED,
 *     seeWhatIsBeingShared: boolean
 * }}
 */
export const SET_SEE_WHAT_IS_BEING_SHARED
        = 'SET_SEE_WHAT_IS_BEING_SHARED';
