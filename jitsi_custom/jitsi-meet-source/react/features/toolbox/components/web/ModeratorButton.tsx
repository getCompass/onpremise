import { connect } from 'react-redux';
import { translate } from '../../../base/i18n/functions';
import AbstractButton, { IProps as AbstractButtonProps } from '../../../base/toolbox/components/AbstractButton';
import { IconModeratorSettings } from "../../../base/icons/svg";

/**
 * An abstract implementation of a button for accessing moderator settings.
 */
class ModeratorButton extends AbstractButton<AbstractButtonProps> {
    accessibilityLabel = 'toolbar.moderatorSettings';
    icon = IconModeratorSettings;
    label = 'toolbar.moderatorSettings';
}

export default translate(connect()(ModeratorButton));
