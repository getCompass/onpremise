// @ts-expect-error
import VideoLayout from '../../../modules/UI/videolayout/VideoLayout';
import StateListenerRegistry from '../base/redux/StateListenerRegistry';
import { getVideoTrackByParticipant } from '../base/tracks/functions.web';

import { getMinimizedVideoParticipant } from './functions';

/**
 * Updates the on stage participant video.
 */
StateListenerRegistry.register(
    /* selector */ state => state['features/minimized-video'].participantId,
    /* listener */ participantId => {
        VideoLayout.updateMinimizedVideo(participantId, true);
    }
);

/**
 * Schedules a minimized video update when the streaming status of the track associated with the minimized video changes.
 */
StateListenerRegistry.register(
    /* selector */ state => {
        const minimizedVideoParticipant = getMinimizedVideoParticipant(state);
        const videoTrack = getVideoTrackByParticipant(state, minimizedVideoParticipant);

        return {
            participantId: minimizedVideoParticipant?.id,
            streamingStatus: videoTrack?.streamingStatus
        };
    },
    /* listener */ ({ participantId, streamingStatus }, previousState: any = {}) => {
        if (streamingStatus !== previousState.streamingStatus) {
            VideoLayout.updateMinimizedVideo(participantId, true);
        }
    }, {
        deepEquals: true
    }
);
