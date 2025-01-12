import React, { ReactElement, useCallback, useState } from 'react';
import { WithTranslation } from 'react-i18next';
import { connect } from 'react-redux';

import { IReduxState, IStore } from '../../../app/types';
import { isMobileBrowser } from '../../../base/environment/utils';
import { translate } from '../../../base/i18n/functions';
import { IconArrowUp, IconFaceSmile } from '../../../base/icons/svg';
import AbstractButton, { type IProps as AbstractButtonProps } from '../../../base/toolbox/components/AbstractButton';
import ToolboxButtonWithPopup from '../../../base/toolbox/components/web/ToolboxButtonWithPopup';
import { toggleReactionsMenuVisibility } from '../../actions.web';
import { IReactionEmojiProps } from '../../constants';
import { getReactionsQueue } from '../../functions.any';
import { getReactionsMenuVisibility, isReactionsButtonEnabled } from '../../functions.web';
import { IReactionsMenuParent } from '../../types';

import RaiseHandButton from './RaiseHandButton';
import ReactionEmoji from './ReactionEmoji';
import ReactionsMenu from './ReactionsMenu';

interface IProps extends WithTranslation {

    /**
     * Whether a mobile browser is used or not.
     */
    _isMobile: boolean;

    /**
     * Whether the reactions should be displayed on separate button or not.
     */
    _reactionsButtonEnabled: boolean;

    /**
     * The button's key.
     */
    buttonKey?: string;

    /**
     * Redux dispatch function.
     */
    dispatch: IStore['dispatch'];

    /**
     * Whether or not it's narrow mode or mobile browser.
     */
    isNarrow: boolean;

    /**
     * Whether or not the reactions menu is open.
     */
    isOpen: boolean;

    /**
     * Notify mode for `toolbarButtonClicked` event -
     * whether to only notify or to also prevent button click routine.
     */
    notifyMode?: string;

    /**
     * The array of reactions to be displayed.
     */
    reactionsQueue: Array<IReactionEmojiProps>;

    /**
     * Whether or not to show the raise hand button.
     */
    showRaiseHand?: boolean;

    /**
     * Whether or not the knocking lobby.
     */
    _lobbyKnocking: boolean;
}

interface AbstractButtonIProps extends AbstractButtonProps {

    /**
     * Whether or not the knocking lobby.
     */
    _lobbyKnocking: boolean;
}

/**
 * Implementation of a button for reactions.
 */
class ReactionsButtonImpl extends AbstractButton<AbstractButtonIProps> {
    accessibilityLabel = 'toolbar.accessibilityLabel.reactions';
    icon = IconFaceSmile;
    label = 'toolbar.reactions';
    toggledLabel = 'toolbar.reactions';

    /**
     * Indicates whether this button is in disabled state or not.
     *
     * @override
     * @protected
     * @returns {boolean}
     */
    _isDisabled() {
        return this.props._lobbyKnocking;
    }
}

const ReactionsButton = translate(connect(buttonMapStateToProps)(ReactionsButtonImpl));

/**
 * Button used for the reactions menu.
 *
 * @returns {ReactElement}
 */
function ReactionsMenuButton({
    _reactionsButtonEnabled,
    _isMobile,
    buttonKey,
    dispatch,
    isOpen,
    isNarrow,
    notifyMode,
    reactionsQueue,
    showRaiseHand,
    _lobbyKnocking,
    t
}: IProps) {
    const toggleReactionsMenu = useCallback(() => {
        dispatch(toggleReactionsMenuVisibility());
    }, [ dispatch ]);

    const openReactionsMenu = useCallback(() => {
        !isOpen && toggleReactionsMenu();
    }, [ isOpen, toggleReactionsMenu ]);

    const closeReactionsMenu = useCallback(() => {
        isOpen && toggleReactionsMenu();
    }, [ isOpen, toggleReactionsMenu ]);

    const [ isHovered, setIsHovered ] = useState(false);

    /**
     * Dispatches an action signaling the toolbar is not being hovered.
     *
     * @private
     * @returns {void}
     */
    const onMouseOut = useCallback(() => setIsHovered(false), []);

    /**
     * Dispatches an action signaling the toolbar is being hovered.
     *
     * @private
     * @returns {void}
     */
    const onMouseOver = useCallback(() => setIsHovered(true), []);

    if (!showRaiseHand && !_reactionsButtonEnabled) {
        return null;
    }

    const reactionsMenu = (<div className = {`reactions-menu-container ${_isMobile ? 'is-mobile' : ''}`}>
        <ReactionsMenu parent = {IReactionsMenuParent.Button} />
    </div>);

    let content: ReactElement | null = null;

    if (showRaiseHand) {
        content = isNarrow || _lobbyKnocking
            ? (
                <RaiseHandButton
                    buttonKey = {buttonKey}
                    notifyMode = {notifyMode} />)
            : (
                <ToolboxButtonWithPopup
                    ariaLabel = {t('toolbar.accessibilityLabel.reactionsMenu')}
                    icon = {IconArrowUp}
                    iconDisabled = {false}
                    onPopoverClose = {toggleReactionsMenu}
                    onPopoverOpen = {openReactionsMenu}
                    popoverContent = {reactionsMenu}
                    trigger = {'click'}
                    visible = {isOpen}
                    hovered = {isOpen || isHovered}>
                    <RaiseHandButton
                        buttonKey = {buttonKey}
                        notifyMode = {notifyMode} />
                </ToolboxButtonWithPopup>);
    } else {
        content = (
            _lobbyKnocking ? (
                <ReactionsButton
                    buttonKey = {buttonKey}
                    notifyMode = {notifyMode} />
            ) : (
                <ToolboxButtonWithPopup
                    ariaLabel = {t('toolbar.accessibilityLabel.reactionsMenu')}
                    onPopoverClose = {closeReactionsMenu}
                    onPopoverOpen = {openReactionsMenu}
                    popoverContent = {reactionsMenu}
                    trigger = {'click'}
                    visible = {isOpen}
                    hovered = {isOpen || isHovered}>
                    <ReactionsButton
                        buttonKey = {buttonKey}
                        notifyMode = {notifyMode} />
                </ToolboxButtonWithPopup>
            )
        );
    }

    return (
        <div
            className = 'reactions-menu-popup-container'
            {...{
                onMouseOut,
                onMouseOver
            }}>
            {content}
            {reactionsQueue.map(({ reaction, uid }, index) => (<ReactionEmoji
                index = {index}
                key = {uid}
                reaction = {reaction}
                uid = {uid} />))}
        </div>
    );

}

/**
 * Function that maps parts of Redux state tree into component props.
 *
 * @param {Object} state - Redux state.
 * @returns {Object}
 */
function mapStateToProps(state: IReduxState) {
    const { isNarrowLayout } = state['features/base/responsive-ui'];
    const { knocking } = state['features/lobby'];

    return {
        _reactionsButtonEnabled: isReactionsButtonEnabled(state),
        _lobbyKnocking: knocking,
        _isMobile: isMobileBrowser(),
        isOpen: getReactionsMenuVisibility(state),
        isNarrow: isNarrowLayout,
        reactionsQueue: getReactionsQueue(state)
    };
}

/**
 * Function that maps parts of Redux state tree into component props.
 *
 * @param {Object} state - Redux state.
 * @returns {Object}
 */
function buttonMapStateToProps(state: IReduxState) {
    const { knocking } = state['features/lobby'];

    return {
        _lobbyKnocking: knocking,
    };
}

export default translate(connect(mapStateToProps)(ReactionsMenuButton));
