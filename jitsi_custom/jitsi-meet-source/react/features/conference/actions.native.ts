import { IStore } from '../app/types';
import { hideDialog, openDialog } from '../base/dialog/actions';
import AlertDialog from '../base/dialog/components/native/AlertDialog';
import { getParticipantDisplayName } from '../base/participants/functions';

import { DISMISS_CALENDAR_NOTIFICATION } from './actionTypes';


/**
 * Notify that we've been kicked out of the conference.
 *
 * @param {JitsiParticipant} participant - The {@link JitsiParticipant}
 * instance which initiated the kick event.
 * @param {?Function} submit - The function to execute after submiting the dialog.
 * @returns {Function}
 */
export function notifyKickedOut(participant: any, submit?: Function) {
    return (dispatch: IStore['dispatch'], getState: IStore['getState']) => {
        if (!participant || participant?.isReplaced?.()) {
            submit?.();

            return;
        }

        dispatch(openDialog(AlertDialog, {
            contentKey: {
                key: 'dialog.kickTitle',
                params: {
                    participantDisplayName: getParticipantDisplayName(getState, participant.getId())
                }
            },
            onSubmit: submit
        }));
    };
}

/**
 * Notify that we've been kicked out of the conference.
 *
 * @param {string} reasonKey - The translation key for the reason why the conference failed.
 * @param {?Function} submit - The function to execute after submiting the dialog.
 * @returns {Function}
 */
export function notifyConferenceFailed(reasonKey: string, submit?: Function) {
    return (dispatch: IStore['dispatch']) => {
        if (!reasonKey || reasonKey === 'dialog.sessTerminatedReason') {
            submit?.();

            return;
        }

        // we have to push the opening of the dialog to the queue
        // so that we make sure it will be visible after the events
        // of conference destroyed are done
        setTimeout(() => dispatch(openDialog(AlertDialog, {
            contentKey: {
                key: reasonKey
            },
            params: {
            },
            onSubmit: () => {
                submit?.();
                dispatch(hideDialog(AlertDialog));
            }
        })));
    };
}

/**
 * Dismisses calendar notification about next or ongoing event.
 *
 * @returns {Object}
 */
export function dismissCalendarNotification() {
    return {
        type: DISMISS_CALENDAR_NOTIFICATION
    };
}
