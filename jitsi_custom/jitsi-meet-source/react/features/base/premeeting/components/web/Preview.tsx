import React, { useEffect, useRef } from 'react';
import { connect } from 'react-redux';

import { IReduxState } from '../../../../app/types';
import Avatar from '../../../avatar/components/Avatar';
import Video from '../../../media/components/web/Video';
import { getLocalParticipant } from '../../../participants/functions';
import { getDisplayName } from '../../../settings/functions.web';
import { getLocalVideoTrack } from '../../../tracks/functions.web';

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
}

/**
 * Component showing the video preview and device status.
 *
 * @param {IProps} props - The props of the component.
 * @returns {ReactElement}
 */
function Preview(props: IProps) {
    const { _participantId, flipVideo, name, videoMuted, videoTrack } = props;
    const className = flipVideo ? 'flipVideoX' : '';
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
            if (videoRef.current) {

                // устанавливаем высоту в зависимости от ширины и соотношения 16:9
                videoRef.current.style.height = `${videoRef.current.clientWidth / (16 / 9)}px`;
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
        <div id = 'preview'>
            {!videoMuted && videoTrack
                ? (
                    <div className = 'web-prejoin-video' ref = {videoRef}>
                        <Video
                            className = {className}
                            id = 'prejoinVideo'
                            videoTrack = {{ jitsiTrack: videoTrack }} />
                    </div>
                )
                : (
                    <Avatar
                        className = 'premeeting-screen-avatar'
                        displayName = {name}
                        participantId = {_participantId}
                        size = {200}
                        url = "images/video_offline.svg" />
                )}
        </div>
    );
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
    const { id: _participantId } = getLocalParticipant(state) ?? {};

    return {
        _participantId: _participantId ?? '',
        flipVideo: Boolean(state['features/base/settings'].localFlipX),
        name,
        videoMuted: ownProps.videoTrack ? ownProps.videoMuted : state['features/base/media'].video.muted,
        videoTrack: ownProps.videoTrack || getLocalVideoTrack(state['features/base/tracks'])?.jitsiTrack
    };
}

export default connect(_mapStateToProps)(Preview);
