import { createRecordingEvent } from '../analytics/AnalyticsEvents';
import { sendAnalytics } from '../analytics/functions';
import { IStore } from '../app/types';
import { APP_WILL_MOUNT, APP_WILL_UNMOUNT } from '../base/app/actionTypes';
import { CONFERENCE_JOIN_IN_PROGRESS, NON_PARTICIPANT_MESSAGE_RECEIVED } from '../base/conference/actionTypes';
import { getCurrentConference } from '../base/conference/functions';
import JitsiMeetJS, { JitsiConferenceEvents, JitsiRecordingConstants } from '../base/lib-jitsi-meet';
import { MEDIA_TYPE } from '../base/media/constants';
import { PARTICIPANT_UPDATED } from '../base/participants/actionTypes';
import { updateLocalRecordingStatus } from '../base/participants/actions';
import { COMMAND_PARTICIPANT_JOINED_INFO, PARTICIPANT_ROLE } from '../base/participants/constants';
import { getLocalParticipant, getParticipantDisplayName, isParticipantModerator } from '../base/participants/functions';
import MiddlewareRegistry from '../base/redux/MiddlewareRegistry';
import StateListenerRegistry from '../base/redux/StateListenerRegistry';
import { playSound, stopSound } from '../base/sounds/actions';
import { TRACK_ADDED } from '../base/tracks/actionTypes';
import { hideNotification, showErrorNotification, showNotification } from '../notifications/actions';
import { NOTIFICATION_ICON, NOTIFICATION_TIMEOUT_TYPE } from '../notifications/constants';
import { isRecorderTranscriptionsRunning } from '../transcribing/functions';

import {
    RECORDING_SESSION_UPDATED,
    START_ELECTRON_RECORDING,
    START_LOCAL_RECORDING,
    STOP_ELECTRON_RECORDING,
    STOP_LOCAL_RECORDING,
    TOGGLE_ELECTRON_RECORDING,
    USER_RECORDING_COUNT_UPDATES
} from './actionTypes';
import {
    clearRecordingSessions,
    hidePendingRecordingNotification,
    showPendingRecordingNotification,
    showRecordingError,
    showRecordingLimitNotification,
    showRecordingWarning,
    showStartedRecordingNotification,
    showStartRecordingNotification,
    showStoppedRecordingNotification,
    updateRecordingSessionData
} from './actions';
import LocalRecordingManager from './components/Recording/LocalRecordingManager';
import {
    COMMAND_USER_RECORDING_COUNT,
    LIVE_STREAMING_OFF_SOUND_ID,
    LIVE_STREAMING_ON_SOUND_ID,
    LOCAL_USER_RECORDING_COUNT_ACTION_DEC,
    LOCAL_USER_RECORDING_COUNT_ACTION_INC,
    RECORDING_OFF_SOUND_ID,
    RECORDING_ON_SOUND_ID,
    START_RECORDING_NOTIFICATION_ID
} from './constants';
import {
    getResourceId,
    getSessionById,
    isRecordingRunning,
    registerRecordingAudioFiles,
    unregisterRecordingAudioFiles,
    updateUserRecordingCount
} from './functions';
import logger from './logger';
import { userRecordingCountUpdates } from "./actions.any";

/**
 * StateListenerRegistry provides a reliable way to detect the leaving of a
 * conference, where we need to clean up the recording sessions.
 */
StateListenerRegistry.register(
    /* selector */ state => getCurrentConference(state),
    /* listener */ (conference, { dispatch }) => {
        if (!conference) {
            dispatch(clearRecordingSessions());
        }
    }
);

/**
 * The redux middleware to handle the recorder updates in a React way.
 *
 * @param {Store} store - The redux store.
 * @returns {Function}
 */
MiddlewareRegistry.register(({ dispatch, getState }) => next => action => {
    let oldSessionData;

    if (action.type === RECORDING_SESSION_UPDATED) {
        oldSessionData
            = getSessionById(getState(), action.sessionData.id);
    }

    const result = next(action);

    switch (action.type) {
    case APP_WILL_MOUNT:
        registerRecordingAudioFiles(dispatch);

        break;

    case APP_WILL_UNMOUNT:
        unregisterRecordingAudioFiles(dispatch);

        break;

    case CONFERENCE_JOIN_IN_PROGRESS: {
        const { conference } = action;

        conference.on(
            JitsiConferenceEvents.RECORDER_STATE_CHANGED,
            (recorderSession: any) => {
                if (recorderSession) {
                    recorderSession.getID() && dispatch(updateRecordingSessionData(recorderSession));
                    recorderSession.getError() && _showRecordingErrorNotification(recorderSession, dispatch, getState);
                }

                return;
            });
        break;
    }

    case START_ELECTRON_RECORDING: {
        const participant = getLocalParticipant(getState());
        const isModerator = isParticipantModerator(participant);
        const { conference } = getState()['features/base/conference'];
        try {
            if (!isModerator) {
                throw new Error('NoPermissionsForRecord');
            }

            dispatch({
                type: TOGGLE_ELECTRON_RECORDING,
                payload: true,
            });

            updateUserRecordingCount(conference, LOCAL_USER_RECORDING_COUNT_ACTION_INC);
        } catch (err: any) {
            logger.error("Capture failed", err);

            let descriptionKey = "recording.error";

            if (err.message === "NoPermissionsForRecord") {
                descriptionKey = "recording.noModeratorPermission";
            }
            const props = {
                descriptionKey,
                titleKey: "recording.failedToStart",
            };

            dispatch(showErrorNotification(props, NOTIFICATION_TIMEOUT_TYPE.MEDIUM));
        }
        break;
    }

    case START_LOCAL_RECORDING: {
        const { localRecording } = getState()["features/base/config"];
        const { onlySelf } = action;
        const participant = getLocalParticipant(getState());
        const isModerator = isParticipantModerator(participant);
        const { conference } = getState()['features/base/conference'];

        if (!isModerator) {

            const props = {
                descriptionKey: 'recording.noModeratorPermission',
                titleKey: 'recording.failedToStart'
            };

            if (typeof APP !== 'undefined') {
                APP.API.notifyRecordingStatusChanged(
                    false, 'local', 'NoPermissionsForRecord', isRecorderTranscriptionsRunning(getState()));
            }

            dispatch(showErrorNotification(props, NOTIFICATION_TIMEOUT_TYPE.MEDIUM));
            break;
        }

        LocalRecordingManager.startLocalRecording({
            dispatch,
            getState
        }, action.onlySelf)
            .then(() => {
                if (localRecording?.notifyAllParticipants && !onlySelf) {
                    dispatch(playSound(RECORDING_ON_SOUND_ID));
                }
                dispatch(updateLocalRecordingStatus(true, onlySelf));
                sendAnalytics(createRecordingEvent('started', `local${onlySelf ? '.self' : ''}`));
                if (typeof APP !== 'undefined') {
                    APP.API.notifyRecordingStatusChanged(
                        true, 'local', undefined, isRecorderTranscriptionsRunning(getState()));
                }

                updateUserRecordingCount(conference, LOCAL_USER_RECORDING_COUNT_ACTION_INC);
            })
            .catch(err => {
                logger.error('Capture failed', err);

                let descriptionKey = 'recording.error';

                if (err.message === 'WrongSurfaceSelected') {
                    descriptionKey = 'recording.surfaceError';

                } else if (err.message === 'NoLocalStreams') {
                    descriptionKey = 'recording.noStreams';
                } else if (err.message === 'NoMicTrack') {
                    descriptionKey = 'recording.noMicPermission';
                }
                const props = {
                    descriptionKey,
                    titleKey: 'recording.failedToStart'
                };

                if (typeof APP !== 'undefined') {
                    APP.API.notifyRecordingStatusChanged(
                        false, 'local', err.message, isRecorderTranscriptionsRunning(getState()));
                }

                dispatch(showErrorNotification(props, NOTIFICATION_TIMEOUT_TYPE.MEDIUM));
            });
        break;
    }

    case STOP_ELECTRON_RECORDING: {
        const { conference } = getState()['features/base/conference'];
        APP.API.notifyRecordingStatusChanged(false, 'local');

        const props = {
            descriptionKey: 'recording.saveInDownloads',
            titleKey: 'recording.pushTitle',
            icon: NOTIFICATION_ICON.RECORDING,
        };

        dispatch(showNotification(props, NOTIFICATION_TIMEOUT_TYPE.MEDIUM));

        dispatch({
            type: TOGGLE_ELECTRON_RECORDING,
            payload: false,
        });


        updateUserRecordingCount(conference, LOCAL_USER_RECORDING_COUNT_ACTION_DEC);
        break;
    }

    case STOP_LOCAL_RECORDING: {
        const { localRecording } = getState()['features/base/config'];
        const { conference } = getState()['features/base/conference'];

        if (LocalRecordingManager.isRecordingLocally()) {
            LocalRecordingManager.stopLocalRecording();
            dispatch(updateLocalRecordingStatus(false));
            if (localRecording?.notifyAllParticipants && !LocalRecordingManager.selfRecording) {
                dispatch(playSound(RECORDING_OFF_SOUND_ID));
            }
            if (typeof APP !== 'undefined') {
                APP.API.notifyRecordingStatusChanged(
                    false, 'local', undefined, isRecorderTranscriptionsRunning(getState()));
            }

            updateUserRecordingCount(conference, LOCAL_USER_RECORDING_COUNT_ACTION_DEC);
        }
        break;
    }

    case RECORDING_SESSION_UPDATED: {
        const state = getState();

        // When in recorder mode no notifications are shown
        // or extra sounds are also not desired
        // but we want to indicate those in case of sip gateway
        const {
            iAmRecorder,
            iAmSipGateway,
            recordingLimit
        } = state['features/base/config'];

        if (iAmRecorder && !iAmSipGateway) {
            break;
        }

        const updatedSessionData
            = getSessionById(state, action.sessionData.id);
        const { initiator, mode = '', terminator } = updatedSessionData ?? {};
        const { PENDING, OFF, ON } = JitsiRecordingConstants.status;

        if (updatedSessionData?.status === PENDING && oldSessionData?.status !== PENDING) {
            dispatch(showPendingRecordingNotification(mode));
            dispatch(hideNotification(START_RECORDING_NOTIFICATION_ID));
            break;
        }

        dispatch(hidePendingRecordingNotification(mode));

        if (updatedSessionData?.status === ON) {

            // We receive 2 updates of the session status ON. The first one is from jibri when it joins.
            // The second one is from jicofo which will deliever the initiator value. Since the start
            // recording notification uses the initiator value we skip the jibri update and show the
            // notification on the update from jicofo.
            // FIXE: simplify checks when the backend start sending only one status ON update containing the
            // initiator.
            if (initiator && !oldSessionData?.initiator) {
                if (typeof recordingLimit === 'object') {
                    dispatch(showRecordingLimitNotification(mode));
                } else {
                    dispatch(showStartedRecordingNotification(mode, initiator, action.sessionData.id));
                }
            }

            if (oldSessionData?.status !== ON) {
                sendAnalytics(createRecordingEvent('start', mode));

                let soundID;

                if (mode === JitsiRecordingConstants.mode.FILE && !isRecorderTranscriptionsRunning(state)) {
                    soundID = RECORDING_ON_SOUND_ID;
                } else if (mode === JitsiRecordingConstants.mode.STREAM) {
                    soundID = LIVE_STREAMING_ON_SOUND_ID;
                }

                if (soundID) {
                    dispatch(playSound(soundID));
                }

                if (typeof APP !== 'undefined') {
                    APP.API.notifyRecordingStatusChanged(
                        true, mode, undefined, isRecorderTranscriptionsRunning(state));
                }
            }
        } else if (updatedSessionData?.status === OFF && oldSessionData?.status !== OFF) {
            if (terminator) {
                dispatch(
                    showStoppedRecordingNotification(
                        mode, getParticipantDisplayName(state, getResourceId(terminator))));
            }

            let duration = 0, soundOff, soundOn;

            if (oldSessionData?.timestamp) {
                duration
                    = (Date.now() / 1000) - oldSessionData.timestamp;
            }
            sendAnalytics(createRecordingEvent('stop', mode, duration));

            if (mode === JitsiRecordingConstants.mode.FILE && !isRecorderTranscriptionsRunning(state)) {
                soundOff = RECORDING_OFF_SOUND_ID;
                soundOn = RECORDING_ON_SOUND_ID;
            } else if (mode === JitsiRecordingConstants.mode.STREAM) {
                soundOff = LIVE_STREAMING_OFF_SOUND_ID;
                soundOn = LIVE_STREAMING_ON_SOUND_ID;
            }

            if (soundOff && soundOn) {
                dispatch(stopSound(soundOn));
                dispatch(playSound(soundOff));
            }

            if (typeof APP !== 'undefined') {
                APP.API.notifyRecordingStatusChanged(
                    false, mode, undefined, isRecorderTranscriptionsRunning(state));
            }
        }

        break;
    }
    case TRACK_ADDED: {
        const { track } = action;

        if (LocalRecordingManager.isRecordingLocally() && track.mediaType === MEDIA_TYPE.AUDIO) {
            const audioTrack = track.jitsiTrack.track;

            LocalRecordingManager.addAudioTrackToLocalRecording(audioTrack);
        }
        break;
    }
    case PARTICIPANT_UPDATED: {
        const { id, role } = action.participant;
        const state = getState();
        const localParticipant = getLocalParticipant(state);

        if (localParticipant?.id !== id) {
            return next(action);
        }

        if (role === PARTICIPANT_ROLE.MODERATOR) {
            dispatch(showStartRecordingNotification());
        }

        return next(action);
    }

    case NON_PARTICIPANT_MESSAGE_RECEIVED: {
        const { json: data } = action;

        if (data.type === COMMAND_USER_RECORDING_COUNT) {
            dispatch(userRecordingCountUpdates(data.value));
        }

        if (data.type === COMMAND_PARTICIPANT_JOINED_INFO) {
            dispatch(userRecordingCountUpdates(data.event_list[COMMAND_USER_RECORDING_COUNT]?.value));
        }
        break;
    }

    case USER_RECORDING_COUNT_UPDATES: {
        const { userRecordingCount } = action;
        _handleUserRecordingCountUpdates(dispatch, getState, userRecordingCount);
        break;
    }
    }

    return result;
});

/**
 * Shows a notification about an error in the recording session. A
 * default notification will display if no error is specified in the passed
 * in recording session.
 *
 * @private
 * @param {Object} session - The recorder session model from the
 * lib.
 * @param {Dispatch} dispatch - The Redux Dispatch function.
 * @param {Function} getState - The Redux getState function.
 * @returns {void}
 */
function _showRecordingErrorNotification(session: any, dispatch: IStore['dispatch'], getState: IStore['getState']) {
    const mode = session.getMode();
    const error = session.getError();
    const isStreamMode = mode === JitsiMeetJS.constants.recording.mode.STREAM;

    switch (error) {
    case JitsiMeetJS.constants.recording.error.SERVICE_UNAVAILABLE:
        dispatch(showRecordingError({
            descriptionKey: 'recording.unavailable',
            descriptionArguments: {
                serviceName: isStreamMode
                    ? '$t(liveStreaming.serviceName)'
                    : '$t(recording.serviceName)'
            },
            titleKey: isStreamMode
                ? 'liveStreaming.unavailableTitle'
                : 'recording.unavailableTitle'
        }));
        break;
    case JitsiMeetJS.constants.recording.error.RESOURCE_CONSTRAINT:
        dispatch(showRecordingError({
            descriptionKey: isStreamMode
                ? 'liveStreaming.busy'
                : 'recording.busy',
            titleKey: isStreamMode
                ? 'liveStreaming.busyTitle'
                : 'recording.busyTitle'
        }));
        break;
    case JitsiMeetJS.constants.recording.error.UNEXPECTED_REQUEST:
        dispatch(showRecordingWarning({
            descriptionKey: isStreamMode
                ? 'liveStreaming.sessionAlreadyActive'
                : 'recording.sessionAlreadyActive',
            titleKey: isStreamMode ? 'liveStreaming.inProgress' : 'recording.inProgress'
        }));
        break;
    default:
        dispatch(showRecordingError({
            descriptionKey: isStreamMode
                ? 'liveStreaming.error'
                : 'recording.error',
            titleKey: isStreamMode
                ? 'liveStreaming.failedToStart'
                : 'recording.failedToStart'
        }));
        break;
    }

    if (typeof APP !== 'undefined') {
        APP.API.notifyRecordingStatusChanged(false, mode, error, isRecorderTranscriptionsRunning(getState()));
    }
}

/**
 * Function to handle an notification about recording.
 *
 * @param {IStore["dispatch"]} dispatch - The Redux store.
 * @param {IStore["getState"]} getState - The Redux store.
 * @param {number} userRecordingCount - Users recording count.
 * @returns {void}
 */
function _handleUserRecordingCountUpdates(dispatch: IStore["dispatch"], getState: IStore["getState"], userRecordingCount: number) {
    const previousUserRecordingCount = getState()['features/recording'].previousUserRecordingCount;
    const isLocalRecording = isRecordingRunning(getState());

    // показываем один раз, а не каждый раз когда кто-то включает запись
    if (previousUserRecordingCount < 1 && userRecordingCount > 0 && !isLocalRecording) {

        dispatch(
            showNotification(
                {
                    titleKey: "recording.localRecordingStartWarningForOtherParticipantsTitle",
                    descriptionKey: "recording.localRecordingStartWarningForOtherParticipants",
                    icon: NOTIFICATION_ICON.RECORDING,
                },
                NOTIFICATION_TIMEOUT_TYPE.LONG
            )
        );
    }
}