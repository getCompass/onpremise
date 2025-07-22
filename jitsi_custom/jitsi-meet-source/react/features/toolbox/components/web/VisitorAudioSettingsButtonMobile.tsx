import React, { Component } from 'react';
import { WithTranslation } from 'react-i18next';
import { connect } from 'react-redux';

import { IReduxState } from '../../../app/types';
import { isMobileBrowser } from '../../../base/environment/utils';
import { translate } from '../../../base/i18n/functions';
import { IGUMPendingState } from '../../../base/media/types';
import { toggleAudioSettings } from '../../../settings/actions.web';
import { getAudioSettingsVisibility } from '../../../settings/functions.web';
import { isAudioSettingsButtonDisabled } from '../../functions.web';
import AudioMuteButtonMobile from "./AudioMuteButtonMobile";

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
     * Indicates whether audio permissions have been granted or denied.
     */
    hasPermissions: boolean;

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
     * Click handler for the small icon. Opens audio options.
     */
    onAudioOptionsClick: Function;

    /**
     * Flag controlling the visibility of the button.
     * AudioSettings popup is disabled on mobile browsers.
     */
    visible: boolean;
}

interface IState {

    /**
     * Whether or not is being hovered.
     */
    isHovered: boolean;
}

/**
 * Button used for audio & audio settings.
 *
 * @returns {ReactElement}
 */
class VisitorAudioSettingsButtonMobile extends Component<IProps, IState> {
    /**
     * Initializes a new {@code VisitorAudioSettingsButtonMobile} instance.
     *
     * @inheritdoc
     */
    constructor(props: IProps) {
        super(props);

        this.state = {
            isHovered: false
        };

        this._onEscClick = this._onEscClick.bind(this);
        this._onClick = this._onClick.bind(this);
        this._onMouseEnter = this._onMouseEnter.bind(this);
        this._onMouseLeave = this._onMouseLeave.bind(this);
    }

    /**
     * Click handler for the more actions entries.
     *
     * @param {KeyboardEvent} event - Esc key click to close the popup.
     * @returns {void}
     */
    _onEscClick(event: React.KeyboardEvent) {
        if (event.key === 'Escape' && this.props.isOpen) {
            event.preventDefault();
            event.stopPropagation();
            this._onClick();
        }
    }

    /**
     * Click handler for the more actions entries.
     *
     * @param {MouseEvent} e - Mouse event.
     * @returns {void}
     */
    _onClick(e?: React.MouseEvent) {
        const { onAudioOptionsClick, isOpen } = this.props;

        if (isOpen) {
            e?.stopPropagation();
        }
        onAudioOptionsClick();
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
     * Implements React's {@link Component#render}.
     *
     * @inheritdoc
     */
    render() {
        const { buttonKey, notifyMode } = this.props;

        return <AudioMuteButtonMobile
            buttonKey = {buttonKey}
            notifyMode = {notifyMode} />
    }
}

/**
 * Function that maps parts of Redux state tree into component props.
 *
 * @param {Object} state - Redux state.
 * @returns {Object}
 */
function mapStateToProps(state: IReduxState) {
    const { permissions = { audio: false } } = state['features/base/devices'];
    const { isNarrowLayout } = state['features/base/responsive-ui'];
    const { gumPending } = state['features/base/media'].audio;

    return {
        gumPending,
        hasPermissions: permissions.audio,
        isDisabled: Boolean(isAudioSettingsButtonDisabled(state)),
        isOpen: Boolean(getAudioSettingsVisibility(state)),
        visible: !isMobileBrowser() && !isNarrowLayout
    };
}

const mapDispatchToProps = {
    onAudioOptionsClick: toggleAudioSettings
};

export default translate(connect(
    mapStateToProps,
    mapDispatchToProps
)(VisitorAudioSettingsButtonMobile));
