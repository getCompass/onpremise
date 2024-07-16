import { IReduxState } from '../app/types';
import { IStateful } from '../base/app/types';
import { hasAvailableDevices } from '../base/devices/functions';
import { TOOLBOX_ALWAYS_VISIBLE, TOOLBOX_ENABLED } from '../base/flags/constants';
import { getFeatureFlag } from '../base/flags/functions';
import { getParticipantCountWithFake } from '../base/participants/functions';
import { toState } from '../base/redux/functions';
import { isLocalVideoTrackDesktop } from '../base/tracks/functions';

export * from './functions.any';

const WIDTH = {
    FIT_9_ICONS: 560,
    FIT_8_ICONS: 500,
    FIT_7_ICONS: 440,
    FIT_6_ICONS: 380
};

/**
 * Returns a set of the buttons that are shown in the toolbar
 * but removed from the overflow menu, based on the width of the screen.
 *
 * @param {number} width - The width of the screen.
 * @returns {Set}
 */
export function getMovableButtons(width: number): Set<string> {
    let buttons: string[] = [];

    switch (true) {
    case width >= WIDTH.FIT_9_ICONS: {
        buttons = [ 'chat', 'togglecamera', 'screensharing', 'raisehand', 'tileview' ];
        break;
    }
    case width >= WIDTH.FIT_8_ICONS: {
        buttons = [ 'chat', 'togglecamera', 'raisehand', 'tileview' ];
        break;
    }

    case width >= WIDTH.FIT_7_ICONS: {
        buttons = [ 'chat', 'togglecamera', 'raisehand' ];
        break;
    }

    case width >= WIDTH.FIT_6_ICONS: {
        buttons = [ 'chat', 'togglecamera' ];
        break;
    }

    default: {
        buttons = [ 'chat' ];
    }
    }

    return new Set(buttons);
}

/**
 * Indicates if the desktop share button is disabled or not.
 *
 * @param {IReduxState} state - The state from the Redux store.
 * @returns {boolean}
 */
export function isDesktopShareButtonDisabled(state: IReduxState) {
    const { muted, unmuteBlocked } = state['features/base/media'].video;
    const videoOrShareInProgress = !muted || isLocalVideoTrackDesktop(state);

    return unmuteBlocked && !videoOrShareInProgress;
}

/**
 * Returns true if the toolbox is visible.
 *
 * @param {IStateful} stateful - A function or object that can be
 * resolved to Redux state by the function {@code toState}.
 * @returns {boolean}
 */
export function isToolboxVisible(stateful: IStateful) {
    const state = toState(stateful);
    const { toolbarConfig } = state['features/base/config'];
    const { alwaysVisible } = toolbarConfig || {};
    const { enabled, visible } = state['features/toolbox'];
    const participantCount = getParticipantCountWithFake(state);
    const alwaysVisibleFlag = getFeatureFlag(state, TOOLBOX_ALWAYS_VISIBLE, false);
    const enabledFlag = getFeatureFlag(state, TOOLBOX_ENABLED, true);

    return enabledFlag && enabled
        && (alwaysVisible || visible || participantCount === 1 || alwaysVisibleFlag);
}

/**
 * Indicates if the video mute button is disabled or not.
 *
 * @param {IReduxState} state - The state from the Redux store.
 * @returns {boolean}
 */
export function isVideoMuteButtonDisabled(state: IReduxState) {
    const { muted, unmuteBlocked } = state['features/base/media'].video;

    return !hasAvailableDevices(state, 'videoInput')
        || (unmuteBlocked && Boolean(muted))
        || isLocalVideoTrackDesktop(state);
}
