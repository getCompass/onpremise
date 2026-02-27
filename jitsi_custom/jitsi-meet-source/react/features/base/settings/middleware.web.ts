import { IStore } from '../../app/types';
import { PREJOIN_INITIALIZED } from '../../prejoin/actionTypes';
import { setPrejoinPageVisibility } from '../../prejoin/actions';
import { APP_WILL_MOUNT } from '../app/actionTypes';
import { getJwtName, publicJwtDecode, parseJWTFromURLParams } from '../jwt/functions';
import { MEDIA_TYPE } from '../media/constants';
import MiddlewareRegistry from '../redux/MiddlewareRegistry';
import { TRACK_ADDED } from '../tracks/actionTypes';
import { ITrack } from '../tracks/types';

import { updateSettings } from './actions';
import logger from './logger';


import './middleware.any';
import { user2participant } from "../jwt/middleware";
import {browser} from "../lib-jitsi-meet";
import {isAudioEnabledOnEnterConference, isLobbyEnabledOnJoin, isVideoEnabledOnEnterConference} from "./functions.any";
import {muteLocal} from "../../video-menu/actions.any";
import {handleToggleVideoMuted} from "../../toolbox/actions.any";
import {getWindowQueryData} from "../../desktop-picker/functions";

/**
 * The middleware of the feature base/settings. Distributes changes to the state
 * of base/settings to the states of other features computed from the state of
 * base/settings.
 *
 * @param {Store} store - The redux store.
 * @returns {Function}
 */
MiddlewareRegistry.register(store => next => action => {
    const result = next(action);

    switch (action.type) {
    case APP_WILL_MOUNT:
        _initializeShowPrejoin(store);
        break;
    case PREJOIN_INITIALIZED:
        _maybeUpdateDisplayName(store);
        break;
    case TRACK_ADDED:
        _maybeUpdateDeviceId(store, action.track);
        break;
    }

    return result;
});

/**
 * Overwrites the showPrejoin flag based on cached used selection for showing prejoin screen.
 *
 * @param {Store} store - The redux store.
 * @private
 * @returns {void}
 */
function _initializeShowPrejoin({ dispatch, getState }: IStore) {
    // compass changes
    // получаем jwt из url
    // сори в этот момент он еще не инициализирован в сторе, поэтому так
    const { locationURL } = getState()['features/base/connection'];
    const jwt = parseJWTFromURLParams(locationURL);
    let isCompassUser = false; // является ли пользователем compass
    let isCreator = false; // является ли создателем конференции

    // если нашли jwt - идем дальше
    // но флаг пока оставил
    if (jwt) {

        // если не смогли спарсить jwt - не падаем
        try {
            const jwtPayload = publicJwtDecode(jwt);

            // если смогли получить payload
            if (jwtPayload) {

                // получаем контекст, если удалось - супер
                const { context, room } = jwtPayload;
                let userId: number | string | undefined;
                let roomId: number | string | undefined;
                if (context) {
                    const user = user2participant(context.user || {});
                    if (user !== undefined && user.type === "compass_user") {
                        isCompassUser = true;

                        userId = typeof user.id === 'string' ? user.id.split(":")[1] : undefined;
                        if (userId) {
                            userId = Number(userId);
                        }
                    }
                }

                if (room && userId) {
                    roomId = typeof room === 'string' ? room.split("_")[0] : undefined;
                    if (roomId) {
                        roomId = Number(roomId);
                    }

                    isCreator = roomId === userId;
                }
            }
        } catch (e) {
        }
    }

    try {
        const isElectron = browser.isElectron();
        const state = getState();
        const queryData = getWindowQueryData();

        const canSkipPreJoinPage = Boolean((isCompassUser && !isElectron) || isCreator);
        const shouldShowPreJoinPage = Boolean(isLobbyEnabledOnJoin(state));

        if (canSkipPreJoinPage) {
            dispatch(setPrejoinPageVisibility(false));
        } else if (isElectron) {
            const {isSingleConference, isSupportPreJoinPage} = queryData;
            const canShowPreJoinPage = shouldShowPreJoinPage && isSupportPreJoinPage && !isSingleConference;
            dispatch(setPrejoinPageVisibility(canShowPreJoinPage));
        }
    } catch (error) {
        try {
            // еще раз скипнем страницу prejoin чтобы в случае ошибки не было этой страницы у пользователя
            isCompassUser && dispatch(setPrejoinPageVisibility(false));
        } catch (e) {
            console.error('[ERROR INIT CONFERENCE] setPrejoinPageVisibility ', error);
        }
        console.error('[ERROR INIT CONFERENCE] ', error);
    }
}

/**
 * Updates the display name to the one in JWT if there is one.
 *
 * @param {Store} store - The redux store.
 * @private
 * @returns {void}
 */
function _maybeUpdateDisplayName({ dispatch, getState }: IStore) {
    const state = getState();
    const hasJwt = Boolean(state['features/base/jwt'].jwt);

    if (hasJwt) {
        const displayName = getJwtName(state);

        if (displayName) {
            dispatch(updateSettings({
                displayName
            }));
        }
    }
}

/**
 * Maybe update the camera or mic device id when local track is added or updated.
 *
 * @param {Store} store - The redux store.
 * @param {ITrack} track - The potential local track.
 * @private
 * @returns {void}
 */
function _maybeUpdateDeviceId({ dispatch, getState }: IStore, track: ITrack) {
    if (track.local) {
        const { cameraDeviceId, micDeviceId } = getState()['features/base/settings'];
        const deviceId = track.jitsiTrack.getDeviceId();

        if (track.mediaType === MEDIA_TYPE.VIDEO && track.videoType === 'camera' && cameraDeviceId !== deviceId) {
            dispatch(updateSettings({
                cameraDeviceId: track.jitsiTrack.getDeviceId()
            }));
            logger.info(`switched local video device to: ${deviceId}`);
        } else if (track.mediaType === MEDIA_TYPE.AUDIO && micDeviceId !== deviceId) {
            dispatch(updateSettings({
                micDeviceId: track.jitsiTrack.getDeviceId()
            }));
            logger.info(`switched local audio input device to: ${deviceId}`);
        }
    }
}