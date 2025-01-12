import { openDialog } from '../base/dialog/actions';
import StopRecordingDialogElectron from '../recording/components/Recording/web/StopRecordingDialogElectron';

import DesktopPicker from './components/DesktopPicker';

/**
 * Signals to open a dialog with the DesktopPicker component.
 *
 * @param {Object} options - Desktop sharing settings.
 * @param {Function} onSourceChoose - The callback to invoke when
 * a DesktopCapturerSource has been chosen.
 * @returns {Object}
 */
export function showDesktopPicker(options: { desktopSharingSources?: any; } = {}, onSourceChoose: Function) {
    const { desktopSharingSources } = options;
    return openDialog(DesktopPicker, {
        desktopSharingSources,
        onSourceChoose
    });
}

export function showConfirmDialog(onConfirm: Function) {
    return openDialog(StopRecordingDialogElectron, {onConfirm});
}
