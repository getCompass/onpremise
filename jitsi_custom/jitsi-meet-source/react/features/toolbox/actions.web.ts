import { IStore } from '../app/types';
import { overwriteConfig } from '../base/config/actions';
import { isMobileBrowser } from '../base/environment/utils';

import {
    CLEAR_TOOLBOX_TIMEOUT,
    FULL_SCREEN_CHANGED,
    SET_COMPASS_MAIN_TOOLBAR_BUTTONS_THRESHOLDS,
    SET_FULL_SCREEN,
    SET_HANGUP_MENU_VISIBLE,
    SET_MAIN_TOOLBAR_BUTTONS_THRESHOLDS,
    SET_OVERFLOW_DRAWER,
    SET_OVERFLOW_MENU_VISIBLE,
    SET_TOOLBAR_HOVERED,
    SET_TOOLBOX_TIMEOUT
} from './actionTypes';
import { setToolboxVisible } from './actions.web';
import { COMPASS_THRESHOLDS, COMPASS_THRESHOLDS_MOBILE, THRESHOLDS } from './constants';
import { getToolbarTimeout } from './functions.web';
import { IMainToolbarButtonThresholds } from './types';

export * from './actions.any';

/**
 * Docks/undocks the Toolbox.
 *
 * @param {boolean} dock - True if dock, false otherwise.
 * @returns {Function}
 */
export function dockToolbox(dock: boolean) {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const state = getState();
        const { visible } = state['features/toolbox'];
        const toolbarTimeout = getToolbarTimeout(state);

        if (dock) {
            // First make sure the toolbox is shown.
            visible || dispatch(showToolbox());

            dispatch(clearToolboxTimeout());
        } else if (visible) {
            dispatch(
                setToolboxTimeout(
                    () => dispatch(hideToolbox()),
                    toolbarTimeout));
        } else {
            dispatch(showToolbox());
        }
    };
}

/**
 * Signals that full screen mode has been entered or exited.
 *
 * @param {boolean} fullScreen - Whether or not full screen mode is currently
 * enabled.
 * @returns {{
 *     type: FULL_SCREEN_CHANGED,
 *     fullScreen: boolean
 * }}
 */
export function fullScreenChanged(fullScreen: boolean) {
    return {
        type: FULL_SCREEN_CHANGED,
        fullScreen
    };
}

/**
 * Hides the toolbox.
 *
 * @param {boolean} force - True to force the hiding of the toolbox without
 * caring about the extended toolbar side panels.
 * @returns {Function}
 */
export function hideToolbox(force = false) {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const state = getState();
        const { toolbarConfig } = state['features/base/config'];
        const alwaysVisible = toolbarConfig?.alwaysVisible;
        const autoHideWhileChatIsOpen = toolbarConfig?.autoHideWhileChatIsOpen;
        const toolbarTimeout = getToolbarTimeout(state);

        if (alwaysVisible) {
            return;
        }

        dispatch(clearToolboxTimeout());

        if (!force
            && (state['features/invite'].calleeInfoVisible
                || (state['features/chat'].isOpen && !autoHideWhileChatIsOpen))) {
            dispatch(
                setToolboxTimeout(
                    () => dispatch(hideToolbox()),
                    toolbarTimeout));
        } else {
            dispatch(setToolboxVisible(false));
        }
    };
}

/**
 * Signals a request to enter or exit full screen mode.
 *
 * @param {boolean} fullScreen - True to enter full screen mode, false to exit.
 * @returns {{
 *     type: SET_FULL_SCREEN,
 *     fullScreen: boolean
 * }}
 */
export function setFullScreen(fullScreen: boolean) {
    return {
        type: SET_FULL_SCREEN,
        fullScreen
    };
}

/**
 * Sets the mainToolbarButtonsThresholds.
 *
 * @returns {Function}
 */
export function setMainToolbarThresholds() {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const { mainToolbarButtons } = getState()['features/base/config'];

        if (!mainToolbarButtons || !Array.isArray(mainToolbarButtons) || mainToolbarButtons.length === 0) {
            return;
        }

        const mainToolbarButtonsThresholds: IMainToolbarButtonThresholds = [];

        const mainToolbarButtonsLenghtMap = new Map();
        let orderIsChanged = false;

        mainToolbarButtons.forEach(buttons => {
            if (!Array.isArray(buttons) || buttons.length === 0) {
                return;
            }

            mainToolbarButtonsLenghtMap.set(buttons.length, buttons);
        });

        THRESHOLDS.forEach(({ width, order }) => {
            let finalOrder = mainToolbarButtonsLenghtMap.get(order.length);

            if (finalOrder) {
                orderIsChanged = true;
            } else {
                finalOrder = order;
            }

            mainToolbarButtonsThresholds.push({
                order: finalOrder,
                width
            });
        });

        if (orderIsChanged) {
            dispatch({
                type: SET_MAIN_TOOLBAR_BUTTONS_THRESHOLDS,
                mainToolbarButtonsThresholds
            });
        }
    };
}

/**
 * Sets the compassMainToolbarButtonsThresholds.
 *
 * @returns {Function}
 */
export function setCompassMainToolbarThresholds() {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const { mainToolbarButtons } = getState()['features/base/config'];

        if (!mainToolbarButtons || !Array.isArray(mainToolbarButtons) || mainToolbarButtons.length === 0) {
            return;
        }

        const compassMainToolbarButtonsThresholds: IMainToolbarButtonThresholds = [];

        const mainToolbarButtonsLenghtMap = new Map();
        let orderIsChanged = false;

        mainToolbarButtons.forEach(buttons => {
            if (!Array.isArray(buttons) || buttons.length === 0) {
                return;
            }

            mainToolbarButtonsLenghtMap.set(buttons.length, buttons);
        });

        let compassThresholds = COMPASS_THRESHOLDS;
        if (isMobileBrowser()) {
            compassThresholds = COMPASS_THRESHOLDS_MOBILE;
        }
        compassThresholds.forEach(({ width, order }) => {
            let finalOrder = mainToolbarButtonsLenghtMap.get(order.length);

            if (finalOrder) {
                orderIsChanged = true;
            } else {
                finalOrder = order;
            }

            compassMainToolbarButtonsThresholds.push({
                order: finalOrder,
                width
            });
        });

        if (orderIsChanged) {
            dispatch({
                type: SET_COMPASS_MAIN_TOOLBAR_BUTTONS_THRESHOLDS,
                compassMainToolbarButtonsThresholds
            });
        }
    };
}

/**
 * Shows the toolbox for specified timeout.
 *
 * @param {number} timeout - Timeout for showing the toolbox.
 * @returns {Function}
 */
export function showToolbox(timeout = 0) {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const state = getState();
        const { toolbarConfig } = state['features/base/config'];
        const toolbarTimeout = getToolbarTimeout(state);
        const initialTimeout = toolbarConfig?.initialTimeout;
        const alwaysVisible = toolbarConfig?.alwaysVisible;

        const {
            enabled,
            visible
        } = state['features/toolbox'];

        if (enabled && !visible) {
            dispatch(setToolboxVisible(true));

            // If the Toolbox is always visible, there's no need for a timeout
            // to toggle its visibility.
            if (!alwaysVisible) {
                if (typeof initialTimeout === 'number') {
                    // reset `initialTimeout` once it is consumed once
                    dispatch(overwriteConfig({
                        toolbarConfig: {
                            ...toolbarConfig,
                            initialTimeout: null
                        }
                    }));
                }
                dispatch(
                    setToolboxTimeout(
                        () => dispatch(hideToolbox()),
                        timeout || initialTimeout || toolbarTimeout));
            }
        }
    };
}

/**
 * Signals a request to display overflow as drawer.
 *
 * @param {boolean} displayAsDrawer - True to display overflow as drawer, false to preserve original behaviour.
 * @returns {{
 *     type: SET_OVERFLOW_DRAWER,
 *     displayAsDrawer: boolean
 * }}
 */
export function setOverflowDrawer(displayAsDrawer: boolean) {
    return {
        type: SET_OVERFLOW_DRAWER,
        displayAsDrawer
    };
}

/**
 * Signals that toolbox timeout should be cleared.
 *
 * @returns {{
 *     type: CLEAR_TOOLBOX_TIMEOUT
 * }}
 */
export function clearToolboxTimeout() {
    return {
        type: CLEAR_TOOLBOX_TIMEOUT
    };
}

/**
 * Shows/hides the hangup menu.
 *
 * @param {boolean} visible - True to show it or false to hide it.
 * @returns {{
 *     type: SET_HANGUP_MENU_VISIBLE,
 *     visible: boolean
 * }}
 */
export function setHangupMenuVisible(visible: boolean) {
    return {
        type: SET_HANGUP_MENU_VISIBLE,
        visible
    };
}

/**
 * Shows/hides the overflow menu.
 *
 * @param {boolean} visible - True to show it or false to hide it.
 * @returns {{
 *     type: SET_OVERFLOW_MENU_VISIBLE,
 *     visible: boolean
 * }}
 */
export function setOverflowMenuVisible(visible: boolean) {
    return {
        type: SET_OVERFLOW_MENU_VISIBLE,
        visible
    };
}

/**
 * Signals that toolbar is hovered value should be changed.
 *
 * @param {boolean} hovered - Flag showing whether toolbar is hovered.
 * @returns {{
 *     type: SET_TOOLBAR_HOVERED,
 *     hovered: boolean
 * }}
 */
export function setToolbarHovered(hovered: boolean) {
    return {
        type: SET_TOOLBAR_HOVERED,
        hovered
    };
}

/**
 * Dispatches an action which sets new timeout for the toolbox visibility and clears the previous one.
 * On mobile browsers the toolbox does not hide on timeout. It is toggled on simple tap.
 *
 * @param {Function} handler - Function to be invoked after the timeout.
 * @param {number} timeoutMS - Delay.
 * @returns {{
 *     type: SET_TOOLBOX_TIMEOUT,
 *     handler: Function,
 *     timeoutMS: number
 * }}
 */
export function setToolboxTimeout(handler: Function, timeoutMS: number) {
    return function (dispatch: IStore['dispatch']) {
        if (isMobileBrowser()) {
            return;
        }

        dispatch({
            type: SET_TOOLBOX_TIMEOUT,
            handler,
            timeoutMS
        });
    };
}

/**
 * Closes the overflow menu if opened.
 *
 * @private
 * @returns {void}
 */
export function closeOverflowMenuIfOpen() {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const { overflowMenuVisible } = getState()['features/toolbox'];

        overflowMenuVisible && dispatch(setOverflowMenuVisible(false));
    };
}
