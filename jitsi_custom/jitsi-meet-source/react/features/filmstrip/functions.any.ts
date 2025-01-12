import { IReduxState, IStore } from '../app/types';
import {
    getActiveSpeakersToBeDisplayed, getLocalParticipant, getRaiseHandsQueue, getSortedModeratorList,
    getVirtualScreenshareParticipantOwnerId
} from '../base/participants/functions';

import { setRemoteParticipants } from './actions';
import { isFilmstripScrollVisible } from './functions';

/**
 * Computes the reorderd list of the remote participants.
 *
 * @param {*} store - The redux store.
 * @param {boolean} force - Does not short circuit, the execution, make execute all checks.
 * @param raisedHandParticipantsQueue
 * @returns {void}
 * @private
 */
export function updateRemoteParticipants(store: IStore, force?: boolean, raisedHandParticipantsQueue?: Array<{
    id: string;
    raisedHandTimestamp: number;
}>) {
    const state = store.getState();
    let reorderedParticipants = [];
    const { sortedRemoteVirtualScreenshareParticipants } = state['features/base/participants'];

    if (!isFilmstripScrollVisible(state) && !sortedRemoteVirtualScreenshareParticipants.size && !force) {
        return;
    }

    const {
        fakeParticipants,
        sortedRemoteParticipants
    } = state['features/base/participants'];
    const remoteParticipants = new Map(sortedRemoteParticipants);
    const localParticipant = getLocalParticipant(state);
    const screenShareParticipants = sortedRemoteVirtualScreenshareParticipants
        ? [ ...sortedRemoteVirtualScreenshareParticipants.keys() ] : [];
    const sharedVideos = fakeParticipants ? Array.from(fakeParticipants.keys()) : [];
    const speakers = getActiveSpeakersToBeDisplayed(state);
    const raisedHandQueue = raisedHandParticipantsQueue ?? getRaiseHandsQueue(state);
    const raisedHandParticipants = raisedHandQueue.map(({ id: particId }) => particId);
    const remoteRaisedHandParticipants = new Set(raisedHandParticipants || []);
    const moderatorsMap = getSortedModeratorList(state);

    if (localParticipant !== undefined && remoteRaisedHandParticipants.has(localParticipant.id)) {
        remoteRaisedHandParticipants.delete(localParticipant.id);
    }

    for (const participant of moderatorsMap.keys()) {
        // Avoid duplicates.
        if (remoteRaisedHandParticipants.has(participant)) {
            remoteRaisedHandParticipants.delete(participant);
        }
        if (remoteParticipants.has(participant)) {
            remoteParticipants.delete(participant);
        }
        if (speakers.has(participant)) {
            speakers.delete(participant);
        }
    }

    for (const participant of remoteRaisedHandParticipants.keys()) {
        // Avoid duplicates.
        if (remoteParticipants.has(participant)) {
            remoteParticipants.delete(participant);
        }
        if (speakers.has(participant)) {
            speakers.delete(participant);
        }
    }

    for (const screenshare of screenShareParticipants) {
        const ownerId = getVirtualScreenshareParticipantOwnerId(screenshare);

        remoteParticipants.delete(ownerId);
        remoteParticipants.delete(screenshare);
        speakers.delete(ownerId);
    }

    for (const sharedVideo of sharedVideos) {
        remoteParticipants.delete(sharedVideo);
    }
    for (const speaker of speakers.keys()) {
        remoteParticipants.delete(speaker);
    }

    // Always update the order of the thumnails.
    const participantsWithScreenShare = screenShareParticipants.reduce<string[]>((acc, screenshare) => {
        const ownerId = getVirtualScreenshareParticipantOwnerId(screenshare);

        acc.push(ownerId);
        acc.push(screenshare);

        return acc;
    }, []);

    // сортируем по id от меньшего к большему
    // НЕ сортируем по id remoteRaisedHandParticipants, чтобы их порядок сохранился по очереди поднятия руки
    participantsWithScreenShare.sort((a, b) => a.localeCompare(b));
    sharedVideos.sort((a, b) => a.localeCompare(b));
    const reorderedSpeakers = Array.from(speakers.keys()).sort((a, b) => a.localeCompare(b));
    const reorderedRemoteParticipants = Array.from(remoteParticipants.keys()).sort((a, b) => a.localeCompare(b));

    reorderedParticipants = [
        ...Array.from(remoteRaisedHandParticipants.keys()),
        ...Array.from(moderatorsMap.keys()),
        ...participantsWithScreenShare,
        ...sharedVideos,
        ...reorderedSpeakers,
        ...reorderedRemoteParticipants
    ];

    store.dispatch(setRemoteParticipants(Array.from(new Set(reorderedParticipants))));
}

/**
 * Private helper to calculate the reordered list of remote participants when a participant leaves.
 *
 * @param {*} store - The redux store.
 * @param {string} participantId - The endpoint id of the participant leaving the call.
 * @returns {void}
 * @private
 */
export function updateRemoteParticipantsOnLeave(store: IStore, participantId: string | null = null) {
    if (!participantId) {
        return;
    }
    const state = store.getState();
    const { remoteParticipants } = state['features/filmstrip'];
    const reorderedParticipants = new Set(remoteParticipants);

    reorderedParticipants.delete(participantId)
    && store.dispatch(setRemoteParticipants(Array.from(reorderedParticipants)));
}

/**
 * Returns whether tileview is completely disabled.
 *
 * @param {IReduxState} state - Redux state.
 * @returns {boolean} - Whether tileview is completely disabled.
 */
export function isTileViewModeDisabled(state: IReduxState) {
    const { tileView = {} } = state['features/base/config'];

    return tileView.disabled;
}
