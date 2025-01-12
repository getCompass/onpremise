import { once } from 'lodash-es';
import { connect } from 'react-redux';

import { createToolbarEvent } from '../../analytics/AnalyticsEvents';
import { sendAnalytics } from '../../analytics/functions';
import { leaveConference } from '../../base/conference/actions';
import { translate } from '../../base/i18n/functions';
import { IProps as AbstractButtonProps } from '../../base/toolbox/components/AbstractButton';
import AbstractHangupButton from '../../base/toolbox/components/AbstractHangupButton';

/**
 * The type of the React {@code Component} props of {@link HangupButton}.
 */
interface IProps extends AbstractButtonProps {
    hovered: boolean;
}

/**
 * Component that renders a toolbar button for leaving the current conference.
 *
 * @augments AbstractHangupButton
 */
class HangupButton extends AbstractHangupButton<IProps> {
    _hangup: Function;

    accessibilityLabel = 'toolbar.accessibilityLabel.hangup';
    label = 'toolbar.hangup';
    customClass = 'hangup-menu-button';

    /**
     * Initializes a new HangupButton instance.
     *
     * @param {Props} props - The read-only properties with which the new
     * instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        this._hangup = once(() => {
            sendAnalytics(createToolbarEvent('hangup'));
            this.props.dispatch(leaveConference());
        });
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
     * Helper function to perform the actual hangup action.
     *
     * @override
     * @protected
     * @returns {void}
     */
    _doHangup() {
        this._hangup();
    }
}

export default translate(connect()(HangupButton));
