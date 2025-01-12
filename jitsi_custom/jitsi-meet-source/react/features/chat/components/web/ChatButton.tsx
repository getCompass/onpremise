import React from 'react';
import { connect } from 'react-redux';

import { createToolbarEvent } from '../../../analytics/AnalyticsEvents';
import { sendAnalytics } from '../../../analytics/functions';
import { IReduxState } from '../../../app/types';
import { translate } from '../../../base/i18n/functions';
import { IconMessage } from '../../../base/icons/svg';
import AbstractButton, { IProps as AbstractButtonProps } from '../../../base/toolbox/components/AbstractButton';
import { closeOverflowMenuIfOpen } from '../../../toolbox/actions.web';
import { toggleChat } from '../../actions.web';

import ChatCounter from './ChatCounter';
import { isMobileBrowser } from "../../../base/environment/utils";

/**
 * The type of the React {@code Component} props of {@link ChatButton}.
 */
interface IProps extends AbstractButtonProps {

    /**
     * Whether or not the chat feature is currently displayed.
     */
    _chatOpen: boolean;

    /**
     * Whether or not the knocking lobby.
     */
    _lobbyKnocking: boolean;
}

/**
 * Implementation of a button for accessing chat pane.
 */
class ChatButton extends AbstractButton<IProps> {
    accessibilityLabel = 'toolbar.accessibilityLabel.openChat';
    toggledAccessibilityLabel = 'toolbar.accessibilityLabel.closeChat';
    icon = IconMessage;
    label = 'toolbar.openChat';
    toggledLabel = 'toolbar.closeChat';

    /**
     * Indicates whether this button is in toggled state or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isToggled() {
        return this.props._chatOpen;
    }

    /**
     * Indicates whether this button is in disabled state or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isDisabled() {
        return this.props._lobbyKnocking;
    }

    /**
     * Overrides AbstractButton's {@link Component#render()}.
     *
     * @override
     * @protected
     * @returns {boReact$Nodeolean}
     */
    render() {
        const isMobile = isMobileBrowser();

        return (
            <div
                className = {`toolbar-button-with-badge ${isMobile ? 'is-mobile' : ''}`}
                key = 'chatcontainer'>
                {super.render()}
                <ChatCounter />
            </div>
        );
    }

    /**
     * Handles clicking the button, and toggles the chat.
     *
     * @private
     * @returns {void}
     */
    _handleClick() {
        const { dispatch } = this.props;

        sendAnalytics(createToolbarEvent(
            'toggle.chat',
            {
                enable: !this.props._chatOpen
            }));
        dispatch(closeOverflowMenuIfOpen());
        dispatch(toggleChat());
    }
}

/**
 * Function that maps parts of Redux state tree into component props.
 *
 * @param {Object} state - Redux state.
 * @returns {Object}
 */
const mapStateToProps = (state: IReduxState) => {
    const { knocking } = state['features/lobby'];

    return {
        _chatOpen: state['features/chat'].isOpen,
        _lobbyKnocking: knocking,
    };
};

export default translate(connect(mapStateToProps)(ChatButton));
