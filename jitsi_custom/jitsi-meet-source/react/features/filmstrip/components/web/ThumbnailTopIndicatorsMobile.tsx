import React from 'react';
import { useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { isMobileBrowser } from "../../../base/environment/utils";
import { STATS_POPOVER_POSITION, THUMBNAIL_TYPE } from '../../constants';
import { getTopIndicatorsTooltipPosition } from '../../functions.web';

import PinnedIndicator from './PinnedIndicator';
import RaisedHandIndicator from './RaisedHandIndicator';
import StatusIndicators from './StatusIndicators';
import VideoMenuTriggerButton from "./VideoMenuTriggerButton";
import ConnectionIndicator from "../../../connection-indicator/components/web/ConnectionIndicator";
import { IReduxState } from "../../../app/types";
import { isScreenShareParticipantById } from "../../../base/participants/functions";
import StatusIndicatorsMobile from "./StatusIndicatorsMobile";

interface IProps {

    /**
     * Whether to hide the connection indicator.
     */
    disableConnectionIndicator?: boolean;

    /**
     * Class name for the status indicators container.
     */
    indicatorsClassName?: string;

    /**
     * Id of the participant for which the component is displayed.
     */
    participantId: string;

    /**
     * The type of thumbnail.
     */
    thumbnailType: string;
}

const useStyles = makeStyles()(() => {
    return {
        container: {
            display: 'flex',
            width: '100%',
            justifyContent: 'space-between',

            '& > *:not(:last-child)': {
                marginRight: '4px'
            }
        }
    };
});

const ThumbnailTopIndicatorsMobile = ({
    disableConnectionIndicator,
    indicatorsClassName,
    participantId,
    thumbnailType
}: IProps) => {
    const { classes: styles, cx } = useStyles();

    const { NORMAL = 16 } = interfaceConfig.INDICATOR_FONT_SIZES || {};
    const _indicatorIconSize = NORMAL;
    const _connectionIndicatorAutoHideEnabled = Boolean(
        useSelector((state: IReduxState) => state['features/base/config'].connectionIndicators?.autoHide) ?? true);
    const _connectionIndicatorDisabled = disableConnectionIndicator
        || Boolean(useSelector((state: IReduxState) => state['features/base/config'].connectionIndicators?.disabled));
    const showConnectionIndicator = !_connectionIndicatorAutoHideEnabled;
    const isVirtualScreenshareParticipant = useSelector(
        (state: IReduxState) => isScreenShareParticipantById(state, participantId)
    );

    if (isVirtualScreenshareParticipant) {
        return (
            <div className = {styles.container}>
                {!_connectionIndicatorDisabled
                    && <ConnectionIndicator
                        alwaysVisible = {showConnectionIndicator}
                        enableStatsDisplay = {true}
                        iconSize = {_indicatorIconSize}
                        participantId = {participantId}
                        statsPopoverPosition = {STATS_POPOVER_POSITION[thumbnailType]} />
                }
            </div>
        );
    }

    const tooltipPosition = getTopIndicatorsTooltipPosition(thumbnailType);

    return (<>
        <div className = {styles.container}>
            <div style = {{
                display: 'flex',
                gap: '8px',
            }}>
                {thumbnailType === THUMBNAIL_TYPE.TILE && (
                    <div className = {cx(indicatorsClassName, 'top-indicators')}>
                        <StatusIndicatorsMobile
                            participantID = {participantId}
                            moderator = {true}
                            screenshare = {false} />
                    </div>
                )}
                <RaisedHandIndicator
                    iconSize = {_indicatorIconSize}
                    participantId = {participantId}
                    tooltipPosition = {tooltipPosition} />
            </div>
            {thumbnailType === THUMBNAIL_TYPE.TILE && !_connectionIndicatorDisabled && (
                <ConnectionIndicator
                    alwaysVisible = {showConnectionIndicator}
                    enableStatsDisplay = {true}
                    iconSize = {_indicatorIconSize}
                    participantId = {participantId}
                    statsPopoverPosition = {STATS_POPOVER_POSITION[thumbnailType]} />
            )}
        </div>
    </>);
};

export default ThumbnailTopIndicatorsMobile;
