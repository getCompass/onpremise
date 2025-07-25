import React, { useCallback, useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState, IStore } from '../../../app/types';
import { isEnabledFromState, isSupported as isAvModerationSupported } from '../../../av-moderation/functions';
import Avatar from '../../../base/avatar/components/Avatar';
import { isIosMobileBrowser, isMobileBrowser } from '../../../base/environment/utils';
import { MEDIA_TYPE } from '../../../base/media/constants';
import { PARTICIPANT_ROLE } from '../../../base/participants/constants';
import { getLocalParticipant, getParticipantCount, isParticipantModerator } from '../../../base/participants/functions';
import { IParticipant } from '../../../base/participants/types';
import { isParticipantAudioMuted, isParticipantVideoMuted } from '../../../base/tracks/functions.any';
import ContextMenu from '../../../base/ui/components/web/ContextMenu';
import ContextMenuItemGroup from '../../../base/ui/components/web/ContextMenuItemGroup';
import { getBreakoutRooms, getCurrentRoomId, isInBreakoutRoom } from '../../../breakout-rooms/functions';
import { IRoom } from '../../../breakout-rooms/types';
import { displayVerification } from '../../../e2ee/functions';
import { setVolume } from '../../../filmstrip/actions.web';
import { QUICK_ACTION_BUTTON } from '../../../participants-pane/constants';
import { getQuickActionButtonType, isForceMuted } from '../../../participants-pane/functions';
import { requestRemoteControl, stopController } from '../../../remote-control/actions';
import { getParticipantMenuButtonsWithNotifyClick, showOverflowDrawer } from '../../../toolbox/functions.web';
import { NOTIFY_CLICK_MODE } from '../../../toolbox/types';
import { getVisitorsCount, iAmVisitor } from '../../../visitors/functions';
import { PARTICIPANT_MENU_BUTTONS as BUTTONS } from '../../constants';

import AskToUnmuteButton from './AskToUnmuteButton';
import CustomOptionButton from './CustomOptionButton';
import DemoteToVisitorButton from './DemoteToVisitorButton';
import GrantModeratorButton from './GrantModeratorButton';
import KickButton from './KickButton';
import MuteButton from './MuteButton';
import MuteEveryoneElseButton from './MuteEveryoneElseButton';
import MuteEveryoneElsesVideoButton from './MuteEveryoneElsesVideoButton';
import MuteVideoButton from './MuteVideoButton';
import PrivateMessageMenuButton from './PrivateMessageMenuButton';
import RemoteControlButton, { REMOTE_CONTROL_MENU_STATES } from './RemoteControlButton';
import SendToRoomButton from './SendToRoomButton';
import TogglePinToStageButton from './TogglePinToStageButton';
import VerifyParticipantButton from './VerifyParticipantButton';
import VolumeSlider from './VolumeSlider';
import VolumeSliderMobile from "./VolumeSliderMobile";
import { isNeedShowElectronOnlyElements } from "../../../base/environment/utils_web";
import { toState } from "../../../base/redux/functions";

interface IProps {

    /**
     * Class name for the context menu.
     */
    className?: string;

    /**
     * Closes a drawer if open.
     */
    closeDrawer?: () => void;

    /**
     * The participant for which the drawer is open.
     * It contains the displayName & participantID.
     */
    drawerParticipant?: {
        displayName: string;
        participantID: string;
    };

    /**
     * Target elements against which positioning calculations are made.
     */
    offsetTarget?: HTMLElement;

    /**
     * Callback for the mouse entering the component.
     */
    onEnter?: (e?: React.MouseEvent) => void;

    /**
     * Callback for the mouse leaving the component.
     */
    onLeave?: (e?: React.MouseEvent) => void;

    /**
     * Callback for making a selection in the menu.
     */
    onSelect: (value?: boolean | React.MouseEvent) => void;

    /**
     * Participant reference.
     */
    participant: IParticipant;

    /**
     * The current state of the participant's remote control session.
     */
    remoteControlState?: number;

    /**
     * Whether or not the menu is displayed in the thumbnail remote video menu.
     */
    thumbnailMenu?: boolean;
}

const useStyles = makeStyles()(theme => {
    return {
        contextMenu: {
            '& .context-menu-item-group:empty + div': {
                display: "none",
            }
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

        contextItem: {
            padding: '8px 12px 6px 12px',
        },

        contextItemMobile: {

            '& .jitsi-icon': {
                '& svg': {
                    fill: 'rgba(255, 255, 255, 0.3) !important'
                },
            },
        },

        separateLineContainer: {
            paddingTop: '6px',
            paddingBottom: '7px',
            paddingLeft: '24px',
            paddingRight: '24px'
        },

        separateLineContainerPaddingX: {
            paddingLeft: '24px',
            paddingRight: '24px'
        },

        separateLine: {
            backgroundColor: 'rgba(255, 255, 255, 0.05)',
            height: '1px'
        },

        separateLineContainerMobile: {
            padding: '0px 18px',
        },

        separateLineMobile: {
            height: '1px',
            backgroundImage: "url(\"data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' stroke='%23FFFFFF1A' stroke-width='4' stroke-dasharray='1%2c 16' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e\")",
        },

        text: {
            color: theme.palette.text02,
            padding: '10px 16px',
            height: '40px',
            overflow: 'hidden',
            display: 'flex',
            alignItems: 'center',
            boxSizing: 'border-box'
        }
    };
});

const ParticipantContextMenu = ({
    className,
    closeDrawer,
    drawerParticipant,
    offsetTarget,
    onEnter,
    onLeave,
    onSelect,
    participant,
    remoteControlState,
    thumbnailMenu
}: IProps) => {
    const dispatch: IStore['dispatch'] = useDispatch();
    const { t } = useTranslation();
    const { classes: styles, cx } = useStyles();

    const localParticipant = useSelector(getLocalParticipant);
    const _isModerator = Boolean(localParticipant?.role === PARTICIPANT_ROLE.MODERATOR);
    const _isVideoForceMuted = useSelector<IReduxState>(state =>
        isForceMuted(participant, MEDIA_TYPE.VIDEO, state));
    const _isAudioMuted = useSelector((state: IReduxState) => isParticipantAudioMuted(participant, state));
    const _isVideoMuted = useSelector((state: IReduxState) => isParticipantVideoMuted(participant, state));
    const _overflowDrawer: boolean = useSelector(showOverflowDrawer);
    const { remoteVideoMenu = {}, disableRemoteMute, startSilent, customParticipantMenuButtons }
        = useSelector((state: IReduxState) => state['features/base/config']);
    const visitorsMode = useSelector((state: IReduxState) => iAmVisitor(state));
    const visitorsSupported = useSelector((state: IReduxState) => state['features/visitors'].supported);
    const visitorsCount = useSelector(getVisitorsCount);
    const { disableDemote, disableKick, disableGrantModerator, disablePrivateChat } = remoteVideoMenu;
    const { lobbyEnabled } = useSelector((state: IReduxState) => state['features/lobby']);
    const { participantsVolume } = useSelector((state: IReduxState) => state['features/filmstrip']);
    const _volume = (participant?.local ?? true ? undefined
        : participant?.id ? participantsVolume[participant?.id] : undefined) ?? 1;
    const isBreakoutRoom = useSelector(isInBreakoutRoom);
    const isModerationSupported = useSelector((state: IReduxState) => isAvModerationSupported()(state));
    const shouldDisplayVerification = useSelector((state: IReduxState) => displayVerification(state, participant?.id));
    const buttonsWithNotifyClick = useSelector(getParticipantMenuButtonsWithNotifyClick);
    const isMobile = isMobileBrowser();
    const isNeedAddAction = isNeedShowElectronOnlyElements();
    const isEnabledVideoFromState = useSelector((state: IReduxState) => isEnabledFromState(MEDIA_TYPE.VIDEO, state));
    const isEnabledAudioModerationFromState = useSelector((state: IReduxState) => isEnabledFromState(MEDIA_TYPE.AUDIO, state));
    const participantCount = useSelector(getParticipantCount);

    const _currentRoomId = useSelector(getCurrentRoomId);
    const _rooms: IRoom[] = Object.values(useSelector(getBreakoutRooms));

    const _onVolumeChange = useCallback(value => {
        dispatch(setVolume(participant.id, value));
    }, [ setVolume, dispatch ]);

    const _getCurrentParticipantId = useCallback(() => {
            const drawer = _overflowDrawer && !thumbnailMenu;

            return (drawer ? drawerParticipant?.participantID : participant?.id) ?? '';
        }
        , [ thumbnailMenu, _overflowDrawer, drawerParticipant, participant ]);

    const notifyClick = useCallback(
        (buttonKey: string) => {
            const notifyMode = buttonsWithNotifyClick?.get(buttonKey);

            if (!notifyMode) {
                return;
            }

            APP.API.notifyParticipantMenuButtonClicked(
                buttonKey,
                _getCurrentParticipantId(),
                notifyMode === NOTIFY_CLICK_MODE.PREVENT_AND_NOTIFY
            );
        }, [ buttonsWithNotifyClick, _getCurrentParticipantId ]);

    const onBreakoutRoomButtonClick = useCallback(() => {
        onSelect(true);
    }, [ onSelect ]);

    const isClickedFromParticipantPane = useMemo(
        () => !_overflowDrawer && !thumbnailMenu,
        [ _overflowDrawer, thumbnailMenu ]);
    const quickActionButtonType = useSelector((state: IReduxState) =>
        getQuickActionButtonType(participant, _isAudioMuted, _isVideoMuted, state));

    const buttons: JSX.Element[] = [];
    const buttons2: JSX.Element[] = [];
    const buttons3: JSX.Element[] = [];

    const showVolumeSlider = !startSilent
        && !isMobileBrowser()
        && (_overflowDrawer || thumbnailMenu)
        && typeof _volume === 'number'
        && !isNaN(_volume);

    const getButtonProps = useCallback((key: string) => {
        const notifyMode = buttonsWithNotifyClick?.get(key);
        const shouldNotifyClick = typeof notifyMode !== 'undefined';

        const notifyClickCallback = (key: string) => {
            notifyClick(key)
            onLeave?.();
        }

        return {
            key,
            notifyMode,
            notifyClick: shouldNotifyClick ? () => notifyClickCallback(key) : onLeave,
            participantID: _getCurrentParticipantId()
        };
    }, [ _getCurrentParticipantId, buttonsWithNotifyClick, notifyClick ]);

    if (_isModerator && isNeedAddAction) {
        if (isModerationSupported) {
            if (_isAudioMuted && !participant.isSilent
                && !(isClickedFromParticipantPane && quickActionButtonType === QUICK_ACTION_BUTTON.ASK_TO_UNMUTE)) {
                buttons.push(<AskToUnmuteButton
                    {...getButtonProps(BUTTONS.ASK_UNMUTE)}
                    className = {styles.contextItem}
                    buttonType = {MEDIA_TYPE.AUDIO} />
                );
            }
            if (_isVideoForceMuted
                && !(isClickedFromParticipantPane && quickActionButtonType === QUICK_ACTION_BUTTON.ALLOW_VIDEO)) {
                buttons.push(<AskToUnmuteButton
                    {...getButtonProps(BUTTONS.ALLOW_VIDEO)}
                    className = {styles.contextItem}
                    buttonType = {MEDIA_TYPE.VIDEO} />
                );
            }
        }

        if (!disableRemoteMute && !participant.isSilent) {
            if (!(isClickedFromParticipantPane && quickActionButtonType === QUICK_ACTION_BUTTON.MUTE)) {
                buttons.push(<MuteButton {...getButtonProps(BUTTONS.MUTE)} className = {styles.contextItem} />);
            }

            if (participantCount > 2) {
                buttons.push(<MuteEveryoneElseButton
                    isEnabledAudioModerationFromState = {isEnabledAudioModerationFromState} {...getButtonProps(BUTTONS.MUTE_OTHERS)}
                    className = {styles.contextItem} />);
            }

            if (!(isClickedFromParticipantPane && quickActionButtonType === QUICK_ACTION_BUTTON.STOP_VIDEO)) {
                buttons.push(<MuteVideoButton {...getButtonProps(BUTTONS.MUTE_VIDEO)} className = {styles.contextItem}
                                              text = {t('participantsPane.actions.stopVideoFull')} />);
            }

            if (participantCount > 2) {
                buttons.push(<MuteEveryoneElsesVideoButton
                    isEnabledVideoFromState = {isEnabledVideoFromState} {...getButtonProps(BUTTONS.MUTE_OTHERS_VIDEO)}
                    className = {styles.contextItem} />);
            }
        }

        if (!disableGrantModerator && !isBreakoutRoom) {
            buttons2.push(<GrantModeratorButton {...getButtonProps(BUTTONS.GRANT_MODERATOR)}
                                                className = {styles.contextItem} />);
        }

        if (!disableDemote && visitorsSupported && _isModerator && !lobbyEnabled && visitorsCount > 0) {
            buttons2.push(<DemoteToVisitorButton {...getButtonProps(BUTTONS.DEMOTE)}
                                                 className = {styles.contextItem} />);
        }

        if (shouldDisplayVerification) {
            buttons2.push(<VerifyParticipantButton {...getButtonProps(BUTTONS.VERIFY)}
                                                   className = {styles.contextItem} />);
        }
    }

    if (!disablePrivateChat && !visitorsMode) {
        buttons2.push(<PrivateMessageMenuButton {...getButtonProps(BUTTONS.PRIVATE_MESSAGE)}
                                                className = {styles.contextItem} />);
    }

    buttons2.push(<TogglePinToStageButton {...getButtonProps(BUTTONS.PIN_TO_STAGE)}
                                          className = {cx(styles.contextItem, isMobile && styles.contextItemMobile)} />);

    if (_isModerator && isNeedAddAction) {
        if (!disableKick) {
            if (isClickedFromParticipantPane) {
                buttons2.push(<KickButton {...getButtonProps(BUTTONS.KICK)} className = {styles.contextItem} />);
            } else {
                buttons3.push(<KickButton {...getButtonProps(BUTTONS.KICK)} className = {styles.contextItem} />);
            }
        }
    }

    if (thumbnailMenu && remoteControlState) {
        const onRemoteControlToggle = useCallback(() => {
            if (remoteControlState === REMOTE_CONTROL_MENU_STATES.STARTED) {
                dispatch(stopController(true));
            } else if (remoteControlState === REMOTE_CONTROL_MENU_STATES.NOT_STARTED) {
                dispatch(requestRemoteControl(_getCurrentParticipantId()));
            }
        }, [ dispatch, remoteControlState, stopController, requestRemoteControl ]);

        buttons2.push(<RemoteControlButton
            {...getButtonProps(BUTTONS.REMOTE_CONTROL)}
            className = {styles.contextItem}
            onClick = {onRemoteControlToggle}
            remoteControlState = {remoteControlState} />
        );
    }

    if (customParticipantMenuButtons) {
        customParticipantMenuButtons.forEach(
            ({ icon, id, text }) => {
                buttons2.push(
                    <CustomOptionButton
                        icon = {icon}
                        key = {id}
                        // eslint-disable-next-line react/jsx-no-bind
                        onClick = {() => notifyClick(id)}
                        className = {styles.contextItem}
                        text = {text} />
                );
            }
        );
    }

    const breakoutRoomsButtons: any = [];

    if (!thumbnailMenu && _isModerator && isNeedAddAction) {
        _rooms.forEach(room => {
            if (room.id !== _currentRoomId) {
                breakoutRoomsButtons.push(
                    <SendToRoomButton
                        {...getButtonProps(BUTTONS.SEND_PARTICIPANT_TO_ROOM)}
                        key = {room.id}
                        onClick = {onBreakoutRoomButtonClick}
                        className = {styles.contextItem}
                        room = {room} />
                );
            }
        });
    }

    return (
        <ContextMenu
            className = {`${styles.contextMenu} ${className}`}
            entity = {participant}
            hidden = {thumbnailMenu ? false : undefined}
            inDrawer = {thumbnailMenu && _overflowDrawer}
            isDrawerOpen = {Boolean(drawerParticipant)}
            offsetTarget = {offsetTarget}
            width = {282}
            onClick = {onSelect}
            onDrawerClose = {thumbnailMenu ? onSelect : closeDrawer}
            onMouseEnter = {onEnter}
            onMouseLeave = {onLeave}>
            {isMobile && (
                <div className = {styles.participantInfoContainer}>
                    <div className = {styles.participantInfoAvatar}>
                        <Avatar
                            id = "participantInfoAvatar"
                            participantId = {participant.id}
                            size = {44} />
                    </div>
                    <div className = {styles.participantInfoNameRoleContainer}>
                        <div
                            className = {styles.participantInfoName}>{participant.name}</div>
                        <div
                            className = {styles.participantInfoRole}>{isParticipantModerator(participant) ? t('videothumbnail.moderator') : t('videothumbnail.member')}</div>
                    </div>
                </div>
            )}
            {!thumbnailMenu && _overflowDrawer && !isMobile && drawerParticipant && <ContextMenuItemGroup
                actions = {[ {
                    accessibilityLabel: drawerParticipant.displayName,
                    customIcon: <Avatar
                        participantId = {drawerParticipant.participantID}
                        size = {20} />,
                    text: drawerParticipant.displayName
                } ]} />}
            {
                showVolumeSlider && (
                    isMobile ? (
                        <>
                            <ContextMenuItemGroup>
                                <VolumeSliderMobile
                                    initialValue = {_volume}
                                    key = 'volume-slider'
                                    onChange = {_onVolumeChange} />
                            </ContextMenuItemGroup>
                            <div className = {styles.separateLineContainerMobile}>
                                <div className = {styles.separateLineMobile} />
                            </div>
                        </>
                    ) : (
                        <>
                            <ContextMenuItemGroup>
                                <VolumeSlider
                                    initialValue = {_volume}
                                    key = 'volume-slider'
                                    onChange = {_onVolumeChange} />
                            </ContextMenuItemGroup>
                            <div
                                className = {cx(styles.separateLineContainer, !isClickedFromParticipantPane && styles.separateLineContainerPaddingX)}>
                                <div className = {styles.separateLine} />
                            </div>
                        </>
                    )
                )}
            {isMobile ? (
                <ContextMenuItemGroup>
                    {buttons.length > 0 && buttons}
                    {buttons2}
                    {buttons3.length > 0 && buttons3}
                </ContextMenuItemGroup>
            ) : (
                <>
                    {buttons.length > 0 && (
                        <>
                            <ContextMenuItemGroup>
                                {buttons}
                            </ContextMenuItemGroup>
                            <div
                                className = {cx(styles.separateLineContainer, !isClickedFromParticipantPane && styles.separateLineContainerPaddingX)}>
                                <div className = {styles.separateLine} />
                            </div>
                        </>
                    )}
                    <ContextMenuItemGroup>
                        {buttons2}
                    </ContextMenuItemGroup>
                    {buttons3.length > 0 && (
                        <>
                            <div
                                className = {cx(styles.separateLineContainer, !isClickedFromParticipantPane && styles.separateLineContainerPaddingX)}>
                                <div className = {styles.separateLine} />
                            </div>
                            <ContextMenuItemGroup>
                                {buttons3}
                            </ContextMenuItemGroup>
                        </>
                    )}
                </>
            )}
            {breakoutRoomsButtons.length > 0 && (
                <ContextMenuItemGroup>
                    <div className = {styles.text}>
                        {t('breakoutRooms.actions.sendToBreakoutRoom')}
                    </div>
                    {breakoutRoomsButtons}
                </ContextMenuItemGroup>
            )}
        </ContextMenu>
    );
};

export default ParticipantContextMenu;
