import { throttle } from 'lodash-es';
import React from 'react';
import { WithTranslation } from 'react-i18next';
import { batch, connect as reactReduxConnect } from 'react-redux';

// @ts-expect-error
import VideoLayout from '../../../../../modules/UI/videolayout/VideoLayout';
import { IReduxState, IStore } from '../../../app/types';
import { getConferenceNameForTitle } from '../../../base/conference/functions';
import { hangup } from '../../../base/connection/actions.web';
import { isMobileBrowser } from '../../../base/environment/utils';
import { translate } from '../../../base/i18n/functions';
import { setColorAlpha } from '../../../base/util/helpers';
import ScreenshareFilmstrip from '../../../filmstrip/components/web/ScreenshareFilmstrip';
import CalleeInfoContainer from '../../../invite/components/callee-info/CalleeInfoContainer';
import LobbyScreen from '../../../lobby/components/web/LobbyScreen';
import { getIsLobbyVisible } from '../../../lobby/functions';
import { getOverlayToRender } from '../../../overlay/functions.web';
import { isPrejoinPageVisible } from '../../../prejoin/functions';
import ReactionAnimations from '../../../reactions/components/web/ReactionsAnimations';
import { toggleToolboxVisible } from '../../../toolbox/actions.any';
import { fullScreenChanged, hideToolbox, setOverflowMenuVisible, showToolbox } from '../../../toolbox/actions.web';
import { LAYOUT_CLASSNAMES_MOBILE } from '../../../video-layout/constants';
import { getCurrentLayout } from '../../../video-layout/functions.any';
import VisitorsQueue from '../../../visitors/components/web/VisitorsQueue';
import { showVisitorsQueue } from '../../../visitors/functions';
import { init } from '../../actions.web';
import { maybeShowSuboptimalExperienceNotification } from '../../functions.web';
import type { AbstractProps } from '../AbstractConference';
import { AbstractConference, abstractMapStateToProps } from '../AbstractConference';
import { default as Notice } from './Notice';
import { setIsInPictureInPictureMode } from "../../../picture-in-picture/actions.web";
import { setFilmstripVisible, setTopPanelVisible } from "../../../filmstrip/actions.web";
import { setTileView } from "../../../video-layout/actions.any";
import PrejoinMobile from "../../../prejoin/components/web/PrejoinMobile";
import ConferenceInfoMobile from "./ConferenceInfoMobile";
import CompassToolboxMobile from "../../../toolbox/components/web/CompassToolboxMobile";
import MainFilmstripMobile from "../../../filmstrip/components/web/MainFilmstripMobile";
import StageFilmstripMobile from "../../../filmstrip/components/web/StageFilmstripMobile";
import LargeVideoMobile from "../../../large-video/components/LargeVideoMobile.web";
import ParticipantsPaneMobile from "../../../participants-pane/components/web/ParticipantsPaneMobile";
import MinimizedVideoMobile from "../../../minimized-video/components/MinimizedVideoMobile.web";
import ChatMobile from "../../../chat/components/web/ChatMobile";

/**
 * DOM events for when full screen mode has changed. Different browsers need
 * different vendor prefixes.
 *
 * @private
 * @type {Array<string>}
 */
const FULL_SCREEN_EVENTS = [
    'webkitfullscreenchange',
    'mozfullscreenchange',
    'fullscreenchange'
];

/**
 * The type of the React {@code Component} props of {@link ConferenceMobile}.
 */
interface IProps extends AbstractProps, WithTranslation {

    /**
     * The alpha(opacity) of the background.
     */
    _backgroundAlpha?: number;

    /**
     * Are any overlays visible?
     */
    _isAnyOverlayVisible: boolean;

    /**
     * The CSS class to apply to the root of {@link ConferenceMobile} to modify the
     * application layout.
     */
    _layoutClassName: string;

    /**
     * The config specified interval for triggering mouseMoved iframe api events.
     */
    _mouseMoveCallbackInterval?: number;

    /**
     *Whether or not the notifications should be displayed in the overflow drawer.
     */
    _overflowDrawer: boolean;

    /**
     * Name for this conference room.
     */
    _roomName: string;

    /**
     * If lobby page is visible or not.
     */
    _showLobby: boolean;

    /**
     * If prejoin page is visible or not.
     */
    _showPrejoin: boolean;

    /**
     * If visitors queue page is visible or not.
     */
    _showVisitorsQueue: boolean;

    /**
     * If pip mode enabled or not.
     */
    _isInPictureInPictureMode: boolean;

    _lobbyKnocking: boolean;

    dispatch: IStore['dispatch'];
}

/**
 * The conference mobile page of the Web application.
 */
class ConferenceMobile extends AbstractConference<IProps, any> {
    _originalOnMouseMove: Function;
    _originalOnShowToolbar: Function;

    /**
     * Initializes a new ConferenceMobile instance.
     *
     * @param {Object} props - The read-only properties with which the new
     * instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        const { _mouseMoveCallbackInterval } = props;

        // Throttle and bind this component's mousemove handler to prevent it
        // from firing too often.
        this._originalOnShowToolbar = this._onShowToolbar;
        this._originalOnMouseMove = this._onMouseMove;

        this._onShowToolbar = throttle(
            () => this._originalOnShowToolbar(),
            100,
            {
                leading: true,
                trailing: false
            });

        this._onMouseMove = throttle(
            event => this._originalOnMouseMove(event),
            _mouseMoveCallbackInterval,
            {
                leading: true,
                trailing: false
            });

        // Bind event handler so it is only bound once for every instance.
        this._onFullScreenChange = this._onFullScreenChange.bind(this);
        this._onVidespaceTouchStart = this._onVidespaceTouchStart.bind(this);
        this._setBackground = this._setBackground.bind(this);
    }

    /**
     * Start the connection and get the UI ready for the conference.
     *
     * @inheritdoc
     */
    componentDidMount() {
        document.title = "Видеоконференция Compass";
        this._start();
    }

    /**
     * Calls into legacy UI to update the application layout, if necessary.
     *
     * @inheritdoc
     * returns {void}
     */
    componentDidUpdate(prevProps: IProps) {
        if (this.props._shouldDisplayTileView
            === prevProps._shouldDisplayTileView) {
            return;
        }

        // TODO: For now VideoLayout is being called as LargeVideo and Filmstrip
        // sizing logic is still handled outside of React. Once all components
        // are in react they should calculate size on their own as much as
        // possible and pass down sizings.
        VideoLayout.refreshLayout();
    }

    /**
     * Disconnect from the conference when component will be
     * unmounted.
     *
     * @inheritdoc
     */
    componentWillUnmount() {
        APP.UI.unbindEvents();

        FULL_SCREEN_EVENTS.forEach(name =>
            document.removeEventListener(name, this._onFullScreenChange));

        APP.conference.isJoined() && this.props.dispatch(hangup());
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const {
            _isAnyOverlayVisible,
            _layoutClassName,
            _notificationsVisible,
            _overflowDrawer,
            _showLobby,
            _showPrejoin,
            _showVisitorsQueue,
            _isInPictureInPictureMode,
            _lobbyKnocking,
            t
        } = this.props;

        // функция для android доступная из dom, чтобы вызывать ее с kotlin
        // @ts-ignore
        window.dispatchIsInPictureInPictureMode = (isInPipMode: boolean) => {
            this.props.dispatch(setIsInPictureInPictureMode(isInPipMode));

            if (isInPipMode) {
                this.props.dispatch(hideToolbox());
                this.props.dispatch(setTopPanelVisible(false));
                this.props.dispatch(setFilmstripVisible(false));
                batch(() => {
                    this.props.dispatch(setTileView(false));
                    this.props.dispatch(setOverflowMenuVisible(false));
                });
            }
        };

        return (
            <div
                id = 'layout_wrapper'
                onMouseEnter = {this._onMouseEnter}
                onMouseLeave = {this._onMouseLeave}
                onMouseMove = {this._onMouseMove}
                ref = {this._setBackground}>
                <div
                    className = {_layoutClassName}
                    id = 'videoconference_page'>
                    <ConferenceInfoMobile />
                    {!_isInPictureInPictureMode && (
                        <Notice />
                    )}
                    <div
                        id = 'videospace'
                        onTouchStart = {this._onVidespaceTouchStart}>
                        <MinimizedVideoMobile />
                        <LargeVideoMobile />
                        {_showPrejoin || _showLobby || (<>
                            <StageFilmstripMobile />
                            <ScreenshareFilmstrip />
                            <MainFilmstripMobile />
                        </>)}
                    </div>

                    {_lobbyKnocking ? <>
                            <span
                                aria-level = {1}
                                className = 'sr-only'
                                role = 'heading'>
                                {t('toolbar.accessibilityLabel.heading')}
                            </span>
                            <CompassToolboxMobile isLobby = {true} />
                        </> :
                        (_showPrejoin || _showLobby || (
                            <>
                            <span
                                aria-level = {1}
                                className = 'sr-only'
                                role = 'heading'>
                                {t('toolbar.accessibilityLabel.heading')}
                            </span>
                                <CompassToolboxMobile />
                            </>
                        ))}

                    {this.renderNotificationsContainer()}

                    <CalleeInfoContainer />

                    {(_showPrejoin && !_showVisitorsQueue) && <PrejoinMobile />}
                    {(_showLobby && !_showVisitorsQueue) && <LobbyScreen />}
                    {_showVisitorsQueue && <VisitorsQueue />}
                </div>
                <ChatMobile />
                <ParticipantsPaneMobile />
                <ReactionAnimations />
            </div>
        );
    }

    /**
     * Sets custom background opacity based on config. It also applies the
     * opacity on parent element, as the parent element is not accessible directly,
     * only though it's child.
     *
     * @param {Object} element - The DOM element for which to apply opacity.
     *
     * @private
     * @returns {void}
     */
    _setBackground(element: HTMLDivElement) {
        if (!element) {
            return;
        }

        if (this.props._backgroundAlpha !== undefined) {
            const elemColor = element.style.background;
            const alphaElemColor = setColorAlpha(elemColor, this.props._backgroundAlpha);

            element.style.background = alphaElemColor;
            if (element.parentElement) {
                const parentColor = element.parentElement.style.background;
                const alphaParentColor = setColorAlpha(parentColor, this.props._backgroundAlpha);

                element.parentElement.style.background = alphaParentColor;
            }
        }
    }

    /**
     * Handler used for touch start on Video container.
     *
     * @private
     * @returns {void}
     */
    _onVidespaceTouchStart() {
        this.props.dispatch(toggleToolboxVisible());
    }

    /**
     * Updates the Redux state when full screen mode has been enabled or
     * disabled.
     *
     * @private
     * @returns {void}
     */
    _onFullScreenChange() {
        this.props.dispatch(fullScreenChanged(APP.UI.isFullScreen()));
    }

    /**
     * Triggers iframe API mouseEnter event.
     *
     * @param {MouseEvent} event - The mouse event.
     * @private
     * @returns {void}
     */
    _onMouseEnter(event: React.MouseEvent) {
        APP.API.notifyMouseEnter(event);
    }

    /**
     * Triggers iframe API mouseLeave event.
     *
     * @param {MouseEvent} event - The mouse event.
     * @private
     * @returns {void}
     */
    _onMouseLeave(event: React.MouseEvent) {
        APP.API.notifyMouseLeave(event);
    }

    /**
     * Triggers iframe API mouseMove event.
     *
     * @param {MouseEvent} event - The mouse event.
     * @private
     * @returns {void}
     */
    _onMouseMove(event: React.MouseEvent) {
        APP.API.notifyMouseMove(event);
    }

    /**
     * Displays the toolbar.
     *
     * @private
     * @returns {void}
     */
    _onShowToolbar() {
        this.props.dispatch(showToolbox());
    }

    /**
     * Until we don't rewrite UI using react components
     * we use UI.start from old app. Also method translates
     * component right after it has been mounted.
     *
     * @inheritdoc
     */
    _start() {
        APP.UI.start();
        APP.UI.bindEvents();

        FULL_SCREEN_EVENTS.forEach(name =>
            document.addEventListener(name, this._onFullScreenChange));

        const { dispatch, t } = this.props;

        dispatch(init());

        maybeShowSuboptimalExperienceNotification(dispatch, t);
    }
}

/**
 * Maps (parts of) the Redux state to the associated props for the
 * {@code ConferenceMobile} component.
 *
 * @param {Object} state - The Redux state.
 * @private
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState) {
    const { backgroundAlpha, mouseMoveCallbackInterval } = state['features/base/config'];
    const { overflowDrawer } = state['features/toolbox'];
    const { is_in_picture_in_picture_mode } = state['features/picture-in-picture'];
    const { knocking } = state['features/lobby'];

    return {
        ...abstractMapStateToProps(state),
        _backgroundAlpha: backgroundAlpha,
        _isAnyOverlayVisible: Boolean(getOverlayToRender(state)),
        _layoutClassName: LAYOUT_CLASSNAMES_MOBILE[getCurrentLayout(state) ?? ''],
        _mouseMoveCallbackInterval: mouseMoveCallbackInterval,
        _overflowDrawer: overflowDrawer && isMobileBrowser(),
        _roomName: getConferenceNameForTitle(state),
        _showLobby: getIsLobbyVisible(state),
        _showPrejoin: isPrejoinPageVisible(state),
        _showVisitorsQueue: showVisitorsQueue(state),
        _isInPictureInPictureMode: is_in_picture_in_picture_mode,
        _lobbyKnocking: knocking
    };
}

export default reactReduxConnect(_mapStateToProps)(translate(ConferenceMobile));
