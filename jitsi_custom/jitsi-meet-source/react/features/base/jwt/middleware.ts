// @ts-expect-error
import jwtDecode from 'jwt-decode';
import { AnyAction } from 'redux';

import { IStore } from '../../app/types';
import { SET_CONFIG } from '../config/actionTypes';
import { SET_LOCATION_URL } from '../connection/actionTypes';
import { participantUpdated } from '../participants/actions';
import { getLocalParticipant } from '../participants/functions';
import { IParticipant } from '../participants/types';
import MiddlewareRegistry from '../redux/MiddlewareRegistry';

import { SET_JWT } from './actionTypes';
import { setJWT } from './actions';
import { parseJWTFromURLParams } from './functions';
import logger from './logger';

/**
 * Middleware to parse token data upon setting a new room URL.
 *
 * @param {Store} store - The redux store.
 * @private
 * @returns {Function}
 */
MiddlewareRegistry.register(store => next => action => {
    switch (action.type) {
    case SET_CONFIG:
    case SET_LOCATION_URL:
        // XXX The JSON Web Token (JWT) is not the only piece of state that we
        // have decided to store in the feature jwt
        return _setConfigOrLocationURL(store, next, action);

    case SET_JWT:
        return _setJWT(store, next, action);
    }

    return next(action);
});

/**
 * Overwrites the properties {@code avatarURL}, {@code email}, and {@code name}
 * of the local participant stored in the redux state base/participants.
 *
 * @param {Store} store - The redux store.
 * @param {Object} localParticipant - The {@code Participant} structure to
 * overwrite the local participant stored in the redux store base/participants
 * with.
 * @private
 * @returns {void}
 */
function _overwriteLocalParticipant(
        { dispatch, getState }: IStore,
        { avatarURL, email, id: jwtId, name, features, type, jitsi_frontend_url, is_moderator, is_lobby_enabled }:
        { avatarURL?: string; email?: string; features?: any; id?: string; name?: string; type?: string, jitsi_frontend_url?: string, is_moderator?: boolean, is_lobby_enabled?: boolean }) {
    let localParticipant;

    if ((avatarURL || email || name || features || type || jitsi_frontend_url || is_moderator !== undefined || is_lobby_enabled !== undefined) && (localParticipant = getLocalParticipant(getState))) {
        const newProperties: IParticipant = {
            id: localParticipant.id,
            local: true
        };

        if (avatarURL) {
            newProperties.avatarURL = avatarURL;
        }
        if (email) {
            newProperties.email = email;
        }
        if (jwtId) {
            newProperties.jwtId = jwtId;
        }
        if (name) {
            newProperties.name = name;
        }
        if (features) {
            newProperties.features = features;
        }
        if (type) {
            newProperties.type = type
        }
        if (jitsi_frontend_url) {
            newProperties.jitsi_frontend_url = jitsi_frontend_url
        }
        if (is_moderator !== undefined) {
            newProperties.is_moderator = is_moderator
        }
        if (is_lobby_enabled !== undefined) {
            newProperties.is_lobby_enabled = is_lobby_enabled
        }
        dispatch(participantUpdated(newProperties));
    }
}

/**
 * Notifies the feature jwt that the action {@link SET_CONFIG} or
 * {@link SET_LOCATION_URL} is being dispatched within a specific redux
 * {@code store}.
 *
 * @param {Store} store - The redux store in which the specified {@code action}
 * is being dispatched.
 * @param {Dispatch} next - The redux dispatch function to dispatch the
 * specified {@code action} to the specified {@code store}.
 * @param {Action} action - The redux action {@code SET_CONFIG} or
 * {@code SET_LOCATION_URL} which is being dispatched in the specified
 * {@code store}.
 * @private
 * @returns {Object} The new state that is the result of the reduction of the
 * specified {@code action}.
 */
function _setConfigOrLocationURL({ dispatch, getState }: IStore, next: Function, action: AnyAction) {
    const result = next(action);

    const { locationURL } = getState()['features/base/connection'];

    dispatch(
        setJWT(locationURL ? parseJWTFromURLParams(locationURL) : undefined));

    return result;
}

/**
 * Notifies the feature jwt that the action {@link SET_JWT} is being dispatched
 * within a specific redux {@code store}.
 *
 * @param {Store} store - The redux store in which the specified {@code action}
 * is being dispatched.
 * @param {Dispatch} next - The redux dispatch function to dispatch the
 * specified {@code action} to the specified {@code store}.
 * @param {Action} action - The redux action {@code SET_JWT} which is being
 * dispatched in the specified {@code store}.
 * @private
 * @returns {Object} The new state that is the result of the reduction of the
 * specified {@code action}.
 */
function _setJWT(store: IStore, next: Function, action: AnyAction) {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { jwt, type, ...actionPayload } = action;

    if (!Object.keys(actionPayload).length) {
        if (jwt) {
            let jwtPayload;

            try {
                jwtPayload = jwtDecode(jwt);
            } catch (e) {
                logger.error(e);
            }

            if (jwtPayload) {
                const { context, iss, sub } = jwtPayload;

                action.jwt = jwt;
                action.issuer = iss;
                if (context) {
                    const user = user2participant(context.user || {});

                    action.callee = context.callee;
                    action.group = context.group;
                    action.server = context.server;
                    action.tenant = context.tenant || sub || undefined;
                    action.user = user;

                    const newUser = user ? { ...user } : {};

                    _overwriteLocalParticipant(
                        store, { ...newUser,
                            features: context.features });

                    // eslint-disable-next-line max-depth
                    if (context.user && context.user.role === 'visitor') {
                        action.preferVisitor = true;
                    }
                } else if (jwtPayload.name || jwtPayload.picture || jwtPayload.email) {
                    // there are some tokens (firebase) having picture and name on the main level.
                    _overwriteLocalParticipant(store, {
                        avatarURL: jwtPayload.picture,
                        name: jwtPayload.name,
                        email: jwtPayload.email
                    });
                }
            }
        } else if (typeof APP === 'undefined') {
            // The logic of restoring JWT overrides make sense only on mobile.
            // On Web it should eventually be restored from storage, but there's
            // no such use case yet.

            const { user } = store.getState()['features/base/jwt'];

            user && _undoOverwriteLocalParticipant(store, user);
        }
    }

    return next(action);
}

/**
 * Undoes/resets the values overwritten by {@link _overwriteLocalParticipant}
 * by either clearing them or setting to default values. Only the values that
 * have not changed since the overwrite happened will be restored.
 *
 * NOTE Once it is possible to edit and save participant properties, this
 * function should restore values from the storage instead.
 *
 * @param {Store} store - The redux store.
 * @param {Object} localParticipant - The {@code Participant} structure used
 * previously to {@link _overwriteLocalParticipant}.
 * @private
 * @returns {void}
 */
function _undoOverwriteLocalParticipant(
        { dispatch, getState }: IStore,
        { avatarURL, name, email }: { avatarURL?: string; email?: string; name?: string; }) {
    let localParticipant;

    if ((avatarURL || name || email)
            && (localParticipant = getLocalParticipant(getState))) {
        const newProperties: IParticipant = {
            id: localParticipant.id,
            local: true
        };

        if (avatarURL === localParticipant.avatarURL) {
            newProperties.avatarURL = undefined;
        }
        if (email === localParticipant.email) {
            newProperties.email = undefined;
        }
        if (name === localParticipant.name) {
            newProperties.name = undefined;
        }
        newProperties.features = undefined;

        dispatch(participantUpdated(newProperties));
    }
}

/**
 * Converts the JWT {@code context.user} structure to the {@code Participant}
 * structure stored in the redux state base/participants.
 *
 * @param {Object} user - The JWT {@code context.user} structure to convert.
 * @public
 * @returns {{
 *     avatarURL: ?string,
 *     email: ?string,
 *     id: ?string,
 *     name: ?string,
 *     hidden-from-recorder: ?boolean
 * }}
 */
export function user2participant({ avatar, avatarUrl, email, id, name, type, jitsi_frontend_url, is_moderator, is_lobby_enabled, 'hidden-from-recorder': hiddenFromRecorder }:
    { avatar?: string; avatarUrl?: string; email: string; 'hidden-from-recorder': string | boolean;
    id: string; name: string; type?: string; jitsi_frontend_url?: string, is_moderator?: boolean, is_lobby_enabled?: boolean }) {
    const participant: {
        avatarURL?: string;
        email?: string;
        hiddenFromRecorder?: boolean;
        id?: string;
        name?: string;
        type?: string;
        jitsi_frontend_url?: string;
        is_moderator?: boolean;
        is_lobby_enabled?: boolean;
    } = {};

    if (typeof avatarUrl === 'string') {
        participant.avatarURL = avatarUrl.trim();
    } else if (typeof avatar === 'string') {
        participant.avatarURL = avatar.trim();
    }
    if (typeof email === 'string') {
        participant.email = email.trim();
    }
    if (typeof id === 'string') {
        participant.id = id.trim();
    }
    if (typeof name === 'string') {
        participant.name = name.trim();
    }
    if (typeof type === 'string') {
        participant.type = type.trim();
    }
    if (typeof jitsi_frontend_url === 'string') {
        participant.jitsi_frontend_url = jitsi_frontend_url.trim();
    }
    if (typeof is_moderator === 'boolean') {
        participant.is_moderator = is_moderator;
    }
    if (typeof is_lobby_enabled === 'boolean') {
        participant.is_lobby_enabled = is_lobby_enabled;
    }

    if (hiddenFromRecorder === 'true' || hiddenFromRecorder === true) {
        participant.hiddenFromRecorder = true;
    }

    return Object.keys(participant).length ? participant : undefined;
}
