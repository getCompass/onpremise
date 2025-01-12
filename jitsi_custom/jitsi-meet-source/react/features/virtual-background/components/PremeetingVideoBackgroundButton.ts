import { connect } from 'react-redux';

import { IReduxState } from '../../app/types';
import { translate } from '../../base/i18n/functions';
import { IconImage } from '../../base/icons/svg';
import AbstractButton, { IProps as AbstractButtonProps } from '../../base/toolbox/components/AbstractButton';
import { isScreenVideoShared } from '../../screen-share/functions';
import { openSettingsDialog } from '../../settings/actions';
import { SETTINGS_TABS } from '../../settings/constants';
import { checkBlurSupport, checkVirtualBackgroundEnabled } from '../functions';
import { openDialog } from "../../base/dialog/actions";
import CompassVirtualBackgroundDialog from "./web/CompassVirtualBackgroundDialog";

/**
 * The type of the React {@code Component} props of {@link PremeetingVideoBackgroundButton}.
 */
interface IProps extends AbstractButtonProps {

    /**
     * True if the video background is blurred or false if it is not.
     */
    _isBackgroundEnabled: boolean;
}

/**
 * An abstract implementation of a button that toggles the video background dialog.
 */
class PremeetingVideoBackgroundButton extends AbstractButton<IProps> {
    accessibilityLabel = 'toolbar.accessibilityLabel.selectBackground';
    icon = IconImage;
    label = 'toolbar.selectBackground';

    /**
     * Handles clicking / pressing the button, and toggles the virtual background dialog
     * state accordingly.
     *
     * @protected
     * @returns {void}
     */
    _handleClick() {
        const { dispatch } = this.props;

        dispatch(openDialog(CompassVirtualBackgroundDialog));
    }

    /**
     * Returns {@code boolean} value indicating if the background effect is
     * enabled or not.
     *
     * @protected
     * @returns {boolean}
     */
    _isToggled() {
        return true;
    }
}

/**
 * Maps (parts of) the redux state to the associated props for the
 * {@code PremeetingVideoBackgroundButton} component.
 *
 * @param {Object} state - The Redux state.
 * @private
 * @returns {{
 *     _isBackgroundEnabled: boolean
 * }}
 */
function _mapStateToProps(state: IReduxState) {

    return {
        _isBackgroundEnabled: Boolean(state['features/virtual-background'].backgroundEffectEnabled),
        visible: checkBlurSupport()
        && !isScreenVideoShared(state)
        && checkVirtualBackgroundEnabled(state)
    };
}

export default translate(connect(_mapStateToProps)(PremeetingVideoBackgroundButton));
