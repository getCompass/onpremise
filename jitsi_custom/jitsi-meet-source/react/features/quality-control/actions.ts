import { REDUCER_UPDATE_QUALITY } from './actionTypes';
import {
    setMaxReceiverVideoQualityForScreenSharingFilmstrip,
    setMaxReceiverVideoQualityForStageFilmstrip,
    setMaxReceiverVideoQualityForTileView,
    setMaxReceiverVideoQualityForVerticalFilmstrip,
    setPreferredVideoQuality
} from "../video-quality/actions";
import { VIDEO_QUALITY_GROUPS } from "../video-quality/constants";
import { IStore } from "../app/types";
import { IJitsiParticipant, ISourceInfo } from "../base/participants/types";
import { MEDIA_TYPE, VIDEO_TYPE } from "../base/media/constants";

export function getQualityGroupByQualityLevel(qualityLevel: string) {

    switch (qualityLevel) {

    case "medium":
        return VIDEO_QUALITY_GROUPS.VIDEO_GROUP_QUALITY_MEDIUM;

    case "low":
        return VIDEO_QUALITY_GROUPS.VIDEO_GROUP_QUALITY_LOW;

    default:
        return VIDEO_QUALITY_GROUPS.VIDEO_GROUP_QUALITY_HIGH;
    }
}

export function setNewReceiverQuality(numberOfParticipants: number, participants: any, isLocalVideoTrackMuted: boolean, dispatch: IStore['dispatch'], quality: string) {

    const qualityGroup = getQualityGroupByQualityLevel(quality);
    const numberOfParticipantsWithVideo = getParticipantsCountWithVideo(participants, isLocalVideoTrackMuted);

    if (numberOfParticipants >= 10) {

        dispatch(setMaxReceiverVideoQualityForTileView(qualityGroup.VIDEO_QUALITY_T4));
        dispatch(setMaxReceiverVideoQualityForStageFilmstrip(qualityGroup.VIDEO_QUALITY_T4));
        dispatch(setMaxReceiverVideoQualityForVerticalFilmstrip(qualityGroup.VIDEO_QUALITY_T4));
        dispatch(setMaxReceiverVideoQualityForScreenSharingFilmstrip(qualityGroup.VIDEO_QUALITY_SCREEN_SHARE));
        if (numberOfParticipantsWithVideo >= 10) {
            dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T2));
        } else {
            dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T1));
        }
        return;
    }

    if (numberOfParticipants >= 7) {

        dispatch(setMaxReceiverVideoQualityForTileView(qualityGroup.VIDEO_QUALITY_T3_MIN));
        dispatch(setMaxReceiverVideoQualityForStageFilmstrip(qualityGroup.VIDEO_QUALITY_T3_MIN));
        dispatch(setMaxReceiverVideoQualityForVerticalFilmstrip(qualityGroup.VIDEO_QUALITY_T3_MIN));
        dispatch(setMaxReceiverVideoQualityForScreenSharingFilmstrip(qualityGroup.VIDEO_QUALITY_SCREEN_SHARE));
        if (numberOfParticipantsWithVideo >= 5) {
            dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T2));
        } else {
            dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T1));
        }
        return;
    }
    if (numberOfParticipants >= 5) {

        dispatch(setMaxReceiverVideoQualityForTileView(qualityGroup.VIDEO_QUALITY_T3));
        dispatch(setMaxReceiverVideoQualityForStageFilmstrip(qualityGroup.VIDEO_QUALITY_T3));
        dispatch(setMaxReceiverVideoQualityForVerticalFilmstrip(qualityGroup.VIDEO_QUALITY_T3));
        dispatch(setMaxReceiverVideoQualityForScreenSharingFilmstrip(qualityGroup.VIDEO_QUALITY_SCREEN_SHARE));
        if (numberOfParticipantsWithVideo >= 5) {
            dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T2));
        } else {
            dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T1));
        }
        return;
    }

    dispatch(setMaxReceiverVideoQualityForTileView(qualityGroup.VIDEO_QUALITY_T2));
    dispatch(setMaxReceiverVideoQualityForStageFilmstrip(qualityGroup.VIDEO_QUALITY_T2));
    dispatch(setMaxReceiverVideoQualityForVerticalFilmstrip(qualityGroup.VIDEO_QUALITY_T2));
    dispatch(setMaxReceiverVideoQualityForScreenSharingFilmstrip(qualityGroup.VIDEO_QUALITY_SCREEN_SHARE));
    dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T1));
}

export function setNewReceiverQualityOnParticipantLeft(numberOfParticipants: number, participants: any, isLocalVideoTrackMuted: boolean, dispatch: IStore['dispatch'], quality: string) {

    const qualityGroup = getQualityGroupByQualityLevel(quality);
    const numberOfParticipantsWithVideo = getParticipantsCountWithVideo(participants, isLocalVideoTrackMuted);

    if (numberOfParticipants >= 11) {

        dispatch(setMaxReceiverVideoQualityForTileView(qualityGroup.VIDEO_QUALITY_T4));
        dispatch(setMaxReceiverVideoQualityForStageFilmstrip(qualityGroup.VIDEO_QUALITY_T4));
        dispatch(setMaxReceiverVideoQualityForVerticalFilmstrip(qualityGroup.VIDEO_QUALITY_T4));
        dispatch(setMaxReceiverVideoQualityForScreenSharingFilmstrip(qualityGroup.VIDEO_QUALITY_SCREEN_SHARE));
        if (numberOfParticipantsWithVideo >= 6) {
            dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T2));
        } else {
            dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T1));
        }
        return;
    }

    if (numberOfParticipants >= 8) {

        dispatch(setMaxReceiverVideoQualityForTileView(qualityGroup.VIDEO_QUALITY_T3_MIN));
        dispatch(setMaxReceiverVideoQualityForStageFilmstrip(qualityGroup.VIDEO_QUALITY_T3_MIN));
        dispatch(setMaxReceiverVideoQualityForVerticalFilmstrip(qualityGroup.VIDEO_QUALITY_T3_MIN));
        dispatch(setMaxReceiverVideoQualityForScreenSharingFilmstrip(qualityGroup.VIDEO_QUALITY_SCREEN_SHARE));
        if (numberOfParticipantsWithVideo >= 6) {
            dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T2));
        } else {
            dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T1));
        }
        return;
    }

    if (numberOfParticipants >= 6) {

        dispatch(setMaxReceiverVideoQualityForTileView(qualityGroup.VIDEO_QUALITY_T3));
        dispatch(setMaxReceiverVideoQualityForStageFilmstrip(qualityGroup.VIDEO_QUALITY_T3));
        dispatch(setMaxReceiverVideoQualityForVerticalFilmstrip(qualityGroup.VIDEO_QUALITY_T3));
        dispatch(setMaxReceiverVideoQualityForScreenSharingFilmstrip(qualityGroup.VIDEO_QUALITY_SCREEN_SHARE));
        if (numberOfParticipantsWithVideo >= 6) {
            dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T2));
        } else {
            dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T1));
        }
        return;
    }

    dispatch(setMaxReceiverVideoQualityForTileView(qualityGroup.VIDEO_QUALITY_T2));
    dispatch(setMaxReceiverVideoQualityForStageFilmstrip(qualityGroup.VIDEO_QUALITY_T2));
    dispatch(setMaxReceiverVideoQualityForVerticalFilmstrip(qualityGroup.VIDEO_QUALITY_T2));
    dispatch(setMaxReceiverVideoQualityForScreenSharingFilmstrip(qualityGroup.VIDEO_QUALITY_SCREEN_SHARE));
    dispatch(setPreferredVideoQuality(qualityGroup.VIDEO_QUALITY_T1));
}

function getParticipantsCountWithVideo(participants: any, isLocalTrackMuted: boolean): number {

    let totalParticipantsWithCameraCount = 0;
    const participantsWithCameraCount = participants?.reduce((participantsWithCameraCount: number, participant: IJitsiParticipant) => {
        const sources: Map<string, Map<string, ISourceInfo>> = participant.getSources();
        const videoSources = sources.get(MEDIA_TYPE.VIDEO);
        const cameraSources = Array.from(videoSources ?? new Map())
            .filter(source => source[1].videoType === VIDEO_TYPE.CAMERA && !source[1].muted)
            .map(source => source[0]);

        participantsWithCameraCount = participantsWithCameraCount + cameraSources.length;

        return participantsWithCameraCount;
    }, 0);

    if (participantsWithCameraCount !== undefined) {
        totalParticipantsWithCameraCount += participantsWithCameraCount;
    }

    if (!isLocalTrackMuted) {
        totalParticipantsWithCameraCount++;
    }

    return totalParticipantsWithCameraCount;
}

/**
 * Action used to set the flag for quality level
 *
 * @param {String} value - The config options that override the default ones (if any).
 * @returns {Function}
 */
export function setReducerQuality(value: string) {
    return {
        type: REDUCER_UPDATE_QUALITY,
        value,
    };
}