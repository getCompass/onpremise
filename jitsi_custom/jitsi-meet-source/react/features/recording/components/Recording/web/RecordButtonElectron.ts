import { connect } from "react-redux";

import { IReduxState } from "../../../../app/types";
import { openDialog } from "../../../../base/dialog/actions";
import { translate } from "../../../../base/i18n/functions";
import AbstractRecordButtonElectron, {
    IProps,
    _mapStateToProps as _abstractMapStateToProps,
} from "../AbstractRecordButtonElectron";

import StartRecordingDialog from "./StartRecordingDialog";
import StopRecordingDialog from "./StopRecordingDialog";

/**
 * Button for opening a dialog where a recording session can be started.
 */
class RecordingButtonElectron extends AbstractRecordButtonElectron<IProps> {
    /**
     * Handles clicking / pressing the button.
     *
     * @override
     * @protected
     * @returns {void}
     */
    _onHandleClick() {
        const { _isRecordingRunning } = this.props;

        _isRecordingRunning
            ? postMessage({ type: "recorder_stop" }, "*")
            : postMessage({ type: "recorder_start", data: { external_save: true } }, "*");
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
    const { toolbarButtons } = state["features/toolbox"];
    const visible = Boolean(toolbarButtons?.includes("recording") && abstractProps.visible);

    return {
        ...abstractProps,
        visible,
    };
}

export default translate(connect(_mapStateToProps)(RecordingButtonElectron));
