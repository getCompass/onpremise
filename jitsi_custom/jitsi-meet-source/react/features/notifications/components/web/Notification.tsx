import { Theme } from '@mui/material';
import React, { isValidElement, useCallback, useContext } from 'react';
import { useTranslation } from 'react-i18next';
import { keyframes } from 'tss-react';
import { makeStyles } from 'tss-react/mui';
import {
    IconPoll,
    IconRecordingNotification,
    IconScreenshareNotification,
    IconWarningColor,
    IconWarningStopHandNotification
} from '../../../base/icons/svg';
import Message from '../../../base/react/components/web/Message';
import { NOTIFICATION_ICON, NOTIFICATION_TYPE } from '../../constants';
import { INotificationProps } from '../../types';
import { NotificationsTransitionContext } from '../NotificationsTransition';
import Avatar from "../../../base/avatar/components/Avatar";
import { isMobileBrowser } from "../../../base/environment/utils";

interface IProps extends INotificationProps {

    /**
     * Callback invoked when the user clicks to dismiss the notification.
     */
    onDismissed: Function;
}

/**
 * Secondary colors for notification icons.
 *
 * @type {{error, info, normal, success, warning}}
 */


const useStyles = makeStyles()((theme: Theme) => {
    return {
        container: {
            backgroundColor: 'rgba(33, 33, 33, 1)',
            padding: '4px 17px 4px 12px',
            display: 'flex',
            position: 'relative' as const,
            borderRadius: '8px',
            marginBottom: '2px',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',

            '&:last-of-type': {
                marginBottom: 0
            },

            animation: `${keyframes`
                0% {
                    opacity: 0;
                    transform: translate3d(0, -100%, 0);
                }

                100% {
                    opacity: 1;
                    transform: translate3d(0, 0, 0);
                }
            `} 0.5s forwards ease`,

            '&.unmount': {
                animation: `${keyframes`
                    0% {
                        opacity: 1;
                    }

                    100% {
                        opacity: 0;
                        transform: translate3d(0, 100%, 0);
                    }
                `} 0.5s forwards ease`
            },

            '&.is-mobile': {
                marginBottom: 0,
                padding: '2px 16px',
                backgroundColor: 'transparent',
                outline: 'transparent',
            }
        },

        content: {
            display: 'flex',
            alignItems: 'flex-start',
            flex: 1,
            maxWidth: '100%',
            paddingTop: '0px',
            paddingBottom: '4px',
        },

        avatarContainer: {
            padding: '8px 0',

            '&.is-mobile': {
                padding: '4px 0',
            }
        },

        avatar: {
            margin: 0,
            position: 'sticky',
            flexShrink: 0,
            top: 0
        },

        textContainer: {
            display: 'flex',
            flexDirection: 'column' as const,
            justifyContent: 'space-between',
            flex: 1,
            marginLeft: '8px',
            padding: '4px 0',

            // maxWidth: 100% minus the icon on left (36px) minus the margins
            maxWidth: 'calc(100% - 36px - 8px)',
            maxHeight: '300px',

            '&.is-mobile': {
                padding: 0
            }
        },

        title: {
            fontFamily: 'Lato Black',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.75)',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',

            '&.is-mobile': {
                fontSize: '16px',
                lineHeight: '20px',
                color: 'rgba(255, 255, 255, 1)',
            }
        },

        description: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '19px',
            color: 'rgba(255, 255, 255, 0.6)',
            overflow: 'auto',
            overflowWrap: 'break-word',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',

            '&.is-mobile': {
                fontSize: '16px',
                lineHeight: '20px',
                color: 'rgba(255, 255, 255, 0.85)',
            }
        },

        actionsContainer: {
            display: 'flex',
            width: '100%',

            '&:not(:empty)': {
                padding: '6px 0px 0px 0px',
                gap: '8px',

                '&.is-mobile': {
                    padding: '12px 0',
                }
            }
        },

        action: {
            border: 0,
            outline: 0,
            backgroundColor: 'transparent',
            color: 'rgba(255, 255, 255, 0.75)',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '13px',
            lineHeight: '18px',
            padding: '3px 8px',
            cursor: 'pointer',
            userSelect: 'none',
            marginTop: '4px',
            '-webkit-tap-highlight-color': 'transparent',

            '&.info': {
                color: 'rgba(255, 255, 255, 1)',
                backgroundColor: 'rgba(255, 255, 255, 0.1)',
            },

            '&.primary': {
                color: 'rgba(4, 164, 90, 1)',
                backgroundColor: 'rgba(4, 164, 90, 0.1)',
            },

            '&.destructive': {
                color: 'rgba(255, 79, 71, 1)',
                backgroundColor: 'rgba(255, 79, 71, 0.1)',
            },

            '&.destructive_gray': {
                color: 'rgba(255, 255, 255, 1)',
                backgroundColor: 'rgba(255, 255, 255, 0.1)',
            },

            '&.is-mobile': {
                padding: '3.5px 8px',
                fontSize: '14px',
                lineHeight: '17px',
            }
        }
    };
});

const Notification = ({
    appearance = NOTIFICATION_TYPE.NORMAL,
    customActionHandler,
    customActionNameKey,
    customActionType,
    description,
    descriptionArguments,
    descriptionKey,
    disableClosing,
    hideErrorSupportLink,
    icon,
    onDismissed,
    title,
    titleArguments,
    titleKey,
    uid,
    participantId
}: IProps) => {
    const { classes, cx, theme } = useStyles();
    const { t } = useTranslation();
    const { unmounting } = useContext(NotificationsTransitionContext);
    const isMobile = isMobileBrowser();

    const onDismiss = useCallback(() => {
        onDismissed(uid);
    }, [ uid ]);

    // eslint-disable-next-line react/no-multi-comp
    const renderDescription = useCallback(() => {
        const descriptionArray = [];

        descriptionKey
        && descriptionArray.push(t(descriptionKey, descriptionArguments));

        description && typeof description === 'string' && descriptionArray.push(description);

        // Keeping in mind that:
        // - Notifications that use the `translateToHtml` function get an element-based description array with one entry
        // - Message notifications receive string-based description arrays that might need additional parsing
        // We look for ready-to-render elements, and if present, we roll with them
        // Otherwise, we use the Message component that accepts a string `text` prop
        const shouldRenderHtml = descriptionArray.length === 1 && isValidElement(descriptionArray[0]);

        // the id is used for testing the UI
        return (
            <div
                className = {cx(classes.description, isMobile && 'is-mobile')}
                data-testid = {descriptionKey}>
                {shouldRenderHtml ? descriptionArray : <Message text = {descriptionArray.join(' ')} />}
                {typeof description === 'object' && description}
            </div>
        );
    }, [ description, descriptionArguments, descriptionKey, classes ]);

    const _onOpenSupportLink = () => {
        window.open(interfaceConfig.SUPPORT_URL, '_blank', 'noopener');
    };

    const mapAppearanceToButtons = useCallback((): {
        content: string; onClick: () => void; testId?: string; type?: string;
    }[] => {
        switch (appearance) {
        case NOTIFICATION_TYPE.ERROR: {
            return [];
        }
        case NOTIFICATION_TYPE.WARNING:
            return [];

        default:
            if (customActionNameKey?.length && customActionHandler?.length) {
                return customActionNameKey.map((customAction: string, customActionIndex: number) => {
                    return {
                        content: t(customAction),
                        onClick: () => {
                            if (customActionHandler?.[customActionIndex]()) {
                                onDismiss();
                            }
                        },
                        type: customActionType?.[customActionIndex],
                        testId: customAction
                    };
                });
            }

            return [];
        }
    }, [ appearance, onDismiss, customActionHandler, customActionNameKey, hideErrorSupportLink ]);

    const iconPathByIconName: { [p: string]: string } = {
        [NOTIFICATION_ICON.POLL]: IconPoll,
        [NOTIFICATION_ICON.RECORDING]: IconRecordingNotification,
        [NOTIFICATION_ICON.SCREENSHARE]: IconScreenshareNotification,
        [NOTIFICATION_ICON.WARNING_STOP_HAND]: IconWarningStopHandNotification,
        [NOTIFICATION_ICON.WARNING]: IconWarningColor,
    };

    const iconPathByAppearance: { [p: string]: string } = {
        [NOTIFICATION_ICON.POLL]: IconPoll,
        [NOTIFICATION_ICON.RECORDING]: IconRecordingNotification,
        [NOTIFICATION_ICON.WARNING_STOP_HAND]: IconWarningStopHandNotification,
    };

    const getIcon = (iconName = '', appearance = ''): string | null => {
        return iconPathByIconName[iconName] || iconPathByAppearance[appearance] || null;
    }

    const iconPath = getIcon(icon, appearance);

    return (
        <div
            aria-atomic = 'false'
            aria-live = 'polite'
            className = {cx(classes.container, isMobile && 'is-mobile', unmounting.get(uid ?? '') && 'unmount')}
            data-testid = {titleKey || descriptionKey}
            id = {uid}
            onClick = {() => (icon === NOTIFICATION_ICON.RECORDING || appearance === NOTIFICATION_ICON.RECORDING) ? onDismiss() : undefined}
        >
            <div className = {classes.content}>
                <div className = {cx(classes.avatarContainer, isMobile && 'is-mobile')}>
                    {(iconPath ?
                            <Avatar
                                className = {cx(classes.avatar, 'avatar')}
                                iconClassName = 'custom-notification-icon'
                                url = {iconPath}
                                size = {36} />
                            :
                            <Avatar
                                className = {cx(classes.avatar, 'avatar')}
                                participantId = {participantId ?? "0"}
                                size = {36} />
                    )
                    }
                </div>
                <div className = {cx(classes.textContainer, isMobile && 'is-mobile')}>
                    <span
                        className = {cx(classes.title, isMobile && 'is-mobile')}>{title || t(titleKey ?? '', titleArguments)}</span>
                    {renderDescription()}
                    <div className = {cx(classes.actionsContainer, isMobile && 'is-mobile')}>
                        {mapAppearanceToButtons().map(({ content, onClick, type, testId }) => (
                            <button
                                className = {cx(classes.action, isMobile && 'is-mobile', type)}
                                data-testid = {testId}
                                key = {content}
                                onClick = {onClick}>
                                {content}
                            </button>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Notification;
