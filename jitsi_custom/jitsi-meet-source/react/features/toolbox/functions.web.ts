import {IReduxState} from '../app/types';
import {hasAvailableDevices} from '../base/devices/functions';
import {MEET_FEATURES} from '../base/jwt/constants';
import {isJwtFeatureEnabled} from '../base/jwt/functions';
import {IGUMPendingState} from '../base/media/types';
import ChatButton from '../chat/components/web/ChatButton';
import ParticipantsPaneButton from '../participants-pane/components/web/ParticipantsPaneButton';
import RaiseHandContainerButton from '../reactions/components/web/RaiseHandContainerButtons';
import ReactionsMenuButton from '../reactions/components/web/ReactionsMenuButton';
import RecordButton from '../recording/components/Recording/web/RecordButton';
import RecordButtonElectron from '../recording/components/Recording/web/RecordButtonElectron';
import {isScreenMediaShared} from '../screen-share/functions';
import SettingsButton from '../settings/components/web/SettingsButton';
import TileViewButton from '../video-layout/components/TileViewButton';
import VideoBackgroundButton from '../virtual-background/components/VideoBackgroundButton';
import {isWhiteboardVisible} from '../whiteboard/functions';
import AudioSettingsButton from './components/web/AudioSettingsButton';
import CustomOptionButton from './components/web/CustomOptionButton';
import FullscreenButton from './components/web/FullscreenButton';
import ShareDesktopButton from './components/web/ShareDesktopButton';
import VideoSettingsButton from './components/web/VideoSettingsButton';
import {TOOLBAR_TIMEOUT} from './constants';
import {IToolboxButton, NOTIFY_CLICK_MODE} from './types';
import SpeakerStatsButton from '../speaker-stats/components/web/SpeakerStatsButton';
import {isMobileBrowser} from "../base/environment/utils";

export * from './functions.any';

/**
 * Helper for getting the height of the toolbox.
 *
 * @returns {number} The height of the toolbox.
 */
export function getToolboxHeight() {
    const toolbox = document.getElementById('new-toolbox');

    return toolbox?.clientHeight || 0;
}

/**
 * Checks if the specified button is enabled.
 *
 * @param {string} buttonName - The name of the button. See {@link interfaceConfig}.
 * @param {Object|Array<string>} state - The redux state or the array with the enabled buttons.
 * @returns {boolean} - True if the button is enabled and false otherwise.
 */
export function isButtonEnabled(buttonName: string, state: IReduxState | Array<string>) {
    const buttons = Array.isArray(state) ? state : state['features/toolbox'].toolbarButtons || [];

    return buttons.includes(buttonName);
}

/**
 * Indicates if the toolbox is visible or not.
 *
 * @param {IReduxState} state - The state from the Redux store.
 * @returns {boolean} - True to indicate that the toolbox is visible, false -
 * otherwise.
 */
export function isToolboxVisible(state: IReduxState) {
    const {iAmRecorder, iAmSipGateway, toolbarConfig} = state['features/base/config'];
    const {alwaysVisible} = toolbarConfig || {};
    const {
        timeoutID,
        visible
    } = state['features/toolbox'];
    const {audioSettingsVisible, videoSettingsVisible} = state['features/settings'];
    const whiteboardVisible = isWhiteboardVisible(state);

    return Boolean(!iAmRecorder && !iAmSipGateway
        && (
            timeoutID
            || visible
            || alwaysVisible
            || audioSettingsVisible
            || videoSettingsVisible
            || whiteboardVisible
        ));
}

/**
 * Indicates if the audio settings button is disabled or not.
 *
 * @param {IReduxState} state - The state from the Redux store.
 * @returns {boolean}
 */
export function isAudioSettingsButtonDisabled(state: IReduxState) {

    return !(hasAvailableDevices(state, 'audioInput')
            || hasAvailableDevices(state, 'audioOutput'))
        || state['features/base/config'].startSilent;
}

/**
 * Indicates if the desktop share button is disabled or not.
 *
 * @param {IReduxState} state - The state from the Redux store.
 * @returns {boolean}
 */
export function isDesktopShareButtonDisabled(state: IReduxState) {
    const {muted, unmuteBlocked} = state['features/base/media'].video;
    const videoOrShareInProgress = !muted || isScreenMediaShared(state);
    const enabledInJwt = isJwtFeatureEnabled(state, MEET_FEATURES.SCREEN_SHARING, true, true);

    return !enabledInJwt || (unmuteBlocked && !videoOrShareInProgress);
}

/**
 * Indicates if the video settings button is disabled or not.
 *
 * @param {IReduxState} state - The state from the Redux store.
 * @returns {boolean}
 */
export function isVideoSettingsButtonDisabled(state: IReduxState) {
    return !hasAvailableDevices(state, 'videoInput');
}

/**
 * Indicates if the video mute button is disabled or not.
 *
 * @param {IReduxState} state - The state from the Redux store.
 * @returns {boolean}
 */
export function isVideoMuteButtonDisabled(state: IReduxState) {
    const {muted, unmuteBlocked, gumPending} = state['features/base/media'].video;

    return !hasAvailableDevices(state, 'videoInput')
        || (unmuteBlocked && Boolean(muted))
        || gumPending !== IGUMPendingState.NONE;
}

/**
 * If an overflow drawer should be displayed or not.
 * This is usually done for mobile devices or on narrow screens.
 *
 * @param {IReduxState} state - The state from the Redux store.
 * @returns {boolean}
 */
export function showOverflowDrawer(state: IReduxState) {
    return state['features/toolbox'].overflowDrawer && isMobileBrowser();
}

/**
 * Returns true if the overflow menu button is displayed and false otherwise.
 *
 * @param {IReduxState} state - The state from the Redux store.
 * @returns {boolean} - True if the overflow menu button is displayed and false otherwise.
 */
export function showOverflowMenu(state: IReduxState) {
    return state['features/toolbox'].overflowMenuVisible;
}

/**
 * Indicates whether the toolbox is enabled or not.
 *
 * @param {IReduxState} state - The state from the Redux store.
 * @returns {boolean}
 */
export function isToolboxEnabled(state: IReduxState) {
    return state['features/toolbox'].enabled;
}

/**
 * Returns the toolbar timeout from config or the default value.
 *
 * @param {IReduxState} state - The state from the Redux store.
 * @returns {number} - Toolbar timeout in milliseconds.
 */
export function getToolbarTimeout(state: IReduxState) {
    const {toolbarConfig} = state['features/base/config'];

    return toolbarConfig?.timeout || TOOLBAR_TIMEOUT;
}

/**
 * Returns all buttons that could be rendered.
 *
 * @param {Object} _customToolbarButtons - An array containing custom buttons objects.
 * @returns {Object} The button maps mainMenuButtons and overflowMenuButtons.
 */
export function getAllToolboxButtons(_customToolbarButtons?: {
    backgroundColor?: string;
    icon: string;
    id: string;
    text: string;
}[]): { [key: string]: IToolboxButton; } {

    const microphone = {
        key: 'microphone',
        Content: AudioSettingsButton,
        group: 0
    };

    const camera = {
        key: 'camera',
        Content: VideoSettingsButton,
        group: 0
    };

    const chat = {
        key: 'chat',
        Content: ChatButton,
        group: 0
    };

    const desktop = {
        key: 'desktop',
        Content: ShareDesktopButton,
        group: 0
    };

    // In Narrow layout and mobile web we are using drawer for popups and that is why it is better to include
    // all forms of reactions in the overflow menu. Otherwise the toolbox will be hidden and the reactions popup
    // misaligned.
    const raisehand = {
        key: 'raisehand',
        Content: RaiseHandContainerButton,
        group: 0
    };

    const reactions = {
        key: 'reactions',
        Content: ReactionsMenuButton,
        group: 0
    };

    const participants = {
        key: 'participants-pane',
        Content: ParticipantsPaneButton,
        group: 0
    };

    const tileview = {
        key: 'tileview',
        Content: TileViewButton,
        group: 0
    };

    const fullscreen = {
        key: 'fullscreen',
        Content: FullscreenButton,
        group: 1
    };

    const recording = {
        key: 'recording',
        Content: RecordButton,
        group: 1
    };

    const recording_electron = {
        key: 'recording_electron',
        Content: RecordButtonElectron,
        group: 1
    };

    const speakerStats = {
        key: 'stats',
        Content: SpeakerStatsButton,
        group: 1
    };

    const virtualBackground = {
        key: 'select-background',
        Content: VideoBackgroundButton,
        group: 1
    };

    const settings = {
        key: 'settings',
        Content: SettingsButton,
        group: 2
    };

    const customButtons = _customToolbarButtons?.reduce((prev, {backgroundColor, icon, id, text}) => {
        return {
            ...prev,
            [id]: {
                backgroundColor,
                key: id,
                Content: CustomOptionButton,
                group: 4,
                icon,
                text
            }
        };
    }, {});

    return {
        microphone,
        camera,
        desktop,
        chat,
        raisehand,
        reactions,
        participants,
        tileview,
        fullscreen,
        recording,
        recording_electron,
        speakerStats,
        virtualBackground,
        settings,
        ...customButtons
    };
}

/**
 * Returns the list of participant menu buttons that have that notify the api when clicked.
 *
 * @param {Object} state - The redux state.
 * @returns {Map<string, NOTIFY_CLICK_MODE>} - The list of participant menu buttons.
 */
export function getParticipantMenuButtonsWithNotifyClick(state: IReduxState): Map<string, NOTIFY_CLICK_MODE> {
    return state['features/toolbox'].participantMenuButtonsWithNotifyClick;
}
