import { connect } from 'react-redux';

import { translate } from '../../../base/i18n/functions';
import { IconHangup, IconHangupToggled } from '../../../base/icons/svg';
import AbstractButton, { IProps as AbstractButtonProps } from '../../../base/toolbox/components/AbstractButton';

/**
 * The type of the React {@code Component} props of {@link HangupToggleButton}.
 */
interface IProps extends AbstractButtonProps {

    /**
     * Whether the more options menu is open.
     */
    isOpen: boolean;

    /**
     * External handler for key down action.
     */
    onKeyDown: Function;

    hovered: boolean;
}

/**
 * Implementation of a button for toggling the hangup menu.
 */
class HangupToggleButton extends AbstractButton<IProps> {
    accessibilityLabel = 'toolbar.accessibilityLabel.hangup';
    icon = IconHangup;
    label = 'toolbar.hangup';
    toggledIcon = IconHangupToggled;
    hoveredIcon = IconHangupToggled;
    toggledLabel = 'toolbar.hangup';

    /**
     * Indicates whether this button is in toggled state or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isToggled() {
        return this.props.isOpen;
    }

    /**
     * Indicates whether this button is in hovered state or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isHovered() {
        return this.props.hovered;
    }

    /**
     * Indicates whether a key was pressed.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _onKeyDown() {
        this.props.onKeyDown();
    }
}

export default connect()(translate(HangupToggleButton));
