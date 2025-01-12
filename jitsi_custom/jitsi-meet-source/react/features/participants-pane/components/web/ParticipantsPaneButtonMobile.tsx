import React from 'react';
import { connect } from 'react-redux';

import { IReduxState } from '../../../app/types';
import { translate } from '../../../base/i18n/functions';
import { IconUsers } from '../../../base/icons/svg';
import AbstractButton, { IProps as AbstractButtonProps } from '../../../base/toolbox/components/AbstractButton';
import { close as closeParticipantsPane, open as openParticipantsPane } from '../../../participants-pane/actions.web';
import { closeOverflowMenuIfOpen } from '../../../toolbox/actions.web';
import { isParticipantsPaneEnabled } from '../../functions';
import { closeChat } from "../../../chat/actions.web";


/**
 * The type of the React {@code Component} props of {@link ParticipantsPaneButtonMobile}.
 */
interface IProps extends AbstractButtonProps {

    /**
     * Whether or not the participants pane is open.
     */
    _isOpen: boolean;

    /**
     * Whether or not the chat is open.
     */
    _isChatOpen: boolean;

    /**
     * Whether participants feature is enabled or not.
     */
    _isParticipantsPaneEnabled: boolean;

    /**
     * Whether or not the knocking lobby.
     */
    _lobbyKnocking: boolean;
}

interface IState {

    /**
     * Whether or not is being hovered.
     */
    isHovered: boolean;
}

/**
 * Implementation of a button for accessing participants pane.
 */
class ParticipantsPaneButtonMobile extends AbstractButton<IProps, IState> {

    /**
     * Initializes a new {@code ParticipantsPaneButtonMobile} instance.
     *
     * @param {Object} props - The read-only properties with which the new
     * instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        this.state = {
            isHovered: false
        };
    }

    accessibilityLabel = 'toolbar.accessibilityLabel.participants';
    toggledAccessibilityLabel = 'toolbar.accessibilityLabel.closeParticipantsPane';
    icon = IconUsers;
    label = 'toolbar.participants';

    /**
     * Indicates whether this button is in toggled state or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isToggled() {
        return this.props._isOpen;
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
     * Handles clicking the button, and toggles the participants pane.
     *
     * @private
     * @returns {void}
     */
    _handleClick() {
        const { dispatch, _isOpen, _isChatOpen } = this.props;

        dispatch(closeOverflowMenuIfOpen());

        if (_isOpen) {
            dispatch(closeParticipantsPane());
        } else {

            if (_isChatOpen) {
                dispatch(closeChat());
            }
            dispatch(openParticipantsPane());
        }
    }

    /**
     * Overrides AbstractButton's {@link Component#render()}.
     *
     * @override
     * @protected
     * @returns {React$Node}
     */
    render() {
        const { _isParticipantsPaneEnabled } = this.props;

        if (!_isParticipantsPaneEnabled) {
            return null;
        }

        return (
            <>
                {super.render()}
            </>
        );
    }
}

/**
 * Maps part of the Redux state to the props of this component.
 *
 * @param {Object} state - The Redux state.
 * @returns {IProps}
 */
function mapStateToProps(state: IReduxState) {
    const { isOpen } = state['features/participants-pane'];
    const _isChatOpen = state['features/chat'].isOpen;
    const { knocking } = state['features/lobby'];

    return {
        _isOpen: isOpen,
        _isChatOpen: _isChatOpen,
        _isParticipantsPaneEnabled: isParticipantsPaneEnabled(state),
        _lobbyKnocking: knocking
    };
}

export default translate(connect(mapStateToProps)(ParticipantsPaneButtonMobile));
