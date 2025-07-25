import { IReduxState } from '../app/types';
import { hasAvailableDevices } from '../base/devices/functions';
import { MEET_FEATURES } from '../base/jwt/constants';
import { isJwtFeatureEnabled } from '../base/jwt/functions';
import { IGUMPendingState } from '../base/media/types';
import { isScreenMediaShared } from '../screen-share/functions';
import { isWhiteboardVisible } from '../whiteboard/functions';

import { MAIN_COMPASS_TOOLBAR_BUTTONS_PRIORITY, MAIN_TOOLBAR_BUTTONS_PRIORITY, TOOLBAR_TIMEOUT } from './constants';
import { IMainToolbarButtonThresholds, IToolboxButton, NOTIFY_CLICK_MODE } from './types';
import { isMobileBrowser } from "../base/environment/utils";
import { browser } from "../base/lib-jitsi-meet";
import { isScreenSharingSupported } from "../desktop-picker/functions";

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
    const { iAmRecorder, iAmSipGateway, toolbarConfig } = state['features/base/config'];
    const { alwaysVisible } = toolbarConfig || {};
    const {
        timeoutID,
        visible
    } = state['features/toolbox'];
    const { audioSettingsVisible, videoSettingsVisible } = state['features/settings'];
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
    const { muted, unmuteBlocked } = state['features/base/media'].video;
    const videoOrShareInProgress = !muted || isScreenMediaShared(state);
    const enabledInJwt = isJwtFeatureEnabled(state, MEET_FEATURES.SCREEN_SHARING, true, true);

    return !enabledInJwt || (unmuteBlocked && !videoOrShareInProgress) || !isScreenSharingSupported();
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
    const { muted, unmuteBlocked, gumPending } = state['features/base/media'].video;

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
    const { toolbarConfig } = state['features/base/config'];

    return toolbarConfig?.timeout || TOOLBAR_TIMEOUT;
}

/**
 * Sets the notify click mode for the buttons.
 *
 * @param {Object} buttons - The list of toolbar buttons.
 * @param {Map} buttonsWithNotifyClick - The buttons notify click configuration.
 * @returns {void}
 */
function setButtonsNotifyClickMode(buttons: Object, buttonsWithNotifyClick: Map<string, NOTIFY_CLICK_MODE>) {
    if (typeof APP === 'undefined' || (buttonsWithNotifyClick?.size ?? 0) <= 0) {
        return;
    }

    Object.values(buttons).forEach((button: any) => {
        if (typeof button === 'object') {
            button.notifyMode = buttonsWithNotifyClick.get(button.key);
        }
    });
}

interface IGetVisibleButtonsParams {
    allButtons: { [key: string]: IToolboxButton; };
    buttonsWithNotifyClick: Map<string, NOTIFY_CLICK_MODE>;
    clientWidth: number;
    jwtDisabledButtons: string[];
    mainToolbarButtonsThresholds: IMainToolbarButtonThresholds;
    toolbarButtons: string[];
}

interface IGetCompassVisibleButtonsParams {
    allButtons: { [key: string]: IToolboxButton; };
    buttonsWithNotifyClick: Map<string, NOTIFY_CLICK_MODE>;
    clientWidth: number;
    jwtDisabledButtons: string[];
    mainToolbarButtonsThresholds: IMainToolbarButtonThresholds;
    toolbarButtons: string[];
    isVisitor: boolean;
    joiningInProgress: boolean;
}

interface IGetLeftSideVisibleButtonsParams {
    allButtons: { [key: string]: IToolboxButton; };
    jwtDisabledButtons: string[];
    toolbarButtons: string[];
    isVisitor: boolean;
}

interface IGetRightSideVisibleButtonsParams {
    allButtons: { [key: string]: IToolboxButton; };
    jwtDisabledButtons: string[];
    toolbarButtons: string[];
}

interface IGetRightSideVisibleButtonsParams {
    allButtons: { [key: string]: IToolboxButton; };
    jwtDisabledButtons: string[];
    toolbarButtons: string[];
}

/**
 * Returns all buttons that need to be rendered on the left side.
 *
 * @param {IGetLeftSideVisibleButtonsParams} params - The parameters needed to extract the visible buttons.
 * @returns {Object} - The visible buttons arrays .
 */
export function getCompassLeftSideButtons({
    allButtons,
    toolbarButtons,
    jwtDisabledButtons,
    isVisitor
}: IGetLeftSideVisibleButtonsParams) {
    let filteredButtons = Object.keys(allButtons).filter(key =>
            typeof key !== 'undefined' // filter invalid buttons that may be coming from config.mainToolbarButtons
            // override
            && !jwtDisabledButtons.includes(key)
            && isButtonEnabled(key, toolbarButtons)
            && (
                (isVisitor && (key === 'visitor-microphone' || key === 'visitor-camera')) // оставляем только кнопки visitor-microphone и visitor-camera
                || (!isVisitor && (key === 'microphone' || key === 'camera')) // оставляем только кнопки microphone и camera
            )
    );

    return filteredButtons.map(key => allButtons[key]);
}

/**
 * Returns all buttons that need to be rendered on the left side.
 *
 * @param {IGetRightSideVisibleButtonsParams} params - The parameters needed to extract the visible buttons.
 * @returns {Object} - The visible buttons arrays .
 */
export function getCompassRightSideButtons({
    allButtons,
    toolbarButtons,
    jwtDisabledButtons,
}: IGetRightSideVisibleButtonsParams) {
    let filteredButtons = Object.keys(allButtons).filter(key =>
        typeof key !== 'undefined' // filter invalid buttons that may be coming from config.mainToolbarButtons
        // override
        && !jwtDisabledButtons.includes(key)
        && isButtonEnabled(key, toolbarButtons)
        && (key === 'participants-pane' || key === 'moderatorSettings') // оставляем только кнопки participants-pane и moderatorSettings
    );

    return filteredButtons.map(key => allButtons[key]);
}

/**
 * Returns all buttons that need to be rendered.
 *
 * @param {IGetCompassVisibleButtonsParams} params - The parameters needed to extract the visible buttons.
 * @returns {Object} - The visible buttons arrays .
 */
export function getCompassVisibleButtons({
    allButtons,
    buttonsWithNotifyClick,
    toolbarButtons,
    clientWidth,
    jwtDisabledButtons,
    mainToolbarButtonsThresholds,
    isVisitor,
    joiningInProgress
}: IGetCompassVisibleButtonsParams) {
    setButtonsNotifyClickMode(allButtons, buttonsWithNotifyClick);

    let filteredButtons: string[];

    if (isMobileBrowser()) {
        filteredButtons = Object.keys(allButtons).filter(key =>
            typeof key !== 'undefined' // filter invalid buttons that may be coming from config.mainToolbarButtons
            // на мобилке оставляем только кнопки camera и microphone
            && (
                (isVisitor && !joiningInProgress && (key === 'visitor-microphone' || key === 'visitor-camera')) // оставляем только кнопки visitor-microphone и visitor-camera
                || (isVisitor && joiningInProgress && (key === 'microphone' || key === 'camera')) // оставляем только кнопки microphone и camera
                || (!isVisitor && (key === 'microphone' || key === 'camera')) // оставляем только кнопки microphone и camera
            )
            && !jwtDisabledButtons.includes(key)
            && isButtonEnabled(key, toolbarButtons));

    } else {
        filteredButtons = Object.keys(allButtons).filter(key =>
            typeof key !== 'undefined' // filter invalid buttons that may be comming from config.mainToolbarButtons
            // override
            && (key !== 'fullscreen' || !isMobileBrowser()) // убираем кнопку fullscreen на мобилках
            && key !== 'microphone' // убираем кнопку microphone
            && key !== 'camera' // убираем кнопку camera
            && key !== 'visitor-microphone' // убираем кнопку visitor-microphone
            && key !== 'visitor-camera' // убираем кнопку visitor-camera
            && key !== 'participants-pane' // убираем кнопку participants-pane
            && key !== 'moderatorSettings' // убираем кнопку moderatorSettings
            && !jwtDisabledButtons.includes(key)
            && isButtonEnabled(key, toolbarButtons));

    }

    if (!browser.isElectron()) {
        filteredButtons = filteredButtons.filter(key => key !== 'recording_electron');
    } else {
        filteredButtons = filteredButtons.filter(key => key !== 'recording');
    }

    const { order } = mainToolbarButtonsThresholds.find(({ width }) => clientWidth > width)
    || mainToolbarButtonsThresholds[mainToolbarButtonsThresholds.length - 1];

    const mainToolbarButtonKeysOrder = [
        ...order.filter(key => filteredButtons.includes(key)),
        ...MAIN_COMPASS_TOOLBAR_BUTTONS_PRIORITY.filter(key => !order.includes(key) && filteredButtons.includes(key)),
        ...filteredButtons.filter(key => !order.includes(key) && !MAIN_COMPASS_TOOLBAR_BUTTONS_PRIORITY.includes(key))
    ];

    const mainButtonsKeys = mainToolbarButtonKeysOrder.slice(0, 8);
    const overflowMenuButtons = filteredButtons.reduce((acc, key) => {
        if (!mainButtonsKeys.includes(key)) {
            acc.push(allButtons[key]);
        }

        return acc;
    }, [] as IToolboxButton[]);

    // if we have 1 button in the overflow menu it is better to directly display it in the main toolbar by replacing
    // the "More" menu button with it.
    if (overflowMenuButtons.length === 1) {
        const button = overflowMenuButtons.shift()?.key;

        button && mainButtonsKeys.push(button);
    }

    return {
        mainMenuButtons: mainButtonsKeys.map(key => allButtons[key]),
        overflowMenuButtons
    };
}

/**
 * Returns all buttons that need to be rendered.
 *
 * @param {IGetVisibleButtonsParams} params - The parameters needed to extract the visible buttons.
 * @returns {Object} - The visible buttons arrays .
 */
export function getVisibleButtons({
    allButtons,
    buttonsWithNotifyClick,
    toolbarButtons,
    clientWidth,
    jwtDisabledButtons,
    mainToolbarButtonsThresholds
}: IGetVisibleButtonsParams) {
    setButtonsNotifyClickMode(allButtons, buttonsWithNotifyClick);

    let filteredButtons = Object.keys(allButtons).filter(key =>
        typeof key !== 'undefined' // filter invalid buttons that may be comming from config.mainToolbarButtons
        // override
        && (key !== 'fullscreen' || !isMobileBrowser()) // убираем кнопку fullscreen на мобилках
        && (key !== 'premeeting-microphone' || !isMobileBrowser()) // убираем кнопку premeeting-microphone на мобилках
        && (key !== 'premeeting-camera' || !isMobileBrowser()) // убираем кнопку premeeting-camera на мобилках
        && (key !== 'premeeting-select-background' || !isMobileBrowser()) // убираем кнопку premeeting-select-background на мобилках
        && !jwtDisabledButtons.includes(key)
        && isButtonEnabled(key, toolbarButtons));

    if (!browser.isElectron()) {
        filteredButtons = filteredButtons.filter(key => key !== 'recording_electron');
    } else {
        filteredButtons = filteredButtons.filter(key => key !== 'recording');
    }

    const { order } = mainToolbarButtonsThresholds.find(({ width }) => clientWidth > width)
    || mainToolbarButtonsThresholds[mainToolbarButtonsThresholds.length - 1];

    const mainToolbarButtonKeysOrder = [
        ...order.filter(key => filteredButtons.includes(key)),
        ...MAIN_TOOLBAR_BUTTONS_PRIORITY.filter(key => !order.includes(key) && filteredButtons.includes(key)),
        ...filteredButtons.filter(key => !order.includes(key) && !MAIN_TOOLBAR_BUTTONS_PRIORITY.includes(key))
    ];

    const mainButtonsKeys = mainToolbarButtonKeysOrder.slice(0, order.length);
    const overflowMenuButtons = filteredButtons.reduce((acc, key) => {
        if (!mainButtonsKeys.includes(key)) {
            acc.push(allButtons[key]);
        }

        return acc;
    }, [] as IToolboxButton[]);

    // if we have 1 button in the overflow menu it is better to directly display it in the main toolbar by replacing
    // the "More" menu button with it.
    if (overflowMenuButtons.length === 1) {
        const button = overflowMenuButtons.shift()?.key;

        button && mainButtonsKeys.push(button);
    }

    return {
        mainMenuButtons: mainButtonsKeys.map(key => allButtons[key]),
        overflowMenuButtons
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

interface ICSSTransitionObject {
    delay: number;
    duration: number;
    easingFunction: string;
}

/**
 * Returns the time, timing function and delay for elements that are position above the toolbar and need to move along
 * with the toolbar.
 *
 * @param {boolean} isToolbarVisible - Whether the toolbar is visible or not.
 * @returns {ICSSTransitionObject}
 */
export function getTransitionParamsForElementsAboveToolbox(isToolbarVisible: boolean): ICSSTransitionObject {
    // The transistion time and delay is different to account for the time when the toolbar is about to hide/show but
    // the elements don't have to move.
    return isToolbarVisible ? {
        duration: 0.15,
        easingFunction: 'ease-in',
        delay: 0.15
    } : {
        duration: 0.24,
        easingFunction: 'ease-in',
        delay: 0
    };
}

/**
 * Converts a given object to a css transition value string.
 *
 * @param {ICSSTransitionObject} object - The object.
 * @returns {string}
 */
export function toCSSTransitionValue(object: ICSSTransitionObject) {
    const { delay, duration, easingFunction } = object;

    return `${duration}s ${easingFunction} ${delay}s`;
}
