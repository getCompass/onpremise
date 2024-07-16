import React from 'react';

import { IconDotsHorizontal } from '../../../base/icons/svg';
import Button from '../../../base/ui/components/web/Button';
import {BUTTON_TYPES} from "../../../base/ui/constants.any";

interface IProps {

    /**
     * Label used for accessibility.
     */
    accessibilityLabel: string;

    /**
     * Click handler function.
     */
    onClick: () => void;

    participantID?: string;
}

const ParticipantActionEllipsis = ({ accessibilityLabel, onClick, participantID }: IProps) => (
    <Button
        accessibilityLabel = { accessibilityLabel }
        icon = { IconDotsHorizontal }
        color = { 'rgba(255, 255, 255, 0.1)' }
        onClick = { onClick }
        size = 'medium'
        type = { BUTTON_TYPES.TERTIARY }
        testId = { participantID ? `participant-more-options-${participantID}` : undefined } />
);

export default ParticipantActionEllipsis;
