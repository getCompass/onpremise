import { debounce } from 'lodash-es';

import { IReduxState, IStore } from '../app/types';
import { _handleParticipantError } from '../base/conference/functions';
import { getSsrcRewritingFeatureFlag } from '../base/config/functions.any';
import { MEDIA_TYPE, VIDEO_TYPE } from '../base/media/constants';
import {
    getLocalParticipant, getParticipantById,
    getSourceNamesByMediaTypeAndParticipant,
    getSourceNamesByVideoTypeAndParticipant, isParticipantModerator
} from '../base/participants/functions';
import StateListenerRegistry from '../base/redux/StateListenerRegistry';
import { getTrackSourceNameByMediaTypeAndParticipant } from '../base/tracks/functions';
import { reportError } from '../base/util/helpers';
import {
    getActiveParticipantsIds,
    getScreenshareFilmstripParticipantId,
    isTopPanelEnabled
} from '../filmstrip/functions';
import { LAYOUTS } from '../video-layout/constants';
import {
    getCurrentLayout,
    getVideoQualityForLargeVideo,
    getVideoQualityForResizableFilmstripThumbnails,
    getVideoQualityForScreenSharingFilmstrip,
    getVideoQualityForStageThumbnails,
    shouldDisplayTileView
} from '../video-layout/functions';

import {
    setMaxReceiverVideoQualityForLargeVideo,
    setMaxReceiverVideoQualityForScreenSharingFilmstrip,
    setMaxReceiverVideoQualityForStageFilmstrip,
    setMaxReceiverVideoQualityForTileView,
    setMaxReceiverVideoQualityForVerticalFilmstrip
} from './actions';
import { MAX_VIDEO_QUALITY, VIDEO_QUALITY_LEVELS, VIDEO_QUALITY_UNLIMITED } from './constants';
import logger from './logger';
import { getNumberOfPartipantsForTileView } from "../filmstrip/functions.web";
import { getQualityGroupByQualityLevel } from "../quality-control/actions";

/**
 * Handles changes in the visible participants in the filmstrip. The listener is debounced
 * so that the client doesn't end up sending too many bridge messages when the user is
 * scrolling through the thumbnails prompting updates to the selected endpoints.
 */
StateListenerRegistry.register(
    /* selector */ state => state['features/filmstrip'].visibleRemoteParticipants,
    /* listener */ debounce((visibleRemoteParticipants, store) => {
        _updateReceiverVideoConstraints(store);
    }, 100));

StateListenerRegistry.register(
    /* selector */ state => state['features/base/tracks'],
    /* listener */(remoteTracks, store) => {
        _updateReceiverVideoConstraints(store);
    });

/**
 * Handles the use case when the on-stage participant has changed.
 */
StateListenerRegistry.register(
    state => state['features/large-video'].participantId,
    (participantId, store) => {
        _updateReceiverVideoConstraints(store);
    }
);

/**
 * Handles the use case when we have set some of the constraints in redux but the conference object wasn't available
 * and we haven't been able to pass the constraints to lib-jitsi-meet.
 */
StateListenerRegistry.register(
    state => state['features/base/conference'].conference,
    (conference, store) => {
        _updateReceiverVideoConstraints(store);
    }
);

/**
 * StateListenerRegistry provides a reliable way of detecting changes to
 * lastn state and dispatching additional actions.
 */
StateListenerRegistry.register(
    /* selector */ state => state['features/base/lastn'].lastN,
    /* listener */ (lastN, store) => {
        _updateReceiverVideoConstraints(store);
    });

/**
 * Updates the receiver constraints when the stage participants change.
 */
StateListenerRegistry.register(
    state => getActiveParticipantsIds(state).sort(),
    (_, store) => {
        _updateReceiverVideoConstraints(store);
    }, {
        deepEquals: true
    }
);

/**
 * Updates the receiver constraints when new video sources are added to the conference.
 */
StateListenerRegistry.register(
    /* selector */ state => state['features/base/participants'].remoteVideoSources,
    /* listener */ (remoteVideoSources, store) => {
        getSsrcRewritingFeatureFlag(store.getState()) && _updateReceiverVideoConstraints(store);
    });

/**
 * StateListenerRegistry provides a reliable way of detecting changes to
 * maxReceiverVideoQuality* and preferredVideoQuality state and dispatching additional actions.
 */
StateListenerRegistry.register(
    /* selector */ state => {
        const {
            maxReceiverVideoQualityForLargeVideo,
            maxReceiverVideoQualityForScreenSharingFilmstrip,
            maxReceiverVideoQualityForStageFilmstrip,
            maxReceiverVideoQualityForTileView,
            maxReceiverVideoQualityForVerticalFilmstrip,
            preferredVideoQuality
        } = state['features/video-quality'];

        // приходится делать такую развилку, чтобы модераторы всегда слали t1, если их кто-то откроет в полный экран
        const localParticipantId = getLocalParticipant(state);
        const isLocalParticipantModerator = isParticipantModerator(localParticipantId);
        const { qualityLevel } = state['features/quality-control'];
        const qualityGroup = getQualityGroupByQualityLevel(qualityLevel);
        let senderVideoQuality = preferredVideoQuality;
        if  (isLocalParticipantModerator) {
            senderVideoQuality = qualityGroup.VIDEO_QUALITY_T1
        }

        return {
            maxReceiverVideoQualityForLargeVideo,
            maxReceiverVideoQualityForScreenSharingFilmstrip,
            maxReceiverVideoQualityForStageFilmstrip,
            maxReceiverVideoQualityForTileView,
            maxReceiverVideoQualityForVerticalFilmstrip,
            preferredVideoQuality,
            senderVideoQuality
        };
    },
    /* listener */ (currentState, store, previousState = {}) => {
        const { senderVideoQuality } = currentState;
        const changedPreferredVideoQuality = senderVideoQuality !== previousState.senderVideoQuality;

        if (changedPreferredVideoQuality) {
            _setSenderVideoConstraint(senderVideoQuality, store);
            typeof APP !== 'undefined' && APP.API.notifyVideoQualityChanged(senderVideoQuality);
        }
        _updateReceiverVideoConstraints(store);
    }, {
        deepEquals: true
    });

/**
 * Implements a state listener in order to calculate max receiver video quality.
 */
StateListenerRegistry.register(
    /* selector */ state => {
        const { reducedUI } = state['features/base/responsive-ui'];
        const _shouldDisplayTileView = shouldDisplayTileView(state);
        const tileViewThumbnailSize = state['features/filmstrip']?.tileViewDimensions?.thumbnailSize;
        const { visibleRemoteParticipants } = state['features/filmstrip'];
        const { height: largeVideoHeight } = state['features/large-video'];
        const activeParticipantsIds = getActiveParticipantsIds(state);
        const {
            screenshareFilmstripDimensions: {
                thumbnailSize
            }
        } = state['features/filmstrip'];
        const screenshareFilmstripParticipantId = getScreenshareFilmstripParticipantId(state);

        return {
            activeParticipantsCount: activeParticipantsIds?.length,
            displayTileView: _shouldDisplayTileView,
            largeVideoHeight,
            participantCount: visibleRemoteParticipants?.size || 0,
            reducedUI,
            screenSharingFilmstripHeight:
                screenshareFilmstripParticipantId && getCurrentLayout(state) === LAYOUTS.STAGE_FILMSTRIP_VIEW
                    ? thumbnailSize?.height : undefined,
            stageFilmstripThumbnailHeight: state['features/filmstrip'].stageFilmstripDimensions?.thumbnailSize?.height,
            tileViewThumbnailHeight: tileViewThumbnailSize?.height,
            verticalFilmstripThumbnailHeight:
                state['features/filmstrip'].verticalViewDimensions?.gridView?.thumbnailSize?.height
        };
    },
    /* listener */ ({
        activeParticipantsCount,
        displayTileView,
        largeVideoHeight,
        participantCount,
        reducedUI,
        screenSharingFilmstripHeight,
        stageFilmstripThumbnailHeight,
        tileViewThumbnailHeight,
        verticalFilmstripThumbnailHeight
    }, store, previousState = {}) => {
        const { dispatch, getState } = store;
        const state = getState();
        const { qualityLevel } = state['features/quality-control'];
        const {
            maxReceiverVideoQualityForLargeVideo,
            maxReceiverVideoQualityForScreenSharingFilmstrip,
            maxReceiverVideoQualityForStageFilmstrip,
            maxReceiverVideoQualityForTileView,
            maxReceiverVideoQualityForVerticalFilmstrip
        } = state['features/video-quality'];
        const { maxFullResolutionParticipants = 2 } = state['features/base/config'];
        let maxVideoQualityChanged = false;
        const numberOfParticipants = getNumberOfPartipantsForTileView(state);
        const qualityGroup = getQualityGroupByQualityLevel(qualityLevel);

        if (displayTileView) {
            let newMaxRecvVideoQuality = qualityGroup.VIDEO_QUALITY_T2;
            if (numberOfParticipants >= 10) {
                newMaxRecvVideoQuality = qualityGroup.VIDEO_QUALITY_T4;
            } else if (numberOfParticipants >= 7) {
                newMaxRecvVideoQuality = qualityGroup.VIDEO_QUALITY_T3_MIN;
            } else if (numberOfParticipants >= 5) {
                newMaxRecvVideoQuality = qualityGroup.VIDEO_QUALITY_T3;
            }

            if (maxReceiverVideoQualityForTileView !== newMaxRecvVideoQuality) {
                maxVideoQualityChanged = true;
                dispatch(setMaxReceiverVideoQualityForTileView(newMaxRecvVideoQuality));
            }
        } else {
            let newMaxRecvVideoQualityForStageFilmstrip;
            let newMaxRecvVideoQualityForVerticalFilmstrip;
            let newMaxRecvVideoQualityForLargeVideo;
            let newMaxRecvVideoQualityForScreenSharingFilmstrip;

            if (reducedUI) {
                newMaxRecvVideoQualityForVerticalFilmstrip
                    = newMaxRecvVideoQualityForStageFilmstrip
                    = newMaxRecvVideoQualityForLargeVideo
                    = newMaxRecvVideoQualityForScreenSharingFilmstrip
                    = VIDEO_QUALITY_LEVELS.LOW_HD;
            } else {
                newMaxRecvVideoQualityForStageFilmstrip
                    = getVideoQualityForStageThumbnails(stageFilmstripThumbnailHeight, state);
                newMaxRecvVideoQualityForVerticalFilmstrip
                    = getVideoQualityForResizableFilmstripThumbnails(verticalFilmstripThumbnailHeight, state);
                newMaxRecvVideoQualityForLargeVideo = getVideoQualityForLargeVideo(largeVideoHeight);
                newMaxRecvVideoQualityForScreenSharingFilmstrip
                    = getVideoQualityForScreenSharingFilmstrip(screenSharingFilmstripHeight, state);

                // Override HD level calculated for the thumbnail height when # of participants threshold is exceeded
                if (maxFullResolutionParticipants !== -1) {
                    if (activeParticipantsCount > 0
                        && newMaxRecvVideoQualityForStageFilmstrip > VIDEO_QUALITY_LEVELS.STANDARD) {
                        const isScreenSharingFilmstripParticipantFullResolution
                            = newMaxRecvVideoQualityForScreenSharingFilmstrip > VIDEO_QUALITY_LEVELS.STANDARD;

                        if (activeParticipantsCount > maxFullResolutionParticipants
                            - (isScreenSharingFilmstripParticipantFullResolution ? 1 : 0)) {
                            newMaxRecvVideoQualityForStageFilmstrip = VIDEO_QUALITY_LEVELS.STANDARD;
                            newMaxRecvVideoQualityForVerticalFilmstrip
                                = Math.min(VIDEO_QUALITY_LEVELS.STANDARD, newMaxRecvVideoQualityForVerticalFilmstrip);
                        } else if (newMaxRecvVideoQualityForVerticalFilmstrip > VIDEO_QUALITY_LEVELS.STANDARD
                            && participantCount > maxFullResolutionParticipants - activeParticipantsCount) {
                            newMaxRecvVideoQualityForVerticalFilmstrip = VIDEO_QUALITY_LEVELS.STANDARD;
                        }
                    } else if (newMaxRecvVideoQualityForVerticalFilmstrip > VIDEO_QUALITY_LEVELS.STANDARD
                        && participantCount > maxFullResolutionParticipants
                        - (newMaxRecvVideoQualityForLargeVideo > VIDEO_QUALITY_LEVELS.STANDARD ? 1 : 0)) {
                        newMaxRecvVideoQualityForVerticalFilmstrip = VIDEO_QUALITY_LEVELS.STANDARD;
                    }
                }
            }

            if (maxReceiverVideoQualityForStageFilmstrip !== newMaxRecvVideoQualityForStageFilmstrip) {
                maxVideoQualityChanged = true;
                dispatch(setMaxReceiverVideoQualityForStageFilmstrip(newMaxRecvVideoQualityForStageFilmstrip));
            }

            if (maxReceiverVideoQualityForVerticalFilmstrip !== newMaxRecvVideoQualityForVerticalFilmstrip) {
                maxVideoQualityChanged = true;
                dispatch(setMaxReceiverVideoQualityForVerticalFilmstrip(newMaxRecvVideoQualityForVerticalFilmstrip));
            }

            if (maxReceiverVideoQualityForLargeVideo !== newMaxRecvVideoQualityForLargeVideo) {
                maxVideoQualityChanged = true;
                dispatch(setMaxReceiverVideoQualityForLargeVideo(newMaxRecvVideoQualityForLargeVideo));
            }

            if (maxReceiverVideoQualityForScreenSharingFilmstrip !== newMaxRecvVideoQualityForScreenSharingFilmstrip) {
                maxVideoQualityChanged = true;
                dispatch(
                    setMaxReceiverVideoQualityForScreenSharingFilmstrip(
                        newMaxRecvVideoQualityForScreenSharingFilmstrip));
            }
        }

        if (!maxVideoQualityChanged && Boolean(displayTileView) !== Boolean(previousState.displayTileView)) {
            _updateReceiverVideoConstraints(store);
        }

    }, {
        deepEquals: true
    });

/**
 * Returns the source names asociated with the given participants list.
 *
 * @param {Array<string>} participantList - The list of participants.
 * @param {Object} state - The redux state.
 * @returns {Array<string>}
 */
function _getSourceNames(participantList: Array<string>, state: IReduxState): Array<string> {
    const { remoteScreenShares } = state['features/video-layout'];
    const tracks = state['features/base/tracks'];
    const sourceNamesList: string[] = [];

    participantList.forEach(participantId => {
        if (getSsrcRewritingFeatureFlag(state)) {
            const sourceNames: string[]
                = getSourceNamesByMediaTypeAndParticipant(state, participantId, MEDIA_TYPE.VIDEO);

            sourceNames.length && sourceNamesList.push(...sourceNames);
        } else {
            let sourceName: string;

            if (remoteScreenShares.includes(participantId)) {
                sourceName = participantId;
            } else {
                sourceName = getTrackSourceNameByMediaTypeAndParticipant(tracks, MEDIA_TYPE.VIDEO, participantId);
            }

            if (sourceName) {
                sourceNamesList.push(sourceName);
            }
        }
    });

    return sourceNamesList;
}

/**
 * Helper function for updating the preferred sender video constraint, based on the user preference.
 *
 * @param {number} preferred - The user preferred max frame height.
 * @returns {void}
 */
function _setSenderVideoConstraint(preferred: number, { getState }: IStore) {
    const state = getState();
    const { conference } = state['features/base/conference'];

    if (!conference) {
        return;
    }

    logger.info(`Setting sender resolution to ${preferred}`);
    conference.setSenderVideoConstraint(preferred)
        .catch((error: any) => {
            _handleParticipantError(error);
            reportError(error, `Changing sender resolution to ${preferred} failed.`);
        });
}

/**
 * Private helper to calculate the receiver video constraints and set them on the bridge channel.
 *
 * @param {*} store - The redux store.
 * @returns {void}
 */
function _updateReceiverVideoConstraints({ getState }: IStore) {
    const state = getState();
    const { conference } = state['features/base/conference'];

    if (!conference) {
        return;
    }
    const { lastN } = state['features/base/lastn'];
    const {
        maxReceiverVideoQualityForTileView,
        maxReceiverVideoQualityForStageFilmstrip,
        maxReceiverVideoQualityForVerticalFilmstrip,
        maxReceiverVideoQualityForLargeVideo,
        maxReceiverVideoQualityForScreenSharingFilmstrip,
        preferredVideoQuality
    } = state['features/video-quality'];
    const { participantId: largeVideoParticipantId = '' } = state['features/large-video'];
    const largeVideoParticipant = getParticipantById(state, largeVideoParticipantId);
    const maxFrameHeightForTileView = maxReceiverVideoQualityForTileView;
    const maxFrameHeightForStageFilmstrip = maxReceiverVideoQualityForStageFilmstrip;
    const maxFrameHeightForVerticalFilmstrip = maxReceiverVideoQualityForVerticalFilmstrip;
    const maxFrameHeightForLargeVideo = preferredVideoQuality;
    const maxFrameHeightForScreenSharingFilmstrip = maxReceiverVideoQualityForScreenSharingFilmstrip;
    const { remoteScreenShares } = state['features/video-layout'];
    const { visibleRemoteParticipants } = state['features/filmstrip'];
    const tracks = state['features/base/tracks'];
    const localParticipantId = getLocalParticipant(state)?.id;
    const activeParticipantsIds = getActiveParticipantsIds(state);
    const screenshareFilmstripParticipantId = isTopPanelEnabled(state) && getScreenshareFilmstripParticipantId(state);

    const { qualityLevel } = state['features/quality-control'];
    const qualityGroup = getQualityGroupByQualityLevel(qualityLevel);

    const receiverConstraints: any = {
        constraints: {},
        defaultConstraints: { 'maxHeight': VIDEO_QUALITY_LEVELS.NONE },
        lastN
    };

    let activeParticipantsSources: string[] = [];
    let visibleRemoteTrackSourceNames: string[] = [];
    let largeVideoSourceName: string | undefined;

    receiverConstraints.onStageSources = [];
    receiverConstraints.selectedSources = [];

    if (visibleRemoteParticipants?.size) {
        visibleRemoteTrackSourceNames = _getSourceNames(Array.from(visibleRemoteParticipants), state);
    }

    if (activeParticipantsIds?.length > 0) {
        activeParticipantsSources = _getSourceNames(activeParticipantsIds, state);
    }

    if (localParticipantId !== largeVideoParticipantId) {
        if (remoteScreenShares.includes(largeVideoParticipantId)) {
            largeVideoSourceName = largeVideoParticipantId;
        } else {
            largeVideoSourceName = getSsrcRewritingFeatureFlag(state)
                ? getSourceNamesByVideoTypeAndParticipant(state, largeVideoParticipantId, VIDEO_TYPE.CAMERA)[0]
                : getTrackSourceNameByMediaTypeAndParticipant(tracks, MEDIA_TYPE.VIDEO, largeVideoParticipantId);
        }
    }

    // Tile view.
    if (shouldDisplayTileView(state)) {
        if (!visibleRemoteTrackSourceNames?.length) {
            return;
        }

        visibleRemoteTrackSourceNames.forEach(sourceName => {
            receiverConstraints.constraints[sourceName] = { 'maxHeight': maxFrameHeightForTileView };
        });

        // Prioritize screenshare in tile view.
        if (remoteScreenShares?.length) {
            receiverConstraints.selectedSources = remoteScreenShares;
        }

        // Stage view.
    } else {
        if (!visibleRemoteTrackSourceNames?.length && !largeVideoSourceName && !activeParticipantsSources?.length) {
            return;
        }

        if (visibleRemoteTrackSourceNames?.length) {
            visibleRemoteTrackSourceNames.forEach(sourceName => {
                receiverConstraints.constraints[sourceName] = { 'maxHeight': maxFrameHeightForVerticalFilmstrip };
            });
        }

        if (getCurrentLayout(state) === LAYOUTS.STAGE_FILMSTRIP_VIEW && activeParticipantsSources.length > 0) {
            const selectedSources: string[] = [];
            const onStageSources: string[] = [];

            // If more than one video source is pinned to the stage filmstrip, they need to be added to the
            // 'selectedSources' so that the bridge can allocate bandwidth for all the sources as opposed to doing
            // greedy allocation for the sources (which happens when they are added to 'onStageSources').
            if (activeParticipantsSources.length > 1) {
                selectedSources.push(...activeParticipantsSources);
            } else {
                onStageSources.push(activeParticipantsSources[0]);
            }

            activeParticipantsSources.forEach(sourceName => {
                const isScreenSharing = remoteScreenShares.includes(sourceName);
                const quality
                    = isScreenSharing && preferredVideoQuality >= MAX_VIDEO_QUALITY
                    ? VIDEO_QUALITY_UNLIMITED : maxFrameHeightForStageFilmstrip;

                receiverConstraints.constraints[sourceName] = { 'maxHeight': quality };
            });

            if (screenshareFilmstripParticipantId) {
                onStageSources.push(screenshareFilmstripParticipantId);
                receiverConstraints.constraints[screenshareFilmstripParticipantId]
                    = {
                    'maxHeight':
                        preferredVideoQuality >= MAX_VIDEO_QUALITY
                            ? VIDEO_QUALITY_UNLIMITED : maxFrameHeightForScreenSharingFilmstrip
                };
            }

            receiverConstraints.onStageSources = onStageSources;
            receiverConstraints.selectedSources = selectedSources;
        } else if (largeVideoSourceName) {
            let quality = VIDEO_QUALITY_UNLIMITED;

            if (preferredVideoQuality < MAX_VIDEO_QUALITY
                || !remoteScreenShares.find(id => id === largeVideoParticipantId)) {
                quality = maxFrameHeightForLargeVideo;
            }
            if (isParticipantModerator(largeVideoParticipant)) {
                quality = qualityGroup.VIDEO_QUALITY_T1;
            }

            receiverConstraints.constraints[largeVideoSourceName] = { 'maxHeight': quality };
            receiverConstraints.onStageSources = [ largeVideoSourceName ];
        }
    }

    try {
        conference.setReceiverConstraints(receiverConstraints);
    } catch (error: any) {
        _handleParticipantError(error);
        reportError(error, `Failed to set receiver video constraints ${JSON.stringify(receiverConstraints)}`);
    }
}
