import React, { useState } from 'react';
import { useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import {
    getParticipantDisplayName,
    getScreenshareParticipantDisplayName,
    isScreenShareParticipant,
    isScreenShareParticipantById
} from '../../../base/participants/functions';
import { getLargeVideoParticipant } from '../../../large-video/functions';
import { getTransitionParamsForElementsAboveToolbox, isToolboxVisible } from '../../../toolbox/functions.web';
import { isLayoutTileView } from '../../../video-layout/functions.any';
import { shouldDisplayStageParticipantBadge } from '../../functions';

import DisplayNameBadge from './DisplayNameBadge';
import {
    getStageParticipantFontSizeRange,
    getStageParticipantNameLabelLineHeight,
    getStageParticipantTypography,
    scaleFontProperty
} from './styles';
import { getVideoTrackByParticipant, isLocalTrackMuted, isRemoteTrackMuted } from "../../../base/tracks/functions.any";
import { isMobileBrowser } from "../../../base/environment/utils";
import { isFilmstripDisabled } from "../../../filmstrip/functions.web";
import { PARTICIPANT_ROLE } from "../../../base/participants/constants";
import { MEDIA_TYPE } from "../../../base/media/constants";
import { THUMBNAIL_TYPE } from "../../../filmstrip/constants";
import VideoMenuTriggerButton from "../../../filmstrip/components/web/VideoMenuTriggerButton";

interface IOptions {
    clientHeight?: number;
}

const useStyles = makeStyles<IOptions, 'screenSharing'>()((theme, options: IOptions = {}, classes) => {
    const typography = {
        ...getStageParticipantTypography(theme)
    };
    const { clientHeight } = options;

    if (typeof clientHeight === 'number' && clientHeight > 0) {
        // We want to show the fontSize and lineHeight configured in theme on a screen with height 1080px. In this case
        // the clientHeight will be 960px if there are some titlebars, toolbars, addressbars, etc visible.For any other
        // screen size we will decrease/increase the font size based on the screen size.

        typography.fontSize = scaleFontProperty(clientHeight, getStageParticipantFontSizeRange(theme));
        typography.lineHeight = getStageParticipantNameLabelLineHeight(theme, clientHeight);
    }

    const toolbarVisibleTransitionProps = getTransitionParamsForElementsAboveToolbox(true);
    const toolbarHiddenTransitionProps = getTransitionParamsForElementsAboveToolbox(false);
    const showTransitionDuration = toolbarVisibleTransitionProps.delay + toolbarVisibleTransitionProps.duration;
    const hideTransitionDuration = toolbarHiddenTransitionProps.delay + toolbarHiddenTransitionProps.duration;
    const showTransition = `opacity ${showTransitionDuration}s ${toolbarVisibleTransitionProps.easingFunction}`;
    const hideTransition = `opacity ${hideTransitionDuration}s ${toolbarHiddenTransitionProps.easingFunction}`;

    return {
        badgeContainer: {
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '12px',
            lineHeight: '15px',
            alignItems: 'center',
            display: 'inline-flex',
            justifyContent: 'space-between',
            maxWidth: 'calc(100% - 16px)',
            marginLeft: '8px',
            marginBottom: '8px',
            pointerEvents: 'none',
            position: 'absolute',
            bottom: 0,
            left: 0,
            width: '100%',
            opacity: "1",
            zIndex: 1,
            transition: `${showTransition}`
        },
        screenSharing: {
            opacity: 0,
            transition: `${hideTransition}`
        },
        containerElevatedJustifyStart: {
            '&.is-mobile': {
                justifyContent: 'start',
            },
        },
        invisible: {
            opacity: "0",
            transition: "opacity .6s ease-in-out",
        },
        container: {
            display: 'flex',
            pointerEvents: "auto",
        },
    };
});

/**
 * Component that renders the dominant speaker's name as a badge above the toolbar in stage view.
 *
 * @returns {ReactElement|null}
 */
const StageParticipantNameLabel = () => {
    const clientHeight = useSelector((state: IReduxState) => state['features/base/responsive-ui'].clientHeight);
    const { classes, cx } = useStyles({ clientHeight });
    const largeVideoParticipant = useSelector(getLargeVideoParticipant);
    const selectedId = largeVideoParticipant?.id;
    const nameToDisplay = useSelector((state: IReduxState) => getParticipantDisplayName(state, selectedId ?? ''));
    const toolboxVisible: boolean = useSelector(isToolboxVisible);
    const visible = useSelector(shouldDisplayStageParticipantBadge);
    const isTileView = useSelector(isLayoutTileView);
    const _isScreenShareParticipant = isScreenShareParticipant(largeVideoParticipant);
    const _screenshareParticipantDisplayName = useSelector((state: IReduxState) => selectedId === undefined ? '' : getScreenshareParticipantDisplayName(state, selectedId));

    const isVirtualScreenshareParticipant = useSelector(
        (state: IReduxState) => isScreenShareParticipantById(state, selectedId)
    );
    const track = useSelector(
        (state: IReduxState) => getVideoTrackByParticipant(state, largeVideoParticipant)
    );
    const isScreenSharing = track?.videoType === 'desktop';

    const { visible: filmstripVisible } = useSelector((state: IReduxState) => state['features/filmstrip']);
    const filmstripDisabled = useSelector(isFilmstripDisabled);
    const isVisibleFilmStrip = filmstripVisible && !filmstripDisabled;
    const { disableModeratorIndicator } = useSelector((state: IReduxState) => state['features/base/config']);
    const showModeratorIndicator = !disableModeratorIndicator && largeVideoParticipant && largeVideoParticipant.role === PARTICIPANT_ROLE.MODERATOR;
    let isAudioMuted = true;

    const audio = !isVirtualScreenshareParticipant;
    const tracks = useSelector((state: IReduxState) => state['features/base/tracks']);
    if (largeVideoParticipant?.local) {
        isAudioMuted = isLocalTrackMuted(tracks, MEDIA_TYPE.AUDIO);
    } else if (!largeVideoParticipant?.fakeParticipant || isVirtualScreenshareParticipant) {
        isAudioMuted = isRemoteTrackMuted(tracks, MEDIA_TYPE.AUDIO, selectedId ?? '');
    }

    const [popoverVisible, setPopoverVisible] = useState(false);

    if (_isScreenShareParticipant) {
        return (
            <div
                className = {cx(
                    'stage-participant-label',
                    classes.badgeContainer,
                    isMobileBrowser() ? 'is-mobile' : '',
                    isVisibleFilmStrip && classes.containerElevatedJustifyStart,
                    !toolboxVisible && classes.invisible
                )}>
                <DisplayNameBadge
                    name = {_screenshareParticipantDisplayName}
                    showAudioIndicator = {false}
                    showModeratorIndicator = {false}
                    isScreenSharing = {isScreenSharing}
                    isVirtualScreenshareParticipant = {isVirtualScreenshareParticipant}
                />
            </div>
        );
    }

    if (visible || (_isScreenShareParticipant && !isTileView)) {
        // For stage participant visibility is true only when the toolbar is visible but we need to keep the element
        // in the DOM in order to make it disappear with an animation.
        return (
            <div
                className = {cx(
                    'stage-participant-label',
                    classes.badgeContainer,
                    isMobileBrowser() ? 'is-mobile' : '',
                    isVisibleFilmStrip && classes.containerElevatedJustifyStart,
                    !toolboxVisible && classes.invisible,
                    _isScreenShareParticipant && classes.screenSharing
                )}>
                <DisplayNameBadge
                    name = {nameToDisplay}
                    showAudioIndicator = {isAudioMuted && audio}
                    showModeratorIndicator = {showModeratorIndicator ?? false}
                    isScreenSharing = {isScreenSharing}
                    isVirtualScreenshareParticipant = {isVirtualScreenshareParticipant}
                />
                <div className = {classes.container}>
                    <VideoMenuTriggerButton
                        hidePopover = {() => setPopoverVisible(false)}
                        local = {largeVideoParticipant?.local ?? false}
                        participantId = {selectedId}
                        popoverVisible = {popoverVisible}
                        showPopover = {() => setPopoverVisible(true)}
                        thumbnailType = {THUMBNAIL_TYPE.HORIZONTAL}
                        visible = {visible} />
                </div>
            </div>
        );
    }

    return null;
};

export default StageParticipantNameLabel;
