// @ts-expect-error
import VideoLayout from '../../../modules/UI/videolayout/VideoLayout';
import { IStore } from '../app/types';

import { OPEN_CHAT } from './actionTypes';
import { closeChat, setIsPollsTabFocused } from './actions.any';
import { close as closeParticipantsPane } from "../participants-pane/actions.any";
import { iAmVisitor } from "../visitors/functions";

export * from './actions.any';

/**
 * Displays the chat panel.
 *
 * @param {Object} participant - The recipient for the private chat.
 * @param {Object} _disablePolls - Used on native.
 * @returns {{
 *     participant: Participant,
 *     type: OPEN_CHAT
 * }}
 */
export function openChat(participant?: Object, _disablePolls?: boolean) {
    return function(dispatch: IStore['dispatch']) {
        dispatch({
            participant,
            type: OPEN_CHAT
        });
    };
}

/**
 * Toggles display of the chat panel.
 *
 * @returns {Function}
 */
export function toggleChat() {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        const { isOpen, isPollsTabFocused } = getState()['features/chat'];
        const isParticipantPaneOpen = getState()['features/participants-pane'].isOpen;
        const imVisitor = iAmVisitor(getState());

        // переключаем на чат, если стали зрителем (опросы недоступны зрителю)
        if (isPollsTabFocused && imVisitor) {
            dispatch(setIsPollsTabFocused(false));
        }

        if (isOpen) {
            dispatch(closeChat());
        } else {

            if (isParticipantPaneOpen) {
                dispatch(closeParticipantsPane());
            }
            dispatch(openChat());
        }

        // Recompute the large video size whenever we toggle the chat, as it takes chat state into account.
        VideoLayout.onResize();
    };
}
