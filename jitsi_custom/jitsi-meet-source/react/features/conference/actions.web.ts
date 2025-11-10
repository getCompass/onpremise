import { IStore } from '../app/types';
import { configureInitialDevices, getAvailableDevices } from '../base/devices/actions.web';
import { getBackendSafeRoomName } from '../base/util/uri';

import { DISMISS_CALENDAR_NOTIFICATION } from './actionTypes';
import logger from './logger';

/**
 * Dismisses calendar notification about next or ongoing event.
 *
 * @returns {Object}
 */
export function dismissCalendarNotification() {
    return {
        type: DISMISS_CALENDAR_NOTIFICATION
    };
}

/**
 * Setups initial devices. Makes sure we populate availableDevices list before configuring.
 *
 * @returns {Promise<any>}
 */
export function setupInitialDevices() {
    return async (dispatch: IStore['dispatch']) => {
        await dispatch(getAvailableDevices());
        await dispatch(configureInitialDevices());
    };
}

/**
 * Init.
 *
 * @returns {Promise<JitsiConnection>}
 */
export function init() {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const room = getBackendSafeRoomName(getState()['features/base/conference'].room);

        // XXX For web based version we use conference initialization logic
        // from the old app (at the moment of writing).
        return dispatch(setupInitialDevices()).then(
            () => APP.conference.init({
                roomName: room
            }).catch((error: Error) => {
                APP.API.notifyConferenceLeft(APP.conference.roomName);
                logger.error(error);
            }));
    };
}
