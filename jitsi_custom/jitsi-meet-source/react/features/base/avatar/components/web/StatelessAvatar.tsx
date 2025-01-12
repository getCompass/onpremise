import React, { useCallback } from 'react';
import { makeStyles } from 'tss-react/mui';

import Icon from '../../../icons/components/Icon';
import { withPixelLineHeight } from '../../../styles/functions.web';
import { isIcon } from '../../functions';
import { IAvatarProps } from '../../types';
import { PRESENCE_AVAILABLE_COLOR, PRESENCE_AWAY_COLOR, PRESENCE_BUSY_COLOR, PRESENCE_IDLE_COLOR } from '../styles';

interface IProps extends IAvatarProps {

    /**
     * External class name passed through props.
     */
    className?: string;

    iconClassName?: string;

    /**
     * The default avatar URL if we want to override the app bundled one (e.g. AlwaysOnTop).
     */
    defaultAvatar?: string;

    /**
     * ID of the component to be rendered.
     */
    id?: string;

    /**
     * One of the expected status strings (e.g. 'available') to render a badge on the avatar, if necessary.
     */
    status?: string;

    /**
     * TestId of the element, if any.
     */
    testId?: string;

    /**
     * The URL of the avatar to render.
     */
    url?: string | Function;

    /**
     * Indicates whether to load the avatar using CORS or not.
     */
    useCORS?: boolean;
}

const useStyles = makeStyles()(() => {
    return {
        avatar: {
            backgroundColor: 'rgba(255, 255, 255, 0.06)',
            borderRadius: '100%',
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            color: '#fff',
            fontSize: 'inherit',
            objectFit: 'cover',
            textAlign: 'center',
            overflow: 'hidden',

            '&.defaultAvatar': {
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                '& .jitsi-icon': {
                    paddingTop: '0px',
                    paddingLeft: '0px',
                    height: '70% !important',
                    width: '70% !important',
                },
            },

            '&.avatar-small': {
                height: '28px !important',
                width: '28px !important'
            },

            '&.avatar-xsmall': {
                height: '16px !important',
                width: '16px !important'
            },

            '& .jitsi-icon': {
                paddingTop: '7px',
                paddingLeft: '7px',
                height: '22px !important',
                width: '22px !important',

                '&.custom-notification-icon': {
                    paddingTop: 0,
                    paddingLeft: 0,
                    height: '36px !important',
                    width: '36px !important',
                },

                '& svg': {
                    width: '100%',
                    height: '100%'
                }
            },

            '& .avatar-svg': {
                height: '100%',
                width: '100%'
            }
        },

        initialsContainer: {
            width: '100%',
            height: '100%',
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center'
        },

        badge: {
            position: 'relative',

            '&.avatar-badge:after': {
                borderRadius: '50%',
                content: '""',
                display: 'block',
                height: '35%',
                position: 'absolute',
                bottom: 0,
                width: '35%'
            },

            '&.avatar-badge-available:after': {
                backgroundColor: PRESENCE_AVAILABLE_COLOR
            },

            '&.avatar-badge-away:after': {
                backgroundColor: PRESENCE_AWAY_COLOR
            },

            '&.avatar-badge-busy:after': {
                backgroundColor: PRESENCE_BUSY_COLOR
            },

            '&.avatar-badge-idle:after': {
                backgroundColor: PRESENCE_IDLE_COLOR
            }
        }
    };
});

const StatelessAvatar = ({
    className,
    iconClassName,
    color,
    iconUser,
    id,
    initials,
    onAvatarLoadError,
    onAvatarLoadErrorParams,
    size,
    status,
    testId,
    url,
    useCORS
}: IProps) => {
    const { classes, cx } = useStyles();

    const _getAvatarStyle = (backgroundColor?: string) => {
        return {
            background: backgroundColor || undefined,
            fontSize: size ? size * 0.39 : '180%',
            lineHeight: size ? size * 0.47 : '200%',
            height: size || '100%',
            width: size || '100%'
        };
    };

    const _getAvatarClassName = (additional?: string) => cx('avatar', additional, className, classes.avatar);

    const _getBadgeClassName = () => {
        if (status) {
            return cx('avatar-badge', `avatar-badge-${status}`, classes.badge);
        }

        return '';
    };

    const _onAvatarLoadError = useCallback(() => {
        if (typeof onAvatarLoadError === 'function') {
            onAvatarLoadError(onAvatarLoadErrorParams);
        }
    }, [ onAvatarLoadError, onAvatarLoadErrorParams ]);

    if (isIcon(url)) {
        return (
            <div
                className = {cx(_getAvatarClassName(), _getBadgeClassName())}
                data-testid = {testId}
                id = {id}
                style = {_getAvatarStyle(color)}>
                <Icon
                    className = {iconClassName}
                    src = {url} />
            </div>
        );
    }

    if (url) {
        return (
            <div className = {_getBadgeClassName()}>
                <img
                    alt = 'avatar'
                    className = {_getAvatarClassName()}
                    crossOrigin = {useCORS ? '' : undefined}
                    data-testid = {testId}
                    id = {id}
                    onError = {_onAvatarLoadError}
                    src = {url}
                    style = {_getAvatarStyle()} />
            </div>
        );
    }

    if (initials) {
        return (
            <div
                className = {cx(_getAvatarClassName(), _getBadgeClassName())}
                data-testid = {testId}
                id = {id}
                style = {_getAvatarStyle(color)}>
                <div className = {classes.initialsContainer}>
                    {initials}
                </div>
            </div>
        );
    }

    // default avatar
    return (
        <div
            className = {cx(_getAvatarClassName('defaultAvatar'), _getBadgeClassName())}
            data-testid = {testId}
            id = {id}
            style = {_getAvatarStyle()}>
            <Icon
                size = {'50%'}
                className = {iconClassName}
                src = {iconUser} />
        </div>
    );
};


export default StatelessAvatar;
