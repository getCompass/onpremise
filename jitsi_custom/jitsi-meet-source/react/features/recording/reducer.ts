import ReducerRegistry from '../base/redux/ReducerRegistry';

import {
    CLEAR_RECORDING_SESSIONS,
    RECORDING_SESSION_UPDATED,
    SET_MEETING_HIGHLIGHT_BUTTON_STATE,
    SET_PENDING_RECORDING_NOTIFICATION_UID,
    SET_SELECTED_RECORDING_SERVICE,
    SET_START_RECORDING_NOTIFICATION_SHOWN,
    SET_STREAM_KEY,
    TOGGLE_ELECTRON_RECORDING, USER_RECORDING_COUNT_UPDATES,
} from './actionTypes';

const DEFAULT_STATE = {
    disableHighlightMeetingMoment: false,
    pendingNotificationUids: {},
    selectedRecordingService: '',
    isStartRecording: false,
    userRecordingCount: 0,
    previousUserRecordingCount: 0,
    sessionDatas: [],
};

export interface ISessionData {
    error?: Error;
    id?: string;
    initiator?: { getId: Function };
    liveStreamViewURL?: string;
    mode?: string;
    status?: string;
    terminator?: { getId: Function };
    timestamp?: number;
}

export interface IRecordingState {
    disableHighlightMeetingMoment: boolean;
    pendingNotificationUids: {
        [key: string]: string | undefined;
    };
    selectedRecordingService: string;
    sessionDatas: Array<ISessionData>;
    streamKey?: string;
    wasStartRecordingSuggested?: boolean;
    isStartRecording: boolean;
    userRecordingCount: number;
    previousUserRecordingCount: number;
}

/**
 * The name of the Redux store this feature stores its state in.
 */
const STORE_NAME = 'features/recording';

/**
 * Reduces the Redux actions of the feature features/recording.
 */
ReducerRegistry.register<IRecordingState>(STORE_NAME, (state = DEFAULT_STATE, action): IRecordingState => {
    switch (action.type) {
        case CLEAR_RECORDING_SESSIONS:
            return {
                ...state,
                sessionDatas: [],
            };

        case RECORDING_SESSION_UPDATED:
            return {
                ...state,
                sessionDatas: _updateSessionDatas(state.sessionDatas, action.sessionData),
            };

        case SET_PENDING_RECORDING_NOTIFICATION_UID: {
            const pendingNotificationUids = {
                ...state.pendingNotificationUids,
            };

            pendingNotificationUids[action.streamType] = action.uid;

            return {
                ...state,
                pendingNotificationUids,
            };
        }

        case SET_SELECTED_RECORDING_SERVICE: {
            return {
                ...state,
                selectedRecordingService: action.selectedRecordingService,
            };
        }

        case SET_STREAM_KEY:
            return {
                ...state,
                streamKey: action.streamKey,
            };

        case SET_MEETING_HIGHLIGHT_BUTTON_STATE:
            return {
                ...state,
                disableHighlightMeetingMoment: action.disabled,
            };

        case TOGGLE_ELECTRON_RECORDING:
            return {
                ...state,
                isStartRecording: action.payload,
            };

        case SET_START_RECORDING_NOTIFICATION_SHOWN:
            return {
                ...state,
                wasStartRecordingSuggested: true,
            };

        case USER_RECORDING_COUNT_UPDATES:
            return {
                ...state,
                userRecordingCount: action.userRecordingCount,
                previousUserRecordingCount: state.userRecordingCount,
            };

        default:
            return state;
    }
});

/**
 * Updates the known information on recording sessions.
 *
 * @param {Array} sessionDatas - The current sessions in the redux store.
 * @param {Object} newSessionData - The updated session data.
 * @private
 * @returns {Array} The session datas with the updated session data added.
 */
function _updateSessionDatas(sessionDatas: ISessionData[], newSessionData: ISessionData) {
    const hasExistingSessionData = sessionDatas.find((sessionData) => sessionData.id === newSessionData.id);
    let newSessionDatas;

    if (hasExistingSessionData) {
        newSessionDatas = sessionDatas.map((sessionData) => {
            if (sessionData.id === newSessionData.id) {
                return {
                    ...newSessionData,
                };
            }

            // Nothing to update for this session data so pass it back in.
            return sessionData;
        });
    } else {
        // If the session data is not present, then there is nothing to update
        // and instead it needs to be added to the known session datas.
        newSessionDatas = [...sessionDatas, { ...newSessionData }];
    }

    return newSessionDatas;
}
