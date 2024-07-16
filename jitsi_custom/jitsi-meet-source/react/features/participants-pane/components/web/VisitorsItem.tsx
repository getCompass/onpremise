import React, {useCallback} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch} from 'react-redux';
import {makeStyles} from 'tss-react/mui';

import Button from '../../../base/ui/components/web/Button';
import {BUTTON_TYPES} from '../../../base/ui/constants.web';
import {approveRequest, denyRequest} from '../../../visitors/actions';
import {IPromotionRequest} from '../../../visitors/types';
import {ACTION_TRIGGER, MEDIA_STATE} from '../../constants';

import ParticipantItem from './ParticipantItem';
import {IconCloseMedium} from "../../../base/icons/svg";

interface IProps {

    /**
     * Promotion request reference.
     */
    request: IPromotionRequest;
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

            '& div > svg': {
                fill: 'rgba(255, 79, 71, 1)'
            }
        }
    };
});

export const VisitorsItem = ({
                                 request: r
                             }: IProps) => {
    const {from, nick} = r;
    const {t} = useTranslation();
    const {classes: styles} = useStyles();
    const dispatch = useDispatch();
    const admit = useCallback(() => dispatch(approveRequest(r)), [dispatch, r]);
    const reject = useCallback(() => dispatch(denyRequest(r)), [dispatch, r]);

    return (
        <ParticipantItem
            actionsTrigger={ACTION_TRIGGER.PERMANENT}
            audioMediaState={MEDIA_STATE.NONE}
            displayName={nick}
            participantID={from}
            raisedHand={true}
            videoMediaState={MEDIA_STATE.NONE}
            youText={t('chat.you')}
            isLobbyParticipantRequest={false}>

            <>
                <Button
                    accessibilityLabel={`${t('participantsPane.actions.admit')} ${r.nick}`}
                    className={styles.acceptButton}
                    labelKey='participantsPane.actions.admit'
                    onClick={admit}
                    testId={`admit-${from}`}/>
                <Button
                    accessibilityLabel={`${t('participantsPane.actions.reject')} ${r.nick}`}
                    className={styles.rejectButton}
                    icon={IconCloseMedium}
                    onClick={reject}
                    size='medium'
                    testId={`reject-${from}`}
                    type={BUTTON_TYPES.DESTRUCTIVE}/>
            </>
        </ParticipantItem>
    );
};
