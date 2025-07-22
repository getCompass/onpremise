import React, { Component } from 'react';
import { WithTranslation } from 'react-i18next';
import { connect } from 'react-redux';

import { IReduxState } from '../../../app/types';
import { isMobileBrowser } from '../../../base/environment/utils';
import { translate } from '../../../base/i18n/functions';
import { IconArrowUp } from '../../../base/icons/svg';
import { IGUMPendingState } from '../../../base/media/types';
import ToolboxButtonWithIcon from '../../../base/toolbox/components/web/ToolboxButtonWithIcon';
import { getLocalJitsiVideoTrack } from '../../../base/tracks/functions.web';
import { toggleVideoSettings } from '../../../settings/actions';
import VideoSettingsPopup from '../../../settings/components/web/video/VideoSettingsPopup';
import { getVideoSettingsVisibility } from '../../../settings/functions.web';
import { isVideoSettingsButtonDisabled } from '../../functions.web';

import VideoMuteButton from './VideoMuteButton';


interface IProps extends WithTranslation {

    /**
     * The button's key.
     */
    buttonKey?: string;

    /**
     * The gumPending state from redux.
     */
    gumPending: IGUMPendingState;

    /**
     * External handler for click action.
     */
    handleClick: Function;

    /**
     * Indicates whether video permissions have been granted or denied.
     */
    hasPermissions: boolean;

    /**
     * Whether there is a video track or not.
     */
    hasVideoTrack: boolean;

    /**
     * If the button should be disabled.
     */
    isDisabled: boolean;

    /**
     * Defines is popup is open.
     */
    isOpen: boolean;

    /**
     * Notify mode for `toolbarButtonClicked` event -
     * whether to only notify or to also prevent button click routine.
     */
    notifyMode?: string;

    /**
     * Flag controlling the visibility of the button.
     * VideoSettings popup is currently disabled on mobile browsers
     * as mobile devices do not support capture of more than one
     * camera at a time.
     */
    visible: boolean;
}

interface IState {}

/**
 * Button used for video & video settings.
 *
 * @returns {ReactElement}
 */
class VisitorVideoSettingsButton extends Component<IProps, IState> {
    /**
     * Initializes a new {@code VisitorVideoSettingsButton} instance.
     *
     * @inheritdoc
     */
    constructor(props: IProps) {
        super(props);
    }

    /**
     * Implements React's {@link Component#render}.
     *
     * @inheritdoc
     */
    render() {
        const { buttonKey, notifyMode } = this.props;

        return <VideoMuteButton
            buttonKey = {buttonKey}
            notifyMode = {notifyMode} />;
    }
}

/**
 * Function that maps parts of Redux state tree into component props.
 *
 * @param {Object} state - Redux state.
 * @returns {Object}
 */
function mapStateToProps(state: IReduxState) {
    const { permissions = { video: false } } = state['features/base/devices'];
    const { isNarrowLayout } = state['features/base/responsive-ui'];
    const { gumPending } = state['features/base/media'].video;

    return {
        gumPending,
        hasPermissions: permissions.video,
        hasVideoTrack: Boolean(getLocalJitsiVideoTrack(state)),
        isDisabled: isVideoSettingsButtonDisabled(state),
        isOpen: Boolean(getVideoSettingsVisibility(state)),
        visible: !isMobileBrowser() && !isNarrowLayout
    };
}

const mapDispatchToProps = {};

export default translate(connect(
    mapStateToProps,
    mapDispatchToProps
)(VisitorVideoSettingsButton));
