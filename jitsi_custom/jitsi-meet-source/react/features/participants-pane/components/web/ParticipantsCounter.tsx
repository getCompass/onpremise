import React from 'react';
import {useSelector} from 'react-redux';
import {makeStyles} from 'tss-react/mui';

import {getParticipantCount} from '../../../base/participants/functions';
import {getKnockingParticipants, getLobbyEnabled} from "../../../lobby/functions";

const useStyles = makeStyles()(theme => {
    return {
        badge: {
            backgroundColor: 'rgba(65, 65, 65, 1)',
            borderRadius: '100%',
            height: '16px',
            minWidth: '16px',
            fontFamily: 'Inter Semibold',
            fontWeight: 'normal' as const,
            fontSize: '13px',
            lineHeight: '18px',
            color: 'rgba(255, 255, 255, 0.9)',
            pointerEvents: 'none',
            position: 'absolute',
            right: '-4px',
            top: '-3px',
            textAlign: 'center',
            padding: '1px',

            '&.lobby': {
                backgroundColor: 'rgba(255, 79, 71, 1)',
            }
        }
    };
});

const ParticipantsCounter = () => {
    const {classes, cx} = useStyles();
    const participantsCount = useSelector(getParticipantCount);
    const lobbyEnabled = useSelector(getLobbyEnabled);
    const lobbyParticipants = useSelector(getKnockingParticipants);

    return <span
        className={cx(classes.badge, (lobbyEnabled && lobbyParticipants.length > 0) && 'lobby')}>{lobbyEnabled && lobbyParticipants.length > 0 ? lobbyParticipants.length : participantsCount}</span>;
};

export default ParticipantsCounter;
