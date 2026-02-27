/* eslint-disable react/jsx-no-bind */
import React from 'react';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';
import { IReduxState } from '../../app/types';
import {getLocalJitsiVideoTrack} from "../../base/tracks/functions.any";
import Video from "../../base/media/components/web/Video";
import Avatar from "../../base/avatar/components/Avatar";
import {isVideoMutedByUser} from "../../base/media/functions";
import {getDisplayName} from "../../base/settings/functions.web";
import {getLocalParticipant} from "../../base/participants/functions";
import DisplayName from "../../display-name/components/web/DisplayName";

interface IProps {
    videoTrack?: Object,
    videoMuted?: boolean,
    name:  string,
    participantId: string | undefined,
    flipVideo: boolean;
}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            gridArea: 'video',
            padding: '8px 8px 2px 8px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
        },

        wrapperContent: {
            position: 'relative',
            aspectRatio: '16/9',
            borderRadius: '8px',
            overflow: 'hidden',
            backgroundColor: '#1C1C1C',

            '& > div': {
                width: '100%',
                height: '100%',
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                borderRadius: '4px',
            },

            ' .displayName': {
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                padding: '4px 6px',
                position: 'absolute',
                left: '7px',
                bottom: '9px',
                backgroundColor: '#282828',
                borderRadius: '4px',
                fontSize: '12px',
            }
        },
        wrapperContentAvatar: {
            width: '100%',
            height: '100%',
            aspectRatio: 'auto',
        },
        videoTag: {
            width: '100%',
            height: '100%',
        },
    };
});

const PrejoinVideo =  ({ videoTrack, videoMuted, name, participantId, flipVideo  }: IProps) => {
    const { classes } = useStyles();
    const isVideo = !videoMuted && videoTrack;

    return <div className={classes.container}>
        <div className={`${classes.wrapperContent} ${!isVideo ? classes.wrapperContentAvatar : ""}`}>
            {isVideo
                ? (
                    <Video
                        className = {classes.videoTag}
                        style={{ transform: `scaleX(${ flipVideo ? '-1' : '1'})` }}
                        id = 'prejoinVideo'
                        videoTrack = {{ jitsiTrack: videoTrack }} />
                )
                : (
                    <Avatar
                        displayName = {name}
                        participantId = {participantId}
                        size = {200}
                        url = "images/video_offline.svg" />
                )}
            <DisplayName
                allowEditing = {false}
                displayNameSuffix = {name}
                elementID = {`participant_${participantId}_name`}
                participantID = {participantId ?? ''}
            />
        </div>
    </div>;
};

function mapStateToProps(state: IReduxState) {
    const { id: participantId } = getLocalParticipant(state) ?? {};
    return {
        videoTrack: getLocalJitsiVideoTrack(state),
        videoMuted: isVideoMutedByUser(state),
        name:  getDisplayName(state),
        flipVideo:  Boolean(state['features/base/settings'].localFlipX),
        participantId,
    };
}

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(PrejoinVideo);
