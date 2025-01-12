import {
    DOMINANT_SPEAKER_CHANGED,
    PARTICIPANT_JOINED,
    PARTICIPANT_LEFT,
    PIN_PARTICIPANT
} from '../base/participants/actionTypes';
import { getDominantSpeakerParticipant, getLocalParticipant, getParticipantById } from '../base/participants/functions';
import MiddlewareRegistry from '../base/redux/MiddlewareRegistry';
import { isTestModeEnabled } from '../base/testing/functions';
import { TRACK_ADDED, TRACK_REMOVED } from '../base/tracks/actionTypes';
import { TOGGLE_DOCUMENT_EDITING } from '../etherpad/actionTypes';

import { selectParticipantInLargeVideo } from './actions';
import logger from './logger';

import './subscriber';
import { setHorizontalViewDimensions } from "../filmstrip/actions.web";
import { SELECT_LARGE_VIDEO_PARTICIPANT } from "./actionTypes";
import { getPrevLargeVideoParticipant } from "./functions";
import { selectParticipantInMinimizedVideo } from "../minimized-video/actions.any";

/**
 * Middleware that catches actions related to participants and tracks and
 * dispatches an action to select a participant depicted by LargeVideo.
 *
 * @param {Store} store - Redux store.
 * @returns {Function}
 */
MiddlewareRegistry.register(store => next => action => {
    switch (action.type) {
    case DOMINANT_SPEAKER_CHANGED: {
        const state = store.getState();
        const localParticipant = getLocalParticipant(state);
        const dominantSpeaker = getDominantSpeakerParticipant(state);


        if (dominantSpeaker?.id === action.participant.id) {
            return next(action);
        }

        const result = next(action);

        if (isTestModeEnabled(state)) {
            logger.info(`Dominant speaker changed event for: ${action.participant.id}`);
        }

        if (localParticipant && localParticipant.id !== action.participant.id) {
            store.dispatch(selectParticipantInLargeVideo());
        }

        store.dispatch(setHorizontalViewDimensions());
        return result;
    }
    case SELECT_LARGE_VIDEO_PARTICIPANT: {
        const result = next(action);

        const state = store.getState();
        if (action.participantId === undefined || action.participantId === 'local') {
            return result;
        }

        const localParticipant = getLocalParticipant(state);
        const participant = getParticipantById(state, action.participantId);
        const prevLargeParticipant = getPrevLargeVideoParticipant(state);

        // если в большой экран открыли не локального - в миниатюре отображаем себя
        if (participant === undefined || participant.local === false) {

            store.dispatch(selectParticipantInMinimizedVideo(localParticipant?.id));
            return result;
        }

        // если же открыли локального, то отображаем предыдущего
        store.dispatch(selectParticipantInMinimizedVideo(prevLargeParticipant?.id));
        return result;
    }
    case PIN_PARTICIPANT: {
        const result = next(action);

        store.dispatch(selectParticipantInLargeVideo(action.participant?.id));
        store.dispatch(setHorizontalViewDimensions());

        return result;
    }
    case PARTICIPANT_JOINED:
    case PARTICIPANT_LEFT:
    case TOGGLE_DOCUMENT_EDITING:
    case TRACK_ADDED:
    case TRACK_REMOVED: {
        const result = next(action);

        store.dispatch(selectParticipantInLargeVideo());

        return result;
    }
    }
    const result = next(action);

    return result;
});
