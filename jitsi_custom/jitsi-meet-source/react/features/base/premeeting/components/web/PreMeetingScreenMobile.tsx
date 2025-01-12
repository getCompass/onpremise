import clsx from 'clsx';
import React, { ReactNode } from 'react';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../../app/types';
import { isButtonEnabled } from '../../../../toolbox/functions.web';
import { PREMEETING_BUTTONS_MOBILE, THIRD_PARTY_PREJOIN_BUTTONS } from '../../../config/constants';
import { withPixelLineHeight } from '../../../styles/functions.web';

import ConnectionStatus from './ConnectionStatus';
import Avatar from "../../../avatar/components/Avatar";
import i18n from "i18next";
import PreviewMobile from "./PreviewMobile";
import CompassToolboxMobile from "../../../../toolbox/components/web/CompassToolboxMobile";
import DeviceStatus from "../../../../prejoin/components/web/preview/DeviceStatus";
import DeviceStatusMobile from "../../../../prejoin/components/web/preview/DeviceStatusMobile";

interface IProps {

    /**
     * The list of toolbar buttons to render.
     */
    _buttons: Array<string>;

    /**
     * The branding background of the premeeting screen(lobby/prejoin).
     */
    _premeetingBackground: string;

    _lobbyKnocking: boolean;

    /**
     * Children component(s) to be rendered on the screen.
     */
    children?: ReactNode;

    /**
     * Additional CSS class names to set on the icon container.
     */
    className?: string;

    /**
     * The name of the participant.
     */
    name?: string;

    /**
     * The id of the participant.
     */
    participantId?: string;

    /**
     * The type of the user that is about to join.
     */
    type?: string;

    /**
     * Indicates whether the copy url button should be shown.
     */
    showCopyUrlButton?: boolean;

    /**
     * Indicates whether the device status should be shown.
     */
    showDeviceStatus: boolean;

    /**
     * The 'Skip prejoin' button to be rendered (if any).
     */
    skipPrejoinButton?: ReactNode;

    /**
     * Whether it's used in the 3rdParty prejoin screen or not.
     */
    thirdParty?: boolean;

    /**
     * Title of the screen.
     */
    title?: string;

    /**
     * True if the preview overlay should be muted, false otherwise.
     */
    videoMuted?: boolean;

    /**
     * The video track to render as preview (if omitted, the default local track will be rendered).
     */
    videoTrack?: Object;

    isLobby: boolean;

    _lobbyNoticeText?: string;
}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            height: '100%',
            position: 'absolute',
            inset: '0 0 0 0',
            display: 'flex',
            zIndex: 252,

            // приходится обращаться так, потому что если делать через компонент, то придется
            // кастомный класс прокидывать слишком далеко
            '.toolbox-icon': {
                width: '64px',
                height: '64px',
                backgroundColor: 'rgba(4, 164, 90, 0.5) !important',
                borderRadius: '100% !important',
                opacity: '100%',

                '&:active': {
                    backgroundColor: 'rgba(4, 164, 90, 0.65) !important',
                },

                '&.toggled.is-mobile': {
                    backgroundColor: 'rgba(255, 255, 255, 0.1) !important',

                    '&:active': {
                        backgroundColor: 'rgba(255, 255, 255, 0.25) !important',
                    },
                }
            },

            // для видео придется переопределять, там при каком-то статусе оно не дает примениться
            '.video-preview .toolbox-icon': {
                backgroundColor: ' transparent !important',
                opacity: ' 60% !important'
            },

            '.video-preview .toolbox-icon:hover': {
                opacity: ' 100% !important'
            },

            '@media (max-width: 720px)': {
                flexDirection: 'column-reverse'
            }
        },

        lobbyNoticeContainer: {
            position: 'absolute',
            bottom: '112px',
            zIndex: 1,
            right: '16px',
            left: '16px',
        },

        lobbyNotice: {
            padding: '11px',
            background: 'linear-gradient(122.89deg, rgba(14, 76, 144, 0.8) -31.07%, rgba(14, 76, 144, 0.8) 6.08%, rgba(34, 96, 164, 0.8) 42.1%, rgba(14, 76, 144, 0.8) 89.18%, rgba(14, 76, 144, 0.8) 122.33%)',
            display: 'flex',
            gap: '8px',
            border: '1px solid rgba(255, 255, 255, 0.2)',
            borderRadius: '8px',
            zIndex: 1,
        },
        lobbyNoticeIcon: {
            width: '60px',
            height: '60px',
        },
        lobbyNoticeText: {
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '14px',
            lineHeight: '20px',
            color: 'rgba(255, 255, 255, 0.8)',
            letterSpacing: '-0.16px',
        },
        content: {
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            flexShrink: 0,
            boxSizing: 'border-box',
            margin: '0 46px',
            padding: '24px 0 16px',
            position: 'absolute',
            width: '302px',
            height: '100%',
            zIndex: 252,
            backgroundColor: 'rgba(28, 28, 28, 0.7)',
            bottom: 0,

            '@media (max-width: 720px)': {
                height: 'auto',
                margin: '0 auto'
            },

            // mobile phone landscape
            '@media (max-width: 430px)': {
                padding: '32px 16px 0px 16px',
                width: '100%'
            },

            '@media (max-width: 400px)': {
                padding: '32px 16px 0px 16px',
            }
        },
        contentControls: {
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            margin: 'auto',
            width: '100%'
        },
        title: {
            ...withPixelLineHeight(theme.typography.heading4),
            color: `${theme.palette.text01}!important`,
            marginTop: "0px",
            marginBottom: "40px",
            textAlign: 'center',

            '@media (max-width: 430px)': {
                display: 'none'
            }
        },
        roomName: {
            ...withPixelLineHeight(theme.typography.heading5),
            color: theme.palette.text01,
            marginBottom: theme.spacing(4),
            overflow: 'hidden',
            textAlign: 'center',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap',
            width: '100%'
        },
        avatarMember: {
            marginBottom: "32px",
        },
        avatarGuest: {
            borderRadius: '0 !important',
            marginBottom: "16px",

            '@media (max-width: 430px)': {
                display: 'none'
            }
        },
    };
});

const PreMeetingScreenMobile = ({
    _buttons,
    _premeetingBackground,
    _lobbyKnocking,
    children,
    className,
    showDeviceStatus,
    skipPrejoinButton,
    title,
    videoMuted,
    videoTrack,
    name,
    participantId,
    type,
    isLobby = false,
    _lobbyNoticeText,
}: IProps) => {
    const { classes } = useStyles();
    const style = _premeetingBackground ? {
        background: _premeetingBackground,
        backgroundPosition: 'center',
        backgroundSize: 'cover'
    } : {};

    return (
        <div className = {clsx('premeeting-screen', classes.container, className)}>
            {isLobby && _lobbyKnocking && (
                <div className = {classes.lobbyNoticeContainer}>
                    <div className = {classes.lobbyNotice}>
                        <div className = {classes.lobbyNoticeIcon}>
                            <svg width = "60" height = "60" viewBox = "0 0 60 60" fill = "none"
                                 xmlns = "http://www.w3.org/2000/svg">
                                <g opacity = "0.65" clip-path = "url(#clip0_9874_26677)">
                                    <path fillRule = "evenodd" clipRule = "evenodd"
                                          d = "M14.7289 5.7334C15.1431 5.7334 15.4789 6.06918 15.4789 6.4834V10.8886C15.4789 16.2292 18.148 21.2164 22.5916 24.1788L39.7502 35.6179C44.6112 38.8585 47.5309 44.3141 47.5309 50.1561V53.2259C47.5309 53.6401 47.1951 53.9759 46.7809 53.9759C46.3667 53.9759 46.0309 53.6401 46.0309 53.2259V50.1561C46.0309 44.8156 43.3618 39.8284 38.9182 36.866L21.7595 25.4269C16.8986 22.1863 13.9789 16.7307 13.9789 10.8886V6.4834C13.9789 6.06918 14.3147 5.7334 14.7289 5.7334Z"
                                          fill = "white" />
                                    <path fillRule = "evenodd" clipRule = "evenodd"
                                          d = "M14.7289 53.9766C15.1431 53.9766 15.4789 53.6408 15.4789 53.2266V50.1568C15.4789 44.8163 18.148 39.8291 22.5916 36.8667L39.7502 25.4275C44.6112 22.1869 47.5309 16.7314 47.5309 10.8893V6.48409C47.5309 6.06987 47.1951 5.73409 46.7809 5.73409C46.3667 5.73409 46.0309 6.06987 46.0309 6.48409V10.8893C46.0309 16.2299 43.3618 21.2171 38.9182 24.1795L21.7595 35.6186C16.8986 38.8592 13.9789 44.3147 13.9789 50.1568V53.2266C13.9789 53.6408 14.3147 53.9766 14.7289 53.9766Z"
                                          fill = "white" />
                                    <path fillRule = "evenodd" clipRule = "evenodd"
                                          d = "M10.1466 6.4834C10.1466 6.06918 10.4824 5.7334 10.8966 5.7334H50.6132C51.0274 5.7334 51.3632 6.06918 51.3632 6.4834C51.3632 6.89761 51.0274 7.2334 50.6132 7.2334H10.8966C10.4824 7.2334 10.1466 6.89761 10.1466 6.4834Z"
                                          fill = "white" />
                                    <path fillRule = "evenodd" clipRule = "evenodd"
                                          d = "M10.1466 53.9766C10.1466 53.5623 10.4824 53.2266 10.8966 53.2266H50.6132C51.0274 53.2266 51.3632 53.5623 51.3632 53.9766C51.3632 54.3908 51.0274 54.7266 50.6132 54.7266H10.8966C10.4824 54.7266 10.1466 54.3908 10.1466 53.9766Z"
                                          fill = "white" />
                                </g>
                                <defs>
                                    <clipPath id = "clip0_9874_26677">
                                        <rect width = "60" height = "60" fill = "white" />
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div className = {classes.lobbyNoticeText}>
                            {_lobbyNoticeText?.split("\n").map((line, index) => (
                                <div key = {index}>{line}</div>
                            ))}
                        </div>
                    </div>
                </div>
            )}
            {!isLobby && (
                <div style = {style}>
                    {showDeviceStatus && <DeviceStatusMobile />}
                    <div className = {classes.content}>
                        <ConnectionStatus />

                        <div className = {classes.contentControls}>
                            {type === "guest" ? (
                                <>
                                    <Avatar
                                        className = {`premeeting-screen-avatar ${classes.avatarGuest}`}
                                        displayName = {name}
                                        participantId = {participantId}
                                        size = {64}
                                        url = "images/compass_icon.png"
                                    />
                                    <h1 className = {classes.title}
                                        dangerouslySetInnerHTML = {{ __html: title ?? '' }}></h1>
                                </>
                            ) : (
                                <Avatar
                                    className = {`premeeting-screen-avatar ${classes.avatarMember}`}
                                    displayName = {name}
                                    participantId = {participantId}
                                    size = {120} />
                            )}
                            {children}
                            {isLobby && _lobbyKnocking ? <></> : (_buttons.length &&
                                <CompassToolboxMobile toolbarButtons = {_buttons} />)}
                            {skipPrejoinButton}
                        </div>
                    </div>
                </div>
            )}
            <PreviewMobile
                videoMuted = {videoMuted}
                videoTrack = {videoTrack} />
        </div>
    );
};


/**
 * Maps (parts of) the redux state to the React {@code Component} props.
 *
 * @param {Object} state - The redux state.
 * @param {Object} ownProps - The props passed to the component.
 * @returns {Object}
 */
function mapStateToProps(state: IReduxState, ownProps: Partial<IProps>) {
    const { hiddenPremeetingButtons } = state['features/base/config'];
    const { toolbarButtons } = state['features/toolbox'];
    const premeetingButtons = (ownProps.thirdParty
        ? THIRD_PARTY_PREJOIN_BUTTONS
        : PREMEETING_BUTTONS_MOBILE).filter((b: any) => !(hiddenPremeetingButtons || []).includes(b));

    const { premeetingBackground } = state['features/dynamic-branding'];
    const { knocking } = state['features/lobby'];

    return {
        // For keeping backwards compat.: if we pass an empty hiddenPremeetingButtons
        // array through external api, we have all prejoin buttons present on premeeting
        // screen regardless of passed values into toolbarButtons config overwrite.
        // If hiddenPremeetingButtons is missing, we hide the buttons according to
        // toolbarButtons config overwrite.
        _buttons: hiddenPremeetingButtons
            ? premeetingButtons
            : premeetingButtons.filter(b => isButtonEnabled(b, toolbarButtons)),
        _premeetingBackground: premeetingBackground,
        _lobbyKnocking: knocking,
        _lobbyNoticeText: i18n.t('lobby.joiningMessage'),
    };
}

export default connect(mapStateToProps)(PreMeetingScreenMobile);
