import React from 'react';
import { connect } from 'react-redux';

import { createToolbarEvent } from '../../../analytics/AnalyticsEvents';
import { sendAnalytics } from '../../../analytics/functions';
import { IReduxState } from '../../../app/types';
import { translate } from '../../../base/i18n/functions';
import { IconMessage, IconVideo, IconVideoMobile } from '../../../base/icons/svg';
import AbstractButton, { IProps as AbstractButtonProps } from '../../../base/toolbox/components/AbstractButton';
import { closeOverflowMenuIfOpen } from '../../../toolbox/actions.web';

import { isMobileBrowser } from "../../../base/environment/utils";
import { SETTINGS_TABS } from "../../../settings/constants";
import { openSettingsDialog } from "../../../settings/actions.web";

/**
 * The type of the React {@code Component} props of {@link VideoSettingsButtonMobile}.
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
class VideoSettingsButtonMobile extends AbstractButton<IProps> {
    accessibilityLabel = this.props._hasPermissions ? 'settings.video' : 'settings.videoNoPermissions';
    icon = IconVideo;
    label = this.props._hasPermissions ? 'settings.video' : 'settings.videoNoPermissions';

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

        dispatch(openSettingsDialog(SETTINGS_TABS.VIDEO));
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
        _hasPermissions: permissions.video,
    };
};

export default translate(connect(mapStateToProps)(VideoSettingsButtonMobile));
