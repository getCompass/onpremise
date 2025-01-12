import clsx from 'clsx';
import React, { ReactNode } from 'react';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../../app/types';
import DeviceStatus from '../../../../prejoin/components/web/preview/DeviceStatus';
import Toolbox from '../../../../toolbox/components/web/Toolbox';
import { isButtonEnabled } from '../../../../toolbox/functions.web';
import { PREMEETING_BUTTONS, PREMEETING_BUTTONS_MOBILE, THIRD_PARTY_PREJOIN_BUTTONS } from '../../../config/constants';
import { withPixelLineHeight } from '../../../styles/functions.web';

import ConnectionStatus from './ConnectionStatus';
import Preview from './Preview';
import RecordingWarning from './RecordingWarning';
import UnsafeRoomWarning from './UnsafeRoomWarning';
import Avatar from "../../../avatar/components/Avatar";
import i18n from "i18next";
import { isMobileBrowser } from "../../../environment/utils";

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
     * Indicates whether to display the recording warning.
     */
    showRecordingWarning?: boolean;

    /**
     * If should show unsafe room warning when joining.
     */
    showUnsafeRoomWarning?: boolean;

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
            backgroundColor: theme.palette.ui13,
            zIndex: 252,

            // приходится обращаться так, потому что если делать через компонент, то придется
            // кастомный класс прокидывать слишком далеко
            '.toolbox-icon': {
                width: '44px',
                height: '44px',
                backgroundColor: ' #171717',
                borderRadius: '8px !important',

                '&.toggled.is-mobile': {
                    backgroundColor: ' #171717 !important'
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
            padding: '15px',
            width: '398px',
            background: 'linear-gradient(122.89deg, rgba(14, 76, 144, 0.8) -31.07%, rgba(14, 76, 144, 0.8) 6.08%, rgba(34, 96, 164, 0.8) 42.1%, rgba(14, 76, 144, 0.8) 89.18%, rgba(14, 76, 144, 0.8) 122.33%)',
            position: 'absolute',
            right: 'calc(50% - 215px)',
            bottom: '96px',
            display: 'flex',
            gap: '12px',
            border: '1px solid rgba(255, 255, 255, 0.2)',
            borderRadius: '8px',
            zIndex: 1,
        },
        lobbyNoticeIcon: {
            width: '66px',
            height: '66px',
        },
        lobbyNoticeText: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.8)',
        },
        content: {
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            flexShrink: 0,
            boxSizing: 'border-box',
            margin: '0 46px',
            padding: '24px 0 16px',
            position: 'relative',
            width: '302px',
            height: '100%',
            zIndex: 252,

            '@media (max-width: 720px)': {
                height: 'auto',
                margin: '0 auto'
            },

            // mobile phone landscape
            '@media (max-width: 430px)': {
                padding: '32px 16px 32px 16px',
                width: '100%'
            },

            '@media (max-width: 400px)': {
                padding: '32px 16px 32px 16px',
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

const PreMeetingScreen = ({
    _buttons,
    _premeetingBackground,
    _lobbyKnocking,
    children,
    className,
    showDeviceStatus,
    showRecordingWarning,
    showUnsafeRoomWarning,
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
                    <div className = {classes.lobbyNoticeIcon}>
                        <svg width = "66" height = "66" viewBox = "0 0 66 66" fill = "none"
                             xmlns = "http://www.w3.org/2000/svg">
                            <g opacity = "0.65" clipPath = "url(#clip0_412_21657)">
                                <path fillRule = "evenodd" clipRule = "evenodd"
                                      d = "M16.2017 6.30695C16.6573 6.30695 17.0267 6.67631 17.0267 7.13195V11.9777C17.0267 17.8523 19.9627 23.3382 24.8507 26.5969L43.7252 39.1799C49.0722 42.7446 52.2839 48.7457 52.2839 55.172V58.5487C52.2839 59.0043 51.9145 59.3737 51.4589 59.3737C51.0033 59.3737 50.6339 59.0043 50.6339 58.5487V55.172C50.6339 49.2974 47.6979 43.8114 42.81 40.5528L23.9354 27.9698C18.5884 24.4051 15.3767 18.404 15.3767 11.9777V7.13195C15.3767 6.67631 15.7461 6.30695 16.2017 6.30695Z"
                                      fill = "white" />
                                <path fillRule = "evenodd" clipRule = "evenodd"
                                      d = "M16.2017 59.3737C16.6573 59.3737 17.0267 59.0043 17.0267 58.5487V55.172C17.0267 49.2974 19.9627 43.8115 24.8507 40.5528L43.7252 27.9698C49.0722 24.4051 52.2839 18.404 52.2839 11.9777V7.13196C52.2839 6.67633 51.9145 6.30696 51.4589 6.30696C51.0033 6.30696 50.6339 6.67633 50.6339 7.13196V11.9777C50.6339 17.8523 47.6979 23.3382 42.81 26.5969L23.9354 39.1799C18.5884 42.7446 15.3767 48.7457 15.3767 55.172V58.5487C15.3767 59.0043 15.7461 59.3737 16.2017 59.3737Z"
                                      fill = "white" />
                                <path fillRule = "evenodd" clipRule = "evenodd"
                                      d = "M11.1611 7.13195C11.1611 6.67631 11.5305 6.30695 11.9861 6.30695H55.6744C56.13 6.30695 56.4994 6.67631 56.4994 7.13195C56.4994 7.58758 56.13 7.95695 55.6744 7.95695H11.9861C11.5305 7.95695 11.1611 7.58758 11.1611 7.13195Z"
                                      fill = "white" />
                                <path fillRule = "evenodd" clipRule = "evenodd"
                                      d = "M11.1611 59.3737C11.1611 58.918 11.5305 58.5487 11.9861 58.5487H55.6744C56.13 58.5487 56.4994 58.918 56.4994 59.3737C56.4994 59.8293 56.13 60.1987 55.6744 60.1987H11.9861C11.5305 60.1987 11.1611 59.8293 11.1611 59.3737Z"
                                      fill = "white" />
                            </g>
                            <defs>
                                <clipPath id = "clip0_412_21657">
                                    <rect width = "66" height = "66" fill = "white" />
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
            )}
            {!isLobby && (
                <div style = {style}>
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
                                <Toolbox toolbarButtons = {_buttons} />)}
                            {skipPrejoinButton}
                            {showUnsafeRoomWarning && <UnsafeRoomWarning />}
                            {showDeviceStatus && <DeviceStatus />}
                            {showRecordingWarning && <RecordingWarning />}
                        </div>
                    </div>
                </div>
            )}
            <Preview
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
        : PREMEETING_BUTTONS).filter((b: any) => !(hiddenPremeetingButtons || []).includes(b));

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

export default connect(mapStateToProps)(PreMeetingScreen);
