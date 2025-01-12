import React, { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import {
    requestDisableAudioModeration,
    requestDisableScreenshareModeration,
    requestDisableVideoModeration,
    requestEnableAudioModeration,
    requestEnableScreenshareModeration,
    requestEnableVideoModeration
} from '../../../av-moderation/actions';
import {
    isEnabled as isAvModerationEnabled,
    isSupported as isAvModerationSupported
} from '../../../av-moderation/functions';
import { openDialog } from '../../../base/dialog/actions';
import { IconCheckboxOffSmall, IconCheckboxOnSmall, IconMicSlashThin, IconVideoOff } from '../../../base/icons/svg';
import { MEDIA_TYPE } from '../../../base/media/constants';
import { getParticipantCount, getRaiseHandsQueue, isEveryoneModerator } from '../../../base/participants/functions';
import ContextMenu from '../../../base/ui/components/web/ContextMenu';
import ContextMenuItemGroup from '../../../base/ui/components/web/ContextMenuItemGroup';
import { isInBreakoutRoom } from '../../../breakout-rooms/functions';
import MuteEveryonesVideoDialog from '../../../video-menu/components/web/MuteEveryonesVideoDialog';
import Icon from "../../../base/icons/components/Icon";
import { isMobileBrowser } from "../../../base/environment/utils";
import MuteEveryoneDialog from "../../../video-menu/components/web/MuteEveryoneDialog";

const useStyles = makeStyles()(theme => {
    return {
        contextMenu: {
            bottom: 'auto',
            margin: '0',
            right: 0,
            top: '-8px',
            transform: 'translateY(-100%)',
        },

        contextMenuItemContainer: {
            padding: '0 !important',
        },

        contextItem: {
            padding: '8px 24px 6px 24px',

            '&:hover': {
                borderRadius: '0 !important',
            }
        },

        contextItemCheckBox: {
            padding: '8px 24px 6px 24px',

            '&:hover': {
                borderRadius: '0 !important',
            },

            '& div > svg': {
                fill: 'transparent'
            }
        },

        contextItemCheckBoxChecked: {
            padding: '8px 24px 6px 24px',

            '&:hover': {
                borderRadius: '0 !important',
            },
        },

        separateLineContainer: {
            padding: '6px 24px 7px 24px',
        },

        separateLine: {
            backgroundColor: 'rgba(255, 255, 255, 0.05)',
            height: '1px'
        },

        actionsContainer: {
            '&.is-mobile': {
                backgroundColor: 'rgba(25, 25, 25, 1)',
            }
        },

        text: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.75)',
            padding: '8px 24px 4px 24px',
            overflow: 'hidden',
            display: 'flex',
            alignItems: 'center',
            boxSizing: 'border-box',

            '&.is-mobile': {
                fontSize: '17px',
                padding: '18px 22px 4px 16px',
                color: 'rgba(255, 255, 255, 0.5)',
            },
        },

        indentedLabel: {
            '& > span': {
                marginLeft: '36px'
            }
        }
    };
});

interface IProps {

    /**
     * Whether the menu is open.
     */
    isOpen: boolean;

    /**
     * Drawer close callback.
     */
    onDrawerClose: (e?: React.MouseEvent) => void;

    /**
     * Callback for the mouse leaving this item.
     */
    onMouseLeave?: (e?: React.MouseEvent) => void;
}

export const FooterContextMenu = ({ isOpen, onDrawerClose, onMouseLeave }: IProps) => {
    const dispatch = useDispatch();
    const isModerationSupported = useSelector((state: IReduxState) => isAvModerationSupported()(state));
    const raisedHandsQueue = useSelector(getRaiseHandsQueue);
    const allModerators = useSelector(isEveryoneModerator);
    const participantCount = useSelector(getParticipantCount);
    const isAudioModerationEnabled = useSelector(isAvModerationEnabled(MEDIA_TYPE.AUDIO));
    const isVideoModerationEnabled = useSelector(isAvModerationEnabled(MEDIA_TYPE.VIDEO));
    const isScreenshareModerationEnabled = useSelector(isAvModerationEnabled(MEDIA_TYPE.SCREENSHARE));
    const isBreakoutRoom = useSelector(isInBreakoutRoom);
    const isMobile = isMobileBrowser();

    const { t } = useTranslation();

    const disableAudioModeration = useCallback(() => dispatch(requestDisableAudioModeration()), [ dispatch ]);
    const enableAudioModeration = useCallback(() => dispatch(requestEnableAudioModeration()), [ dispatch ]);

    const disableVideoModeration = useCallback(() => dispatch(requestDisableVideoModeration()), [ dispatch ]);
    const enableVideoModeration = useCallback(() => dispatch(requestEnableVideoModeration()), [ dispatch ]);

    const disableScreenshareModeration = useCallback(() => dispatch(requestDisableScreenshareModeration()), [ dispatch ]);
    const enableScreenshareModeration = useCallback(() => dispatch(requestEnableScreenshareModeration()), [ dispatch ]);

    const { classes, cx } = useStyles();

    const muteAll = useCallback(() => {
        dispatch(openDialog(MuteEveryoneDialog));
    }, []);

    const muteAllVideo = useCallback(
        () => dispatch(openDialog(MuteEveryonesVideoDialog)), [ dispatch ]);

    const actions = [
        {
            containerClassName: classes.contextMenuItemContainer,
            className: isAudioModerationEnabled ? classes.contextItemCheckBox : classes.contextItemCheckBoxChecked,
            accessibilityLabel: t('moderatorSettings.participantsCanUnmuteAudio'),
            id: isAudioModerationEnabled
                ? 'participants-pane-context-menu-stop-audio-moderation'
                : 'participants-pane-context-menu-start-audio-moderation',
            customIcon: <Icon
                size = {18}
                src = {isAudioModerationEnabled ? IconCheckboxOffSmall : IconCheckboxOnSmall} />,
            onClick: isAudioModerationEnabled ? disableAudioModeration : enableAudioModeration,
            text: t('moderatorSettings.participantsCanUnmuteAudio')
        },
        {
            containerClassName: classes.contextMenuItemContainer,
            className: isVideoModerationEnabled ? classes.contextItemCheckBox : classes.contextItemCheckBoxChecked,
            accessibilityLabel: t('moderatorSettings.participantsCanUnmuteVideo'),
            id: isVideoModerationEnabled
                ? 'participants-pane-context-menu-stop-video-moderation'
                : 'participants-pane-context-menu-start-video-moderation',
            customIcon: <Icon
                size = {18}
                src = {isVideoModerationEnabled ? IconCheckboxOffSmall : IconCheckboxOnSmall} />,
            onClick: isVideoModerationEnabled ? disableVideoModeration : enableVideoModeration,
            text: t('moderatorSettings.participantsCanUnmuteVideo')
        },
        {
            containerClassName: classes.contextMenuItemContainer,
            className: isScreenshareModerationEnabled ? classes.contextItemCheckBox : classes.contextItemCheckBoxChecked,
            accessibilityLabel: t('moderatorSettings.participantsCanScreenshare'),
            id: isScreenshareModerationEnabled
                ? 'moderator-settings-context-menu-stop-screenshare-moderation'
                : 'moderator-settings-context-menu-start-screenshare-moderation',
            customIcon: <Icon
                size = {18}
                src = {isScreenshareModerationEnabled ? IconCheckboxOffSmall : IconCheckboxOnSmall} />,
            onClick: isScreenshareModerationEnabled ? disableScreenshareModeration : enableScreenshareModeration,
            text: t('moderatorSettings.participantsCanScreenshare')
        }
    ];

    return (
        <ContextMenu
            activateFocusTrap = {true}
            needCloseOnClick = {false}
            className = {classes.contextMenu}
            hidden = {!isOpen}
            isDrawerOpen = {isOpen}
            onDrawerClose = {onDrawerClose}
            onMouseLeave = {onMouseLeave}>
            <ContextMenuItemGroup
                actions = {isMobile ? [
                    {
                        className: classes.contextItem,
                        accessibilityLabel: t('participantsPane.actions.muteAll'),
                        id: 'participants-pane-context-menu-stop-audio',
                        customIcon: <Icon
                            size = {isMobile ? 22 : 18}
                            src = {IconMicSlashThin}
                            color = {'rgba(255, 255, 255, 0.3)'} />,
                        onClick: muteAll,
                        text: t('participantsPane.actions.muteAll')
                    },
                    {
                        className: classes.contextItem,
                        accessibilityLabel: t('participantsPane.actions.stopEveryonesVideo'),
                        id: 'participants-pane-context-menu-stop-video',
                        customIcon: <Icon
                            size = {isMobile ? 22 : 18}
                            src = {IconVideoOff}
                            color = {'rgba(255, 255, 255, 0.3)'} />,
                        onClick: muteAllVideo,
                        text: t('participantsPane.actions.stopEveryonesVideo')
                    }
                ] : [
                    {
                        containerClassName: classes.contextMenuItemContainer,
                        className: classes.contextItem,
                        accessibilityLabel: t('moderatorSettings.muteAllVideo'),
                        id: 'participants-pane-context-menu-stop-video',
                        customIcon: <Icon
                            size = {isMobile ? 22 : 18}
                            src = {IconVideoOff}
                            color = {'rgba(255, 255, 255, 0.3)'} />,
                        onClick: muteAllVideo,
                        text: t('moderatorSettings.muteAllVideo')
                    }
                ]} />
            {!isBreakoutRoom && isModerationSupported && (participantCount === 1 || !allModerators) && (
                <>
                    {!isMobile && (
                        <div className = {classes.separateLineContainer}>
                            <div className = {classes.separateLine} />
                        </div>
                    )}
                    <ContextMenuItemGroup actions = {actions} className = {cx(classes.actionsContainer, isMobile && 'is-mobile')}>
                    </ContextMenuItemGroup>
                </>
            )}
        </ContextMenu>
    );
};
