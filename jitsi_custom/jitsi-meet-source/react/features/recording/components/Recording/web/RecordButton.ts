import { connect } from 'react-redux';

import { IReduxState } from '../../../../app/types';
import { openDialog } from '../../../../base/dialog/actions';
import { translate } from '../../../../base/i18n/functions';
import AbstractRecordButton, {
    IProps,
    _mapStateToProps as _abstractMapStateToProps
} from '../AbstractRecordButton';

import StartRecordingDialog from './StartRecordingDialog';
import StopRecordingDialog from './StopRecordingDialog';
import { getLocalParticipant, isParticipantModerator } from "../../../../base/participants/functions";


/**
 * Button for opening a dialog where a recording session can be started.
 */
class RecordingButton extends AbstractRecordButton<IProps> {

    /**
     * Handles clicking / pressing the button.
     *
     * @override
     * @protected
     * @returns {void}
     */
    _onHandleClick() {
        const { _isRecordingRunning, dispatch } = this.props;

        dispatch(openDialog(
            _isRecordingRunning ? StopRecordingDialog : StartRecordingDialog
        ));
    }

    /**
     * Indicates whether this button is in disabled state or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isDisabled() {
        return !this.props._isModerator || (this.props._lobbyKnocking !== undefined && this.props._lobbyKnocking);
    }
}

/**
 * Maps (parts of) the redux state to the associated props for the
 * {@code RecordButton} component.
 *
 * @param {Object} state - The Redux state.
 * @private
 * @returns {{
 *     _fileRecordingsDisabledTooltipKey: ?string,
 *     _isRecordingRunning: boolean,
 *     _disabled: boolean,
 *     visible: boolean
 * }}
 */
export function _mapStateToProps(state: IReduxState) {
    const abstractProps = _abstractMapStateToProps(state);
    const { toolbarButtons } = state['features/toolbox'];
    const visible = Boolean(toolbarButtons?.includes('recording') && abstractProps.visible);
    const { knocking } = state['features/lobby'];
    const participant = getLocalParticipant(state);
    const isModerator = isParticipantModerator(participant);

    return {
        ...abstractProps,
        _isModerator: isModerator,
        _lobbyKnocking: knocking,
        visible
    };
}

export default translate(connect(_mapStateToProps)(RecordingButton));
