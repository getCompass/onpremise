import React, { useCallback, useEffect, useRef } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { isMobileBrowser } from '../../../base/environment/utils';
import { getLocalParticipant, isLocalParticipantModerator } from '../../../base/participants/functions';
import { setHangupMenuVisible, setOverflowMenuVisible, setToolbarHovered, showToolbox } from '../../actions.web';
import {
    getCompassVisibleButtons,
    getJwtDisabledButtons,
    isButtonEnabled,
    isToolboxVisible
} from '../../functions.web';
import { useCompassToolboxButtons, useKeyboardShortcuts } from '../../hooks.web';
import Separator from './Separator';
import HangupButtonMobile from "../HangupButtonMobile";
import { isPrejoinPageVisible } from "../../../prejoin/functions.any";
import { iAmVisitor } from "../../../visitors/functions";

/**
 * The type of the React {@code Component} props of {@link CompassToolboxMobile}.
 */
interface IProps {

    /**
     * Explicitly passed array with the buttons which this CompassToolboxMobile should display.
     */
    toolbarButtons?: Array<string>;

    isLobby?: boolean;
}

const useStyles = makeStyles()(() => {
    return {
        contextMenu: {
            position: 'relative',
            right: 'auto',
            margin: 0,
            marginBottom: '8px',
            maxHeight: 'calc(100dvh - 100px)',
            minWidth: '240px'
        },

        hangupMenu: {
            width: '265px',
            position: 'relative',
            right: 'auto',
            display: 'flex',
            flexDirection: 'column',
            rowGap: '8px',
            margin: 0,
            padding: '16px',
            marginBottom: '10px',
            backgroundColor: 'rgba(33, 33, 33, 0.9)',
        },

        hangupMenuMobileButtonsContainer: {
            padding: '16px 16px 8px 16px',
            display: 'flex',
            background: 'rgba(28, 28, 28, 1)',
            flexDirection: 'column',
            gap: '16px',
        }
    };
});

/**
 * A component that renders the main toolbar.
 *
 * @param {IProps} props - The props of the component.
 * @returns {ReactElement}
 */
export default function CompassToolboxMobile({
    toolbarButtons,
    isLobby
}: IProps) {
    const { cx } = useStyles();
    const dispatch = useDispatch();
    const _toolboxRef = useRef<HTMLDivElement>(null);

    const isNarrowLayout = useSelector((state: IReduxState) => state['features/base/responsive-ui'].isNarrowLayout);
    const clientWidth = useSelector((state: IReduxState) => state['features/base/responsive-ui'].clientWidth);
    const customToolbarButtons = useSelector(
        (state: IReduxState) => state['features/base/config'].customToolbarButtons);
    const iAmRecorder = useSelector((state: IReduxState) => state['features/base/config'].iAmRecorder);
    const iAmSipGateway = useSelector((state: IReduxState) => state['features/base/config'].iAmSipGateway);
    const shiftUp = useSelector((state: IReduxState) => state['features/toolbox'].shiftUp);
    const overflowMenuVisible = useSelector((state: IReduxState) => state['features/toolbox'].overflowMenuVisible);
    const hangupMenuVisible = useSelector((state: IReduxState) => state['features/toolbox'].hangupMenuVisible);
    const buttonsWithNotifyClick
        = useSelector((state: IReduxState) => state['features/toolbox'].buttonsWithNotifyClick);
    const reduxToolbarButtons = useSelector((state: IReduxState) => state['features/toolbox'].toolbarButtons);
    const toolbarButtonsToUse = toolbarButtons || reduxToolbarButtons;
    const chatOpen = useSelector((state: IReduxState) => state['features/chat'].isOpen);
    const isDialogVisible = useSelector((state: IReduxState) => Boolean(state['features/base/dialog'].component));
    const jwt = useSelector((state: IReduxState) => state['features/base/jwt'].jwt);
    const localParticipant = useSelector(getLocalParticipant);
    const jwtDisabledButtons = useSelector((state: IReduxState) =>
        getJwtDisabledButtons(state, jwt, localParticipant?.features));
    const isPrejoinVisible = useSelector(isPrejoinPageVisible);
    const isCompassToolboxMobileVisible = useSelector(isToolboxVisible);
    const toolbarVisible = isLobby || isCompassToolboxMobileVisible || isPrejoinVisible;
    const compassMainToolbarButtonsThresholds
        = useSelector((state: IReduxState) => state['features/toolbox'].compassMainToolbarButtonsThresholds);
    const allButtons = useCompassToolboxButtons(customToolbarButtons);
    const isVisitor = useSelector((state: IReduxState) => iAmVisitor(state));
    const { joiningInProgress } = useSelector((state: IReduxState) => state['features/prejoin']);
    const forcedBooleanJoiningInProgress: boolean = !!joiningInProgress;

    useKeyboardShortcuts(toolbarButtonsToUse);

    useEffect(() => {
        if (!toolbarVisible) {
            if (document.activeElement instanceof HTMLElement
                && _toolboxRef.current?.contains(document.activeElement)) {
                document.activeElement.blur();
            }
        }
    }, [ toolbarVisible ]);

    /**
     * Sets the visibility of the hangup menu.
     *
     * @param {boolean} visible - Whether or not the hangup menu should be
     * displayed.
     * @private
     * @returns {void}
     */
    const onSetHangupVisible = useCallback((visible: boolean) => {
        dispatch(setHangupMenuVisible(visible));
        dispatch(setToolbarHovered(visible));
    }, [ dispatch ]);

    /**
     * Sets the visibility of the overflow menu.
     *
     * @param {boolean} visible - Whether or not the overflow menu should be
     * displayed.
     * @private
     * @returns {void}
     */
    const onSetOverflowVisible = useCallback((visible: boolean) => {
        dispatch(setOverflowMenuVisible(visible));
        dispatch(setToolbarHovered(visible));
    }, [ dispatch ]);

    useEffect(() => {
        if (hangupMenuVisible && !toolbarVisible) {
            onSetHangupVisible(false);
            dispatch(setToolbarHovered(false));
        }
    }, [ dispatch, hangupMenuVisible, toolbarVisible, onSetHangupVisible ]);

    useEffect(() => {
        if (overflowMenuVisible && isDialogVisible) {
            onSetOverflowVisible(false);
            dispatch(setToolbarHovered(false));
        }
    }, [ dispatch, overflowMenuVisible, isDialogVisible, onSetOverflowVisible ]);

    /**
     * Key handler for overflow/hangup menus.
     *
     * @param {KeyboardEvent} e - Esc key click to close the popup.
     * @returns {void}
     */
    const onEscKey = useCallback((e?: React.KeyboardEvent) => {
        if (e?.key === 'Escape') {
            e?.stopPropagation();
            hangupMenuVisible && dispatch(setHangupMenuVisible(false));
            overflowMenuVisible && dispatch(setOverflowMenuVisible(false));
        }
    }, [ dispatch, hangupMenuVisible, overflowMenuVisible ]);

    /**
     * Dispatches an action signaling the toolbar is not being hovered.
     *
     * @private
     * @returns {void}
     */
    const onMouseOut = useCallback(() => {
        !overflowMenuVisible && dispatch(setToolbarHovered(false));
    }, [ dispatch, overflowMenuVisible ]);

    /**
     * Dispatches an action signaling the toolbar is being hovered.
     *
     * @private
     * @returns {void}
     */
    const onMouseOver = useCallback(() => {
        dispatch(setToolbarHovered(true));
    }, [ dispatch ]);

    /**
     * Toggle the toolbar visibility when tabbing into it.
     *
     * @returns {void}
     */
    const onTabIn = useCallback(() => {
        if (!toolbarVisible) {
            dispatch(showToolbox());
        }
    }, [ toolbarVisible, dispatch ]);

    if (iAmRecorder || iAmSipGateway) {
        return null;
    }

    const isMobile = isMobileBrowser();

    const rootClassNames = `compass-new-toolbox-mobile ${toolbarVisible ? 'visible' : ''} ${
        toolbarButtonsToUse.length ? '' : 'no-buttons'} ${chatOpen ? 'shift-right' : ''} ${isLobby ? 'lobby' : ''}`;

    const containerClassName = `compass-toolbox-content${isMobile || isNarrowLayout ? ' toolbox-content-mobile' : ''}`;

    const { mainMenuButtons } = getCompassVisibleButtons({
        allButtons,
        buttonsWithNotifyClick,
        toolbarButtons: toolbarButtonsToUse,
        clientWidth,
        jwtDisabledButtons,
        mainToolbarButtonsThresholds: compassMainToolbarButtonsThresholds,
        isVisitor,
        joiningInProgress: forcedBooleanJoiningInProgress
    });

    return (
        <div
            className = {cx(rootClassNames, shiftUp && 'shift-up')}
            id = 'new-toolbox'>
            <div className = {containerClassName}>
                <div
                    className = 'compass-toolbox-content-wrapper'
                    onFocus = {onTabIn}
                    {...(isMobile ? {} : {
                        onMouseOut,
                        onMouseOver
                    })}>

                    <div
                        className = {cx('compass-toolbox-content-items', 'compass-toolbox-content-items-mobile')}
                        ref = {_toolboxRef}>
                        {mainMenuButtons.map(({ Content, key, ...rest }) => Content !== Separator && (
                            <Content
                                {...rest}
                                buttonKey = {key}
                                key = {key} />))}
                        {isButtonEnabled('hangup', toolbarButtonsToUse) && (
                            <HangupButtonMobile
                                buttonKey = 'hangup'
                                customClass = 'hangup-button-mobile'
                                key = 'hangup-button'
                                notifyMode = {buttonsWithNotifyClick.get('hangup')}
                                visible = {isButtonEnabled('hangup', toolbarButtonsToUse)}
                                hovered = {false} />
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
