import { PARTICIPANT_ID_CHANGED } from '../base/participants/actionTypes';
import ReducerRegistry from '../base/redux/ReducerRegistry';

import {
    SELECT_MINIMIZED_VIDEO_PARTICIPANT,
    SET_MINIMIZED_VIDEO_DIMENSIONS,
    SET_SEE_WHAT_IS_BEING_SHARED,
    UPDATE_KNOWN_MINIMIZED_VIDEO_RESOLUTION
} from './actionTypes';

export interface IMinimizedVideoState {
    height?: number;
    participantId?: string;
    resolution?: number;
    seeWhatIsBeingShared?: boolean;
    width?: number;
}

ReducerRegistry.register<IMinimizedVideoState>('features/minimized-video', (state = {}, action): IMinimizedVideoState => {
    switch (action.type) {

    // When conference is joined, we update ID of local participant from default
    // 'local' to real ID. However, in minimized video we might have already
    // selected 'local' as participant on stage. So in this case we must update
    // ID of participant on stage to match ID in 'participants' state to avoid
    // additional changes in state and (re)renders.
    case PARTICIPANT_ID_CHANGED:
        if (state.participantId === action.oldValue) {
            return {
                ...state,
                participantId: action.newValue
            };
        }
        break;

    case SELECT_MINIMIZED_VIDEO_PARTICIPANT:

        return {
            ...state,
            participantId: action.participantId
        };

    case SET_MINIMIZED_VIDEO_DIMENSIONS:
        return {
            ...state,
            height: action.height,
            width: action.width
        };

    case UPDATE_KNOWN_MINIMIZED_VIDEO_RESOLUTION:
        return {
            ...state,
            resolution: action.resolution
        };

    case SET_SEE_WHAT_IS_BEING_SHARED:
        return {
            ...state,
            seeWhatIsBeingShared: action.seeWhatIsBeingShared
        };

    }

    return state;
});
