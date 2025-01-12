import React from 'react';
import { useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { IconPin } from '../../../base/icons/svg';
import { getParticipantById } from '../../../base/participants/functions';
import BaseIndicator from '../../../base/react/components/web/BaseIndicator';
import { TOOLTIP_POSITION } from '../../../base/ui/constants.any';
import { getPinnedActiveParticipants, isStageFilmstripAvailable } from '../../functions.web';
import { useTranslation } from "react-i18next";

/**
 * The type of the React {@code Component} props of {@link PinnedIndicator}.
 */
interface IProps {

    /**
     * The font-size for the icon.
     */
    iconSize: number;

    /**
     * The participant id who we want to render the raised hand indicator
     * for.
     */
    participantId: string;

    /**
     * From which side of the indicator the tooltip should appear from.
     */
    tooltipPosition: TOOLTIP_POSITION;

    onIndicatorClick?: () => void;
}

const useStyles = makeStyles()(() => {
    return {
        pinnedIndicator: {
            backgroundColor: 'rgba(33, 33, 33, 0.9)',
            padding: '4px 8px 4px 6px',
            zIndex: 3,
            display: 'flex',
            gap: '4px',
            flexDirection: 'row',
            borderRadius: '4px',
            boxSizing: 'border-box'
        },
        pinnedIndicatorText: {
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '12px',
            lineHeight: '15px',
            color: 'rgba(255, 255, 255, 1)',
        },
        clickable: {
            cursor: 'pointer',
            pointerEvents: 'auto',

            '&:hover': {
                backgroundColor: 'rgba(63, 63, 63, 0.9)',
            }
        },
    };
});

/**
 * Thumbnail badge showing that the participant would like to speak.
 *
 * @returns {ReactElement}
 */
const PinnedIndicator = ({
    iconSize,
    participantId,
    tooltipPosition = 'top',
    onIndicatorClick
}: IProps) => {
    const stageFilmstrip = useSelector(isStageFilmstripAvailable);
    const pinned = useSelector((state: IReduxState) => getParticipantById(state, participantId))?.pinned;
    const activePinnedParticipants: Array<{ participantId: string; pinned?: boolean; }>
        = useSelector(getPinnedActiveParticipants);
    const isPinned = activePinnedParticipants.find(p => p.participantId === participantId);
    const { classes: styles, cx } = useStyles();
    const { t } = useTranslation();

    if ((stageFilmstrip && !isPinned) || (!stageFilmstrip && !pinned)) {
        return null;
    }

    return (
        <div
            className = {cx(styles.pinnedIndicator, onIndicatorClick !== undefined && styles.clickable)}
            id = {`pin-indicator-${participantId}`}
            onClick = {onIndicatorClick ? () => onIndicatorClick() : undefined}>
            <BaseIndicator
                icon = {IconPin}
                iconSize = {`${iconSize}px`}
                tooltipKey = 'pinnedParticipant'
                tooltipPosition = {tooltipPosition} />
            <div className = {styles.pinnedIndicatorText}>{t('videothumbnail.unpinFromStage')}</div>
        </div>
    );
};

export default PinnedIndicator;
