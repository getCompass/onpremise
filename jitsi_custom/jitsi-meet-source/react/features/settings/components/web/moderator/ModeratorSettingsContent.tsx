/* eslint-disable react/no-multi-comp */
import React, { useCallback, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { connect, useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState, IStore } from '../../../../app/types';
import ContextMenu from '../../../../base/ui/components/web/ContextMenu';
import ContextMenuItemGroup from '../../../../base/ui/components/web/ContextMenuItemGroup';
import { toggleNoiseSuppression } from '../../../../noise-suppression/actions';
import QualityButtons from "../../../../quality-control/components/web/QualityButtons";
import Icon from "../../../../base/icons/components/Icon";
import {
    IconCheckboxOffSmall,
    IconCheckboxOnSmall,
    IconFilledSquare,
    IconMicSlash,
    IconQuestionCircle,
    IconRecord,
    IconVideoOff
} from "../../../../base/icons/svg";
import { openDialog } from "../../../../base/dialog/actions";
import MuteEveryoneDialog from "../../../../video-menu/components/web/MuteEveryoneDialog";
import MuteEveryonesVideoDialog from "../../../../video-menu/components/web/MuteEveryonesVideoDialog";
import { browser } from "../../../../base/lib-jitsi-meet";
import { isRecordingRunning, supportsLocalRecording } from "../../../../recording/functions";
import { isRecorderTranscriptionsRunning } from "../../../../transcribing/functions";
import { isEnabled as isAvModerationEnabled, } from "../../../../av-moderation/functions";
import { MEDIA_TYPE } from "../../../../base/media/constants";
import {
    requestDisableAudioModeration,
    requestDisableScreenshareModeration,
    requestDisableVideoModeration,
    requestEnableAudioModeration,
    requestEnableScreenshareModeration,
    requestEnableVideoModeration
} from "../../../../av-moderation/actions";
import { startLocalVideoRecording, stopLocalVideoRecording } from "../../../../recording/actions.any";
import { isScreenSharingSupported } from "../../../../desktop-picker/functions";
import UnsupportedScreenSharing from "../UnsupportedScreenSharing";
import RecordingReminderDialog from '../../../../recording/components/web/RecordingReminderDialog';

export interface IProps {
    isAlreadyRecording: boolean;
}

const useStyles = makeStyles()(theme => {
    return {
        contextMenu: {
            padding: '20px 0px 12px 0px',
            position: 'relative',
            right: 'auto',
            margin: 0,
            marginBottom: '16px',
            maxHeight: 'calc(100dvh - 100px)',
            overflow: 'auto',
        },

        contextMenuItemGroup: {
            padding: '0px 24px !important',
        },

        contextMenuTitle: {
            fontFamily: 'Lato Bold',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '20px',
            color: 'rgba(255, 255, 255, 0.75)',
        },

        qualityButtonsContainer: {
            marginTop: '17px',
        },

        contextMenuItemContainer: {
            padding: '0 !important',
        },

        contextItem: {
            padding: '8px 24px 6px 24px',

            '&:hover': {
                borderRadius: '0 !important',

                '.context-text-item': {
                    color: 'rgba(255, 255, 255, 1)',
                },
            },
            '&[aria-disabled="true"]': {
                '.context-text-item': {
                    color: 'rgba(255, 255, 255, 0.2);',
                }
            }
        },

        contextItemCheckBox: {
            padding: '8px 24px 6px 24px',

            '&:hover': {
                borderRadius: '0 !important',

                '.context-text-item': {
                    color: 'rgba(255, 255, 255, 1)',
                },
            },

            '& div > svg': {
                fill: 'transparent'
            }
        },

        contextItemCheckBoxChecked: {
            padding: '8px 24px 6px 24px',

            '&:hover': {
                borderRadius: '0 !important',

                '.context-text-item': {
                    color: 'rgba(255, 255, 255, 1)',
                },
            },
        },

        header: {
            '&:hover': {
                backgroundColor: 'initial',
                cursor: 'initial'
            }
        },

        list: {
            margin: 0,
            padding: 0,
            listStyleType: 'none'
        },

        checkboxContainer: {
            padding: '10px 16px'
        },

        separateLineContainer: {
            padding: '6px 24px 7px 24px',
        },

        separateLine: {
            backgroundColor: 'rgba(255, 255, 255, 0.05)',
            height: '1px'
        },
        contextMenuFaqItem: {
            marginLeft: '-8px',
            width: '26px',
            height: '22px',
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            pointerEvents: 'all',

            '.jitsi-icon svg': {
                fill: 'rgba(255, 255, 255, 0.3) !important',
            },

            '&:hover': {
                '.jitsi-icon svg': {
                    fill: 'rgba(255, 255, 255, 0.75) !important',
                },
            }
        }
    };
});

const ModeratorSettingsContent = (props: IProps) => {
    const { isAlreadyRecording } = props
    const { classes } = useStyles();
    const { t } = useTranslation();
    const dispatch = useDispatch();
    const isAudioModerationEnabled = useSelector(isAvModerationEnabled(MEDIA_TYPE.AUDIO));
    const isVideoModerationEnabled = useSelector(isAvModerationEnabled(MEDIA_TYPE.VIDEO));
    const isScreenshareModerationEnabled = useSelector(isAvModerationEnabled(MEDIA_TYPE.SCREENSHARE));
    const { localRecording } = useSelector((state: IReduxState) => state['features/base/config']);
    const localRecordingEnabled = !localRecording?.disable && supportsLocalRecording();
    const [isPopoverVisible, changeIsPopoverVisible] = useState(false);

    const startRecording = useCallback(() => {
        if (browser.isElectron()) {
            postMessage({
            type: "recorder_start",
            data: { external_save: true }
            }, "*");

            dispatch(openDialog(RecordingReminderDialog));

            return;
        }

        dispatch(startLocalVideoRecording(false));
    }, [])

    const stopRecording = useCallback(() => {
        browser.isElectron() ? postMessage({ type: "recorder_stop" }, "*") : dispatch(stopLocalVideoRecording());
    }, [])

    const muteAllAudio = useCallback(() => {
        dispatch(openDialog(MuteEveryoneDialog));
    }, []);

    const muteAllVideo = useCallback(
        () => dispatch(openDialog(MuteEveryonesVideoDialog)), [ dispatch ]);

    const disableAudioModeration = useCallback((e?: React.MouseEvent<any>) => {
        e?.stopPropagation();
        dispatch(requestDisableAudioModeration())
    }, [ dispatch ]);
    const enableAudioModeration = useCallback((e?: React.MouseEvent<any>) => {
        e?.stopPropagation();
        dispatch(requestEnableAudioModeration())
    }, [ dispatch ]);

    const disableVideoModeration = useCallback((e?: React.MouseEvent<any>) => {
        e?.stopPropagation();
        dispatch(requestDisableVideoModeration())
    }, [ dispatch ]);
    const enableVideoModeration = useCallback((e?: React.MouseEvent<any>) => {
        e?.stopPropagation();
        dispatch(requestEnableVideoModeration())
    }, [ dispatch ]);

    const disableScreenshareModeration = useCallback((e?: React.MouseEvent<any>) => {
        e?.stopPropagation();
        dispatch(requestDisableScreenshareModeration())
    }, [ dispatch ]);
    const enableScreenshareModeration = useCallback((e?: React.MouseEvent<any>) => {
        e?.stopPropagation();
        dispatch(requestEnableScreenshareModeration())
    }, [ dispatch ]);
    const isScreenRecordingNotAvailable = !isScreenSharingSupported();
    let text = isAlreadyRecording ? t('moderatorSettings.stopRecording') : t('moderatorSettings.startRecording');
    text = isScreenRecordingNotAvailable ? t('dialog.screenRecordingNotAvailableButton') : text;

    const buttonActions = localRecordingEnabled ? [
        {
            containerClassName: classes.contextMenuItemContainer,
            className: classes.contextItem,
            accessibilityLabel: t('moderatorSettings.startRecording'),
            id: 'moderator-settings-context-menu-recording',
            customIcon: <Icon
                size={18}
                src={isAlreadyRecording ? IconFilledSquare : IconRecord}
                color={!isScreenRecordingNotAvailable ? 'rgba(255, 79, 71, 1)' : 'rgba(255, 79, 71, 0.3)'}/>,
            onClick: isAlreadyRecording ? stopRecording : startRecording,
            disabled: isScreenRecordingNotAvailable,
            text: text,
            children: isScreenRecordingNotAvailable ?
                <UnsupportedScreenSharing isRecording={true} isVisible={isPopoverVisible}>
                    {<div
                        className={classes.contextMenuFaqItem}
                        onMouseLeave = {() => changeIsPopoverVisible(false)}
                        onClick = {() => changeIsPopoverVisible(true)}
                        onMouseEnter = {() => changeIsPopoverVisible(true)}
                        >
                        <Icon
                            src={IconQuestionCircle} size={16} color={'rgba(255, 255, 255, 0.3)'}
                        />
                    </div>
                }</UnsupportedScreenSharing> : undefined,
        },
        {
            containerClassName: classes.contextMenuItemContainer,
            className: classes.contextItem,
            accessibilityLabel: t('moderatorSettings.muteAllAudio'),
            id: 'moderator-settings-context-mute-all-audio',
            customIcon: <Icon
                size = {18}
                src = {IconMicSlash}
                color = {'rgba(255, 255, 255, 0.3)'} />,
            onClick: muteAllAudio,
            text: t('moderatorSettings.muteAllAudio')
        },
        {
            containerClassName: classes.contextMenuItemContainer,
            className: classes.contextItem,
            accessibilityLabel: t('moderatorSettings.muteAllVideo'),
            id: 'moderator-settings-context-mute-all-video',
            customIcon: <Icon
                size = {18}
                src = {IconVideoOff}
                color = {'rgba(255, 255, 255, 0.3)'} />,
            onClick: muteAllVideo,
            text: t('moderatorSettings.muteAllVideo')
        }
    ] : [
        {
            containerClassName: classes.contextMenuItemContainer,
            className: classes.contextItem,
            accessibilityLabel: t('moderatorSettings.muteAllAudio'),
            id: 'moderator-settings-context-mute-all-audio',
            customIcon: <Icon
                size = {18}
                src = {IconMicSlash}
                color = {'rgba(255, 255, 255, 0.3)'} />,
            onClick: muteAllAudio,
            text: t('moderatorSettings.muteAllAudio')
        },
        {
            containerClassName: classes.contextMenuItemContainer,
            className: classes.contextItem,
            accessibilityLabel: t('moderatorSettings.muteAllVideo'),
            id: 'moderator-settings-context-mute-all-video',
            customIcon: <Icon
                size = {18}
                src = {IconVideoOff}
                color = {'rgba(255, 255, 255, 0.3)'} />,
            onClick: muteAllVideo,
            text: t('moderatorSettings.muteAllVideo')
        }
    ];

    const checkBoxActions = [
        {
            containerClassName: classes.contextMenuItemContainer,
            className: isAudioModerationEnabled ? classes.contextItemCheckBox : classes.contextItemCheckBoxChecked,
            accessibilityLabel: t('moderatorSettings.participantsCanUnmuteAudio'),
            id: isAudioModerationEnabled
                ? 'moderator-settings-context-menu-stop-audio-moderation'
                : 'moderator-settings-context-menu-start-audio-moderation',
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
                ? 'moderator-settings-context-menu-stop-video-moderation'
                : 'moderator-settings-context-menu-start-video-moderation',
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
            aria-labelledby = 'moderator-settings-button'
            className = {classes.contextMenu}
            hidden = {false}
            id = 'moderator-settings-dialog'
            tabIndex = {-1}>
            <ContextMenuItemGroup className = {classes.contextMenuItemGroup}>
                <div className = {classes.contextMenuTitle}>{t('moderatorSettings.conferenceQualityTitle')}</div>
                <QualityButtons className = {classes.qualityButtonsContainer} />
            </ContextMenuItemGroup>
            <div className = {classes.separateLineContainer} style = {{
                marginTop: "16px",
            }}>
                <div className = {classes.separateLine} />
            </div>
            <ContextMenuItemGroup
                actions = {buttonActions} />
            <div className = {classes.separateLineContainer} style = {{
                marginTop: "4px",
            }}>
                <div className = {classes.separateLine} />
            </div>
            <ContextMenuItemGroup
                actions = {checkBoxActions} />
        </ContextMenu>
    );
};

const mapStateToProps = (state: IReduxState) => {
    const isAlreadyRecording = isRecordingRunning(state) || isRecorderTranscriptionsRunning(state);

    return {
        isAlreadyRecording
    };
};

const mapDispatchToProps = (dispatch: IStore['dispatch']) => {
    return {
        toggleSuppression() {
            dispatch(toggleNoiseSuppression());
        }
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(ModeratorSettingsContent);
