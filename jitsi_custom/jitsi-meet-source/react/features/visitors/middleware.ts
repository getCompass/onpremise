import i18n from 'i18next';
import { batch } from 'react-redux';

import { IStore } from '../app/types';
import { IStateful } from '../base/app/types';
import {
    CONFERENCE_JOINED,
    CONFERENCE_JOIN_IN_PROGRESS,
    ENDPOINT_MESSAGE_RECEIVED,
    UPDATE_CONFERENCE_METADATA
} from '../base/conference/actionTypes';
import { SET_CONFIG } from '../base/config/actionTypes';
import { CONNECTION_FAILED } from '../base/connection/actionTypes';
import { connect, setPreferVisitor } from '../base/connection/actions';
import { disconnect } from '../base/connection/actions.any';
import { openDialog } from '../base/dialog/actions';
import { JitsiConferenceEvents, JitsiConnectionErrors } from '../base/lib-jitsi-meet';
import { PARTICIPANT_UPDATED } from '../base/participants/actionTypes';
import { raiseHand } from '../base/participants/actions';
import {
    getLocalParticipant,
    getParticipantById,
    isLocalParticipantModerator
} from '../base/participants/functions';
import MiddlewareRegistry from '../base/redux/MiddlewareRegistry';
import { toState } from '../base/redux/functions';
import { BUTTON_TYPES } from '../base/ui/constants.any';
import { hideNotification, showNotification } from '../notifications/actions';
import {
    NOTIFICATION_ICON,
    NOTIFICATION_TIMEOUT_TYPE,
    VISITORS_NOT_LIVE_NOTIFICATION_ID,
    VISITORS_PROMOTION_NOTIFICATION_ID, VISITORS_PROMOTION_REJECTED_NOTIFICATION_ID
} from '../notifications/constants';
import { INotificationProps } from '../notifications/types';
import { open as openParticipantsPane } from '../participants-pane/actions';
import { joinConference } from '../prejoin/actions';

import { UPDATE_VISITORS_IN_QUEUE_COUNT } from './actionTypes';
import {
    approveRequest,
    clearPromotionRequest,
    denyRequest,
    goLive,
    promotionRequestReceived,
    setInVisitorsQueue,
    setVisitorDemoteActor,
    setVisitorsSupported,
    updateVisitorsCount,
    updateVisitorsInQueueCount
} from './actions';
import { JoinMeetingDialog } from './components';
import { getPromotionRequests, getVisitorsCount, getVisitorsInQueueCount } from './functions';
import logger from './logger';
import { WebsocketClient } from './websocket-client';
import { plural } from "../base/compass/functions";

MiddlewareRegistry.register(({ dispatch, getState }) => next => action => {
    switch (action.type) {
    case CONFERENCE_JOIN_IN_PROGRESS: {
        const { conference } = action;

        conference.on(JitsiConferenceEvents.PROPERTIES_CHANGED, (properties: { 'visitor-count': number; }) => {
            const visitorCount = Number(properties?.['visitor-count']);

            if (!isNaN(visitorCount) && getVisitorsCount(getState) !== visitorCount) {
                dispatch(updateVisitorsCount(visitorCount));
            }
        });
        break;
    }
    case CONFERENCE_JOINED: {
        const { conference } = action;

        if (getState()['features/visitors'].iAmVisitor) {

            const { demoteActorDisplayName } = getState()['features/visitors'];

            if (demoteActorDisplayName) {
                const notificationParams: INotificationProps = {
                    titleKey: 'visitors.notification.title',
                    descriptionKey: 'visitors.notification.demoteDescription',
                    icon: NOTIFICATION_ICON.WARNING,
                };

                batch(() => {
                    dispatch(showNotification(notificationParams, NOTIFICATION_TIMEOUT_TYPE.LONG));
                    dispatch(setVisitorDemoteActor(undefined));
                });
            } else {
                dispatch(openDialog(JoinMeetingDialog));
            }

        } else {
            dispatch(setVisitorsSupported(conference.isVisitorsSupported()));
            conference.on(JitsiConferenceEvents.VISITORS_SUPPORTED_CHANGED, (value: boolean) => {
                dispatch(setVisitorsSupported(value));
            });
        }

        conference.on(JitsiConferenceEvents.VISITORS_MESSAGE, (
            msg: { action: string; actor: string; from: string; id: string; nick: string; on: boolean; }) => {

            if (msg.action === 'demote-request') {
                // we need it before the disconnect
                const participantById = getParticipantById(getState, msg.actor);
                const localParticipant = getLocalParticipant(getState);

                if (localParticipant && localParticipant.id === msg.id) {
                    // handle demote
                    dispatch(disconnect(true))
                        .then(() => {
                            dispatch(setPreferVisitor(true));

                            // we need to set the name, so we can use it later in the notification
                            if (participantById) {
                                dispatch(setVisitorDemoteActor(participantById.name));
                            }

                            return dispatch(connect());
                        });
                }
            } else if (msg.action === 'promotion-request') {
                const request = {
                    from: msg.from,
                    nick: msg.nick
                };

                if (msg.on) {
                    dispatch(promotionRequestReceived(request));
                } else {
                    dispatch(clearPromotionRequest(request));
                }
                _handlePromotionNotification({
                    dispatch,
                    getState
                });
            } else {
                logger.error('Unknown action:', msg.action);
            }
        });

        conference.on(JitsiConferenceEvents.VISITORS_REJECTION, () => {

            dispatch(showNotification({
                titleKey: 'notify.visitorJoinRejectedTitle',
                descriptionKey: 'notify.visitorJoinRejected',
                uid: VISITORS_PROMOTION_REJECTED_NOTIFICATION_ID,
                icon: NOTIFICATION_ICON.WARNING
            }, NOTIFICATION_TIMEOUT_TYPE.LONG));

            dispatch(raiseHand(false));
        });

        break;
    }
    case ENDPOINT_MESSAGE_RECEIVED: {
        const { data } = action;

        if (data?.action === 'promotion-response' && data.approved) {
            const request = getPromotionRequests(getState())
                .find((r: any) => r.from === data.id);

            request && dispatch(clearPromotionRequest(request));
        }
        break;
    }
    case CONNECTION_FAILED: {
        const { error } = action;

        if (error?.name !== JitsiConnectionErrors.NOT_LIVE_ERROR) {
            break;
        }

        const { hosts, visitors: visitorsConfig } = getState()['features/base/config'];
        const { locationURL, preferVisitor } = getState()['features/base/connection'];

        if (!visitorsConfig?.queueService || !locationURL || !preferVisitor) {
            break;
        }

        // let's subscribe for visitor waiting queue
        const { room } = getState()['features/base/conference'];
        const conferenceJid = `${room}@${hosts?.muc}`;

        WebsocketClient.getInstance()
            .connect(`wss://${visitorsConfig?.queueService}/visitor/websocket`,
                `/secured/conference/visitor/topic.${conferenceJid}`,
                msg => {
                    if ('status' in msg && msg.status === 'live') {
                        logger.info('The conference is now live!');

                        WebsocketClient.getInstance().disconnect()
                            .then(() => {
                                let delay = 0;

                                // now let's connect to meeting
                                if ('randomDelayMs' in msg) {
                                    delay = msg.randomDelayMs;
                                }

                                if (WebsocketClient.getInstance().connectCount > 1) {
                                    // if we keep connecting/disconnecting, let's slow it down
                                    delay = 30 * 1000;
                                }

                                setTimeout(() => {
                                    dispatch(joinConference());
                                    dispatch(setInVisitorsQueue(false));
                                }, Math.random() * delay);
                            });
                    }
                },

                getState()['features/base/jwt'].jwt,
                () => {
                    dispatch(setInVisitorsQueue(true));
                });

        break;
    }
    case PARTICIPANT_UPDATED: {
        const { visitors: visitorsConfig } = toState(getState)['features/base/config'];

        if (visitorsConfig?.queueService && isLocalParticipantModerator(getState)) {
            const { metadata } = getState()['features/base/conference'];

            if (metadata?.visitors?.live === false && !WebsocketClient.getInstance().isActive()) {
                // when go live is available and false, we should subscribe
                // to the service if available to listen for waiting visitors
                _subscribeQueueStats(getState(), dispatch);
            }
        }

        break;
    }
    case SET_CONFIG: {
        const result = next(action);
        const { preferVisitor } = action.config;

        if (preferVisitor !== undefined) {
            setPreferVisitor(preferVisitor);
        }

        return result;
    }
    case UPDATE_CONFERENCE_METADATA: {
        const { metadata } = action;
        const { visitors: visitorsConfig } = toState(getState)['features/base/config'];

        if (!visitorsConfig?.queueService) {
            break;
        }

        if (isLocalParticipantModerator(getState)) {
            if (metadata?.visitors?.live === false) {
                if (!WebsocketClient.getInstance().isActive()) {
                    // if metadata go live changes to goLive false and local is moderator
                    // we should subscribe to the service if available to listen for waiting visitors
                    _subscribeQueueStats(getState(), dispatch);
                }

                _showNotLiveNotification(dispatch, getVisitorsInQueueCount(getState));
            } else if (metadata?.visitors?.live) {
                dispatch(hideNotification(VISITORS_NOT_LIVE_NOTIFICATION_ID));
                WebsocketClient.getInstance().disconnect();
            }
        }

        break;
    }
    case UPDATE_VISITORS_IN_QUEUE_COUNT: {
        _showNotLiveNotification(dispatch, action.count);

        break;
    }
    }

    return next(action);
});

/**
 * Shows a notification that the meeting is not live.
 *
 * @param {Dispatch} dispatch - The Redux dispatch function.
 * @param {number} count - The count of visitors waiting.
 * @returns {void}
 */
function _showNotLiveNotification(dispatch: IStore['dispatch'], count: number): void {
    // let's show notification
    dispatch(showNotification({
        titleKey: 'notify.waitingVisitorsTitle',
        descriptionKey: 'notify.waitingVisitors',
        descriptionArguments: {
            waitingVisitors: count
        },
        disableClosing: true,
        uid: VISITORS_NOT_LIVE_NOTIFICATION_ID,
        customActionNameKey: [ 'participantsPane.actions.goLive' ],
        customActionType: [ BUTTON_TYPES.PRIMARY ],
        customActionHandler: [ () => batch(() => {
            dispatch(hideNotification(VISITORS_NOT_LIVE_NOTIFICATION_ID));
            dispatch(goLive());
        }) ],
        icon: NOTIFICATION_ICON.PARTICIPANTS
    }, NOTIFICATION_TIMEOUT_TYPE.LONG));
}

/**
 * Subscribe for moderator stats.
 *
 * @param {Function|Object} stateful - The redux store or {@code getState}
 * function.
 * @param {Dispatch} dispatch - The Redux dispatch function.
 * @returns {void}
 */
function _subscribeQueueStats(stateful: IStateful, dispatch: IStore['dispatch']) {
    const { hosts } = toState(stateful)['features/base/config'];
    const { room } = toState(stateful)['features/base/conference'];
    const conferenceJid = `${room}@${hosts?.muc}`;

    const { visitors: visitorsConfig } = toState(stateful)['features/base/config'];

    WebsocketClient.getInstance()
        .connect(`wss://${visitorsConfig?.queueService}/visitor/websocket`,
            `/secured/conference/state/topic.${conferenceJid}`,
            msg => {
                if ('visitorsWaiting' in msg) {
                    dispatch(updateVisitorsInQueueCount(msg.visitorsWaiting));
                }
            },
            toState(stateful)['features/base/jwt'].jwt);
}

/**
 * Function to handle the promotion notification.
 *
 * @param {Object} store - The Redux store.
 * @returns {void}
 */
function _handlePromotionNotification(
    { dispatch, getState }: { dispatch: IStore['dispatch']; getState: IStore['getState']; }) {
    const requests = getPromotionRequests(getState());

    if (requests.length === 0) {
        dispatch(hideNotification(VISITORS_PROMOTION_NOTIFICATION_ID));

        return;
    }

    let notificationTitle;
    let customActionNameKey;
    let customActionHandler;
    let customActionType;
    let descriptionKey;
    let icon;

    if (requests.length === 1) {
        const firstRequest = requests[0];

        descriptionKey = 'notify.visitorWantsToJoin';
        notificationTitle = i18n.t('notify.visitorWantsToJoinTitle', {
            participantName: firstRequest.nick
        });
        icon = NOTIFICATION_ICON.WARNING_STOP_HAND;
        customActionNameKey = [ 'participantsPane.actions.promoteVisitorAdmit', 'participantsPane.actions.promoteVisitorReject' ];
        customActionType = [ BUTTON_TYPES.PRIMARY, BUTTON_TYPES.DESTRUCTIVE ];
        customActionHandler = [ () => batch(() => {
            dispatch(hideNotification(VISITORS_PROMOTION_NOTIFICATION_ID));
            dispatch(approveRequest(firstRequest));
        }),
            () => batch(() => {
                dispatch(hideNotification(VISITORS_PROMOTION_NOTIFICATION_ID));
                dispatch(denyRequest(firstRequest));
            }) ];
    } else {
        descriptionKey = 'notify.visitorsWantToJoin';
        notificationTitle = i18n.t('notify.raisedHandVisitors', {
            raisedHandVisitors: `${requests.length} ${plural(requests.length, i18n.t('notify.oneVisitor'), i18n.t('notify.twoVisitors'), i18n.t('notify.fiveVisitors'))}`
        });
        icon = NOTIFICATION_ICON.WARNING_STOP_HAND;
        customActionNameKey = [ 'notify.viewVisitors' ];
        customActionType = [ BUTTON_TYPES.DESTRUCTIVE_GRAY ];
        customActionHandler = [ () => batch(() => {
            dispatch(hideNotification(VISITORS_PROMOTION_NOTIFICATION_ID));
            dispatch(openParticipantsPane());
        }) ];
    }

    dispatch(showNotification({
        title: notificationTitle,
        descriptionKey,
        uid: VISITORS_PROMOTION_NOTIFICATION_ID,
        customActionNameKey,
        customActionType,
        customActionHandler,
        icon
    }, NOTIFICATION_TIMEOUT_TYPE.LONG));
}
