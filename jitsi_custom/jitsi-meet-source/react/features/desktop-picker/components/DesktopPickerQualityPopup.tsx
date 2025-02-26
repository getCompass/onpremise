import React, { ReactNode } from 'react';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../app/types';
import Popover from '../../base/popover/components/Popover.web';
import { SMALL_MOBILE_WIDTH } from '../../base/responsive-ui/constants';

import { getDesktopShareQualitySettingsVisibility } from "../../settings/functions.any";
import DesktopPickerQualityContent from "./DesktopPickerQualityContent";
import { toggleDesktopShareQualitySettings } from "../../settings/actions.web";


interface IProps {

    /**
     * Component's children (the desktop share quality button).
     */
    children: ReactNode;

    /**
     * Flag controlling the visibility of the popup.
     */
    isOpen: boolean;

    /**
     * Callback executed when the popup closes.
     */
    onClose: Function;

    /**
     * The popup placement enum value.
     */
    popupPlacement: string;

    screenShareHint: "detail" | "motion";

    onClick: Function;
}

const useStyles = makeStyles()(() => {
    return {
        container: {
            display: 'inline-block',
            flexShrink: 0,
        }
    };
});

/**
 * Popup with desktop share quality settings.
 *
 * @returns {ReactElement}
 */
function DesktopPickerQualityPopup({
    children,
    isOpen,
    onClose,
    popupPlacement,
    screenShareHint,
    onClick,
}: IProps) {
    const { classes, cx } = useStyles();

    return (
        <div className = {classes.container}>
            <Popover
                allowClick = {true}
                content = {<DesktopPickerQualityContent screenShareHint = {screenShareHint} onClick={onClick} />}
                headingId = 'desktop-share-quality-settings-button'
                onPopoverClose = {onClose}
                position = {popupPlacement}
                trigger = 'click'
                visible = {isOpen}>
                {children}
            </Popover>
        </div>
    );
}

/**
 * Function that maps parts of Redux state tree into component props.
 *
 * @param {Object} state - Redux state.
 * @returns {Object}
 */
function mapStateToProps(state: IReduxState) {
    const { clientWidth } = state['features/base/responsive-ui'];

    return {
        popupPlacement: clientWidth <= Number(SMALL_MOBILE_WIDTH) ? 'auto' : 'top-start_block',
        isOpen: Boolean(getDesktopShareQualitySettingsVisibility(state)),
    };
}

const mapDispatchToProps = {
    onClose: toggleDesktopShareQualitySettings,
};

export default connect(mapStateToProps, mapDispatchToProps)(DesktopPickerQualityPopup);
