import { IStore } from '../app/types';
import { openDialog } from '../base/dialog/actions';
import { browser } from '../base/lib-jitsi-meet';
import { shouldHideShareAudioHelper } from '../base/settings/functions.web';
import { toggleScreensharing } from '../base/tracks/actions.web';

import {
    SET_SCREENSHARE_TRACKS,
    SET_SCREEN_AUDIO_SHARE_STATE, SET_LOCAL_VIDEO_WAS_MUTED_BEFORE_SCREENSHARING
} from './actionTypes';
import ShareAudioDialog from './components/web/ShareAudioDialog';
import ShareMediaWarningDialog from './components/web/ShareScreenWarningDialog';
import { isAudioOnlySharing, isScreenVideoShared } from './functions';
import { getLocalParticipant, isParticipantModerator } from "../base/participants/functions";
import { isEnabledFromState } from "../av-moderation/functions";
import { MEDIA_TYPE } from "../base/media/constants";
import { showNotification } from "../notifications/actions";
import {
    NOTIFICATION_ICON,
    NOTIFICATION_TIMEOUT_TYPE,
    SCREENSHARE_NO_PERMISSIONS_NOTIFICATION_ID
} from "../notifications/constants";

export * from './actions.any';

/**
 * Updates the current known status of the shared video.
 *
 * @param {boolean} isSharingAudio - Is audio currently being shared or not.
 * @returns {{
 *     type: SET_SCREEN_AUDIO_SHARE_STATE,
 *     isSharingAudio: boolean
 * }}
 */
export function setScreenAudioShareState(isSharingAudio: boolean) {
    return {
        type: SET_SCREEN_AUDIO_SHARE_STATE,
        isSharingAudio
    };
}

/**
 * Updates the audio track associated with the screenshare.
 *
 * @param {JitsiLocalTrack} desktopAudioTrack - The audio track captured from the screenshare.
 * @returns {{
 *      type: SET_SCREENSHARE_TRACKS,
 *      desktopAudioTrack: JitsiTrack
 * }}
 */
export function setScreenshareAudioTrack(desktopAudioTrack: any) {
    return {
        type: SET_SCREENSHARE_TRACKS,
        desktopAudioTrack
    };
}

/**
 * Updates the current known status of the video before screensharing.
 *
 * @param {boolean} isMuted - Is video was muted or not.
 * @returns {{
 *     type: SET_LOCAL_VIDEO_WAS_MUTED_BEFORE_SCREENSHARING,
 *     videoWasMutedBeforeScreensharing: boolean
 * }}
 */
export function setLocalVideoWasMutedBeforeScreensharingState(isMuted: boolean) {
    return {
        type: SET_LOCAL_VIDEO_WAS_MUTED_BEFORE_SCREENSHARING,
        videoWasMutedBeforeScreensharing: isMuted
    };
}

/**
 * Start the audio only screen sharing flow. Function will switch between off and on states depending on the context.
 *
 * @param {Object} state - The state of the application.
 * @returns {void}
 */
export function startAudioScreenShareFlow() {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const state = getState();
        const audioOnlySharing = isAudioOnlySharing(state);

        // If we're already in a normal screen sharing session, warn the user.
        if (isScreenVideoShared(state)) {
            dispatch(openDialog(ShareMediaWarningDialog, { _isAudioScreenShareWarning: true }));

            return;
        }

        // If users opted out of the helper dialog toggle directly.
        // If we're in an electron environment the helper dialog is not needed as there's only one option
        // available for audio screen sharing, namely full window audio.
        // If we're already sharing audio, toggle off.
        if (shouldHideShareAudioHelper(state) || browser.isElectron() || audioOnlySharing) {
            // We don't want to explicitly set the screens share state, by passing undefined we let the
            // underlying logic decide if it's on or off.
            dispatch(toggleScreensharing(undefined, true));

            return;
        }

        dispatch(openDialog(ShareAudioDialog));
    };
}

/**
 * Start normal screen sharing flow.Function will switch between off and on states depending on the context, and if
 * not explicitly told otherwise.
 *
 * @param {boolean} enabled - Explicitly set the screen sharing state.
 * @returns {void}
 */
export function startScreenShareFlow(enabled: boolean) {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const state = getState();
        const audioOnlySharing = isAudioOnlySharing(state);
        const localParticipant = getLocalParticipant(state);
        const isLocalParticipantModerator = isParticipantModerator(localParticipant);
        const isScreenshareModerationEnabled = isEnabledFromState(MEDIA_TYPE.SCREENSHARE, state);
        const hasPermissions = isLocalParticipantModerator || (!isLocalParticipantModerator && !isScreenshareModerationEnabled);
        const screenSharing = isScreenVideoShared(state);

        // если скриншейринг запрещен модератором и не включен в данный момент
        if (!hasPermissions && !screenSharing) {

            dispatch(showNotification({
                titleKey: 'screenshare.no_permissions_notification.title',
                descriptionKey: 'screenshare.no_permissions_notification.description',
                uid: SCREENSHARE_NO_PERMISSIONS_NOTIFICATION_ID,
                icon: NOTIFICATION_ICON.SCREENSHARE,
            }, NOTIFICATION_TIMEOUT_TYPE.SHORT));
            return;
        }

        // If we're in an audio screen sharing session, warn the user.
        if (audioOnlySharing) {
            dispatch(openDialog(ShareMediaWarningDialog, { _isAudioScreenShareWarning: false }));

            return;
        }

        dispatch(toggleScreensharing(enabled));
    };
}
