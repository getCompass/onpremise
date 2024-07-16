import React from 'react';
import { connect } from 'react-redux';

import { IReduxState } from '../../../app/types';
import { translate } from '../../../base/i18n/functions';
import { IconUsers } from '../../../base/icons/svg';
import AbstractButton, { IProps as AbstractButtonProps } from '../../../base/toolbox/components/AbstractButton';
import {
    close as closeParticipantsPane,
    open as openParticipantsPane
} from '../../../participants-pane/actions.web';
import { isParticipantsPaneEnabled } from '../../functions';

import ParticipantsCounter from './ParticipantsCounter';


/**
 * The type of the React {@code Component} props of {@link ParticipantsPaneButton}.
 */
interface IProps extends AbstractButtonProps {

    /**
     * Whether or not the participants pane is open.
     */
    _isOpen: boolean;

    /**
     * Whether participants feature is enabled or not.
     */
    _isParticipantsPaneEnabled: boolean;

    /**
     * Whether or not the knocking lobby.
     */
    _lobbyKnocking: boolean;
}

/**
 * Implementation of a button for accessing participants pane.
 */
class ParticipantsPaneButton extends AbstractButton<IProps> {
    accessibilityLabel = 'toolbar.accessibilityLabel.participants';
    toggledAccessibilityLabel = 'toolbar.accessibilityLabel.closeParticipantsPane';
    icon = IconUsers;
    label = 'toolbar.participants';
    tooltip = 'toolbar.openParticipantsPane';
    toggledTooltip = 'toolbar.closeParticipantsPane';

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
        const { dispatch, _isOpen } = this.props;

        if (_isOpen) {
            dispatch(closeParticipantsPane());
        } else {
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
            <div
                className = 'toolbar-button-with-badge'>
                { super.render() }
                <ParticipantsCounter />
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
    const {knocking} = state['features/lobby'];

    return {
        _isOpen: isOpen,
        _isParticipantsPaneEnabled: isParticipantsPaneEnabled(state),
        _lobbyKnocking: knocking,
    };
}

export default translate(connect(mapStateToProps)(ParticipantsPaneButton));
