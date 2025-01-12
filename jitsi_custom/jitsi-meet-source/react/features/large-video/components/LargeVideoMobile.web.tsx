import React, { Component } from 'react';
import { connect, useSelector } from 'react-redux';

// @ts-expect-error
import VideoLayout from '../../../../modules/UI/videolayout/VideoLayout';
import { IReduxState, IStore } from '../../app/types';
import { MEDIA_TYPE, VIDEO_TYPE } from '../../base/media/constants';
import { getLocalParticipant, isScreenShareParticipantById } from '../../base/participants/functions';
import Watermarks from '../../base/react/components/web/Watermarks';
import { getHideSelfView } from '../../base/settings/functions.any';
import {
    getVideoTrackByParticipant,
    isLocalTrackMuted,
    isParticipantAudioMuted,
    isRemoteTrackMuted
} from '../../base/tracks/functions.web';
import { setColorAlpha } from '../../base/util/helpers';
import StageParticipantNameLabel from '../../display-name/components/web/StageParticipantNameLabel';
import {
    COMPASS_HORIZONTAL_FILMSTRIP_HEIGHT,
    FILMSTRIP_BREAKPOINT, FILMSTRIP_TYPE,
    STATS_POPOVER_POSITION
} from '../../filmstrip/constants';
import {
    getThumbnailTypeFromLayout,
    getVerticalViewMaxWidth,
    isFilmstripResizable
} from '../../filmstrip/functions.web';
import SharedVideo from '../../shared-video/components/web/SharedVideo';
import Captions from '../../subtitles/components/web/Captions';
import { setTileView } from '../../video-layout/actions.web';
import Whiteboard from '../../whiteboard/components/web/Whiteboard';
import { isWhiteboardEnabled } from '../../whiteboard/functions';
import { setSeeWhatIsBeingShared } from '../actions.web';
import { getLargeVideoParticipant } from '../functions';

import ScreenSharePlaceholder from './ScreenSharePlaceholder.web';
import StageParticipantTopIndicators from "../../display-name/components/web/StageParticipantTopIndicators";
import { getCurrentLayout } from "../../video-layout/functions.any";
import { LAYOUTS } from "../../video-layout/constants";
import { isMobileBrowser } from "../../base/environment/utils";
import ConnectionIndicator from "../../connection-indicator/components/web/ConnectionIndicator";
import AudioMutedIndicator from "../../filmstrip/components/web/AudioMutedIndicator";
import ConferenceTimer from "../../conference/components/ConferenceTimer";

interface IProps {

    /**
     * The alpha(opacity) of the background.
     */
    _backgroundAlpha?: number;

    /**
     * The user selected background image url.
     */
    _customBackgroundImageUrl: string;

    /**
     * Whether or not the hideSelfView is enabled.
     */
    _hideSelfView: boolean;

    /**
     * Prop that indicates whether the chat is open.
     */
    _isChatOpen: boolean;

    /**
     * Whether or not the screen share is on large-video.
     */
    _isScreenSharing: boolean;

    /**
     * Whether or not the local screen share is on large-video.
     */
    _isLocalScreenSharing: boolean;

    /**
     * The large video participant id.
     */
    _largeVideoParticipantId: string;

    /**
     * Local Participant id.
     */
    _localParticipantId: string;

    /**
     * Used to determine the value of the autoplay attribute of the underlying
     * video element.
     */
    _noAutoPlayVideo: boolean;

    /**
     * Whether or not the filmstrip is resizable.
     */
    _resizableFilmstrip: boolean;

    /**
     * Whether or not the screen sharing is visible.
     */
    _seeWhatIsBeingShared: boolean;

    /**
     * The width of the vertical filmstrip (user resized).
     */
    _verticalFilmstripWidth?: number | null;

    /**
     * The max width of the vertical filmstrip.
     */
    _verticalViewMaxWidth: number;

    /**
     * Whether or not the filmstrip is visible.
     */
    _visibleFilmstrip: boolean;

    /**
     * Whether or not the whiteboard is ready to be used.
     */
    _whiteboardEnabled: boolean;

    /**
     * Whether or not in a pip mode.
     */
    _isInPipMode: boolean;

    _remoteParticipantsLength: number;

    /**
     * The current layout of the filmstrip.
     */
    _currentLayout?: string;

    _connectionIndicatorDisabled: boolean;
    _showConnectionIndicator: boolean;

    _thumbnailType: string;

    _isLargeVideoParticipantMuted: boolean;

    _showAudioMutedIndicator: boolean;
    _largeVideoParticipantName: string;

    /**
     * The Redux dispatch function.
     */
    dispatch: IStore['dispatch'];
}

/** .
 * Implements a React {@link Component} which represents the large video (a.k.a.
 * The conference participant who is on the local stage) on Web/React.
 *
 * @augments Component
 */
class LargeVideoMobile extends Component<IProps> {
    _tappedTimeout: number | undefined;

    _containerRef: React.RefObject<HTMLDivElement>;

    _wrapperRef: React.RefObject<HTMLDivElement>;

    /**
     * Constructor of the component.
     *
     * @inheritdoc
     */
    constructor(props: IProps) {
        super(props);

        this._containerRef = React.createRef<HTMLDivElement>();
        this._wrapperRef = React.createRef<HTMLDivElement>();

        this._clearTapTimeout = this._clearTapTimeout.bind(this);
        this._onDoubleTap = this._onDoubleTap.bind(this);
        this._updateLayout = this._updateLayout.bind(this);
    }

    /**
     * Implements {@code Component#componentDidUpdate}.
     *
     * @inheritdoc
     */
    componentDidUpdate(prevProps: IProps) {
        const {
            _visibleFilmstrip,
            _isLocalScreenSharing,
            _seeWhatIsBeingShared,
            _largeVideoParticipantId,
            _hideSelfView,
            _localParticipantId,
            _currentLayout,
            _remoteParticipantsLength
        } = this.props;

        if (prevProps._visibleFilmstrip !== _visibleFilmstrip) {
            this._updateLayout();
        }

        if (prevProps._isLocalScreenSharing !== _isLocalScreenSharing && !_isLocalScreenSharing) {
            this.props.dispatch(setSeeWhatIsBeingShared(false));
        }

        if (_isLocalScreenSharing && _seeWhatIsBeingShared) {
            VideoLayout.updateLargeVideo(_largeVideoParticipantId, true, true);
        }

        if (_largeVideoParticipantId === _localParticipantId
            && prevProps._hideSelfView !== _hideSelfView) {
            VideoLayout.updateLargeVideo(_largeVideoParticipantId, true, false);
        }

        if ((prevProps._currentLayout !== _currentLayout || prevProps._remoteParticipantsLength !== _remoteParticipantsLength) && _currentLayout !== LAYOUTS.TILE_VIEW) {
            this._updateLayout();
        }
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {React$Element}
     */
    render() {
        const {
            _isChatOpen,
            _noAutoPlayVideo,
            _whiteboardEnabled,
            _isInPipMode,
            _remoteParticipantsLength,
            _connectionIndicatorDisabled,
            _showConnectionIndicator,
            _thumbnailType,
            _largeVideoParticipantId,
            _isLargeVideoParticipantMuted,
            _showAudioMutedIndicator,
            _largeVideoParticipantName,
            _isScreenSharing,
        } = this.props;
        const style = this._getCustomStyles();
        const className = `videocontainer${_isChatOpen ? ' shift-right' : ''}${isMobileBrowser() ? ' is-mobile' : ''}`;
        const { NORMAL = 16 } = interfaceConfig.INDICATOR_FONT_SIZES || {};

        return (
            <div
                className = {className}
                id = 'largeVideoContainer'
                ref = {this._containerRef}
                style = {style}>
                <div
                    id = 'largeVideoWrapperContainer'
                    className = {_remoteParticipantsLength > 0 ? 'have-remote-participants' : 'no-remote-participants'}
                    style = {{
                        backgroundColor: "rgba(255, 255, 255, 0.02)",
                        borderRadius: "4px",
                        width: '100%',
                        height: '100%',
                        inset: 0,
                        margin: 0,
                    }}>
                    {!_connectionIndicatorDisabled && !_isLargeVideoParticipantMuted && (
                        <div style = {{
                            position: "absolute",
                            top: '68px', // top panel height(60px) + padding(8px)
                            left: '22px',
                            zIndex: 1,
                        }}>
                            <ConnectionIndicator
                                alwaysVisible = {_showConnectionIndicator}
                                enableStatsDisplay = {false}
                                iconSize = {NORMAL}
                                participantId = {_largeVideoParticipantId}
                                statsPopoverPosition = {STATS_POPOVER_POSITION[_thumbnailType]} />
                        </div>
                    )}
                    <SharedVideo />
                    {_whiteboardEnabled && <Whiteboard />}
                    <div id = 'etherpad' />

                    <Watermarks />

                    <div
                        id = 'dominantSpeaker'
                        onTouchEnd = {this._onDoubleTap}
                        className = {`is-mobile ${_isInPipMode ? 'pipMode ' : ''}`}>
                        {!_connectionIndicatorDisabled && _isLargeVideoParticipantMuted && (
                            <div style = {{
                                position: 'absolute',
                                top: '33px',
                                left: '33px',
                                zIndex: 1,
                            }}>
                                <ConnectionIndicator
                                    alwaysVisible = {_showConnectionIndicator}
                                    enableStatsDisplay = {false}
                                    iconSize = {NORMAL}
                                    participantId = {_largeVideoParticipantId}
                                    statsPopoverPosition = {STATS_POPOVER_POSITION[_thumbnailType]} />
                            </div>
                        )}
                        <div className = {`dynamic-shadow ${_isInPipMode ? 'pipMode' : ''}`} />
                        <div id = 'dominantSpeakerAvatarContainer'
                             className = {`${_isInPipMode ? 'pipMode ' : ''}`}
                             style = {{ pointerEvents: 'none' }} />
                        {_isLargeVideoParticipantMuted && (
                            <div style = {{
                                display: 'flex',
                                justifyContent: 'center',
                                alignItems: 'center',
                                flexDirection: 'column',
                                gap: '12px',
                                position: 'relative',
                                marginTop: ' 82px', // #dominantSpeakerAvatarContainer top (50px) + margin-top (32px)
                            }}>
                                <div style = {{
                                    display: 'flex',
                                    gap: '4px',
                                    fontFamily: 'Lato Bold',
                                    fontWeight: 'normal' as const,
                                    fontSize: '24px',
                                    lineHeight: '29px',
                                    color: 'rgba(255, 255, 255, 1)',
                                    maxWidth: '100%',
                                }}>
                                    {_showAudioMutedIndicator &&
                                        <AudioMutedIndicator tooltipPosition = 'top'
                                                             iconColor = 'rgba(255, 255, 255, 1)'
                                                             iconSize = {24} />}
                                    <div style = {{
                                        overflow: 'hidden',
                                        textOverflow: 'ellipsis',
                                        whiteSpace: 'nowrap',
                                        maxWidth: '100%',
                                    }}>{_largeVideoParticipantName}</div>
                                </div>
                                <ConferenceTimer textStyle = {{
                                    fontFamily: 'Lato Regular',
                                    fontWeight: 'normal' as const,
                                    fontSize: '15px',
                                    lineHeight: '20px',
                                    letterSpacing: '-0.16px',
                                    color: 'rgba(255, 255, 255, 0.8)',
                                }} />
                            </div>
                        )}
                    </div>
                    <div id = 'remotePresenceMessage' />
                    <div id = 'largeVideoElementsContainer'>
                        <div id = 'largeVideoCompassContainer'>
                            <div id = 'largeVideoBackgroundContainer'>
                                <div className = "large-video-background">
                                    <div className = "animation_spin500ms preloader25">
                                        <svg width = "25" height = "25" viewBox = "0 0 25 25" fill = "none"
                                             xmlns = "http://www.w3.org/2000/svg">
                                            <path
                                                d = "M21.3388 3.66116C23.087 5.40932 24.2775 7.63661 24.7598 10.0614C25.2421 12.4861 24.9946 14.9995 24.0485 17.2835C23.1024 19.5676 21.5002 21.5199 19.4446 22.8934C17.389 24.2669 14.9723 25 12.5 25C10.0277 25 7.61098 24.2669 5.55537 22.8934C3.49976 21.5199 1.8976 19.5676 0.951505 17.2835C0.00540978 14.9995 -0.242131 12.4861 0.240185 10.0614C0.722501 7.6366 1.91301 5.40932 3.66117 3.66116L5.2528 5.25279C3.81943 6.68615 2.8433 8.51237 2.44784 10.5005C2.05237 12.4886 2.25534 14.5494 3.03107 16.4222C3.8068 18.2949 5.12045 19.8956 6.80591 21.0218C8.49136 22.148 10.4729 22.7491 12.5 22.7491C14.5271 22.7491 16.5086 22.148 18.1941 21.0218C19.8795 19.8956 21.1932 18.2949 21.9689 16.4222C22.7447 14.5494 22.9476 12.4886 22.5522 10.5005C22.1567 8.51237 21.1806 6.68615 19.7472 5.25279L21.3388 3.66116Z"
                                                fill = "#BFBFC1"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                id = 'largeVideoWrapper'
                                className = 'is-mobile'
                                onTouchEnd = {this._onDoubleTap}
                                ref = {this._wrapperRef}
                                role = 'figure'>
                                <video
                                    className = {`is-mobile ${_isScreenSharing ? 'screenshare' : ''}`}
                                    autoPlay = {!_noAutoPlayVideo}
                                    id = 'largeVideo'
                                    muted = {true}
                                    playsInline = {true} /* for Safari on iOS to work */ />
                            </div>
                        </div>
                    </div>
                    <StageParticipantTopIndicators />
                </div>
            </div>
        );
    }

    /**
     * Refreshes the video layout to determine the dimensions of the stage view.
     * If the filmstrip is toggled it adds CSS transition classes and removes them
     * when the transition is done.
     *
     * @returns {void}
     */
    _updateLayout() {
        const { _verticalFilmstripWidth, _resizableFilmstrip } = this.props;

        if (_resizableFilmstrip && Number(_verticalFilmstripWidth) >= FILMSTRIP_BREAKPOINT) {
            this._containerRef.current?.classList.add('transition');
            this._wrapperRef.current?.classList.add('transition');
            VideoLayout.refreshLayout();

            setTimeout(() => {
                this._containerRef?.current && this._containerRef.current.classList.remove('transition');
                this._wrapperRef?.current && this._wrapperRef.current.classList.remove('transition');
            }, 1000);
        } else {
            VideoLayout.refreshLayout();
        }
    }

    /**
     * Clears the '_tappedTimout'.
     *
     * @private
     * @returns {void}
     */
    _clearTapTimeout() {
        clearTimeout(this._tappedTimeout);
        this._tappedTimeout = undefined;
    }

    /**
     * Creates the custom styles object.
     *
     * @private
     * @returns {Object}
     */
    _getCustomStyles() {
        const styles: any = {};
        const {
            _customBackgroundImageUrl,
            _verticalFilmstripWidth,
            _verticalViewMaxWidth,
            _visibleFilmstrip
        } = this.props;

        styles.backgroundColor = "rgba(23, 23, 23, 1)";

        if (this.props._backgroundAlpha !== undefined) {
            const alphaColor = setColorAlpha(styles.backgroundColor, this.props._backgroundAlpha);

            styles.backgroundColor = alphaColor;
        }

        if (_customBackgroundImageUrl) {
            styles.backgroundImage = `url(${_customBackgroundImageUrl})`;
            styles.backgroundSize = 'cover';
        }

        if (_visibleFilmstrip && Number(_verticalFilmstripWidth) >= FILMSTRIP_BREAKPOINT) {
            styles.width = `calc(100% - ${_verticalViewMaxWidth || 0}px)`;
        }

        return styles;
    }

    /**
     * Sets view to tile view on double tap.
     *
     * @param {Object} e - The event.
     * @private
     * @returns {void}
     */
    _onDoubleTap(e: React.TouchEvent) {
        e.stopPropagation();
        e.preventDefault();

        if (this._tappedTimeout) {
            this._clearTapTimeout();
            this.props.dispatch(setTileView(true));
        } else {
            this._tappedTimeout = window.setTimeout(this._clearTapTimeout, 300);
        }
    }
}


/**
 * Maps (parts of) the Redux state to the associated LargeVideoMobile props.
 *
 * @param {Object} state - The Redux state.
 * @private
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState) {
    const testingConfig = state['features/base/config'].testing;
    const { backgroundImageUrl } = state['features/dynamic-branding'];
    const { isOpen: isChatOpen } = state['features/chat'];
    const { width: verticalFilmstripWidth, visible } = state['features/filmstrip'];
    const seeWhatIsBeingShared = true;
    const localParticipantId = getLocalParticipant(state)?.id;
    const largeVideoParticipant = getLargeVideoParticipant(state);
    const videoTrack = getVideoTrackByParticipant(state, largeVideoParticipant);
    const isScreenshareOnLargeVideo = videoTrack?.videoType === VIDEO_TYPE.DESKTOP;
    const isLocalScreenshareOnLargeVideo = largeVideoParticipant?.id?.includes(localParticipantId ?? '')
        && isScreenshareOnLargeVideo;
    const { is_in_picture_in_picture_mode } = state['features/picture-in-picture'];
    const { remoteParticipants } = state['features/filmstrip'];
    const _currentLayout = getCurrentLayout(state) ?? '';
    const _connectionIndicatorAutoHideEnabled = Boolean(state['features/base/config'].connectionIndicators?.autoHide ?? true);
    const connectionIndicatorDisabled = _currentLayout !== LAYOUTS.HORIZONTAL_FILMSTRIP_VIEW
        || Boolean(state['features/base/config'].connectionIndicators?.disabled);
    const showConnectionIndicator = !_connectionIndicatorAutoHideEnabled;
    const tileType = getThumbnailTypeFromLayout(_currentLayout, FILMSTRIP_TYPE.MAIN) ?? '';
    const _isLargeVideoParticipantMuted = videoTrack === undefined || videoTrack?.muted;
    const largeVideoParticipantName = largeVideoParticipant?.name ?? '';
    const isLargeParticipantAudioMuted = largeVideoParticipant === undefined ? true : isParticipantAudioMuted(largeVideoParticipant, state);

    return {
        _backgroundAlpha: state['features/base/config'].backgroundAlpha,
        _customBackgroundImageUrl: backgroundImageUrl,
        _hideSelfView: getHideSelfView(state),
        _isChatOpen: isChatOpen,
        _isScreenSharing: Boolean(isScreenshareOnLargeVideo),
        _isLocalScreenSharing: Boolean(isLocalScreenshareOnLargeVideo),
        _largeVideoParticipantId: largeVideoParticipant?.id ?? '',
        _localParticipantId: localParticipantId ?? '',
        _noAutoPlayVideo: Boolean(testingConfig?.noAutoPlayVideo),
        _resizableFilmstrip: isFilmstripResizable(state),
        _seeWhatIsBeingShared: Boolean(seeWhatIsBeingShared),
        _verticalFilmstripWidth: verticalFilmstripWidth.current,
        _verticalViewMaxWidth: getVerticalViewMaxWidth(state),
        _visibleFilmstrip: visible,
        _whiteboardEnabled: isWhiteboardEnabled(state),
        _isInPipMode: is_in_picture_in_picture_mode,
        _remoteParticipantsLength: remoteParticipants.length,
        _currentLayout,
        _connectionIndicatorDisabled: connectionIndicatorDisabled,
        _showConnectionIndicator: showConnectionIndicator,
        _thumbnailType: tileType,
        _isLargeVideoParticipantMuted,
        _showAudioMutedIndicator: isLargeParticipantAudioMuted,
        _largeVideoParticipantName: largeVideoParticipantName,
    };
}

export default connect(_mapStateToProps)(LargeVideoMobile);
