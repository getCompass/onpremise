import React, { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { batch, connect, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState, IStore } from '../../../app/types';
import { isMobileBrowser } from '../../../base/environment/utils';
import { IconDotsHorizontal } from '../../../base/icons/svg';
import { getLocalParticipant, getParticipantCount } from '../../../base/participants/functions';
import Popover from '../../../base/popover/components/Popover.web';
import { setParticipantContextMenuOpen } from '../../../base/responsive-ui/actions';
import { getHideSelfView } from '../../../base/settings/functions.web';
import { getLocalVideoTrack } from '../../../base/tracks/functions';
import Button from '../../../base/ui/components/web/Button';
import ContextMenu from '../../../base/ui/components/web/ContextMenu';
import ContextMenuItemGroup from '../../../base/ui/components/web/ContextMenuItemGroup';
import ConnectionIndicatorContent from '../../../connection-indicator/components/web/ConnectionIndicatorContent';
import { THUMBNAIL_TYPE } from '../../../filmstrip/constants';
import { isStageFilmstripAvailable } from '../../../filmstrip/functions.web';
import { getParticipantMenuButtonsWithNotifyClick } from '../../../toolbox/functions.web';
import { NOTIFY_CLICK_MODE } from '../../../toolbox/types';
import { renderConnectionStatus } from '../../actions.web';
import { PARTICIPANT_MENU_BUTTONS as BUTTONS } from '../../constants';

import ConnectionStatusButton from './ConnectionStatusButton';
import DemoteToVisitorButton from './DemoteToVisitorButton';
import FlipLocalVideoButton from './FlipLocalVideoButton';
import TogglePinToStageButton from './TogglePinToStageButton';
import { BUTTON_TYPES } from "../../../base/ui/constants.any";
import Avatar from "../../../base/avatar/components/Avatar";
import { PARTICIPANT_ROLE } from "../../../base/participants/constants";

/**
 * The type of the React {@code Component} props of
 * {@link LocalVideoMenuTriggerButton}.
 */
interface IProps {

    /**
     * The id of the local participant.
     */
    _localParticipantId: string;

    /**
     * The name of the local participant.
     */
    _localParticipantDisplayName: string;

    /**
     * The role of the local participant.
     */
    _localParticipantRole: string;

    /**
     * The position relative to the trigger the local video menu should display
     * from.
     */
    _menuPosition: string;

    /**
     * Whether to display the Popover as a drawer.
     */
    _overflowDrawer: boolean;

    /**
     * Whether to render the connection info pane.
     */
    _showConnectionInfo: boolean;

    /**
     * Shows/hides the local switch to visitor button.
     */
    _showDemote: boolean;

    /**
     * Whether to render the hide self view button.
     */
    _showHideSelfViewButton: boolean;

    /**
     * Shows/hides the local video flip button.
     */
    _showLocalVideoFlipButton: boolean;

    /**
     * Whether to render the pin to stage button.
     */
    _showPinToStage: boolean;

    /**
     * Whether or not the button should be visible.
     */
    buttonVisible: boolean;

    /**
     * The redux dispatch function.
     */
    dispatch: IStore['dispatch'];

    /**
     * Hides popover.
     */
    hidePopover?: Function;

    /**
     * Whether the popover is visible or not.
     */
    popoverVisible?: boolean;

    /**
     * Shows popover.
     */
    showPopover?: Function;

    /**
     * The type of the thumbnail.
     */
    thumbnailType: string;
}

const useStyles = makeStyles()(() => {
    return {
        triggerButton: {
            padding: '4px !important',
            borderRadius: '4px',

            '& svg': {
                width: '16px',
                height: '16px'
            }
        },

        contextMenu: {
            width: '327px',
            position: 'relative',
            marginTop: 0,
            right: 'auto',
        },

        participantInfoContainer: {
            display: 'flex',
            background: 'rgba(25, 25, 25, 1)',
            padding: '14px 16px',
            gap: '8px',
        },
        participantInfoAvatar: {},
        participantInfoNameRoleContainer: {
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'center',
        },
        participantInfoName: {
            fontFamily: 'Lato Bold',
            fontWeight: 'normal' as const,
            fontSize: '16px',
            lineHeight: '20px',
            color: 'rgba(255, 255, 255, 0.85)',
        },
        participantInfoRole: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '14px',
            lineHeight: '20px',
            color: 'rgba(255, 255, 255, 0.3)',
        },

        flipText: {
            marginLeft: '36px',

            '&.is-mobile': {
                marginLeft: 0
            }
        }
    };
});

const LocalVideoMenuTriggerButton = ({
    _localParticipantId,
    _localParticipantDisplayName,
    _localParticipantRole,
    _menuPosition,
    _overflowDrawer,
    _showConnectionInfo,
    _showDemote,
    _showHideSelfViewButton,
    _showLocalVideoFlipButton,
    _showPinToStage,
    buttonVisible,
    dispatch,
    hidePopover,
    showPopover,
    popoverVisible
}: IProps) => {
    const { classes, cx } = useStyles();
    const { t } = useTranslation();
    const buttonsWithNotifyClick = useSelector(getParticipantMenuButtonsWithNotifyClick);
    const visitorsSupported = useSelector((state: IReduxState) => state['features/visitors'].supported);
    const isMobile = isMobileBrowser();

    const notifyClick = useCallback(
        (buttonKey: string) => {
            const notifyMode = buttonsWithNotifyClick?.get(buttonKey);

            if (!notifyMode) {
                return;
            }

            APP.API.notifyParticipantMenuButtonClicked(
                buttonKey,
                _localParticipantId,
                notifyMode === NOTIFY_CLICK_MODE.PREVENT_AND_NOTIFY
            );
        }, [ buttonsWithNotifyClick ]);

    const _onPopoverOpen = useCallback(() => {
        showPopover?.();
        dispatch(setParticipantContextMenuOpen(true));
    }, []);

    const _onPopoverClose = useCallback(() => {
        hidePopover?.();
        batch(() => {
            dispatch(setParticipantContextMenuOpen(false));
            dispatch(renderConnectionStatus(false));
        });
    }, []);

    const content = _showConnectionInfo
        ? <ConnectionIndicatorContent participantId = {_localParticipantId} />
        : (
            <ContextMenu
                className = {classes.contextMenu}
                hidden = {false}
                inDrawer = {_overflowDrawer}>
                {isMobile && (
                    <div className = {classes.participantInfoContainer}>
                        <div className = {classes.participantInfoAvatar}>
                            <Avatar
                                id = "participantInfoAvatar"
                                participantId = {_localParticipantId}
                                size = {44} />
                        </div>
                        <div className = {classes.participantInfoNameRoleContainer}>
                            <div
                                className = {classes.participantInfoName}>{`${_localParticipantDisplayName} (${t('me')})`}</div>
                            <div
                                className = {classes.participantInfoRole}>{_localParticipantRole === PARTICIPANT_ROLE.MODERATOR ? t('videothumbnail.moderator') : t('videothumbnail.member')}</div>
                        </div>
                    </div>
                )}
                <ContextMenuItemGroup>
                    {_showLocalVideoFlipButton
                        && <FlipLocalVideoButton
                            className = {cx(_overflowDrawer ? classes.flipText : '', isMobile && 'is-mobile')}
                            // eslint-disable-next-line react/jsx-no-bind
                            notifyClick = {() => notifyClick(BUTTONS.FLIP_LOCAL_VIDEO)}
                            notifyMode = {buttonsWithNotifyClick?.get(BUTTONS.FLIP_LOCAL_VIDEO)}
                            onClick = {hidePopover} />
                    }
                    {
                        _showPinToStage && <TogglePinToStageButton
                            textClassName = {cx(_overflowDrawer ? classes.flipText : '', isMobile && 'is-mobile')}
                            noIcon = {false}
                            // eslint-disable-next-line react/jsx-no-bind
                            notifyClick = {() => notifyClick(BUTTONS.PIN_TO_STAGE)}
                            notifyMode = {buttonsWithNotifyClick?.get(BUTTONS.PIN_TO_STAGE)}
                            onClick = {hidePopover}
                            participantID = {_localParticipantId} />
                    }
                    {
                        _showDemote && visitorsSupported && <DemoteToVisitorButton
                            className = {cx(_overflowDrawer ? classes.flipText : '', isMobile && 'is-mobile')}
                            noIcon = {true}
                            // eslint-disable-next-line react/jsx-no-bind
                            notifyClick = {() => notifyClick(BUTTONS.DEMOTE)}
                            notifyMode = {buttonsWithNotifyClick?.get(BUTTONS.DEMOTE)}
                            onClick = {hidePopover}
                            participantID = {_localParticipantId} />
                    }
                    {
                        isMobileBrowser() && <ConnectionStatusButton
                            // eslint-disable-next-line react/jsx-no-bind
                            notifyClick = {() => notifyClick(BUTTONS.CONN_STATUS)}
                            notifyMode = {buttonsWithNotifyClick?.get(BUTTONS.CONN_STATUS)}
                            participantID = {_localParticipantId} />
                    }
                </ContextMenuItemGroup>
            </ContextMenu>
        );

    return (
        isMobileBrowser() || _showLocalVideoFlipButton || _showHideSelfViewButton
            ? <Popover
                content = {content}
                headingLabel = {t('dialog.localUserControls')}
                id = 'local-video-menu-trigger'
                onPopoverClose = {_onPopoverClose}
                onPopoverOpen = {_onPopoverOpen}
                position = {_menuPosition}
                trigger = { 'click' }
                visible = {Boolean(popoverVisible)}>
                {buttonVisible && !isMobileBrowser() && (
                    <Button
                        accessibilityLabel = {t('dialog.localUserControls')}
                        className = {classes.triggerButton}
                        type = {BUTTON_TYPES.TRIGGER}
                        icon = {IconDotsHorizontal}
                        size = 'small' />
                )}
            </Popover>
            : null
    );
};

/**
 * Maps (parts of) the Redux state to the associated {@code LocalVideoMenuTriggerButton}'s props.
 *
 * @param {Object} state - The Redux state.
 * @param {Object} ownProps - The own props of the component.
 * @private
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState, ownProps: Partial<IProps>) {
    const { thumbnailType } = ownProps;
    const localParticipant = getLocalParticipant(state);
    const { disableLocalVideoFlip, disableSelfDemote, disableSelfViewSettings } = state['features/base/config'];
    const videoTrack = getLocalVideoTrack(state['features/base/tracks']);
    const { overflowDrawer } = state['features/toolbox'];
    const { showConnectionInfo } = state['features/base/connection'];
    const showHideSelfViewButton = !disableSelfViewSettings && !getHideSelfView(state);

    let _menuPosition;

    switch (thumbnailType) {
    case THUMBNAIL_TYPE.TILE:
        _menuPosition = 'left-end';
        break;
    case THUMBNAIL_TYPE.VERTICAL:
        _menuPosition = 'left-end';
        break;
    case THUMBNAIL_TYPE.HORIZONTAL:
        _menuPosition = 'bottom-start';
        break;
    default:
        _menuPosition = 'auto';
    }

    return {
        _menuPosition,
        _showDemote: !disableSelfDemote && getParticipantCount(state) > 1,
        _showLocalVideoFlipButton: !disableLocalVideoFlip && videoTrack?.videoType !== 'desktop',
        _showHideSelfViewButton: showHideSelfViewButton,
        _overflowDrawer: overflowDrawer && isMobileBrowser(),
        _localParticipantId: localParticipant?.id ?? '',
        _localParticipantDisplayName: localParticipant?.name ?? '',
        _localParticipantRole: localParticipant?.role ?? '',
        _showConnectionInfo: Boolean(showConnectionInfo),
        _showPinToStage: isStageFilmstripAvailable(state)
    };
}

export default connect(_mapStateToProps)(LocalVideoMenuTriggerButton);
