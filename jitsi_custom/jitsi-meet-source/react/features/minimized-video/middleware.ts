import { DOMINANT_SPEAKER_CHANGED, PIN_PARTICIPANT } from '../base/participants/actionTypes';
import { getDominantSpeakerParticipant } from '../base/participants/functions';
import MiddlewareRegistry from '../base/redux/MiddlewareRegistry';
import { isTestModeEnabled } from '../base/testing/functions';

import { selectParticipantInMinimizedVideo } from './actions';
import logger from './logger';

import './subscriber';
import { setHorizontalViewDimensions } from "../filmstrip/actions.web";

/**
 * Middleware that catches actions related to participants and tracks and
 * dispatches an action to select a participant depicted by MinimizedVideo.
 *
 * @param {Store} store - Redux store.
 * @returns {Function}
 */
MiddlewareRegistry.register(store => next => action => {
    switch (action.type) {
    case DOMINANT_SPEAKER_CHANGED: {
        const state = store.getState();
        const dominantSpeaker = getDominantSpeakerParticipant(state);


        if (dominantSpeaker?.id === action.participant.id) {
            return next(action);
        }

        const result = next(action);

        if (isTestModeEnabled(state)) {
            logger.info(`Dominant speaker changed event for: ${action.participant.id}`);
        }

        store.dispatch(setHorizontalViewDimensions());
        return result;
    }
    }
    const result = next(action);

    return result;
});
