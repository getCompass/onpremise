// @ts-ignore
import React from "react";
import { connect } from 'react-redux';

import { createToolbarEvent } from '../../../analytics/AnalyticsEvents';
import { sendAnalytics } from '../../../analytics/functions';
import { IReduxState } from '../../../app/types';
import { translate } from '../../../base/i18n/functions';
import { IconScreenshare, IconScreenshareToggled } from '../../../base/icons/svg';
import JitsiMeetJS from '../../../base/lib-jitsi-meet/_';
import AbstractButton, { IProps as AbstractButtonProps } from '../../../base/toolbox/components/AbstractButton';
import { startScreenShareFlow } from '../../../screen-share/actions.web';
import { isScreenVideoShared } from '../../../screen-share/functions';
import { closeOverflowMenuIfOpen } from '../../actions.web';
import { isDesktopShareButtonDisabled } from '../../functions.web';
import { getLocalParticipant, isParticipantModerator } from "../../../base/participants/functions";
import { isEnabledFromState } from "../../../av-moderation/functions";
import { MEDIA_TYPE } from "../../../base/media/constants";
import { showNotification } from "../../../notifications/actions";
import {
    NOTIFICATION_ICON,
    NOTIFICATION_TIMEOUT_TYPE,
    SCREENSHARE_NO_PERMISSIONS_NOTIFICATION_ID
} from "../../../notifications/constants";
import {isScreenSharingSupported} from "../../../desktop-picker/functions";
import UnsupportedScreenSharing from "../../../settings/components/web/UnsupportedScreenSharing";

interface IProps extends AbstractButtonProps {

    /**
     * Whether or not screen-sharing is initialized.
     */
    _desktopSharingEnabled: boolean;

    /**
     * Whether or not the local participant is screen-sharing.
     */
    _screensharing: boolean;

    /**
     * Whether or not the knocking lobby.
     */
    _lobbyKnocking: boolean;
}

/**
 * Implementation of a button for sharing desktop / windows.
 */
class ShareDesktopButton extends AbstractButton<IProps> {
    accessibilityLabel = 'toolbar.accessibilityLabel.shareYourScreen';
    toggledAccessibilityLabel = 'toolbar.accessibilityLabel.stopScreenSharing';
    label = 'toolbar.startScreenSharing';
    icon = IconScreenshare;
    toggledIcon = IconScreenshareToggled;
    toggledLabel = 'toolbar.stopScreenSharing';

    constructor(props: IProps) {
        super(props);
        this.state = { isPopoverVisible: false };
    }


    /**
     * Indicates whether this button is in toggled state or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isToggled() {
        return this.props._screensharing;
    }

    /**
     * Indicates whether this button is in disabled state or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isDisabled() {
        return this.props._lobbyKnocking || !this.props._desktopSharingEnabled;
    }

    /**
     * Handles clicking the button, and toggles the chat.
     *
     * @private
     * @returns {void}
     */
    _handleClick() {
        const { dispatch, _screensharing } = this.props;

        sendAnalytics(createToolbarEvent(
            'toggle.screen.sharing',
            { enable: !_screensharing }));

        dispatch(closeOverflowMenuIfOpen());
        dispatch(startScreenShareFlow(!_screensharing));
    }

    _changeIsPopoverVisible(value: boolean) {
        this.setState({ isPopoverVisible: value });
    }

    render() {
        const needShowPopover = !isScreenSharingSupported();
        if (needShowPopover) {
            return <UnsupportedScreenSharing isVisible={ this.state.isPopoverVisible } isRecording={ false }>
                <div
                    onMouseLeave = {() => this._changeIsPopoverVisible(false)}
                    onClick = {() => this._changeIsPopoverVisible(true)}
                    onMouseEnter = {() => this._changeIsPopoverVisible(true)}>{super.render()}</div>
            </UnsupportedScreenSharing>
        }
        return super.render();
    }
}

/**
 * Function that maps parts of Redux state tree into component props.
 *
 * @param {Object} state - Redux state.
 * @returns {Object}
 */
const mapStateToProps = (state: IReduxState) => {
    // Disable the screen-share button if the video sender limit is reached and there is no video or media share in
    // progress.
    const desktopSharingEnabled
        = JitsiMeetJS.isDesktopSharingEnabled() && !isDesktopShareButtonDisabled(state);
    const { knocking } = state['features/lobby'];

    return {
        _desktopSharingEnabled: desktopSharingEnabled,
        _screensharing: isScreenVideoShared(state),
        _lobbyKnocking: knocking,
        customClass: 'screen-share-button',
        visible: JitsiMeetJS.isDesktopSharingEnabled(),
    };
};

export default translate(connect(mapStateToProps)(ShareDesktopButton));
