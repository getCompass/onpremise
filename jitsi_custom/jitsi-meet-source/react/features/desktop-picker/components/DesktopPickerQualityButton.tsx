import React, { Component } from 'react';
import { WithTranslation } from 'react-i18next';
import { connect } from 'react-redux';

import { IReduxState, IStore } from '../../app/types';
import { translate } from '../../base/i18n/functions';
import DesktopPickerQualityPopup from "./DesktopPickerQualityPopup";
import { isNeedShowElectronOnlyElements } from "../../base/environment/utils_web";
import { toggleDesktopShareQualitySettings } from "../../settings/actions.web";
import { getDesktopShareQualitySettingsVisibility } from "../../settings/functions.any";

/**
 * The type of the React {@code Component} props of {@link DesktopPickerQualityButton}.
 */
interface IProps extends WithTranslation {
    /**
     * Used to request DesktopCapturerSources.
     */
    dispatch: IStore['dispatch'];

    /**
     * Defines is popup is open.
     */
    isOpen: boolean;

    /**
     * Click handler for the button. Opens quality settings options.
     */
    onButtonClick: Function;

    onClick: Function;

    screenShareHint: 'detail' | 'motion';
}

/**
 * React component for QualityPopover.
 *
 * @augments Component
 */
class DesktopPickerQualityButton extends Component<IProps> {

    /**
     * Initializes a new QualityPopover instance.
     *
     * @param {Object} props - The read-only properties with which the new
     * instance is to be initialized.
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
    _onClick(e?: React.MouseEvent, ) {
        const { onButtonClick, isOpen } = this.props;

        if (isOpen) {
            e?.stopPropagation();
        }
        onButtonClick();
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     */
    render() {
        const { t, screenShareHint, onClick } = this.props;

        if (!isNeedShowElectronOnlyElements()) {
            return <></>;
        }

        return (
            <DesktopPickerQualityPopup screenShareHint={screenShareHint} onClick={onClick}>
                <div
                    className = 'desktop-picker-picker-quality-button'
                    onClick = {this._onClick}
                >
                    {`${t('screenshare.quality')} – ${screenShareHint === "detail" ? t('screenshare.high_readability_text.title') : t('screenshare.smooth_video.title')}`}
                </div>
            </DesktopPickerQualityPopup>
        );
    }
}

/**
 * Maps part of the Redux state to the props of this component.
 *
 * @param {IReduxState} state - The Redux state.
 * @private
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState) {
    return {
        isOpen: Boolean(getDesktopShareQualitySettingsVisibility(state)),
    };
}

const mapDispatchToProps = {
    onButtonClick: toggleDesktopShareQualitySettings
};

export default translate(connect(_mapStateToProps, mapDispatchToProps)(DesktopPickerQualityButton));
