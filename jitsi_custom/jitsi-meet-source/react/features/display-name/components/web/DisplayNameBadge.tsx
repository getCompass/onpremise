import React from 'react';
import { makeStyles } from 'tss-react/mui';
import ScreenShareIndicator from "../../../filmstrip/components/web/ScreenShareIndicator";
import ModeratorIndicator from "../../../filmstrip/components/web/ModeratorIndicator";
import AudioMutedIndicator from "../../../filmstrip/components/web/AudioMutedIndicator";

const useStyles = makeStyles()(theme => {

    return {
        badge: {
            display: 'flex',
            gap: '4px',
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '12px',
            lineHeight: '15px',
            background: 'rgba(33, 33, 33, 0.9)',
            borderRadius: '4px',
            color: 'rgba(255, 255, 255, 1)',
            maxWidth: '50%',
            overflow: 'hidden',
            padding: '4px 8px',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap'
        }
    };
});

/**
 * Component that displays a name badge.
 *
 * @param {Props} props - The props of the component.
 * @returns {ReactElement}
 */
const DisplayNameBadge: React.FC<{
    name: string;
    showAudioIndicator?: boolean;
    showModeratorIndicator: boolean;
    isScreenSharing: boolean;
    isVirtualScreenshareParticipant: boolean
}> = ({ name, showAudioIndicator, showModeratorIndicator, isScreenSharing, isVirtualScreenshareParticipant }) => {
    const { classes } = useStyles();

    return (
        <div className = {classes.badge}>
            {showAudioIndicator && <AudioMutedIndicator tooltipPosition = 'top'/>}
            {showModeratorIndicator && <ModeratorIndicator tooltipPosition = 'top'/>}
            {(isScreenSharing && isVirtualScreenshareParticipant) && <ScreenShareIndicator tooltipPosition = "top" />}
            <div>{name}</div>
        </div>
    );
};

export default DisplayNameBadge;
