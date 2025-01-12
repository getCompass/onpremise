/* eslint-disable react/no-multi-comp */
import React, { Component } from 'react';
import { batch, connect } from 'react-redux';

import { IReduxState, IStore } from '../../../app/types';
import { JitsiRecordingConstants } from '../../../base/lib-jitsi-meet';
import RecordingLabel from '../../../recording/components/web/RecordingLabel';
import { setOverflowMenuVisible, setToolbarHovered, showToolbox } from '../../../toolbox/actions.web';
import { isToolboxVisible } from '../../../toolbox/functions.web';
import { getConferenceInfo } from '../functions.web';

import ConferenceInfoContainer from './ConferenceInfoContainer';
import ToggleCameraButton from "../../../toolbox/components/web/ToggleCameraButton";
import { isPrejoinPageVisible } from "../../../prejoin/functions.any";
import { getParticipantCount, isScreenShareParticipantById } from "../../../base/participants/functions";
import ConferenceTimer from "../ConferenceTimer";
import { translate } from "../../../base/i18n/functions";
import { WithTranslation } from "react-i18next";
import { plural } from "../../../base/compass/functions";
import { MEDIA_TYPE } from "../../../base/media/constants";
import {
    isLocalTrackMuted,
    isParticipantAudioMuted,
    isParticipantVideoMuted,
    isRemoteTrackMuted
} from "../../../base/tracks/functions.any";
import { isToggleCameraEnabled } from "../../../base/tracks/functions.web";
import { IToolboxButton } from "../../../toolbox/types";
import OverflowMenuButtonMobile from "../../../toolbox/components/web/OverflowMenuButtonMobile";
import { useCompassOverflowMenuButtons } from "../../../toolbox/hooks.web";
import { isParticipantsPaneEnabled } from "../../../participants-pane/functions";
import OverflowToggleButtonMobile from "../../../toolbox/components/web/OverflowToggleButtonMobile";
import AudioMutedIndicator from "../../../filmstrip/components/web/AudioMutedIndicator";
import { getLargeVideoParticipant } from "../../../large-video/functions";
import RaisedHandIndicatorMobile from "../../../filmstrip/components/web/RaisedHandIndicatorMobile";
import { setTileView } from "../../../video-layout/actions.any";
import { getCurrentLayout } from "../../../video-layout/functions.any";
import { LAYOUTS } from "../../../video-layout/constants";
import TileViewButtonMobile from "../../../video-layout/components/TileViewButtonMobile";
import { open as openParticipantsPane } from "../../../participants-pane/actions.web";
import ChatCounterMobile from "../../../chat/components/web/ChatCounterMobile";

/**
 * The type of the React {@code Component} props of {@link Subject}.
 */
interface IProps extends WithTranslation {

    /**
     * The conference info labels to be shown in the conference header.
     */
    _conferenceInfo: {
        alwaysVisible?: string[];
        autoHide?: string[];
    };

    /**
     * Indicates whether the component should be visible or not.
     */
    _visible: boolean;

    /**
     * Indicates whether the component should be visible or not.
     */
    _isPrejoin: boolean;

    _isToggleCameraButtonVisible: boolean;

    _overflowMenuButtons: IToolboxButton[];

    /**
     * Indicates whether the component should be visible or not.
     */
    _lobbyKnocking: boolean;

    _participantsCount: number;

    _remoteParticipantsLength: number;

    _showAudioMutedIndicator: boolean;

    _largeVideoParticipantName: string;

    _currentLayout: string;

    _isLargeVideoParticipantMuted: boolean;

    _isParticipantsPaneEnabled: boolean;

    /**
     * Invoked to active other features of the app.
     */
    dispatch: IStore['dispatch'];
}

const COMPONENTS: Array<{
    Component: React.ComponentType<any>;
    id: string;
}> = [
    {
        Component: () => (
            <>
                <RecordingLabel mode = {JitsiRecordingConstants.mode.FILE} />
                <RecordingLabel mode = {JitsiRecordingConstants.mode.STREAM} />
            </>
        ),
        id: 'recording'
    }
];

/**
 * The upper band of the meeing containing the conference name, timer and labels.
 *
 * @param {Object} props - The props of the component.
 * @returns {React$None}
 */
class ConferenceInfoMobile extends Component<IProps> {
    /**
     * Initializes a new {@code ConferenceInfoMobile} instance.
     *
     * @param {IProps} props - The read-only React {@code Component} props with
     * which the new instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        this._renderAutoHide = this._renderAutoHide.bind(this);
        this._renderAlwaysVisible = this._renderAlwaysVisible.bind(this);
        this._onTabIn = this._onTabIn.bind(this);
    }

    /**
     * Callback invoked when the component is focused to show the conference
     * info if necessary.
     *
     * @returns {void}
     */
    _onTabIn() {
        if (this.props._conferenceInfo.autoHide?.length && !this.props._visible) {
            this.props.dispatch(showToolbox());
        }
    }

    /**
     * Renders auto-hidden info header labels.
     *
     * @returns {void}
     */
    _renderAutoHide() {
        const { autoHide } = this.props._conferenceInfo;

        if (!autoHide?.length) {
            return null;
        }

        return (
            <ConferenceInfoContainer
                id = 'autoHide'
                visible = {this.props._visible}>
                {
                    COMPONENTS
                        .filter(comp => autoHide.includes(comp.id))
                        .map(c =>
                            <c.Component key = {c.id} />
                        )
                }
            </ConferenceInfoContainer>
        );
    }

    /**
     * Renders the always visible info header labels.
     *
     * @returns {void}
     */
    _renderAlwaysVisible() {
        const { alwaysVisible } = this.props._conferenceInfo;

        if (!alwaysVisible?.length) {
            return null;
        }

        return (
            <ConferenceInfoContainer
                id = 'alwaysVisible'
                visible = {true}>
                {
                    COMPONENTS
                        .filter(comp => alwaysVisible.includes(comp.id))
                        .map(c =>
                            <c.Component key = {c.id} />
                        )
                }
            </ConferenceInfoContainer>
        );
    }

    _renderConferenceInfo() {
        const {
            _participantsCount,
            _remoteParticipantsLength,
            _showAudioMutedIndicator,
            _largeVideoParticipantName,
            _isLargeVideoParticipantMuted,
            _currentLayout,
            _isParticipantsPaneEnabled,
            dispatch,
            t
        } = this.props;

        // инфа о конференции не отображается сверху когда мы одним в звонке
        // или когда видео собеседника в режиме спикера НЕ замьючено
        if (_remoteParticipantsLength < 1 || (_currentLayout === LAYOUTS.HORIZONTAL_FILMSTRIP_VIEW && _isLargeVideoParticipantMuted && _remoteParticipantsLength <= 1)) {
            return <></>;
        }

        // при звонке 1х1 показываем имя того кто выбран в большом экране
        if (_remoteParticipantsLength === 1) {
            return (
                <div
                    onClick = {() => _isParticipantsPaneEnabled ? dispatch(openParticipantsPane()) : undefined}
                    style = {{
                        display: 'flex',
                        justifyContent: 'center',
                        alignItems: 'center',
                        flexDirection: 'column',
                        gap: 0,
                        overflow: 'hidden',
                    }}>
                    <div style = {{
                        display: 'flex',
                        gap: '4px',
                        fontFamily: 'Lato SemiBold',
                        fontWeight: 'normal' as const,
                        fontSize: '17px',
                        lineHeight: '22px',
                        color: 'rgba(255, 255, 255, 0.85)',
                        letterSpacing: '-0.24px',
                        maxWidth: '100%',
                    }}>
                        {_showAudioMutedIndicator &&
                            <AudioMutedIndicator tooltipPosition = 'top' iconColor = 'rgba(255, 255, 255, 0.85)'
                                                 iconSize = {20} />}
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
                        fontSize: '12px',
                        lineHeight: '15px',
                        color: 'rgba(255, 255, 255, 0.7)',
                    }} />
                </div>
            );
        }

        // иначе отображаем дефолт инфу
        return (
            <div style = {{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                flexDirection: 'column',
                gap: 0,
                overflow: 'hidden',
            }}>
                <div
                    onClick = {() => _isParticipantsPaneEnabled ? dispatch(openParticipantsPane()) : undefined}
                    style = {{
                        display: 'flex',
                        gap: '4px',
                        fontFamily: 'Lato SemiBold',
                        fontWeight: 'normal' as const,
                        fontSize: '17px',
                        lineHeight: '22px',
                        color: 'rgba(255, 255, 255, 0.85)',
                        letterSpacing: '-0.24px',
                        maxWidth: '100%',
                    }}>
                    <div style = {{
                        overflow: 'hidden',
                        textOverflow: 'ellipsis',
                        whiteSpace: 'nowrap',
                        maxWidth: '100%',
                    }}>{t('conference.title')}</div>
                    <ConferenceTimer />
                </div>
                <div style = {{
                    fontFamily: 'Lato Regular',
                    fontWeight: 'normal' as const,
                    fontSize: '12px',
                    lineHeight: '15px',
                    color: 'rgba(255, 255, 255, 0.3)',
                }}>{`${_participantsCount} ${plural(_participantsCount, t('conference.one_member'), t('conference.two_members'), t('conference.five_members'))}`}</div>
            </div>
        );
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const {
            _isPrejoin,
            _lobbyKnocking,
            _isToggleCameraButtonVisible,
            _overflowMenuButtons,
            _remoteParticipantsLength,
            _currentLayout
        } = this.props;
        const { NORMAL = 16 } = interfaceConfig.INDICATOR_FONT_SIZES || {};

        if (_isPrejoin || _lobbyKnocking) {

            return (
                <div
                    className = {`details-container-mobile ${_isPrejoin ? 'prejoin-page' : ''} ${this.props._visible ? 'visible' : ''}`}
                    onFocus = {this._onTabIn}>
                    <div style = {{
                        display: 'flex',
                        paddingLeft: '16px',
                        justifyContent: 'center',
                        alignItems: 'center'
                    }}>
                        {this._renderAlwaysVisible()}
                        {this._renderAutoHide()}
                    </div>
                    <div
                        className = {`header-buttons-mobile-container header-buttons-mobile-container-right ${this.props._visible ? 'visible' : ''}`}>
                        {_isToggleCameraButtonVisible ? (
                            <ToggleCameraButton customClass = 'mobile-header-button' />
                        ) : (
                            <div style = {{
                                width: '36px',
                                height: '36px',
                                opacity: 0,
                                pointerEvents: 'none',
                            }} />
                        )}
                    </div>
                </div>
            );
        }

        return (
            <div
                className = {`details-container-mobile ${this.props._visible ? 'visible' : ''}`}
                onFocus = {this._onTabIn}>
                <div
                    className = {`header-buttons-mobile-container header-buttons-mobile-container-left ${this.props._visible ? 'visible' : ''}`}>
                    {_currentLayout !== LAYOUTS.TILE_VIEW && _remoteParticipantsLength > 1 ? (
                        <TileViewButtonMobile customClass = 'mobile-header-button' />
                    ) : (
                        <OverflowMenuButtonMobile
                            ariaControls = 'overflow-menu'
                            buttons = {_overflowMenuButtons.reduce<Array<IToolboxButton[]>>((acc, val) => {
                                if (acc.length) {
                                    const prev = acc[acc.length - 1];
                                    const group = prev[prev.length - 1].group;

                                    if (group === val.group) {
                                        prev.push(val);
                                    } else {
                                        acc.push([ val ]);
                                    }
                                } else {
                                    acc.push([ val ]);
                                }

                                return acc;
                            }, [])}
                            key = 'overflow-menu'
                            headerContent = {
                                <div
                                    className = {`details-container-mobile ${this.props._visible ? 'visible' : ''}`}
                                    style = {{ position: 'relative', backgroundColor: 'rgba(23, 23, 23, 1)' }}>
                                    <div
                                        className = {`header-buttons-mobile-container header-buttons-mobile-container-left ${this.props._visible ? 'visible' : ''}`}
                                        style = {{
                                            position: 'relative'
                                        }}>
                                        <OverflowToggleButtonMobile
                                            handleClick = {() => {
                                                this.props.dispatch(setOverflowMenuVisible(false));
                                                this.props.dispatch(setToolbarHovered(false));
                                            }}
                                            isOpen = {true}
                                            onKeyDown = {() => {
                                                this.props.dispatch(setOverflowMenuVisible(false));
                                                this.props.dispatch(setToolbarHovered(false));
                                            }}
                                            customClass = 'mobile-header-button' />
                                        <ChatCounterMobile customClass = "overflow-button" />
                                    </div>
                                    {this._renderConferenceInfo()}
                                    <div
                                        className = {`header-buttons-mobile-container header-buttons-mobile-container-right ${this.props._visible ? 'visible' : ''}`}>
                                        {_isToggleCameraButtonVisible ? (
                                            <ToggleCameraButton customClass = 'mobile-header-button' />
                                        ) : (
                                            <div style = {{
                                                width: '36px',
                                                height: '36px',
                                                opacity: 0,
                                                pointerEvents: 'none',
                                            }} />
                                        )}
                                    </div>
                                </div>
                            } />
                    )}
                    <RaisedHandIndicatorMobile
                        iconSize = {NORMAL}
                        tooltipPosition = 'top' />
                </div>
                {this._renderConferenceInfo()}
                <div
                    className = {`header-buttons-mobile-container header-buttons-mobile-container-right ${this.props._visible ? 'visible' : ''}`}>
                    <div style = {{ width: '24px', height: '24px', opacity: 0, pointerEvents: 'none' }} />
                    {_isToggleCameraButtonVisible ? (
                        <ToggleCameraButton customClass = 'mobile-header-button' />
                    ) : (
                        <div style = {{
                            width: '36px',
                            height: '36px',
                            opacity: 0,
                            pointerEvents: 'none',
                        }} />
                    )}
                </div>
            </div>
        );
    }
}

/**
 * Maps (parts of) the Redux state to the associated
 * {@code Subject}'s props.
 *
 * @param {Object} state - The Redux state.
 * @private
 * @returns {{
 *     _visible: boolean,
 *     _conferenceInfo: Object
 * }}
 */
function _mapStateToProps(state: IReduxState) {
    const { is_in_picture_in_picture_mode } = state['features/picture-in-picture'];
    const isPrejoinVisible = isPrejoinPageVisible(state);
    const { knocking } = state['features/lobby'];
    const participantsCount = getParticipantCount(state);
    const { enabled: audioOnly } = state['features/base/audio-only'];
    const tracks = state['features/base/tracks'];
    const isToggleCameraButtonEnabled = isToggleCameraEnabled(state);
    const participantsPaneEnabled = isParticipantsPaneEnabled(state);
    const { remoteParticipants } = state['features/filmstrip'];
    const remoteParticipantsLength = remoteParticipants?.length ?? 0;
    const overflowMenuButtons = useCompassOverflowMenuButtons(participantsPaneEnabled, remoteParticipantsLength);
    const largeVideoParticipant = getLargeVideoParticipant(state);
    const participantId = largeVideoParticipant?.id ?? '';
    const largeVideoParticipantName = largeVideoParticipant?.name ?? '';
    const _currentLayout = getCurrentLayout(state) ?? '';
    const _isLargeVideoParticipantMuted = isParticipantVideoMuted(largeVideoParticipant, state);

    let isAudioMuted = true;
    if (largeVideoParticipant?.local) {
        isAudioMuted = isLocalTrackMuted(tracks, MEDIA_TYPE.AUDIO);
    } else if (!largeVideoParticipant?.fakeParticipant || isScreenShareParticipantById(state, participantId)) {
        isAudioMuted = isRemoteTrackMuted(tracks, MEDIA_TYPE.AUDIO, participantId);
    }

    return {
        _isToggleCameraButtonVisible: !Boolean(audioOnly) && isToggleCameraButtonEnabled && !isLocalTrackMuted(tracks, MEDIA_TYPE.VIDEO),
        _overflowMenuButtons: overflowMenuButtons,
        _visible: (isToolboxVisible(state) || isPrejoinVisible) && !is_in_picture_in_picture_mode,
        _isPrejoin: isPrejoinVisible,
        _conferenceInfo: getConferenceInfo(state),
        _lobbyKnocking: knocking,
        _participantsCount: participantsCount,
        _remoteParticipantsLength: remoteParticipantsLength,
        _showAudioMutedIndicator: isAudioMuted,
        _largeVideoParticipantName: largeVideoParticipantName,
        _currentLayout,
        _isLargeVideoParticipantMuted,
        _isParticipantsPaneEnabled: participantsPaneEnabled,
    };
}

export default translate(connect(_mapStateToProps)(ConferenceInfoMobile));
