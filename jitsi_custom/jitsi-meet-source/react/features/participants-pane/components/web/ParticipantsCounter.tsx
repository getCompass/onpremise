import React from 'react';
import { useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { getParticipantCount } from '../../../base/participants/functions';
import { getKnockingParticipants, getLobbyEnabled } from "../../../lobby/functions";
import { getVisitorsCount } from "../../../visitors/functions";

/**
 * The type of the React {@code Component} props of {@link ParticipantsCounter}.
 */
interface IProps {

    /**
     * Whether or not visible.
     */
    visible: boolean;
}

const useStyles = makeStyles()(theme => {
    return {
        badge: {
            opacity: 0,
            transition: 'opacity 0.2s ease-in-out',
            backgroundColor: 'rgba(65, 65, 65, 1)',
            borderRadius: '100%',
            height: '18px',
            minWidth: '18px',
            fontFamily: 'Lato Black',
            fontWeight: 'normal' as const,
            fontSize: '10px',
            lineHeight: '12px',
            color: 'rgba(255, 255, 255, 0.9)',
            pointerEvents: 'none',
            position: 'absolute',
            right: '-4px',
            top: '-3px',
            textAlign: 'center',
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',

            '&.lobby': {
                backgroundColor: 'rgba(255, 79, 71, 1)',
            },

            '&.visible': {
                opacity: 1,
            },
        },
    };
});

const ParticipantsCounter = ({ visible }: IProps) => {
    const { classes, cx } = useStyles();
    const participantsCount = useSelector(getParticipantCount);
    const visitorsCount = useSelector(getVisitorsCount);
    const lobbyEnabled = useSelector(getLobbyEnabled);
    const lobbyParticipants = useSelector(getKnockingParticipants);
    const isHasLobbyParticipants = lobbyEnabled && lobbyParticipants.length > 0;

    return <span
        className = {cx(
            classes.badge,
            isHasLobbyParticipants && 'lobby',
            (visible || isHasLobbyParticipants) && 'visible'
        )}>
        {lobbyEnabled && lobbyParticipants.length > 0 ? lobbyParticipants.length : (participantsCount + visitorsCount)}
    </span>;
};

export default ParticipantsCounter;
