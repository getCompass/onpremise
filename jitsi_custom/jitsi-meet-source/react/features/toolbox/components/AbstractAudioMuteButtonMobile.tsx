import { IReduxState } from '../../app/types';
import { AUDIO_MUTE_BUTTON_ENABLED } from '../../base/flags/constants';
import { getFeatureFlag } from '../../base/flags/functions';
import { MEDIA_TYPE } from '../../base/media/constants';
import { IProps as AbstractButtonProps } from '../../base/toolbox/components/AbstractButton';
import BaseAudioMuteButtonMobile from '../../base/toolbox/components/BaseAudioMuteButtonMobile';
import { isLocalTrackMuted } from '../../base/tracks/functions';
import { muteLocal } from '../../video-menu/actions';
import { isAudioMuteButtonDisabled } from '../functions';
import { iAmVisitor } from "../../visitors/functions";
import { openDialog } from "../../base/dialog/actions";
import { JoinMeetingDialog } from "../../visitors/components/index.web";


/**
 * The type of the React {@code Component} props of {@link AbstractAudioMuteButtonMobile}.
 */
export interface IProps extends AbstractButtonProps {


    /**
     * Whether audio is currently muted or not.
     */
    _audioMuted: boolean;

    /**
     * Whether the button is disabled.
     */
    _disabled: boolean;

    _isVisitor?: boolean;
}

/**
 * Component that renders a toolbar button for toggling audio mute.
 *
 * @augments BaseAudioMuteButtonMobile
 */
export default class AbstractAudioMuteButtonMobile<P extends IProps> extends BaseAudioMuteButtonMobile<P> {
    accessibilityLabel = 'toolbar.accessibilityLabel.mute';
    toggledAccessibilityLabel = 'toolbar.accessibilityLabel.unmute';
    label = 'toolbar.mute';
    toggledLabel = 'toolbar.unmute';

    /**
     * Indicates if audio is currently muted or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isAudioMuted() {
        return this.props._audioMuted;
    }

    /**
     * Changes the muted state.
     *
     * @param {boolean} audioMuted - Whether audio should be muted or not.
     * @protected
     * @returns {void}
     */
    _setAudioMuted(audioMuted: boolean) {
        if (this.props._isVisitor) {

            this.props.dispatch(openDialog(JoinMeetingDialog));
            return;
        }

        this.props.dispatch(muteLocal(audioMuted, MEDIA_TYPE.AUDIO));
    }

    /**
     * Return a boolean value indicating if this button is disabled or not.
     *
     * @returns {boolean}
     */
    _isDisabled() {
        return this.props._disabled;
    }
}

/**
 * Maps (parts of) the redux state to the associated props for the
 * {@code AbstractAudioMuteButtonMobile} component.
 *
 * @param {Object} state - The Redux state.
 * @private
 * @returns {{
 *     _audioMuted: boolean,
 *     _disabled: boolean
 * }}
 */
export function mapStateToProps(state: IReduxState) {
    const _audioMuted = isLocalTrackMuted(state['features/base/tracks'], MEDIA_TYPE.AUDIO);
    const _disabled = isAudioMuteButtonDisabled(state);
    const enabledFlag = getFeatureFlag(state, AUDIO_MUTE_BUTTON_ENABLED, true);
    const _isVisitor = iAmVisitor(state);

    return {
        _audioMuted,
        _disabled,
        _isVisitor,
        visible: enabledFlag
    };
}
