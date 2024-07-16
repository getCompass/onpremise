import React from 'react';
import {useSelector} from 'react-redux';
import {makeStyles} from 'tss-react/mui';

import {IReduxState} from '../../../app/types';
import {isDisplayNameVisible} from '../../../base/config/functions.any';
import {
    getLocalParticipant,
    getParticipantDisplayName, isScreenShareParticipantById,
    isWhiteboardParticipant
} from '../../../base/participants/functions';
import {withPixelLineHeight} from '../../../base/styles/functions.web';
import {getLargeVideoParticipant} from '../../../large-video/functions';
import {isToolboxVisible} from '../../../toolbox/functions.web';
import {isLayoutTileView} from '../../../video-layout/functions.web';

import DisplayNameBadge from './DisplayNameBadge';
import ScreenShareIndicator from "../../../filmstrip/components/web/ScreenShareIndicator";
import {getVideoTrackByParticipant} from "../../../base/tracks/functions.any";
import {isMobileBrowser} from "../../../base/environment/utils";
import {FILMSTRIP_TYPE} from "../../../filmstrip/constants";
import {isFilmstripDisabled, isStageFilmstripTopPanel} from "../../../filmstrip/functions.web";

const useStyles = makeStyles()(theme => {
    return {
        badgeContainer: {
            ...withPixelLineHeight(theme.typography.bodyShortRegularLarge),
            alignItems: 'center',
            display: 'inline-flex',
            justifyContent: 'center',
            marginBottom: '48px',
            transition: 'margin-bottom 0.3s',
            pointerEvents: 'none',
            position: 'absolute',
            bottom: 0,
            left: 0,
            width: '100%',
            opacity: "1",
            zIndex: 1
        },
        containerElevated: {
            marginBottom: '84px',

            '&.is-mobile': {
                padding: '0 12px',
            },
        },
        containerElevatedJustifyStart: {
            '&.is-mobile': {
                justifyContent: 'start',
            },
        },
        invisible: {
            opacity: "0",
            transition: "opacity .6s ease-in-out",
        }
    };
});

/**
 * Component that renders the dominant speaker's name as a badge above the toolbar in stage view.
 *
 * @returns {ReactElement|null}
 */
const StageParticipantNameLabel = () => {
    const {classes, cx} = useStyles();
    const largeVideoParticipant = useSelector(getLargeVideoParticipant);
    const selectedId = largeVideoParticipant?.id;
    const nameToDisplay = useSelector((state: IReduxState) => getParticipantDisplayName(state, selectedId ?? ''));

    const localParticipant = useSelector(getLocalParticipant);
    const localId = localParticipant?.id;

    const isTileView = useSelector(isLayoutTileView);
    const toolboxVisible: boolean = useSelector(isToolboxVisible);
    const showDisplayName = useSelector(isDisplayNameVisible);
    const isVirtualScreenshareParticipant = useSelector(
        (state: IReduxState) => isScreenShareParticipantById(state, selectedId)
    );
    const track = useSelector(
        (state: IReduxState) => getVideoTrackByParticipant(state, largeVideoParticipant)
    );
    const isScreenSharing = track?.videoType === 'desktop';

    const {visible} = useSelector((state: IReduxState) => state['features/filmstrip']);
    const filmstripDisabled = useSelector(isFilmstripDisabled);
    const isVisibleFilmStrip = visible && !filmstripDisabled;

    if (showDisplayName
        && nameToDisplay
        && selectedId !== localId
        && !isTileView
        && !isWhiteboardParticipant(largeVideoParticipant)
    ) {
        return (
            <div
                className={cx(
                    'stage-participant-label',
                    classes.badgeContainer,
                    isMobileBrowser() ? 'is-mobile' : '',
                    toolboxVisible && classes.containerElevated,
                    isVisibleFilmStrip && classes.containerElevatedJustifyStart,
                    !toolboxVisible && classes.invisible
                )}>
                <DisplayNameBadge
                    name={nameToDisplay}
                    isScreenSharing={isScreenSharing}
                    isVirtualScreenshareParticipant={isVirtualScreenshareParticipant}
                />
            </div>
        );
    }

    return null;
};

export default StageParticipantNameLabel;
