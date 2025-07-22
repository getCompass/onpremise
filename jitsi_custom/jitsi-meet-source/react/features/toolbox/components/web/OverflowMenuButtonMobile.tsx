import React, { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { createToolbarEvent } from '../../../analytics/AnalyticsEvents';
import { sendAnalytics } from '../../../analytics/functions';
import ContextMenu from '../../../base/ui/components/web/ContextMenu';
import ContextMenuItemGroup from '../../../base/ui/components/web/ContextMenuItemGroup';
import { setGifMenuVisibility } from '../../../gifs/actions';
import { isGifsMenuOpen } from '../../../gifs/functions.web';
import { DRAWER_MAX_HEIGHT } from '../../constants';
import { showOverflowDrawer } from '../../functions.web';

import Drawer from './Drawer';
import JitsiPortal from './JitsiPortal';
import { setHangupMenuVisible, setOverflowMenuVisible, setToolbarHovered } from "../../actions.web";
import { IReduxState } from "../../../app/types";
import OverflowToggleButtonMobile from "./OverflowToggleButtonMobile";
import ChatCounterMobile from "../../../chat/components/web/ChatCounterMobile";

/**
 * The type of the React {@code Component} props of {@link OverflowMenuButtonMobile}.
 */
interface IProps {

    /**
     * ID of the menu that is controlled by this button.
     */
    ariaControls: string;

    /**
     * Information about the buttons that need to be rendered in the overflow menu.
     */
    buttons: Object[];

    headerContent?: React.ReactNode;
}

const useStyles = makeStyles<{ overflowDrawer: boolean; reactionsMenuHeight: number; }>()(
    (_theme, { reactionsMenuHeight, overflowDrawer }) => {
        return {
            mt12: {
                marginTop: "12px",
            },
            overflowMenuDrawer: {
                overflowY: 'scroll',
                maxHeight: `calc(${DRAWER_MAX_HEIGHT})`
            },
            contextMenu: {
                position: 'relative' as const,
                right: 'auto',
                margin: 0,
                marginBottom: '10px',
                maxHeight: overflowDrawer ? undefined : [
                    'calc(100vh - 100px)',
                    'calc(100dvh - 100px)',
                ],
                minWidth: '298px',
                overflow: 'hidden'
            },
            separateLineContainer: {
                paddingTop: '6px',
                paddingBottom: '7px',
                paddingLeft: '24px',
                paddingRight: '24px'
            },

            separateLine: {
                backgroundColor: 'rgba(255, 255, 255, 0.05)',
                height: '1px'
            },
            content: {
                position: 'relative',
                maxHeight: [
                    overflowDrawer
                        ? `calc(100% - ${reactionsMenuHeight}px - 16px)` : `calc(100vh - 100px - ${reactionsMenuHeight}px)`,
                    overflowDrawer
                        ? `calc(100% - ${reactionsMenuHeight}px - 16px)` : `calc(100dvh - 100px - ${reactionsMenuHeight}px)`,
                ],
                overflowY: 'auto'
            },
            footerSeparatorContainer: {
                padding: '0px 18px',
            },
            footerSeparator: {
                borderTop: '1px dashed rgba(255, 255, 255, 0.1)',
            },
            footer: {
                position: 'absolute',
                bottom: 0,
                left: 0,
                right: 0,

                '&.is-mobile': {
                    position: 'relative',
                }
            },
            reactionsPadding: {
                height: `${reactionsMenuHeight}px`
            }
        };
    });

const OverflowMenuButtonMobile = ({
    buttons,
    headerContent
}: IProps) => {
    const overflowDrawer = useSelector(showOverflowDrawer);
    const isGiphyVisible = useSelector(isGifsMenuOpen);
    const dispatch = useDispatch();
    const isOpen = useSelector((state: IReduxState) => state['features/toolbox'].overflowMenuVisible);
    const hangupMenuVisible = useSelector((state: IReduxState) => state['features/toolbox'].hangupMenuVisible);

    /**
     * Sets the visibility of the overflow menu.
     *
     * @param {boolean} visible - Whether or not the overflow menu should be
     * displayed.
     * @private
     * @returns {void}
     */
    const onVisibilityChange = useCallback((visible: boolean) => {
        dispatch(setOverflowMenuVisible(visible));
        dispatch(setToolbarHovered(visible));
    }, [ dispatch ]);

    const onCloseDialog = useCallback(() => {
        onVisibilityChange(false);
        if (isGiphyVisible && !overflowDrawer) {
            dispatch(setGifMenuVisibility(false));
        }
    }, [ onVisibilityChange, setGifMenuVisibility, isGiphyVisible, overflowDrawer, dispatch ]);

    const onEscClick = useCallback((event: React.KeyboardEvent) => {
        if (event.key === 'Escape' && isOpen) {
            event.preventDefault();
            event.stopPropagation();
            onCloseDialog();
        }
    }, [ onCloseDialog ]);

    const toggleDialogVisibility = useCallback(() => {
        sendAnalytics(createToolbarEvent('overflow'));

        onVisibilityChange(!isOpen);
    }, [ isOpen, onVisibilityChange ]);

    const toolbarAccLabel = 'toolbar.accessibilityLabel.moreActionsMenu';
    const { t } = useTranslation();
    let reactionsMenuHeight = 0;
    const { classes, cx } = useStyles({
        reactionsMenuHeight,
        overflowDrawer
    });

    /**
     * Key handler for overflow/hangup menus.
     *
     * @param {KeyboardEvent} e - Esc key click to close the popup.
     * @returns {void}
     */
    const onToolboxEscKey = useCallback((e?: React.KeyboardEvent) => {
        if (e?.key === 'Escape') {
            e?.stopPropagation();
            hangupMenuVisible && dispatch(setHangupMenuVisible(false));
            isOpen && dispatch(setOverflowMenuVisible(false));
        }
    }, [ dispatch, hangupMenuVisible, isOpen ]);

    const groupsJSX = (
        <>
            {headerContent}
            <ContextMenuItemGroup className = {classes.mt12} key = {`group-0`}>
                {buttons.map((buttonGroup: any) => (
                    buttonGroup
                        .map(({ key, Content, ...rest }: { Content: React.ElementType; key: string; }) => {
                            const props: {
                                buttonKey?: string;
                                contextMenu?: boolean;
                                showLabel?: boolean;
                            } = { ...rest };

                            if (key !== 'reactions') {
                                props.buttonKey = key;
                                props.contextMenu = true;
                                props.showLabel = true;
                            }

                            return (<Content
                                {...props}
                                key = {key} />);
                        })
                ))}
            </ContextMenuItemGroup>
        </>
    );

    const overflowMenu = groupsJSX && (
        <ContextMenu
            accessibilityLabel = {t(toolbarAccLabel)}
            className = {classes.contextMenu}
            hidden = {false}
            id = 'overflow-context-menu'
            inDrawer = {overflowDrawer}
            onKeyDown = {onToolboxEscKey}>
            <div className = {classes.content}>
                {groupsJSX}
            </div>
        </ContextMenu>);

    return (
        <div className = 'toolbox-button-wth-dialog context-menu'>
            <>
                <div style = {{
                    position: "relative",
                }}>
                    <OverflowToggleButtonMobile
                        handleClick = {toggleDialogVisibility}
                        isOpen = {isOpen}
                        onKeyDown = {onEscClick}
                        customClass = 'mobile-header-button' />
                    <ChatCounterMobile customClass = "overflow-button" />
                </div>
                <JitsiPortal>
                    <Drawer
                        isOpen = {isOpen}
                        onClose = {onCloseDialog}
                        position = "top">
                        <>
                            <div className = {classes.overflowMenuDrawer}>
                                {overflowMenu}
                            </div>
                        </>
                    </Drawer>
                </JitsiPortal>
            </>
        </div>
    );
};

export default OverflowMenuButtonMobile;
