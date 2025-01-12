import React, { Component } from 'react';
import { WithTranslation } from 'react-i18next';
import { connect } from 'react-redux';

import { IReduxState, IStore } from '../../../app/types';
import { CHAT_ENABLED } from '../../../base/flags/constants';
import { getFeatureFlag } from '../../../base/flags/functions';
import { translate } from '../../../base/i18n/functions';
import { IconMessage } from '../../../base/icons/svg';
import { getParticipantById } from '../../../base/participants/functions';
import { IParticipant } from '../../../base/participants/types';
import ContextMenuItem from '../../../base/ui/components/web/ContextMenuItem';
import { openChat } from '../../../chat/actions.web';
import { isButtonEnabled } from '../../../toolbox/functions.web';
import { NOTIFY_CLICK_MODE } from '../../../toolbox/types';
import { IButtonProps } from '../../types';
import { close as closeParticipantsPane } from "../../../participants-pane/actions.any";
import { isMobileBrowser } from "../../../base/environment/utils";
import ContextMenuItemMobile from "../../../base/ui/components/web/ContextMenuItemMobile";

interface IProps extends IButtonProps, WithTranslation {

    /**
     * True if the private chat functionality is disabled, hence the button is not visible.
     */
    _hidden: boolean;

    className?: string;

    _isParticipantPaneOpen: boolean;

    /**
     * The participant to send the message to.
     */
    _participant?: IParticipant;

    /**
     * Redux dispatch function.
     */
    dispatch: IStore['dispatch'];
}

/**
 * A custom implementation of the PrivateMessageButton specialized for
 * the web version of the remote video menu. When the web platform starts to use
 * the {@code AbstractButton} component for the remote video menu, we can get rid
 * of this component and use the generic button in the chat feature.
 */
class PrivateMessageMenuButton extends Component<IProps> {
    /**
     * Instantiates a new Component instance.
     *
     * @inheritdoc
     */
    constructor(props: IProps) {
        super(props);

        this._onClick = this._onClick.bind(this);
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const { _hidden, className, t } = this.props;

        if (_hidden) {
            return null;
        }

        if (isMobileBrowser()) {
            return (
                <ContextMenuItemMobile
                    accessibilityLabel = {t('toolbar.accessibilityLabel.privateMessage')}
                    icon = {IconMessage}
                    onClick = {this._onClick}
                    className = {className}
                    text = {t('toolbar.privateMessage')} />
            );
        }

        return (
            <ContextMenuItem
                accessibilityLabel = {t('toolbar.accessibilityLabel.privateMessage')}
                icon = {IconMessage}
                onClick = {this._onClick}
                className = {className}
                text = {t('toolbar.privateMessage')} />
        );
    }

    /**
     * Callback to be invoked on pressing the button.
     *
     * @param {React.MouseEvent|undefined} e - The click event.
     * @returns {void}
     */
    _onClick() {
        const { _participant, dispatch, notifyClick, notifyMode, _isParticipantPaneOpen } = this.props;

        notifyClick?.();
        if (notifyMode === NOTIFY_CLICK_MODE.PREVENT_AND_NOTIFY) {
            return;
        }

        if (_isParticipantPaneOpen) {
            dispatch(closeParticipantsPane());
        }
        dispatch(openChat(_participant));
    }
}

/**
 * Maps part of the Redux store to the props of this component.
 *
 * @param {Object} state - The Redux state.
 * @param {IProps} ownProps - The own props of the component.
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState, ownProps: any) {
    const enabled = getFeatureFlag(state, CHAT_ENABLED, true);
    const isParticipantPaneOpen = state['features/participants-pane'].isOpen;
    const { visible = enabled } = ownProps;

    return {
        _isParticipantPaneOpen: isParticipantPaneOpen,
        _participant: getParticipantById(state, ownProps.participantID),
        visible,
        _hidden: typeof interfaceConfig !== 'undefined'
            && (interfaceConfig.DISABLE_PRIVATE_MESSAGES || !isButtonEnabled('chat', state))
    };
}

export default translate(connect(_mapStateToProps)(PrivateMessageMenuButton));
