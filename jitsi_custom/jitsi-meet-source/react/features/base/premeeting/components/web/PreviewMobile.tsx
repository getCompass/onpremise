import React, { useEffect, useRef } from 'react';
import { connect } from 'react-redux';

import { IReduxState } from '../../../../app/types';
import Avatar from '../../../avatar/components/Avatar';
import Video from '../../../media/components/web/Video';
import { getLocalParticipant } from '../../../participants/functions';
import { getDisplayName } from '../../../settings/functions.web';
import { getLocalVideoTrack, isParticipantAudioMuted } from '../../../tracks/functions.web';
import AudioMutedIndicator from "../../../../filmstrip/components/web/AudioMutedIndicator";

export interface IProps {

    /**
     * Local participant id.
     */
    _participantId: string;

    /**
     * Flag controlling whether the video should be flipped or not.
     */
    flipVideo: boolean;

    /**
     * The name of the user that is about to join.
     */
    name: string;

    /**
     * Flag signaling the visibility of camera preview.
     */
    videoMuted: boolean;

    /**
     * The JitsiLocalTrack to display.
     */
    videoTrack?: Object;

    _showAudioMutedIndicator: boolean;

    _lobbyKnocking: boolean;
}

/**
 * Component showing the video preview and device status.
 *
 * @param {IProps} props - The props of the component.
 * @returns {ReactElement}
 */
function PreviewMobile(props: IProps) {
    const {
        _participantId,
        flipVideo,
        name,
        videoMuted,
        videoTrack,
        _showAudioMutedIndicator,
        _lobbyKnocking
    } = props;
    const className = `is-mobile ${flipVideo ? 'flipVideoX' : ''}`;
    const videoRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        APP.API.notifyPrejoinVideoVisibilityChanged(Boolean(!videoMuted && videoTrack));
    }, [ videoMuted, videoTrack ]);

    useEffect(() => {
        APP.API.notifyPrejoinLoaded();

        return () => APP.API.notifyPrejoinVideoVisibilityChanged(false);
    }, []);

    useEffect(() => {
        const updateVideoHeight = () => {

            if (videoMuted && videoRef.current) {

                videoRef.current.style.height = 'unset';
                return;
            }

            if (videoRef.current) {

                // устанавливаем высоту в зависимости от ширины и соотношения 16:9
                videoRef.current.style.height = '100%';
            }
        };

        // вызываем функцию для первоначальной установки высоты
        if (videoRef.current) {
            updateVideoHeight();
        }

        // добавляем обработчик на изменение размера окна, чтобы высота менялась динамически
        window.addEventListener('resize', updateVideoHeight);

        // убираем обработчик при размонтировании компонента
        return () => window.removeEventListener('resize', updateVideoHeight);
    }, [ videoTrack, videoMuted ]);

    return (
        <div id = 'preview' className = 'is-mobile'>
            {!videoMuted && videoTrack
                ? (
                    <div className = 'web-prejoin-video-mobile' ref = {videoRef}>
                        <Video
                            className = {className}
                            id = 'prejoinVideo'
                            videoTrack = {{ jitsiTrack: videoTrack }} />
                    </div>
                )
                : (
                    _lobbyKnocking ? (
                        <div className = 'premeeting-screen-avatar is-mobile'
                             style = {{
                                 height: "unset !important",
                                 display: 'flex',
                                 flexDirection: 'column',
                                 alignItems: 'center',
                                 gap: '32px',
                             }}>
                            <Avatar
                                displayName = {name}
                                participantId = {_participantId}
                                size = {200} />
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
                                }}>{name}</div>
                            </div>
                        </div>
                    ) : (
                        <Avatar
                            className = 'premeeting-screen-avatar is-mobile'
                            displayName = {name}
                            participantId = {_participantId}
                            size = {200}
                            url = "images/video_offline.svg" />
                    )
                )
            }
        </div>
    )
        ;
}

/**
 * Maps part of the Redux state to the props of this component.
 *
 * @param {Object} state - The Redux state.
 * @param {IProps} ownProps - The own props of the component.
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState, ownProps: any) {
    const name = getDisplayName(state);
    const localParticipant = getLocalParticipant(state);
    const _participantId = localParticipant?.id ?? '';
    const isAudioMuted = localParticipant === undefined ? true : isParticipantAudioMuted(localParticipant, state);
    const { knocking } = state['features/lobby'];

    return {
        _participantId,
        flipVideo: Boolean(state['features/base/settings'].localFlipX),
        name,
        videoMuted: ownProps.videoTrack ? ownProps.videoMuted : state['features/base/media'].video.muted,
        videoTrack: ownProps.videoTrack || getLocalVideoTrack(state['features/base/tracks'])?.jitsiTrack,
        _showAudioMutedIndicator: isAudioMuted,
        _lobbyKnocking: knocking,
    };
}

export default connect(_mapStateToProps)(PreviewMobile);
