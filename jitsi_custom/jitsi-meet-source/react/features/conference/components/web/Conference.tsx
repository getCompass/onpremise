import _ from 'lodash';
import React from 'react';
import {WithTranslation} from 'react-i18next';
import {connect as reactReduxConnect} from 'react-redux';

// @ts-expect-error
import VideoLayout from '../../../../../modules/UI/videolayout/VideoLayout';
import {IReduxState, IStore} from '../../../app/types';
import {getConferenceNameForTitle} from '../../../base/conference/functions';
import {hangup} from '../../../base/connection/actions.web';
import {isMobileBrowser} from '../../../base/environment/utils';
import {translate} from '../../../base/i18n/functions';
import {setColorAlpha} from '../../../base/util/helpers';
import Chat from '../../../chat/components/web/Chat';
import MainFilmstrip from '../../../filmstrip/components/web/MainFilmstrip';
import ScreenshareFilmstrip from '../../../filmstrip/components/web/ScreenshareFilmstrip';
import StageFilmstrip from '../../../filmstrip/components/web/StageFilmstrip';
import CalleeInfoContainer from '../../../invite/components/callee-info/CalleeInfoContainer';
import LargeVideo from '../../../large-video/components/LargeVideo.web';
import LobbyScreen from '../../../lobby/components/web/LobbyScreen';
import {getIsLobbyVisible} from '../../../lobby/functions';
import {getOverlayToRender} from '../../../overlay/functions.web';
import ParticipantsPane from '../../../participants-pane/components/web/ParticipantsPane';
import Prejoin from '../../../prejoin/components/web/Prejoin';
import {isPrejoinPageVisible} from '../../../prejoin/functions';
import ReactionAnimations from '../../../reactions/components/web/ReactionsAnimations';
import {toggleToolboxVisible} from '../../../toolbox/actions.any';
import {fullScreenChanged, showToolbox} from '../../../toolbox/actions.web';
import JitsiPortal from '../../../toolbox/components/web/JitsiPortal';
import Toolbox from '../../../toolbox/components/web/Toolbox';
import {LAYOUT_CLASSNAMES} from '../../../video-layout/constants';
import {getCurrentLayout} from '../../../video-layout/functions.any';
import {init} from '../../actions.web';
import {maybeShowSuboptimalExperienceNotification} from '../../functions.web';
import type {AbstractProps} from '../AbstractConference';
import {AbstractConference, abstractMapStateToProps} from '../AbstractConference';

import ConferenceInfo from './ConferenceInfo';
import {default as Notice} from './Notice';
import {setIsInPictureInPictureMode} from "../../../picture-in-picture/actions.web";
import {setFilmstripVisible, setTopPanelVisible} from "../../../filmstrip/actions.web";
import {isDemoNode} from "../../../app/functions.web";
import {getParticipantsPaneOpen} from "../../../participants-pane/functions";
import DownloadMenu from "../../../base/compass/components/web/DownloadMenu";
import {openDialog} from "../../../base/dialog/actions";
import RequestConsultationDialog from "../../../base/compass/components/web/RequestConsultation";
import {browser} from "../../../base/lib-jitsi-meet";
import {getCompassDownloadLink} from "../../../base/compass/functions";

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
 * The type of the React {@code Component} props of {@link Conference}.
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
     * The CSS class to apply to the root of {@link Conference} to modify the
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
     * If pip mode enabled or not.
     */
    _isInPictureInPictureMode: boolean;

    /**
     * If participant pane opened or not.
     */
    _isParticipantsPaneOpen: boolean;

    _lobbyKnocking: boolean;

    dispatch: IStore['dispatch'];
}

/**
 * The conference page of the Web application.
 */
class Conference extends AbstractConference<IProps, any> {
    _originalOnMouseMove: Function;
    _originalOnShowToolbar: Function;

    /**
     * Initializes a new Conference instance.
     *
     * @param {Object} props - The read-only properties with which the new
     * instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        const {_mouseMoveCallbackInterval} = props;

        // Throttle and bind this component's mousemove handler to prevent it
        // from firing too often.
        this._originalOnShowToolbar = this._onShowToolbar;
        this._originalOnMouseMove = this._onMouseMove;

        this._onShowToolbar = _.throttle(
            () => this._originalOnShowToolbar(),
            100,
            {
                leading: true,
                trailing: false
            });

        this._onMouseMove = _.throttle(
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
            _isInPictureInPictureMode,
            _isParticipantsPaneOpen,
            _lobbyKnocking,
            t,
            dispatch
        } = this.props;

        const screenWidth = document.body.clientWidth;

        // функция для android доступная из dom, чтобы вызывать ее с kotlin
        // @ts-ignore
        window.dispatchIsInPictureInPictureMode = (isInPipMode: boolean) => {
            this.props.dispatch(setIsInPictureInPictureMode(isInPipMode));

            if (isInPipMode) {
                this.props.dispatch(setTopPanelVisible(false));
                this.props.dispatch(setFilmstripVisible(false));
            }
        };

        return (
            <>
                {isDemoNode() && (
                    <div id='layout_demo_header' className={isMobileBrowser() ? "mobile" : ""}>
                        <div id="layout_demo_header_compass_title_container"
                             className={isMobileBrowser() ? "mobile" : ""}
                             style={{gap: isMobileBrowser() ? "10px" : "12px"}}
                             onClick={() => (window.location.href = "https://getcompass.ru/")}
                        >
                            <div>
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <rect width="32" height="32" rx="6" fill="white" fillOpacity="0.05"/>
                                    <path fillRule="evenodd" clipRule="evenodd"
                                          d="M14.9856 5.95837L8.5635 24.0933C8.21522 25.0768 9.37425 25.9086 10.2224 25.2838L15.3556 21.5024C15.7375 21.2211 16.2625 21.2211 16.6444 21.5024L21.7776 25.2838C22.6258 25.9086 23.7848 25.0768 23.4365 24.0933L17.0144 5.95837C16.68 5.01388 15.32 5.01388 14.9856 5.95837Z"
                                          fill="url(#paint0_linear_3830_16227)"/>
                                    <defs>
                                        <linearGradient id="paint0_linear_3830_16227" x1="16" y1="5.25" x2="16"
                                                        y2="25.5"
                                                        gradientUnits="userSpaceOnUse">
                                            <stop stopColor="#FD5A09"/>
                                            <stop offset="1" stopColor="#F33202"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                            <div id="layout_demo_header_compass_title">
                                <div style={{
                                    textTransform: 'uppercase',
                                    fontFamily: 'Lato Bold',
                                    fontWeight: 'normal' as const,
                                    fontSize: isMobileBrowser() ? '14px' : '15px',
                                    lineHeight: isMobileBrowser() ? '17px' : '21px',
                                    color: 'rgba(255, 255, 255, 0.75)',
                                }}>COMPASS
                                </div>
                                <div style={{
                                    width: '1px',
                                    height: '14px',
                                    backgroundColor: 'rgb(255, 255, 255)',
                                    opacity: '30%'
                                }}/>
                                <div style={{
                                    fontFamily: 'Lato Regular',
                                    fontWeight: 'normal' as const,
                                    fontSize: isMobileBrowser() ? '14px' : '15px',
                                    lineHeight: isMobileBrowser() ? '17px' : '21px',
                                    color: 'rgba(255, 255, 255, 0.75)',
                                }}>Видеоконференции
                                </div>
                            </div>
                        </div>
                        {isMobileBrowser() ? (
                            <div id="layout_demo_header_buttons_container" className="mobile">
                                {screenWidth <= 385 && (
                                    <div style={{width: "21px", height: "22px"}}>
                                        <svg width="21" height="22" viewBox="0 0 21 22" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <rect width="21" height="22" fill="url(#paint0_linear_4925_22717)"/>
                                            <defs>
                                                <linearGradient id="paint0_linear_4925_22717" x1="19" y1="11"
                                                                x2="4.97294e-09" y2="11" gradientUnits="userSpaceOnUse">
                                                    <stop stopColor="#1B1B1C"/>
                                                    <stop offset="1" stopColor="#1B1B1C" stopOpacity="0"/>
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    </div>
                                )}
                                <div id="layout_demo_header_button_download_compass" className="mobile" onClick={() => {

                                    const isAndroid = browser.getOS().toLowerCase() === "android";
                                    const isMobileHuawei = browser._parser.getDevice().vendor !== undefined && browser._parser.getDevice().vendor.toLowerCase() === "huawei";
                                    const isMobileAndroid = !isMobileHuawei && isAndroid;
                                    if (isMobileAndroid) {
                                        window.open(getCompassDownloadLink(true, "android"), '_blank');
                                        return;
                                    }

                                    if (isMobileHuawei) {
                                        window.open(getCompassDownloadLink(true, "huawei"), '_blank');
                                        return;
                                    }
                                    window.open(getCompassDownloadLink(true, "ios"), '_blank');
                                }}>
                                    {"Установить"}
                                </div>
                            </div>
                        ) : (
                            <div id="layout_demo_header_buttons_container">
                                <div id="layout_demo_header_button_request_consultation"
                                     onClick={() => dispatch(openDialog(RequestConsultationDialog))}>Заказать
                                    консультацию
                                </div>
                                <DownloadMenu
                                    triggerEl={
                                        <div id="layout_demo_header_button_download_compass">
                                            {"Скачать Compass"}
                                        </div>
                                    }
                                    position="bottom-end32"
                                />
                            </div>
                        )}
                    </div>
                )}
                <div
                    id={isDemoNode() ? 'layout_wrapper_demo' : 'layout_wrapper'}
                    onMouseEnter={this._onMouseEnter}
                    onMouseLeave={this._onMouseLeave}
                    onMouseMove={this._onMouseMove}
                    ref={this._setBackground}>
                    {(isDemoNode() && !isMobileBrowser() && !_isParticipantsPaneOpen) && (
                        <div id="floating_demo_conference_container">
                            <div id="floating_demo_conference_content">
                                <div id="floating_demo_conference_content_icon_title_desc_container">
                                    <div id="floating_demo_conference_icon">
                                        <svg width="58" height="50" viewBox="0 0 58 50" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <g filter="url(#filter0_d_4797_22623)">
                                                <path
                                                    d="M29.7924 29.0484C29.447 29.0484 29.2474 28.8703 29.1934 28.5142C29.0208 27.3272 28.8265 26.3506 28.6107 25.5844C28.3949 24.8182 28.0765 24.2139 27.6557 23.7715C27.2348 23.3182 26.6467 22.9783 25.8913 22.7517C25.1467 22.5143 24.1539 22.3308 22.9129 22.2013C22.5568 22.1474 22.3788 21.9585 22.3788 21.6348C22.3788 21.2895 22.5568 21.0898 22.9129 21.0359C23.9057 20.8956 24.7313 20.7445 25.3895 20.5827C26.0478 20.41 26.5874 20.1834 27.0082 19.9028C27.4291 19.6222 27.7636 19.2661 28.0118 18.8345C28.2708 18.392 28.4866 17.8363 28.6593 17.1672C28.8319 16.4982 29.01 15.6888 29.1934 14.7392C29.269 14.3939 29.4686 14.2212 29.7924 14.2212C30.1269 14.2212 30.3211 14.3993 30.3751 14.7554C30.5154 15.6942 30.6664 16.4928 30.8283 17.151C30.9902 17.7985 31.2006 18.3435 31.4596 18.7859C31.7294 19.2284 32.0801 19.5953 32.5117 19.8866C32.9434 20.1672 33.4937 20.3938 34.1628 20.5665C34.8427 20.7391 35.6736 20.8956 36.6556 21.0359C37.0117 21.0898 37.1897 21.2895 37.1897 21.6348C37.1897 21.9801 37.0117 22.169 36.6556 22.2013C35.6628 22.3416 34.8373 22.4981 34.179 22.6708C33.5207 22.8434 32.9812 23.07 32.5603 23.3506C32.1394 23.6312 31.7995 23.9927 31.5405 24.4351C31.2923 24.8776 31.0819 25.4333 30.9092 26.1024C30.7366 26.7714 30.5585 27.5808 30.3751 28.5304C30.2995 28.8757 30.1053 29.0484 29.7924 29.0484ZM23.4147 31.6868C23.1881 31.6868 23.0424 31.5466 22.9777 31.266C22.8158 30.4998 22.6809 29.9441 22.573 29.5987C22.4759 29.2426 22.2601 28.9998 21.9255 28.8703C21.591 28.73 21.0029 28.5952 20.1612 28.4657C19.8914 28.4333 19.7565 28.2822 19.7565 28.0124C19.7565 27.7534 19.8806 27.6078 20.1288 27.5754C20.9813 27.4351 21.5748 27.2948 21.9094 27.1545C22.2547 27.0142 22.4759 26.7714 22.573 26.4261C22.6809 26.07 22.8158 25.5143 22.9777 24.7589C23.0424 24.4783 23.1881 24.338 23.4147 24.338C23.6629 24.338 23.814 24.4783 23.868 24.7589C24.019 25.5143 24.1485 26.07 24.2564 26.4261C24.3644 26.7714 24.5856 27.0142 24.9201 27.1545C25.2654 27.2948 25.8697 27.4351 26.733 27.5754C26.9812 27.6078 27.1053 27.7534 27.1053 28.0124C27.1053 28.2714 26.9812 28.4225 26.733 28.4657C25.8697 28.5952 25.2654 28.73 24.9201 28.8703C24.5856 28.9998 24.3644 29.2426 24.2564 29.5987C24.1485 29.9549 24.019 30.5268 23.868 31.3145C23.814 31.5627 23.6629 31.6868 23.4147 31.6868ZM12.9256 35.944C11.6307 35.944 10.6109 35.5771 9.86633 34.8433C9.13253 34.0987 8.76562 33.0789 8.76562 31.784V13.2176C8.76562 11.9227 9.13253 10.9083 9.86633 10.1745C10.6109 9.42992 11.6307 9.05762 12.9256 9.05762H45.0727C46.3677 9.05762 47.3821 9.42992 48.1159 10.1745C48.8605 10.9083 49.2328 11.9227 49.2328 13.2176V31.784C49.2328 33.0789 48.8605 34.0987 48.1159 34.8433C47.3821 35.5771 46.3677 35.944 45.0727 35.944H12.9256ZM12.9742 33.3379H45.0242C45.5314 33.3379 45.9253 33.1976 46.2058 32.917C46.4864 32.6365 46.6267 32.248 46.6267 31.7516V13.25C46.6267 12.7536 46.4864 12.3651 46.2058 12.0846C45.9253 11.804 45.5314 11.6637 45.0242 11.6637H12.9742C12.467 11.6637 12.0731 11.804 11.7926 12.0846C11.512 12.3651 11.3717 12.7536 11.3717 13.25V31.7516C11.3717 32.248 11.512 32.6365 11.7926 32.917C12.0731 33.1976 12.467 33.3379 12.9742 33.3379ZM20.8572 41.4313C20.5011 41.4313 20.1935 41.3018 19.9346 41.0429C19.6756 40.7839 19.5461 40.4763 19.5461 40.1202C19.5461 39.7533 19.6756 39.4403 19.9346 39.1814C20.1935 38.9332 20.5011 38.8091 20.8572 38.8091H37.1412C37.4973 38.8091 37.8048 38.9332 38.0638 39.1814C38.3228 39.4403 38.4523 39.7533 38.4523 40.1202C38.4523 40.4763 38.3228 40.7839 38.0638 41.0429C37.8048 41.3018 37.4973 41.4313 37.1412 41.4313H20.8572Z"
                                                    fill="#9746FF"/>
                                            </g>
                                            <defs>
                                                <filter id="filter0_d_4797_22623" x="0.765625" y="1.05762"
                                                        width="56.4668" height="48.3735" filterUnits="userSpaceOnUse"
                                                        colorInterpolationFilters="sRGB">
                                                    <feFlood floodOpacity="0" result="BackgroundImageFix"/>
                                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                                                   values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                                                   result="hardAlpha"/>
                                                    <feOffset/>
                                                    <feGaussianBlur stdDeviation="4"/>
                                                    <feComposite in2="hardAlpha" operator="out"/>
                                                    <feColorMatrix type="matrix"
                                                                   values="0 0 0 0 0.592157 0 0 0 0 0.27451 0 0 0 0 1 0 0 0 0.4 0"/>
                                                    <feBlend mode="normal" in2="BackgroundImageFix"
                                                             result="effect1_dropShadow_4797_22623"/>
                                                    <feBlend mode="normal" in="SourceGraphic"
                                                             in2="effect1_dropShadow_4797_22623" result="shape"/>
                                                </filter>
                                            </defs>
                                        </svg>
                                    </div>
                                    <div id="floating_demo_conference_title_desc_container">
                                        <div id="floating_demo_conference_title">Вы используете демо-конференцию
                                            Compass
                                        </div>
                                        <div id="floating_demo_conference_desc">Скачайте приложение Compass для общения
                                            без ограничений.
                                        </div>
                                    </div>
                                </div>
                                <DownloadMenu
                                    triggerEl={
                                        <div id="floating_demo_conference_download_button">Скачать Compass</div>
                                    }
                                    position="bottom-mid"
                                />
                            </div>
                        </div>
                    )}
                    <Chat/>
                    <div
                        className={_layoutClassName}
                        id='videoconference_page'
                        onMouseMove={isMobileBrowser() ? undefined : this._onShowToolbar}>
                        {!_isInPictureInPictureMode && (
                            <>
                                <ConferenceInfo/>
                                <Notice/>
                            </>
                        )}
                        <div
                            id='videospace'
                            onTouchStart={this._onVidespaceTouchStart}>
                            <LargeVideo/>
                            {(!_isInPictureInPictureMode || isMobileBrowser()) && (
                                _showPrejoin || _showLobby || (<>
                                    <StageFilmstrip/>
                                    <ScreenshareFilmstrip/>
                                    <MainFilmstrip/>
                                </>)
                            )}
                        </div>

                        {_isInPictureInPictureMode ? <></> : _lobbyKnocking ? <>
                            <span
                                aria-level={1}
                                className='sr-only'
                                role='heading'>
                                {t('toolbar.accessibilityLabel.heading')}
                            </span>
                                <Toolbox isLobby={true}/>
                            </> :
                            (_showPrejoin || _showLobby || (
                                <>
                            <span
                                aria-level={1}
                                className='sr-only'
                                role='heading'>
                                {t('toolbar.accessibilityLabel.heading')}
                            </span>
                                    <Toolbox/>
                                </>
                            ))}

                        {!_isInPictureInPictureMode && _notificationsVisible && !_isAnyOverlayVisible && (_overflowDrawer
                            ? <JitsiPortal className={`notification-portal${isMobileBrowser() ? ' is-mobile' : ''}`}>
                                {this.renderNotificationsContainer({portal: true})}
                            </JitsiPortal>
                            : this.renderNotificationsContainer())
                        }

                        <CalleeInfoContainer/>

                        {_showPrejoin && <Prejoin/>}
                        {_showLobby && <LobbyScreen/>}
                    </div>
                    <ParticipantsPane/>
                    <ReactionAnimations/>
                </div>
            </>
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

        APP.UI.registerListeners();
        APP.UI.bindEvents();

        FULL_SCREEN_EVENTS.forEach(name =>
            document.addEventListener(name, this._onFullScreenChange));

        const {dispatch, t} = this.props;

        dispatch(init());

        maybeShowSuboptimalExperienceNotification(dispatch, t);
    }
}

/**
 * Maps (parts of) the Redux state to the associated props for the
 * {@code Conference} component.
 *
 * @param {Object} state - The Redux state.
 * @private
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState) {
    const {backgroundAlpha, mouseMoveCallbackInterval} = state['features/base/config'];
    const {overflowDrawer} = state['features/toolbox'];
    const {is_in_picture_in_picture_mode} = state['features/picture-in-picture'];
    const {knocking} = state['features/lobby'];
    const is_participants_pane_open = getParticipantsPaneOpen(state);

    return {
        ...abstractMapStateToProps(state),
        _backgroundAlpha: backgroundAlpha,
        _isAnyOverlayVisible: Boolean(getOverlayToRender(state)),
        _layoutClassName: LAYOUT_CLASSNAMES[getCurrentLayout(state) ?? ''],
        _mouseMoveCallbackInterval: mouseMoveCallbackInterval,
        _overflowDrawer: overflowDrawer && isMobileBrowser(),
        _roomName: getConferenceNameForTitle(state),
        _showLobby: getIsLobbyVisible(state),
        _showPrejoin: isPrejoinPageVisible(state),
        _isInPictureInPictureMode: is_in_picture_in_picture_mode,
        _lobbyKnocking: knocking,
        _isParticipantsPaneOpen: is_participants_pane_open,
    };
}

export default reactReduxConnect(_mapStateToProps)(translate(Conference));
