/* eslint-disable react/no-multi-comp */
import React from 'react';
import { useTranslation } from 'react-i18next';
import { connect, useDispatch } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../../app/types';
import ContextMenuItemGroup from '../../../../base/ui/components/web/ContextMenuItemGroup';
import Icon from "../../../../base/icons/components/Icon";
import {
    IconCheckboxOffSmall,
    IconCheckboxOnSmall,
} from "../../../../base/icons/svg";


import { isAudioEnabledOnEnterConference, isVideoEnabledOnEnterConference, isLobbyEnabledOnJoin } from '../../../../base/settings/functions.any'
import { updateSettings } from '../../../../base/settings/actions';
import clsx from "clsx";

export interface IProps {
    isAudioEnabledOnJoinConference?: boolean;
    isVideoEnabledOnJoinConference?: boolean;
    isLobbyEnabledOnEnter?: boolean;
}

const OnJoinSettings = (props: IProps) => {
    const { isAudioEnabledOnJoinConference, isVideoEnabledOnJoinConference, isLobbyEnabledOnEnter } = props
    const { classes } = useStyles();
    const dispatch = useDispatch();
    const { t } = useTranslation();

    const disableAudioOnJoin = () => {
        dispatch(updateSettings({
            isStartWithAudio: false,
        }));
    };
    const enableAudioOnJoin = () => {
        dispatch(updateSettings({
            isStartWithAudio: true,
        }));
    };

    const disableVideoOnJoin = () => {
        dispatch(updateSettings({
            isStartWithVideo: false,
        }));
    };
    const enableVideoOnJoin = () => {
        dispatch(updateSettings({
            isStartWithVideo: true,
        }));
    };

    const disableLobbyOnJoin = () => {
        dispatch(updateSettings({
            isLobbyEnabledOnJoin: false,
        }));
    };
    const enableLobbyOnJoin = () => {
        dispatch(updateSettings({
            isLobbyEnabledOnJoin: true,
        }));
    };

    const joinActions = [
        {
            containerClassName: classes.contextMenuItemContainer,
            className: !isAudioEnabledOnJoinConference ? classes.contextItemCheckBox : classes.contextItemCheckBoxChecked,
            accessibilityLabel: t('settings.joinSettings.option_1'),
            id: isAudioEnabledOnJoinConference
                ? 'moderator-settings-context-menu-stop-audio-on-join-conference'
                : 'moderator-settings-context-menu-start-audio-on-join-conference',
            customIcon: <Icon
                size = {18}
                src = {!isAudioEnabledOnJoinConference ? IconCheckboxOffSmall : IconCheckboxOnSmall} />,
            onClick: isAudioEnabledOnJoinConference ? disableAudioOnJoin : enableAudioOnJoin,
            text: t('settings.joinSettings.option_1')
        },
        {
            containerClassName: classes.contextMenuItemContainer,
            className: !isVideoEnabledOnJoinConference ? classes.contextItemCheckBox : classes.contextItemCheckBoxChecked,
            accessibilityLabel: t('settings.joinSettings.option_2'),
            id: isVideoEnabledOnJoinConference
                ? 'moderator-settings-context-menu-stop-video-on-join-conference'
                : 'moderator-settings-context-menu-start-video-on-join-conference',
            customIcon: <Icon
                size = {18}
                src = {!isVideoEnabledOnJoinConference ? IconCheckboxOffSmall : IconCheckboxOnSmall} />,
            onClick: isVideoEnabledOnJoinConference ? disableVideoOnJoin : enableVideoOnJoin,
            text: t('settings.joinSettings.option_2')
        },
        {
            containerClassName: classes.contextMenuItemContainer,
            className: !isLobbyEnabledOnEnter ? classes.contextItemCheckBox : classes.contextItemCheckBoxChecked,
            accessibilityLabel: t('settings.joinSettings.option_3'),
            id: isLobbyEnabledOnEnter
                ? 'moderator-settings-context-menu-stop-lobby-on-join-conference'
                : 'moderator-settings-context-menu-start-lobby-on-join-conference',
            customIcon: <Icon
                size = {18}
                src = {!isLobbyEnabledOnEnter ? IconCheckboxOffSmall : IconCheckboxOnSmall} />,
            onClick: isLobbyEnabledOnEnter ? disableLobbyOnJoin : enableLobbyOnJoin,
            text: t('settings.joinSettings.option_3')
        }
    ];

    return (
        <div onClick={(e) => e.stopPropagation()}>
            <div className = {clsx(classes.contextMenuTitle, classes.contextMenuTitleSettingsOnJoin)}>{t('settings.joinSettings.title')}</div>
            <ContextMenuItemGroup
                actions = {joinActions} />
        </div>
    );
};

const mapStateToProps = (state: IReduxState) => {
    return {
        isAudioEnabledOnJoinConference: isAudioEnabledOnEnterConference(state),
        isVideoEnabledOnJoinConference: isVideoEnabledOnEnterConference(state),
        isLobbyEnabledOnEnter: isLobbyEnabledOnJoin(state),
    };
};

const mapDispatchToProps = () => {
    return {};
};

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
        contextMenuTitle: {
            fontFamily: 'Lato Bold',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '20px',
            color: 'rgba(255, 255, 255, 0.75)',
        },
        contextMenuTitleSettingsOnJoin: {
            marginLeft: '24px',
            paddingBottom: '7px',
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
    };
});

export default connect(mapStateToProps, mapDispatchToProps)(OnJoinSettings);
