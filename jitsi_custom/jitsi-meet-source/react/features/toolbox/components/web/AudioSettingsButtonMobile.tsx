import React from 'react';
import { connect } from 'react-redux';
import { IReduxState } from '../../../app/types';
import { translate } from '../../../base/i18n/functions';
import { IconVolumeUp } from '../../../base/icons/svg';
import AbstractButton, { IProps as AbstractButtonProps } from '../../../base/toolbox/components/AbstractButton';
import { openSettingsDialog } from "../../../settings/actions.web";
import { SETTINGS_TABS } from "../../../settings/constants";

/**
 * The type of the React {@code Component} props of {@link AudioSettingsButtonMobile}.
 */
interface IProps extends AbstractButtonProps {
    /**
     * Whether or not the knocking lobby.
     */
    _lobbyKnocking: boolean;

    _hasPermissions: boolean;
}

/**
 * Implementation of a button for accessing chat pane.
 */
class AudioSettingsButtonMobile extends AbstractButton<IProps> {
    accessibilityLabel = this.props._hasPermissions ? 'settings.audio' : 'settings.audioNoPermissions';
    icon = IconVolumeUp;
    label = this.props._hasPermissions ? 'settings.audio' : 'settings.audioNoPermissions';
    containerClassName = 'last-context-menu-item';

    /**
     * Indicates whether this button is in disabled state or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isDisabled() {
        return this.props._lobbyKnocking || !this.props._hasPermissions;
    }

    /**
     * Overrides AbstractButton's {@link Component#render()}.
     *
     * @override
     * @protected
     * @returns {boReact$Nodeolean}
     */
    render() {
        return (
            <>
                {super.render()}
            </>
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

        dispatch(openSettingsDialog(SETTINGS_TABS.AUDIO));
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
    const { permissions } = state['features/base/devices'];

    return {
        _lobbyKnocking: knocking,
        _hasPermissions: permissions.audio,
    };
};

export default translate(connect(mapStateToProps)(AudioSettingsButtonMobile));
