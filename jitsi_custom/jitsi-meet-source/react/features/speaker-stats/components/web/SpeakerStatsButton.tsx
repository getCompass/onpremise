import {connect} from 'react-redux';

import {createToolbarEvent} from '../../../analytics/AnalyticsEvents';
import {sendAnalytics} from '../../../analytics/functions';
import {IReduxState} from '../../../app/types';
import {openDialog} from '../../../base/dialog/actions';
import {translate} from '../../../base/i18n/functions';
import {isSpeakerStatsDisabled} from '../../functions';
import AbstractSpeakerStatsButton from '../AbstractSpeakerStatsButton';

import SpeakerStats from './SpeakerStats';

/**
 * Implementation of a button for opening speaker stats dialog.
 */
class SpeakerStatsButton extends AbstractSpeakerStatsButton {

    /**
     * Handles clicking / pressing the button, and opens the appropriate dialog.
     *
     * @protected
     * @returns {void}
     */
    _handleClick() {
        const {dispatch} = this.props;

        sendAnalytics(createToolbarEvent('speaker.stats'));
        dispatch(openDialog(SpeakerStats));
    }

    /**
     * Indicates whether this button is in disabled state or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isDisabled() {
        return this.props._lobbyKnocking !== undefined && this.props._lobbyKnocking;
    }
}

/**
 * Function that maps parts of Redux state tree into component props.
 *
 * @param {Object} state - Redux state.
 * @returns {Object}
 */
const mapStateToProps = (state: IReduxState) => {
    const {knocking} = state['features/lobby'];

    return {
        visible: !isSpeakerStatsDisabled(state),
        _lobbyKnocking: knocking,
    };
};

export default translate(connect(mapStateToProps)(SpeakerStatsButton));
