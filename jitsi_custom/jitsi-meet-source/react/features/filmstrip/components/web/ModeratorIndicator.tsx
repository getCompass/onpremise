import React from 'react';

import { IconModerator } from '../../../base/icons/svg';
import BaseIndicator from '../../../base/react/components/web/BaseIndicator';
import { TOOLTIP_POSITION } from '../../../base/ui/constants.any';

/**
 * The type of the React {@code Component} props of {@link ModeratorIndicator}.
 */
interface IProps {

    /**
     * From which side of the indicator the tooltip should appear from.
     */
    tooltipPosition: TOOLTIP_POSITION;

    /**
     * The color of the icon.
     */
    iconColor?: string;
}

/**
 * React {@code Component} for showing a moderator icon with a tooltip.
 *
 * @returns {JSX.Element}
 */
const ModeratorIndicator = ({ tooltipPosition, iconColor }: IProps): JSX.Element => (
    <BaseIndicator
        icon = {IconModerator}
        iconColor = {iconColor}
        iconSize = {16}
        tooltipKey = 'videothumbnail.moderator'
        tooltipPosition = {tooltipPosition} />
);

export default ModeratorIndicator;
