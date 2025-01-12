import React, { useCallback } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { getParticipantById, isScreenShareParticipant } from '../../../base/participants/functions';
import { getLargeVideoParticipant } from '../../../large-video/functions';
import { getTransitionParamsForElementsAboveToolbox, isToolboxVisible } from '../../../toolbox/functions.web';
import { isLayoutTileView } from '../../../video-layout/functions.any';
import { shouldDisplayStageParticipantBadge } from '../../functions';
import { isMobileBrowser } from "../../../base/environment/utils";
import { isFilmstripDisabled } from "../../../filmstrip/functions.web";
import PinnedIndicator from "../../../filmstrip/components/web/PinnedIndicator";
import { NOTIFY_CLICK_MODE } from "../../../toolbox/types";
import { togglePinStageParticipant } from "../../../filmstrip/actions.web";
import { pinParticipant } from "../../../base/participants/actions";

const useStyles = makeStyles()(() => {
    return {
        badgeContainer: {
            alignItems: 'center',
            display: 'inline-flex',
            justifyContent: 'start',
            marginLeft: '8px',
            marginTop: '8px',
            pointerEvents: 'none',
            position: 'absolute',
            top: 0,
            left: 0,
            width: '100%',
            zIndex: 1,
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
const StageParticipantTopIndicators = () => {
    const { classes, cx } = useStyles();
    const dispatch = useDispatch();
    const largeVideoParticipant = useSelector(getLargeVideoParticipant);
    const selectedId = largeVideoParticipant === undefined ? '' : largeVideoParticipant.id;
    const toolboxVisible: boolean = useSelector(isToolboxVisible);
    const isTileView = useSelector(isLayoutTileView);

    const onClick = useCallback(() => {
        dispatch(pinParticipant(largeVideoParticipant?.pinned ? null : selectedId));
    }, [ dispatch, largeVideoParticipant, selectedId ]);

    if (!isTileView && !isMobileBrowser()) {
        return (
            <div
                className = {cx(
                    'stage-participant-label',
                    classes.badgeContainer,
                    !toolboxVisible && classes.invisible,
                )}>
                <PinnedIndicator
                    iconSize = {16}
                    participantId = {selectedId}
                    tooltipPosition = 'top'
                    onIndicatorClick = {onClick} />
            </div>
        );
    }

    return null;
};

export default StageParticipantTopIndicators;
