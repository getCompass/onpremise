import React from 'react';
import { connect } from 'react-redux';

import { translate } from '../../../base/i18n/functions';
import Dialog from '../../../base/ui/components/web/Dialog';
import AbstractKickRemoteParticipantDialog from '../AbstractKickRemoteParticipantDialog';

/**
 * Dialog to confirm a remote participant kick action.
 */
class KickRemoteParticipantDialog extends AbstractKickRemoteParticipantDialog {
    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        return (
            <Dialog
                ok = {{ translationKey: 'dialog.kickParticipantButton' }}
                onSubmit = {this._onSubmit}
                hideCloseButton = {true}
                titleKey = 'dialog.kickParticipantTitle'
                className = {'kick-user-dialog-container'}
                classNameHeader = {'kick-user-header-dialog'}
                classNameContent = {'kick-user-content-dialog'}
                classNameFooter = {'kick-user-footer-dialog'}>
                <div>
                    {this.props.t('dialog.kickParticipantDialog')}
                </div>
            </Dialog>
        );
    }
}

export default translate(connect()(KickRemoteParticipantDialog));
