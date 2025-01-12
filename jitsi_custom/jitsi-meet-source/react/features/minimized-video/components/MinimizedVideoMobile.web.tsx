import React, { Component } from 'react';
import { connect } from 'react-redux';

// @ts-expect-error
import VideoLayout from '../../../../modules/UI/videolayout/VideoLayout';
import { IReduxState, IStore } from '../../app/types';
import { VIDEO_TYPE } from '../../base/media/constants';
import { getLocalParticipant } from '../../base/participants/functions';
import { getHideSelfView } from '../../base/settings/functions.any';
import { getVideoTrackByParticipant } from '../../base/tracks/functions.web';
import { setColorAlpha } from '../../base/util/helpers';
import { FILMSTRIP_BREAKPOINT } from '../../filmstrip/constants';
import { isFilmstripResizable, isStageFilmstripAvailable } from '../../filmstrip/functions.web';
import { setSeeWhatIsBeingShared } from '../actions.web';
import { getMinimizedVideoParticipant } from '../functions';
import { getCurrentLayout } from "../../video-layout/functions.any";
import { LAYOUTS } from "../../video-layout/constants";
import { isMobileBrowser } from "../../base/environment/utils";
import { pinParticipant } from "../../base/participants/actions";
import { IParticipant } from "../../base/participants/types";
import { togglePinStageParticipant } from "../../filmstrip/actions.web";

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
     * Whether or not the local screen share is on minimized-video.
     */
    _isScreenSharing: boolean;

    /**
     * The minimized video participant id.
     */
    _minimizedVideoParticipantId: string;

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
     * Whether or not the filmstrip is visible.
     */
    _visibleFilmstrip: boolean;

    _remoteParticipantsLength: number;

    /**
     * The current layout of the filmstrip.
     */
    _currentLayout?: string;

    /**
     * An object with information about the participant related to the thumbnail.
     */
    _minimizedParticipant?: IParticipant;

    /**
     * Whether or not the current layout is stage filmstrip layout.
     */
    _stageFilmstripLayout: boolean;

    _isMinimizedVideoParticipantMuted: boolean;

    /**
     * The Redux dispatch function.
     */
    dispatch: IStore['dispatch'];
}

/** .
 * Implements a React {@link Component} which represents the minimized video (a.k.a.
 * The conference participant who is on the local stage) on Web/React.
 *
 * @augments Component
 */
class MinimizedVideoMobile extends Component<IProps> {
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

        this._updateLayout = this._updateLayout.bind(this);
        this._onClick = this._onClick.bind(this);
        this._onTouchStart = this._onTouchStart.bind(this);
    }

    /**
     * Implements {@code Component#componentDidUpdate}.
     *
     * @inheritdoc
     */
    componentDidUpdate(prevProps: IProps) {
        const {
            _visibleFilmstrip,
            _isScreenSharing,
            _seeWhatIsBeingShared,
            _minimizedVideoParticipantId,
            _hideSelfView,
            _localParticipantId,
            _currentLayout,
            _remoteParticipantsLength
        } = this.props;

        if (prevProps._visibleFilmstrip !== _visibleFilmstrip) {
            this._updateLayout();
        }

        if (prevProps._isScreenSharing !== _isScreenSharing && !_isScreenSharing) {
            this.props.dispatch(setSeeWhatIsBeingShared(false));
        }

        if (_isScreenSharing && _seeWhatIsBeingShared) {
            VideoLayout.updateMinimizedVideo(_minimizedVideoParticipantId, true, true);
        }

        if (_minimizedVideoParticipantId === _localParticipantId
            && prevProps._hideSelfView !== _hideSelfView) {
            VideoLayout.updateMinimizedVideo(_minimizedVideoParticipantId, true, false);
        }

        if ((prevProps._currentLayout !== _currentLayout || prevProps._remoteParticipantsLength !== _remoteParticipantsLength) && _currentLayout !== LAYOUTS.TILE_VIEW) {
            this._updateLayout();
        }
    }

    /**
     * On click handler.
     *
     * @returns {void}
     */
    _onClick() {
        const { _minimizedParticipant, dispatch, _stageFilmstripLayout } = this.props;

        if (_minimizedParticipant) {
            const { id, pinned } = _minimizedParticipant;

            if (_stageFilmstripLayout) {
                dispatch(togglePinStageParticipant(id));
            } else {
                dispatch(pinParticipant(pinned ? null : id));
            }
        }
    }

    /**
     * Handler for touch start.
     *
     * @returns {void}
     */
    _onTouchStart(e: React.TouchEvent) {
        e.stopPropagation();
        this._onClick();
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {React$Element}
     */
    render() {
        const {
            _noAutoPlayVideo,
        } = this.props;
        const style = this._getCustomStyles();
        const className = 'videocontainer is-mobile';

        if (!isMobileBrowser()) {
            return <></>;
        }

        return (
            <div
                className = {className}
                id = 'minimizedVideoContainer'
                ref = {this._containerRef}
                style = {style}>
                <div
                    id = 'minimizedVideoWrapperContainer'
                    {...({
                        onTouchStart: this._onTouchStart
                    })}>
                    <div id = 'minimizedSpeaker'>
                        <div id = 'minimizedSpeakerAvatarContainer' style = {{ pointerEvents: 'none' }} />
                    </div>
                    <div id = 'minimizedVideoElementsContainer'>
                        <div id = 'minimizedVideoCompassContainer'>
                            <div id = 'minimizedVideoBackgroundContainer'>
                                <div className = "minimized-video-background">
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
                                id = 'minimizedVideoWrapper'
                                className = 'is-mobile'
                                ref = {this._wrapperRef}
                                role = 'figure'>
                                <video
                                    className = 'is-mobile'
                                    autoPlay = {!_noAutoPlayVideo}
                                    id = 'minimizedVideo'
                                    muted = {true}
                                    playsInline = {true} /* for Safari on iOS to work */ />
                            </div>
                        </div>
                    </div>
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
     * Creates the custom styles object.
     *
     * @private
     * @returns {Object}
     */
    _getCustomStyles() {
        const styles: any = {};
        const {
            _customBackgroundImageUrl,
            _remoteParticipantsLength,
            _currentLayout,
            _isMinimizedVideoParticipantMuted,
        } = this.props;

        if (this.props._backgroundAlpha !== undefined) {
            styles.backgroundColor = setColorAlpha(styles.backgroundColor, this.props._backgroundAlpha);
        }

        if (_customBackgroundImageUrl) {
            styles.backgroundImage = `url(${_customBackgroundImageUrl})`;
            styles.backgroundSize = 'cover';
        }

        if (_currentLayout !== LAYOUTS.HORIZONTAL_FILMSTRIP_VIEW || _remoteParticipantsLength < 1 || _isMinimizedVideoParticipantMuted) {
            styles.display = 'none';
        }

        return styles;
    }
}


/**
 * Maps (parts of) the Redux state to the associated MinimizedVideoMobile props.
 *
 * @param {Object} state - The Redux state.
 * @private
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState) {
    const testingConfig = state['features/base/config'].testing;
    const { backgroundImageUrl } = state['features/dynamic-branding'];
    const { width: verticalFilmstripWidth, visible } = state['features/filmstrip'];
    const seeWhatIsBeingShared = true;
    const localParticipantId = getLocalParticipant(state)?.id;
    const minimizedVideoParticipant = getMinimizedVideoParticipant(state);
    const videoTrack = getVideoTrackByParticipant(state, minimizedVideoParticipant);
    const isLocalScreenshareOnMinimizedVideo = minimizedVideoParticipant?.id?.includes(localParticipantId ?? '')
        && videoTrack?.videoType === VIDEO_TYPE.DESKTOP;
    const { remoteParticipants } = state['features/filmstrip'];
    const _remoteParticipantsLength = remoteParticipants.length ?? 0;
    const _currentLayout = getCurrentLayout(state);
    const _isMinimizedVideoParticipantMuted = videoTrack === undefined || videoTrack?.muted;

    return {
        _backgroundAlpha: state['features/base/config'].backgroundAlpha,
        _customBackgroundImageUrl: backgroundImageUrl,
        _hideSelfView: getHideSelfView(state),
        _isScreenSharing: Boolean(isLocalScreenshareOnMinimizedVideo),
        _minimizedVideoParticipantId: minimizedVideoParticipant?.id ?? '',
        _localParticipantId: localParticipantId ?? '',
        _noAutoPlayVideo: Boolean(testingConfig?.noAutoPlayVideo),
        _resizableFilmstrip: isFilmstripResizable(state),
        _seeWhatIsBeingShared: Boolean(seeWhatIsBeingShared),
        _verticalFilmstripWidth: verticalFilmstripWidth.current,
        _visibleFilmstrip: visible,
        _remoteParticipantsLength,
        _currentLayout,
        _minimizedParticipant: minimizedVideoParticipant,
        _stageFilmstripLayout: isStageFilmstripAvailable(state),
        _isMinimizedVideoParticipantMuted
    };
}

export default connect(_mapStateToProps)(MinimizedVideoMobile);
