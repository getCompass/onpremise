import React from 'react';
import { useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { IconRaiseHand } from '../../../base/icons/svg';
import { getParticipantById, hasRaisedHand } from '../../../base/participants/functions';
import { IParticipant } from '../../../base/participants/types';
import BaseIndicator from '../../../base/react/components/web/BaseIndicator';
import { TOOLTIP_POSITION } from '../../../base/ui/constants.any';
import { isMobileBrowser } from "../../../base/environment/utils";
import { size } from "lodash-es";
import { getCurrentLayout } from "../../../video-layout/functions.any";
import { LAYOUTS } from "../../../video-layout/constants";

/**
 * The type of the React {@code Component} props of {@link RaisedHandIndicatorMobile}.
 */
interface IProps {

    /**
     * The font-size for the icon.
     */
    iconSize: number;

    /**
     * From which side of the indicator the tooltip should appear from.
     */
    tooltipPosition: TOOLTIP_POSITION;
}

const useStyles = makeStyles()(theme => {
    return {
        RaisedHandIndicatorMobile: {
            backgroundColor: 'rgba(255, 214, 56, 1)',
            padding: '4px',
            zIndex: 3,
            display: 'inline-block',
            borderRadius: '6px',
            boxSizing: 'border-box',
        },
        invisible: {
            opacity: 0,
            pointerEvents: 'none',
        },
    };
});

/**
 * Thumbnail badge showing that the participant would like to speak.
 *
 * @returns {ReactElement}
 */
const RaisedHandIndicatorMobile = ({
    iconSize,
    tooltipPosition
}: IProps) => {

    const raisedHandsCount = useSelector((state: IReduxState) =>
        (state['features/base/participants'].raisedHandsQueue || []).length);
    const _raisedHand = raisedHandsCount > 0;
    const currentLayout = useSelector((state: IReduxState) => getCurrentLayout(state));

    const { classes: styles, cx } = useStyles();

    if (!_raisedHand || currentLayout !== LAYOUTS.HORIZONTAL_FILMSTRIP_VIEW) {
        return (
            <div className = {cx(styles.RaisedHandIndicatorMobile, styles.invisible)}>
                <div style = {{
                    width: iconSize,
                    height: iconSize,
                }} />
            </div>
        );
    }

    return (
        <div className = {cx(styles.RaisedHandIndicatorMobile)}>
            <BaseIndicator
                icon = {IconRaiseHand}
                iconColor = {'rgba(0, 0, 0, 1)'}
                iconSize = {`${iconSize}px`}
                tooltipKey = 'raisedHand'
                tooltipPosition = {tooltipPosition} />
        </div>
    );
};

export default RaisedHandIndicatorMobile;
