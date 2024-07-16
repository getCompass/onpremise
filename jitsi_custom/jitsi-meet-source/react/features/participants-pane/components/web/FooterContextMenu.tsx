import React, {useCallback} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch, useSelector} from 'react-redux';
import {makeStyles} from 'tss-react/mui';

import {IReduxState} from '../../../app/types';
import {
    requestDisableAudioModeration,
    requestDisableVideoModeration,
    requestEnableAudioModeration,
    requestEnableVideoModeration
} from '../../../av-moderation/actions';
import {
    isEnabled as isAvModerationEnabled,
    isSupported as isAvModerationSupported
} from '../../../av-moderation/functions';
import {openDialog} from '../../../base/dialog/actions';
import {
    IconCheckboxOffSmall,
    IconCheckboxOnSmall,
    IconGearSmall,
    IconMicSlashThin,
    IconVideoOff
} from '../../../base/icons/svg';
import {MEDIA_TYPE} from '../../../base/media/constants';
import {getParticipantCount, isEveryoneModerator} from '../../../base/participants/functions';
import ContextMenu from '../../../base/ui/components/web/ContextMenu';
import ContextMenuItemGroup from '../../../base/ui/components/web/ContextMenuItemGroup';
import {isInBreakoutRoom} from '../../../breakout-rooms/functions';
import {openSettingsDialog} from '../../../settings/actions.web';
import {SETTINGS_TABS} from '../../../settings/constants';
import {shouldShowModeratorSettings} from '../../../settings/functions.web';
import MuteEveryonesVideoDialog from '../../../video-menu/components/web/MuteEveryonesVideoDialog';
import Icon from "../../../base/icons/components/Icon";
import {isMobileBrowser} from "../../../base/environment/utils";
import MuteEveryoneDialog from "../../../video-menu/components/web/MuteEveryoneDialog";

const useStyles = makeStyles()(theme => {
    return {
        contextMenu: {
            bottom: 'auto',
            margin: '0',
            right: 0,
            top: '-8px',
            transform: 'translateY(-100%)',
            width: '282px'
        },

        contextItem: {
            padding: '8px 12px 6px 12px'
        },

        contextItemCheckBox: {
            '& div > svg': {
                fill: 'transparent'
            }
        },

        contextItemCheckBoxChecked: {},

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

export const FooterContextMenu = ({isOpen, onDrawerClose, onMouseLeave}: IProps) => {
    const dispatch = useDispatch();
    const isModerationSupported = useSelector((state: IReduxState) => isAvModerationSupported()(state));
    const allModerators = useSelector(isEveryoneModerator);
    const isModeratorSettingsTabEnabled = useSelector(shouldShowModeratorSettings);
    const participantCount = useSelector(getParticipantCount);
    const isAudioModerationEnabled = useSelector(isAvModerationEnabled(MEDIA_TYPE.AUDIO));
    const isVideoModerationEnabled = useSelector(isAvModerationEnabled(MEDIA_TYPE.VIDEO));
    const isBreakoutRoom = useSelector(isInBreakoutRoom);
    const isMobile = isMobileBrowser();

    const {t} = useTranslation();

    const disableAudioModeration = useCallback(() => dispatch(requestDisableAudioModeration()), [dispatch]);

    const disableVideoModeration = useCallback(() => dispatch(requestDisableVideoModeration()), [dispatch]);

    const enableAudioModeration = useCallback(() => dispatch(requestEnableAudioModeration()), [dispatch]);

    const enableVideoModeration = useCallback(() => dispatch(requestEnableVideoModeration()), [dispatch]);

    const {classes, cx} = useStyles();

    const muteAll = useCallback(() => {
        dispatch(openDialog(MuteEveryoneDialog));
    }, []);

    const muteAllVideo = useCallback(
        () => dispatch(openDialog(MuteEveryonesVideoDialog)), [dispatch]);

    const openModeratorSettings = () => dispatch(openSettingsDialog(SETTINGS_TABS.MODERATOR));

    const actions = [
        {
            accessibilityLabel: t('participantsPane.actions.audioModeration'),
            className: isAudioModerationEnabled ? classes.contextItemCheckBox : classes.contextItemCheckBoxChecked,
            id: isAudioModerationEnabled
                ? 'participants-pane-context-menu-stop-audio-moderation'
                : 'participants-pane-context-menu-start-audio-moderation',
            customIcon: <Icon
                size={18}
                src={isAudioModerationEnabled ? IconCheckboxOffSmall : IconCheckboxOnSmall}/>,
            onClick: isAudioModerationEnabled ? disableAudioModeration : enableAudioModeration,
            text: t('participantsPane.actions.audioModeration')
        }, {
            accessibilityLabel: t('participantsPane.actions.videoModeration'),
            className: isVideoModerationEnabled ? classes.contextItemCheckBox : classes.contextItemCheckBoxChecked,
            id: isVideoModerationEnabled
                ? 'participants-pane-context-menu-stop-video-moderation'
                : 'participants-pane-context-menu-start-video-moderation',
            customIcon: <Icon
                size={18}
                src={isVideoModerationEnabled ? IconCheckboxOffSmall : IconCheckboxOnSmall}/>,
            onClick: isVideoModerationEnabled ? disableVideoModeration : enableVideoModeration,
            text: t('participantsPane.actions.videoModeration')
        }
    ];

    return (
        <ContextMenu
            activateFocusTrap={true}
            needCloseOnClick={false}
            className={classes.contextMenu}
            hidden={!isOpen}
            isDrawerOpen={isOpen}
            onDrawerClose={onDrawerClose}
            onMouseLeave={onMouseLeave}>
            <ContextMenuItemGroup
                actions={isMobile ? [
                    {
                        className: classes.contextItem,
                        accessibilityLabel: t('participantsPane.actions.muteAll'),
                        id: 'participants-pane-context-menu-stop-audio',
                        customIcon: <Icon
                            size={isMobile ? 22 : 18}
                            src={IconMicSlashThin}
                            color={'rgba(255, 255, 255, 0.3)'}/>,
                        onClick: muteAll,
                        text: t('participantsPane.actions.muteAll')
                    },
                    {
                        className: classes.contextItem,
                        accessibilityLabel: t('participantsPane.actions.stopEveryonesVideo'),
                        id: 'participants-pane-context-menu-stop-video',
                        customIcon: <Icon
                            size={isMobile ? 22 : 18}
                            src={IconVideoOff}
                            color={'rgba(255, 255, 255, 0.3)'}/>,
                        onClick: muteAllVideo,
                        text: t('participantsPane.actions.stopEveryonesVideo')
                    }
                ] : [{
                    className: classes.contextItem,
                    accessibilityLabel: t('participantsPane.actions.stopEveryonesVideo'),
                    id: 'participants-pane-context-menu-stop-video',
                    customIcon: <Icon
                        size={isMobile ? 22 : 18}
                        src={IconVideoOff}
                        color={'rgba(255, 255, 255, 0.3)'}/>,
                    onClick: muteAllVideo,
                    text: t('participantsPane.actions.stopEveryonesVideo')
                }]}/>
            {!isBreakoutRoom && isModerationSupported && (participantCount === 1 || !allModerators) && (
                <>
                    {!isMobile && (
                        <div className={classes.separateLineContainer}>
                            <div className={classes.separateLine}/>
                        </div>
                    )}
                    <ContextMenuItemGroup actions={actions}
                                          className={cx(classes.actionsContainer, isMobile && 'is-mobile')}>
                        <div className={cx(classes.text, isMobile && 'is-mobile')}>
                            <span>{`${t('participantsPane.actions.allow')}${isMobile ? '' : ':'}`}</span>
                        </div>
                    </ContextMenuItemGroup>
                </>
            )}
        </ContextMenu>
    );
};
