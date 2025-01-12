/* global APP  */

import Logger from '@jitsi/logger';

import { isMobileBrowser } from '../../../react/features/base/environment/utils';
import { MEDIA_TYPE, VIDEO_TYPE } from '../../../react/features/base/media/constants';
import {
    getParticipantById,
    getPinnedParticipant,
    isScreenShareParticipantById
} from '../../../react/features/base/participants/functions';
import {
    getTrackByMediaTypeAndParticipant,
    getVideoTrackByParticipant
} from '../../../react/features/base/tracks/functions.any';

import LargeVideoManager from './LargeVideoManager';
import MinimizedVideoManager from './MinimizedVideoManager';
import { VIDEO_CONTAINER_TYPE } from './VideoContainer';

const logger = Logger.getLogger(__filename);
let largeVideo;
let minimizedVideo;

const VideoLayout = {
    /**
     * Handler for local flip X changed event.
     */
    onLocalFlipXChanged(localFlipX) {
        if (largeVideo) {
            largeVideo.onLocalFlipXChange(localFlipX);
        }

        if (minimizedVideo && isMobileBrowser()) {
            minimizedVideo.onLocalFlipXChange(localFlipX);
        }
    },

    /**
     * Cleans up state of this singleton {@code VideoLayout}.
     *
     * @returns {void}
     */
    reset() {
        this._resetLargeVideo();
        this._resetMinimizedVideo();
    },

    initLargeVideo() {
        this._resetLargeVideo();

        largeVideo = new LargeVideoManager();

        const { store } = APP;
        const { localFlipX } = store.getState()['features/base/settings'];

        if (typeof localFlipX === 'boolean') {
            largeVideo.onLocalFlipXChange(localFlipX);
        }
        largeVideo.updateContainerSize();
    },

    initMinimizedVideo() {

        if (!isMobileBrowser()) {
            return;
        }

        this._resetMinimizedVideo();

        minimizedVideo = new MinimizedVideoManager();

        const { store } = APP;
        const { localFlipX } = store.getState()['features/base/settings'];

        if (typeof localFlipX === 'boolean') {
            minimizedVideo.onLocalFlipXChange(localFlipX);
        }
        minimizedVideo.updateContainerSize();
    },

    /**
     * Sets the audio level of the video elements associated to the given id.
     *
     * @param id the video identifier in the form it comes from the library
     * @param lvl the new audio level to update to
     */
    setAudioLevel(id, lvl) {
        if (largeVideo && id === largeVideo.id) {
            largeVideo.updateLargeVideoAudioLevel(lvl);
        }
        if (minimizedVideo && id === minimizedVideo.id && isMobileBrowser()) {
            minimizedVideo.updateMinimizedVideoAudioLevel(lvl);
        }
    },

    /**
     * FIXME get rid of this method once muted indicator are reactified (by
     * making sure that user with no tracks is displayed as muted )
     *
     * If participant has no tracks will make the UI display muted status.
     * @param {string} participantId
     */
    updateVideoMutedForNoTracks(participantId) {
        const participant = APP.conference.getParticipantById(participantId);

        if (participant && !participant.getTracksByMediaType('video').length) {
            VideoLayout._updateLargeVideoIfDisplayed(participantId, true);

            if (isMobileBrowser()) {
                VideoLayout._updateMinimizedVideoIfDisplayed(participantId, true);
            }
        }
    },

    /**
     * Return the type of the remote video.
     * @param id the id for the remote video
     * @returns {String} the video type video or screen.
     */
    getRemoteVideoType(id) {
        const state = APP.store.getState();
        const participant = getParticipantById(state, id);
        const isScreenShare = isScreenShareParticipantById(state, id);

        if (participant?.fakeParticipant && !isScreenShare) {
            return VIDEO_TYPE.CAMERA;
        }

        if (isScreenShare) {
            return VIDEO_TYPE.DESKTOP;
        }

        const videoTrack = getTrackByMediaTypeAndParticipant(state['features/base/tracks'], MEDIA_TYPE.VIDEO, id);

        return videoTrack?.videoType;
    },

    getPinnedId() {
        const { id } = getPinnedParticipant(APP.store.getState()) || {};

        return id || null;
    },

    /**
     * On last N change event.
     *
     * @param endpointsLeavingLastN the list currently leaving last N
     * endpoints
     * @param endpointsEnteringLastN the list currently entering last N
     * endpoints
     */
    onLastNEndpointsChanged(endpointsLeavingLastN, endpointsEnteringLastN) {
        if (endpointsLeavingLastN) {
            endpointsLeavingLastN.forEach(this._updateLargeVideoIfDisplayed, this);
            if (isMobileBrowser()) {
                endpointsLeavingLastN.forEach(this._updateMinimizedVideoIfDisplayed, this);
            }
        }

        if (endpointsEnteringLastN) {
            endpointsEnteringLastN.forEach(this._updateLargeVideoIfDisplayed, this);
            if (isMobileBrowser()) {
                endpointsEnteringLastN.forEach(this._updateMinimizedVideoIfDisplayed, this);
            }
        }
    },

    /**
     * Resizes the video area.
     */
    resizeVideoArea() {
        if (largeVideo) {
            largeVideo.updateContainerSize();
            largeVideo.resize(false);
        }

        if (isMobileBrowser() && minimizedVideo) {
            minimizedVideo.updateContainerSize();
            minimizedVideo.resize(false);
        }
    },

    isLargeVideoVisible() {
        return this.isLargeContainerTypeVisible(VIDEO_CONTAINER_TYPE);
    },

    isMinimizedVideoVisible() {
        return isMobileBrowser() && this.isMinimizedContainerTypeVisible(VIDEO_CONTAINER_TYPE);
    },

    /**
     * @return {LargeContainer} the currently displayed container on large
     * video.
     */
    getCurrentlyOnLargeContainer() {
        return largeVideo.getCurrentContainer();
    },

    /**
     * @return {LargeContainer} the currently displayed container on minimized
     * video.
     */
    getCurrentlyOnMinimizedContainer() {
        return minimizedVideo.getCurrentContainer();
    },

    isCurrentlyOnLarge(id) {
        return largeVideo && largeVideo.id === id;
    },

    isCurrentlyOnMinimized(id) {
        return isMobileBrowser() && minimizedVideo && minimizedVideo.id === id;
    },

    updateLargeVideo(id, forceUpdate, forceStreamToReattach = false) {
        if (!largeVideo) {
            logger.debug(`Ignoring large video update with user id ${id}: large video not initialized yet!`);

            return;
        }
        const currentContainer = largeVideo.getCurrentContainer();
        const currentContainerType = largeVideo.getCurrentContainerType();
        const isOnLarge = this.isCurrentlyOnLarge(id);
        const state = APP.store.getState();
        const participant = getParticipantById(state, id);
        const videoTrack = getVideoTrackByParticipant(state, participant);
        const videoStream = videoTrack?.jitsiTrack;

        if (videoStream && forceStreamToReattach) {
            videoStream.forceStreamToReattach = forceStreamToReattach;
        }

        if (isOnLarge && !forceUpdate
                && LargeVideoManager.isVideoContainer(currentContainerType)
                && videoStream) {
            const currentStreamId = currentContainer.getStreamID();
            const newStreamId = videoStream?.getId() || null;

            // FIXME it might be possible to get rid of 'forceUpdate' argument
            if (currentStreamId !== newStreamId) {
                logger.debug('Enforcing large video update for stream change');
                forceUpdate = true; // eslint-disable-line no-param-reassign
            }
        }

        if (!isOnLarge || forceUpdate) {
            const videoType = this.getRemoteVideoType(id);

            largeVideo.updateLargeVideo(
                id,
                videoStream,
                videoType || VIDEO_TYPE.CAMERA
            ).catch(() => {
                // do nothing
            });
        }
    },

    updateMinimizedVideo(id, forceUpdate, forceStreamToReattach = false) {
        if (!minimizedVideo || !isMobileBrowser()) {
            logger.debug(`Ignoring minimized video update with user id ${id}: minimized video not initialized yet!`);

            return;
        }
        const currentContainer = minimizedVideo.getCurrentContainer();
        const currentContainerType = minimizedVideo.getCurrentContainerType();
        const isOnMinimized = this.isCurrentlyOnMinimized(id);
        const state = APP.store.getState();
        const participant = getParticipantById(state, id);
        const videoTrack = getVideoTrackByParticipant(state, participant);
        const videoStream = videoTrack?.jitsiTrack;

        if (videoStream && forceStreamToReattach) {
            videoStream.forceStreamToReattach = forceStreamToReattach;
        }

        if (isOnMinimized && !forceUpdate
                && MinimizedVideoManager.isVideoContainer(currentContainerType)
                && videoStream) {
            const currentStreamId = currentContainer.getStreamID();
            const newStreamId = videoStream?.getId() || null;

            // FIXME it might be possible to get rid of 'forceUpdate' argument
            if (currentStreamId !== newStreamId) {
                logger.debug('Enforcing minimized video update for stream change');
                forceUpdate = true; // eslint-disable-line no-param-reassign
            }
        }

        if (!isOnMinimized || forceUpdate) {
            const videoType = this.getRemoteVideoType(id);

            minimizedVideo.updateMinimizedVideo(
                id,
                videoStream,
                videoType || VIDEO_TYPE.CAMERA
            ).catch(() => {
                // do nothing
            });
        }
    },

    addLargeVideoContainer(type, container) {
        largeVideo && largeVideo.addContainer(type, container);
    },

    addMinimizedVideoContainer(type, container) {
        (minimizedVideo && isMobileBrowser()) && minimizedVideo.addContainer(type, container);
    },

    removeLargeVideoContainer(type) {
        largeVideo && largeVideo.removeContainer(type);
    },

    removeMinimizedVideoContainer(type) {
        (minimizedVideo && isMobileBrowser()) && minimizedVideo.removeContainer(type);
    },

    /**
     * @returns Promise
     */
    showLargeVideoContainer(type, show) {
        if (!largeVideo) {
            return Promise.reject();
        }

        const isVisible = this.isLargeContainerTypeVisible(type);

        if (isVisible === show) {
            return Promise.resolve();
        }

        let containerTypeToShow = type;

        // if we are hiding a container and there is focusedVideo
        // (pinned remote video) use its video type,
        // if not then use default type - large video

        if (!show) {
            const pinnedId = this.getPinnedId();

            if (pinnedId) {
                containerTypeToShow = this.getRemoteVideoType(pinnedId);
            } else {
                containerTypeToShow = VIDEO_CONTAINER_TYPE;
            }
        }

        return largeVideo.showContainer(containerTypeToShow);
    },

    /**
     * @returns Promise
     */
    showMinimizedVideoContainer(type, show) {
        if (!minimizedVideo || !isMobileBrowser()) {
            return Promise.reject();
        }

        const isVisible = this.isMinimizedContainerTypeVisible(type);

        if (isVisible === show) {
            return Promise.resolve();
        }

        let containerTypeToShow = type;

        // if we are hiding a container and there is focusedVideo
        // (pinned remote video) use its video type,
        // if not then use default type - minimized video

        if (!show) {
            const pinnedId = this.getPinnedId();

            if (pinnedId) {
                containerTypeToShow = this.getRemoteVideoType(pinnedId);
            } else {
                containerTypeToShow = VIDEO_CONTAINER_TYPE;
            }
        }

        return minimizedVideo.showContainer(containerTypeToShow);
    },

    isLargeContainerTypeVisible(type) {
        return largeVideo && largeVideo.state === type;
    },

    isMinimizedContainerTypeVisible(type) {
        return (minimizedVideo && isMobileBrowser()) && minimizedVideo.state === type;
    },

    /**
     * Returns the id of the current video shown on large.
     * Currently used by tests (torture).
     */
    getLargeVideoID() {
        return largeVideo && largeVideo.id;
    },

    /**
     * Returns the id of the current video shown on large.
     * Currently used by tests (torture).
     */
    getMinimizedVideoID() {
        return (minimizedVideo && isMobileBrowser()) && minimizedVideo.id;
    },

    /**
     * Returns the the current video shown on large.
     * Currently used by tests (torture).
     */
    getLargeVideo() {
        return largeVideo;
    },

    /**
     * Returns the the current video shown on minimized.
     * Currently used by tests (torture).
     */
    getMinimizedVideo() {
        return minimizedVideo;
    },

    /**
     * Returns the wrapper jquery selector for the largeVideo
     * @returns {JQuerySelector} the wrapper jquery selector for the largeVideo
     */
    getLargeVideoWrapper() {
        return this.getCurrentlyOnLargeContainer().$wrapper;
    },

    /**
     * Returns the wrapper jquery selector for the minimizedVideo
     * @returns {JQuerySelector} the wrapper jquery selector for the minimizedVideo
     */
    getMinimizedVideoWrapper() {
        return this.getCurrentlyOnMinimizedContainer().$wrapper;
    },

    /**
     * Helper method to invoke when the video layout has changed and elements
     * have to be re-arranged and resized.
     *
     * @returns {void}
     */
    refreshLayout() {
        VideoLayout.resizeVideoArea();
    },

    /**
     * Cleans up any existing largeVideo instance.
     *
     * @private
     * @returns {void}
     */
    _resetLargeVideo() {
        if (largeVideo) {
            largeVideo.destroy();
        }

        largeVideo = null;
    },

    /**
     * Cleans up any existing largeVideo instance.
     *
     * @private
     * @returns {void}
     */
    _resetMinimizedVideo() {
        if (minimizedVideo) {
            minimizedVideo.destroy();
        }

        minimizedVideo = null;
    },

    /**
     * Triggers an update of large video if the passed in participant is
     * currently displayed on large video.
     *
     * @param {string} participantId - The participant ID that should trigger an
     * update of large video if displayed.
     * @param {boolean} force - Whether or not the large video update should
     * happen no matter what.
     * @returns {void}
     */
    _updateLargeVideoIfDisplayed(participantId, force = false) {
        if (this.isCurrentlyOnLarge(participantId)) {
            this.updateLargeVideo(participantId, force, false);
        }
    },

    /**
     * Triggers an update of minimized video if the passed in participant is
     * currently displayed on minimized video.
     *
     * @param {string} participantId - The participant ID that should trigger an
     * update of minimized video if displayed.
     * @param {boolean} force - Whether or not the large video update should
     * happen no matter what.
     * @returns {void}
     */
    _updateMinimizedVideoIfDisplayed(participantId, force = false) {
        if (this.isCurrentlyOnMinimized(participantId)) {
            this.updateMinimizedVideo(participantId, force, false);
        }
    },

    /**
     * Handles window resizes.
     */
    onResize() {
        VideoLayout.resizeVideoArea();
    }
};

export default VideoLayout;
