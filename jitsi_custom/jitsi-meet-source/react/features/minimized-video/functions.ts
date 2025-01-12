import { IReduxState } from '../app/types';
import { getParticipantById } from '../base/participants/functions';

/**
 * Selector for the participant currently displaying on the minimized video.
 *
 * @param {Object} state - The redux state.
 * @returns {Object}
 */
export function getMinimizedVideoParticipant(state: IReduxState) {
    const { participantId } = state['features/minimized-video'];

    return getParticipantById(state, participantId ?? '');
}
