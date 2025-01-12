import React, { Component } from 'react';
import { WithTranslation } from 'react-i18next';
import { connect } from 'react-redux';

import { IReduxState } from '../../../app/types';
import { isMobileBrowser } from '../../../base/environment/utils';
import { translate } from '../../../base/i18n/functions';
import { toggleModeratorSettings } from '../../../settings/actions.web';
import { getModeratorSettingsVisibility } from '../../../settings/functions.web';

import ModeratorButton from './ModeratorButton';
import { getLocalParticipant, isParticipantModerator } from "../../../base/participants/functions";
import ModeratorSettingsPopup from "../../../settings/components/web/moderator/ModeratorSettingsPopup";
import { isNeedShowElectronOnlyElements } from "../../../base/environment/utils_web";

interface IProps extends WithTranslation {

    /**
     * The button's key.
     */
    buttonKey?: string;

    /**
     * External handler for click action.
     */
    handleClick: Function;

    /**
     * Defines is popup is open.
     */
    isOpen: boolean;

    /**
     * Click handler for the small icon. Opens moderator options.
     */
    onModeratorOptionsClick: Function;

    /**
     * Flag controlling the visibility of the button.
     * ModeratorSettings popup is disabled on mobile browsers or is not have permissions.
     */
    visible: boolean;
}

/**
 * Button used for moderator settings.
 *
 * @returns {ReactElement}
 */
class ModeratorSettingsButton extends Component<IProps> {
    /**
     * Initializes a new {@code ModeratorSettingsButton} instance.
     *
     * @inheritdoc
     */
    constructor(props: IProps) {
        super(props);

        this._onEscClick = this._onEscClick.bind(this);
        this._onClick = this._onClick.bind(this);
    }

    /**
     * Click handler for the more actions entries.
     *
     * @param {KeyboardEvent} event - Esc key click to close the popup.
     * @returns {void}
     */
    _onEscClick(event: React.KeyboardEvent) {
        if (event.key === 'Escape' && this.props.isOpen) {
            event.preventDefault();
            event.stopPropagation();
            this._onClick();
        }
    }

    /**
     * Click handler for the more actions entries.
     *
     * @param {MouseEvent} e - Mouse event.
     * @returns {void}
     */
    _onClick(e?: React.MouseEvent) {
        const { onModeratorOptionsClick, isOpen } = this.props;

        if (isOpen) {
            e?.stopPropagation();
        }
        onModeratorOptionsClick();
    }

    /**
     * Implements React's {@link Component#render}.
     *
     * @inheritdoc
     */
    render() {
        const { visible } = this.props;

        if (!isNeedShowElectronOnlyElements()) {
            return (
                <div style = {{
                    width: '48px',
                    height: '48px',
                    opacity: 0,
                    pointerEvents: 'none',
                }} />
            );
        }

        return visible ? (
            <ModeratorSettingsPopup>
                <div onClick = {this._onClick}>
                    <ModeratorButton />
                </div>
            </ModeratorSettingsPopup>
        ) : <></>;
    }
}

/**
 * Function that maps parts of Redux state tree into component props.
 *
 * @param {Object} state - Redux state.
 * @returns {Object}
 */
function mapStateToProps(state: IReduxState) {
    const localParticipant = getLocalParticipant(state);
    const isModerator = isParticipantModerator(localParticipant);

    return {
        isOpen: Boolean(getModeratorSettingsVisibility(state)),
        visible: !isMobileBrowser() && isModerator
    };
}

const mapDispatchToProps = {
    onModeratorOptionsClick: toggleModeratorSettings
};

export default translate(connect(
    mapStateToProps,
    mapDispatchToProps
)(ModeratorSettingsButton));
