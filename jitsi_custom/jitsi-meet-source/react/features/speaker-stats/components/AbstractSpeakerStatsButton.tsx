import { IconConnection } from '../../base/icons/svg';
import AbstractButton, { IProps as AbstractButtonProps } from '../../base/toolbox/components/AbstractButton';

/**
 * The type of the React {@code Component} props of {@link AbstractSpeakerStatsButton}.
 */
interface IProps extends AbstractButtonProps {

    /**
     * Whether or not the knocking lobby.
     */
    _lobbyKnocking?: boolean;
}

/**
 * Implementation of a button for opening speaker stats dialog.
 */
class AbstractSpeakerStatsButton extends AbstractButton<IProps> {
    accessibilityLabel = 'toolbar.accessibilityLabel.speakerStats';
    icon = IconConnection;
    label = 'toolbar.speakerStats';
    tooltip = 'toolbar.speakerStats';
}

export default AbstractSpeakerStatsButton;
