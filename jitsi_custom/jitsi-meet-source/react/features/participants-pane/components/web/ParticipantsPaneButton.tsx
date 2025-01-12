import React from 'react';
import { connect } from 'react-redux';

import { IReduxState } from '../../../app/types';
import { translate } from '../../../base/i18n/functions';
import { IconUsers } from '../../../base/icons/svg';
import AbstractButton, { IProps as AbstractButtonProps } from '../../../base/toolbox/components/AbstractButton';
import { close as closeParticipantsPane, open as openParticipantsPane } from '../../../participants-pane/actions.web';
import { closeOverflowMenuIfOpen } from '../../../toolbox/actions.web';
import { isParticipantsPaneEnabled } from '../../functions';

import ParticipantsCounter from './ParticipantsCounter';
import { closeChat } from "../../../chat/actions.web";


/**
 * The type of the React {@code Component} props of {@link ParticipantsPaneButton}.
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
class ParticipantsPaneButton extends AbstractButton<IProps, IState> {

    /**
     * Initializes a new {@code ParticipantsPaneButton} instance.
     *
     * @param {Object} props - The read-only properties with which the new
     * instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        this.state = {
            isHovered: false
        };

        // Bind event handlers so they are only bound once for every instance.
        this._onMouseEnter = this._onMouseEnter.bind(this);
        this._onMouseLeave = this._onMouseLeave.bind(this);
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
     * Button is being hovered.
     *
     * @param {MouseEvent} e - The mouse down event.
     * @returns {void}
     */
    _onMouseEnter() {
        this.setState({
            isHovered: true
        });
    }

    /**
     * Button is not being hovered.
     *
     * @returns {void}
     */
    _onMouseLeave() {
        if (this.state.isHovered) {
            this.setState({
                isHovered: false
            });
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
        const { _isParticipantsPaneEnabled, _isOpen } = this.props;
        const { isHovered } = this.state;

        if (!_isParticipantsPaneEnabled) {
            return null;
        }

        return (
            <div
                className = 'toolbar-button-with-badge'
                onMouseLeave = {() => this._onMouseLeave()}
                onMouseEnter = {() => this._onMouseEnter()}>
                {super.render()}
                <ParticipantsCounter visible = {!this._isDisabled() && (_isOpen || isHovered)} />
            </div>
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

export default translate(connect(mapStateToProps)(ParticipantsPaneButton));
