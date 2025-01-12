import React from 'react';
import { useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { isDisplayNameVisible, isNameReadOnly } from '../../../base/config/functions.any';
import { isScreenShareParticipantById } from '../../../base/participants/functions';
import { isToolboxVisible } from "../../../toolbox/functions.web";
import StatusIndicatorsMobile from "./StatusIndicatorsMobile";
import DisplayNameMobile from "../../../display-name/components/web/DisplayNameMobile";

interface IProps {

    /**
     * Class name for indicators container.
     */
    className?: string;

    /**
     * Whether or not the indicators are for the local participant.
     */
    local: boolean;

    /**
     * Id of the participant for which the component is displayed.
     */
    participantId: string;

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

const ThumbnailBottomIndicatorsMobile = ({
    className,
    local,
    participantId,
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

    return (<>
        <div className = {cx(className, 'bottom-indicators', !toolboxVisible && styles.invisible)}>
            {
                showStatusIndicators && <StatusIndicatorsMobile
                    audio = {!isVirtualScreenshareParticipant}
                    participantID = {participantId}
                    moderator = {false}
                    screenshare = {isVirtualScreenshareParticipant}
                    thumbnailType = {thumbnailType} />
            }
            {
                _showDisplayName && (
                    <span className = {styles.nameContainer}>
                    <DisplayNameMobile
                        allowEditing = {local ? _allowEditing : false}
                        displayNameSuffix = {local ? _defaultLocalDisplayName : ''}
                        elementID = {local ? 'localDisplayName' : `participant_${participantId}_name`}
                        participantID = {participantId}
                        thumbnailType = {thumbnailType} />
                </span>
                )
            }
        </div>
    </>);
};

export default ThumbnailBottomIndicatorsMobile;
