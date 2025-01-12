import React from 'react';
import { useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { isDisplayNameVisible, isNameReadOnly } from '../../../base/config/functions.any';
import { isScreenShareParticipantById } from '../../../base/participants/functions';
import DisplayName from '../../../display-name/components/web/DisplayName';

import StatusIndicators from './StatusIndicators';
import { isToolboxVisible } from "../../../toolbox/functions.web";
import ConnectionIndicator from "../../../connection-indicator/components/web/ConnectionIndicator";
import { STATS_POPOVER_POSITION, THUMBNAIL_TYPE } from "../../constants";
import { isMobileBrowser } from "../../../base/environment/utils";
import VideoMenuTriggerButton from "./VideoMenuTriggerButton";

interface IProps {

    /**
     * Whether to hide the connection indicator.
     */
    disableConnectionIndicator?: boolean;

    /**
     * Hide popover callback.
     */
    hidePopover?: Function;

    /**
     * Class name for indicators container.
     */
    className?: string;

    /**
     * Whether or not the thumbnail is hovered.
     */
    isHovered: boolean;

    /**
     * Whether or not the indicators are for the local participant.
     */
    local: boolean;

    /**
     * Id of the participant for which the component is displayed.
     */
    participantId: string;

    /**
     * Whether popover is visible or not.
     */
    popoverVisible?: boolean;

    /**
     * Show popover callback.
     */
    showPopover?: Function;

    /**
     * Whether or not to show the status indicators.
     */
    showStatusIndicators?: boolean;

    /**
     * The type of thumbnail.
     */
    thumbnailType?: string;
}

const useStyles = makeStyles()(() => {
    return {
        container: {
            display: 'flex',

            '& > *:not(:last-child)': {
                marginRight: '4px'
            }
        },
        nameContainer: {
            display: 'flex',
            overflow: 'hidden',
            opacity: "1",

            '&>div': {
                display: 'flex',
                overflow: 'hidden'
            }
        },
        indicatorsContainer2: {
            display: 'flex',
            width: '100%',
            justifyContent: 'space-between',
            marginLeft: "8px",
        },
        invisible: {
            opacity: "0",
            transition: "opacity .6s ease-in-out",
        },
    };
});

const ThumbnailBottomIndicators = ({
    disableConnectionIndicator,
    hidePopover,
    className,
    isHovered,
    local,
    participantId,
    popoverVisible,
    showPopover,
    showStatusIndicators = true,
    thumbnailType,
}: IProps) => {
    const { classes: styles, cx } = useStyles();
    const _allowEditing = !useSelector(isNameReadOnly);
    const _defaultLocalDisplayName = interfaceConfig.DEFAULT_LOCAL_DISPLAY_NAME;
    const _showDisplayName = useSelector(isDisplayNameVisible);
    const isVirtualScreenshareParticipant = useSelector(
        (state: IReduxState) => isScreenShareParticipantById(state, participantId)
    );
    const toolboxVisible: boolean = useSelector(isToolboxVisible);

    const _isMobile = isMobileBrowser();
    const { NORMAL = 16 } = interfaceConfig.INDICATOR_FONT_SIZES || {};
    const _indicatorIconSize = NORMAL;
    const _connectionIndicatorAutoHideEnabled = Boolean(
        useSelector((state: IReduxState) => state['features/base/config'].connectionIndicators?.autoHide) ?? true);
    const _connectionIndicatorDisabled = _isMobile || disableConnectionIndicator
        || Boolean(useSelector((state: IReduxState) => state['features/base/config'].connectionIndicators?.disabled));
    const showConnectionIndicator = isHovered || !_connectionIndicatorAutoHideEnabled;

    return (<>
        <div className = {cx(className, 'bottom-indicators', !toolboxVisible && styles.invisible)}>
            {
                showStatusIndicators && <StatusIndicators
                    audio = {!isVirtualScreenshareParticipant}
                    moderator = {true}
                    participantID = {participantId}
                    screenshare = {isVirtualScreenshareParticipant}
                    thumbnailType = {thumbnailType} />
            }
            {
                _showDisplayName && (
                    <span className = {styles.nameContainer}>
                    <DisplayName
                        allowEditing = {local ? _allowEditing : false}
                        displayNameSuffix = {local ? _defaultLocalDisplayName : ''}
                        elementID = {local ? 'localDisplayName' : `participant_${participantId}_name`}
                        participantID = {participantId}
                        thumbnailType = {thumbnailType} />
                </span>
                )
            }
        </div>
        {thumbnailType === THUMBNAIL_TYPE.TILE && (
            <div className = {cx(styles.indicatorsContainer2, !toolboxVisible && styles.invisible)}>
                {!_connectionIndicatorDisabled
                    && <ConnectionIndicator
                        alwaysVisible = {showConnectionIndicator}
                        enableStatsDisplay = {true}
                        iconSize = {_indicatorIconSize}
                        participantId = {participantId}
                        statsPopoverPosition = {STATS_POPOVER_POSITION[thumbnailType]} />
                }

                <div className = {styles.container}>
                    <VideoMenuTriggerButton
                        hidePopover = {hidePopover}
                        local = {local}
                        participantId = {participantId}
                        popoverVisible = {popoverVisible}
                        showPopover = {showPopover}
                        thumbnailType = {thumbnailType}
                        visible = {isHovered} />
                </div>
            </div>
        )}
    </>);
};

export default ThumbnailBottomIndicators;
