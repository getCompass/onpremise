import { batch, connect } from 'react-redux';
import { IReduxState } from '../../app/types';
import { translate } from '../../base/i18n/functions';
import { IconTileViewMobile } from '../../base/icons/svg';
import AbstractButton, { IProps as AbstractButtonProps } from '../../base/toolbox/components/AbstractButton';
import { setOverflowMenuVisible } from '../../toolbox/actions';
import { setTileView } from '../actions';

/**
 * The type of the React {@code Component} props of {@link TileViewButtonMobile}.
 */
interface IProps extends AbstractButtonProps {}

/**
 * Component that renders a toolbar button for toggling the tile layout view.
 *
 * @augments AbstractButton
 */
class TileViewButtonMobile<P extends IProps> extends AbstractButton<P> {
    accessibilityLabel = 'toolbar.accessibilityLabel.enterTileView';
    toggledAccessibilityLabel = 'toolbar.accessibilityLabel.exitTileView';
    label = 'toolbar.enterTileView';
    toggledLabel = 'toolbar.exitTileView';
    icon = IconTileViewMobile;

    /**
     * Handles clicking / pressing the button.
     *
     * @override
     * @protected
     * @returns {void}
     */
    _handleClick() {
        const { dispatch } = this.props;

        batch(() => {
            dispatch(setTileView(true));
            navigator.product !== 'ReactNative' && dispatch(setOverflowMenuVisible(false));
        });

    }
}

/**
 * Maps (parts of) the redux state to the associated props for the
 * {@code TileViewButtonMobile} component.
 *
 * @param {Object} state - The Redux state.
 * @param {Object} ownProps - The properties explicitly passed to the component instance.
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState, ownProps: any) {
    return {};
}

export default translate(connect(_mapStateToProps)(TileViewButtonMobile));
