import { connect } from 'react-redux';
import { withStyles } from 'tss-react/mui';

import { ACTION_SHORTCUT_TRIGGERED, createShortcutEvent, VIDEO_MUTE } from '../../../analytics/AnalyticsEvents';
import { sendAnalytics } from '../../../analytics/functions';
import { IReduxState } from '../../../app/types';
import { translate } from '../../../base/i18n/functions';
import { IGUMPendingState } from '../../../base/media/types';
import AbstractButton from '../../../base/toolbox/components/AbstractButton';
import { registerShortcut, unregisterShortcut } from '../../../keyboard-shortcuts/actions';
import AbstractVideoMuteButtonMobile, {
    IProps as AbstractVideoMuteButtonMobileProps,
    mapStateToProps as abstractMapStateToProps
} from '../AbstractVideoMuteButtonMobile';

const styles = () => {
    return {
        pendingContainer: {
            position: 'absolute' as const,
            bottom: '3px',
            right: '3px'
        }
    };
};

/**
 * The type of the React {@code Component} props of {@link VideoMuteButtonMobile}.
 */
export interface IProps extends AbstractVideoMuteButtonMobileProps {

    /**
     * The gumPending state from redux.
     */
    _gumPending: IGUMPendingState;

    /**
     * An object containing the CSS classes.
     */
    classes?: Partial<Record<keyof ReturnType<typeof styles>, string>>;
}

/**
 * Component that renders a toolbar button for toggling video mute.
 *
 * @augments AbstractVideoMuteButtonMobile
 */
class VideoMuteButtonMobile extends AbstractVideoMuteButtonMobile<IProps> {

    /**
     * Initializes a new {@code VideoMuteButtonMobile} instance.
     *
     * @param {IProps} props - The read-only React {@code Component} props with
     * which the new instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        // Bind event handlers so they are only bound once per instance.
        this._onKeyboardShortcut = this._onKeyboardShortcut.bind(this);
    }

    /**
     * Registers the keyboard shortcut that toggles the video muting.
     *
     * @inheritdoc
     * @returns {void}
     */
    componentDidMount() {
        this.props.dispatch(registerShortcut({
            character: 'V',
            helpDescription: 'keyboardShortcuts.videoMute',
            handler: this._onKeyboardShortcut
        }));

        this.props.dispatch(registerShortcut({
            character: 'М',
            helpDescription: 'keyboardShortcuts.videoMute',
            handler: this._onKeyboardShortcut
        }));
    }

    /**
     * Unregisters the keyboard shortcut that toggles the video muting.
     *
     * @inheritdoc
     * @returns {void}
     */
    componentWillUnmount() {
        this.props.dispatch(unregisterShortcut('V'));
        this.props.dispatch(unregisterShortcut('М'));
    }

    /**
     * Gets the current accessibility label, taking the toggled and GUM pending state into account. If no toggled label
     * is provided, the regular accessibility label will also be used in the toggled state.
     *
     * The accessibility label is not visible in the UI, it is meant to be used by assistive technologies, mainly screen
     * readers.
     *
     * @private
     * @returns {string}
     */
    _getAccessibilityLabel() {
        return super._getAccessibilityLabel();
    }

    /**
     * Gets the current label, taking the toggled and GUM pending state into account. If no
     * toggled label is provided, the regular label will also be used in the toggled state.
     *
     * @private
     * @returns {string}
     */
    _getLabel() {
        return super._getLabel();
    }

    /**
     * Indicates if video is currently muted or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isVideoMuted() {
        if (this.props._gumPending === IGUMPendingState.PENDING_UNMUTE) {
            return false;
        }

        return super._isVideoMuted();
    }

    /**
     * Creates an analytics keyboard shortcut event and dispatches an action to
     * toggle the video muting.
     *
     * @private
     * @returns {void}
     */
    _onKeyboardShortcut() {
        // Ignore keyboard shortcuts if the video button is disabled.
        if (this._isDisabled()) {
            return;
        }

        sendAnalytics(
            createShortcutEvent(
                VIDEO_MUTE,
                ACTION_SHORTCUT_TRIGGERED,
                { enable: !this._isVideoMuted() }));

        AbstractButton.prototype._onClick.call(this);
    }
}

/**
 * Maps (parts of) the redux state to the associated props for the
 * {@code VideoMuteButtonMobile} component.
 *
 * @param {Object} state - The Redux state.
 * @private
 * @returns {{
 *     _videoMuted: boolean
 * }}
 */
function _mapStateToProps(state: IReduxState) {
    const { gumPending } = state['features/base/media'].video;

    return {
        ...abstractMapStateToProps(state),
        _gumPending: gumPending
    };
}

export default withStyles(translate(connect(_mapStateToProps)(VideoMuteButtonMobile)), styles);
