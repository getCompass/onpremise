import React from 'react';
import { connect } from 'react-redux';

import { translate } from '../../../base/i18n/functions';
import Dialog from '../../../base/ui/components/web/Dialog';
import Switch from '../../../base/ui/components/web/Switch';
import AbstractMuteEveryonesVideoDialog, { type IProps, abstractMapStateToProps }
    from '../AbstractMuteEveryonesVideoDialog';
import { isMobileBrowser } from "../../../base/environment/utils";

/**
 * A React Component with the contents for a dialog that asks for confirmation
 * from the user before disabling all remote participants cameras.
 *
 * @augments AbstractMuteEveryonesVideoDialog
 */
class MuteEveryonesVideoDialog extends AbstractMuteEveryonesVideoDialog<IProps> {

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const isMobile = isMobileBrowser();

        return (
            <Dialog
                ok = {{ translationKey: 'dialog.muteParticipantsVideoButton' }}
                onSubmit = {this._onSubmit}
                hideCloseButton = {true}
                title = {this.props.title}
                className = {'mute-everyone-video-dialog-container'}
                classNameHeader = {'mute-everyone-video-header-dialog'}
                classNameContent = {'mute-everyone-video-content-dialog'}
                classNameFooter = {'mute-everyone-video-footer-dialog'}>
                <div className = 'mute-dialog'>
                    <div className = {`mute-dialog-text${isMobile ? ' is-mobile' : ''}`}>
                        {this.state.content}
                    </div>
                    {this.props.isModerationSupported && this.props.exclude.length === 0 && (
                        <>
                            <div className = {`control-row${isMobile ? ' is-mobile' : ''}`}>
                                <div className = {`control-row-container${isMobile ? ' is-mobile' : ''}`}>
                                    <label htmlFor = 'moderation-switch' className = {isMobile ? ' is-mobile' : ''}>
                                        {this.props.t('dialog.moderationVideoLabel')}
                                    </label>
                                    <Switch
                                        checked = {!this.state.moderationEnabled}
                                        id = 'moderation-switch'
                                        onChange = {this._onToggleModeration} />
                                </div>
                            </div>
                        </>
                    )}
                </div>
            </Dialog>
        );
    }
}

export default translate(connect(abstractMapStateToProps)(MuteEveryonesVideoDialog));
