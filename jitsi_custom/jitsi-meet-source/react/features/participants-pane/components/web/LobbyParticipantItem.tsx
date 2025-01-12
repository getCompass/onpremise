import React from 'react';
import { useTranslation } from 'react-i18next';
import { makeStyles } from 'tss-react/mui';
import { hasRaisedHand } from '../../../base/participants/functions';
import { IParticipant } from '../../../base/participants/types';
import Button from '../../../base/ui/components/web/Button';
import { BUTTON_TYPES } from '../../../base/ui/constants.web';
import { ACTION_TRIGGER, MEDIA_STATE } from '../../constants';
import { useLobbyActions } from '../../hooks';

import ParticipantItem from './ParticipantItem';
import { IconCloseMedium } from "../../../base/icons/svg";

interface IProps {

    /**
     * Callback used to open a drawer with admit/reject actions.
     */
    openDrawerForParticipant: Function;

    /**
     * If an overflow drawer should be displayed.
     */
    overflowDrawer: boolean;

    /**
     * Participant reference.
     */
    participant: IParticipant;
}

const useStyles = makeStyles()(theme => {
    return {
        acceptButton: {
            fontFamily: 'Inter Semibold !important',
            fontWeight: 'normal' as const,
            backgroundColor: 'rgba(4, 164, 90, 0.1)',
            padding: '7px 14px 7px 16px',
            fontSize: '14px',
            lineHeight: '21px',
            color: 'rgba(4, 164, 90, 1)',
            marginRight: '8px',

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: 'rgba(4, 164, 90, 0.25)',
                },
            }
        },
        rejectButton: {
            backgroundColor: 'rgba(255, 79, 71, 0.1)',
            padding: '7px',
            fontSize: '14px',
            lineHeight: '21px',
            color: 'rgba(4, 164, 90, 1)',

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: 'rgba(255, 79, 71, 0.25)',
                },
            },

            '&.is-mobile': {
                backgroundColor: 'rgba(255, 79, 71, 0.1)',
            },

            '& div > svg': {
                fill: 'rgba(255, 79, 71, 1)'
            }
        }
    };
});

export const LobbyParticipantItem = ({
    overflowDrawer,
    participant: p,
    openDrawerForParticipant
}: IProps) => {
    const { id } = p;
    const [ admit, reject, chat ] = useLobbyActions({ participantID: id });
    const { t } = useTranslation();
    const { classes: styles } = useStyles();

    const renderAdmitButton = () => (
        <Button
            accessibilityLabel = {`${t('participantsPane.actions.admit')} ${p.name}`}
            className = {styles.acceptButton}
            labelKey = {'participantsPane.actions.admit'}
            onClick = {admit}
            size = 'small'
            testId = {`admit-${id}`} />);

    return (
        <ParticipantItem
            actionsTrigger = {ACTION_TRIGGER.PERMANENT}
            audioMediaState = {MEDIA_STATE.NONE}
            displayName = {p.name}
            local = {p.local}
            openDrawerForParticipant = {openDrawerForParticipant}
            overflowDrawer = {overflowDrawer}
            participantID = {id}
            raisedHand = {hasRaisedHand(p)}
            videoMediaState = {MEDIA_STATE.NONE}
            youText = {t('chat.you')}
            isLobbyParticipantRequest = {true}>

            <>
                {renderAdmitButton()}
                <Button
                    accessibilityLabel = {`${t('participantsPane.actions.reject')} ${p.name}`}
                    className = {styles.rejectButton}
                    icon = {IconCloseMedium}
                    onClick = {reject}
                    size = 'medium'
                    testId = {`reject-${id}`}
                    type = {BUTTON_TYPES.DESTRUCTIVE} />
            </>
        </ParticipantItem>
    );
};
