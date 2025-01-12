import React from 'react';
import { makeStyles } from 'tss-react/mui';

import { withPixelLineHeight } from '../../../base/styles/functions.web';
import { IDisplayProps } from '../ConferenceTimer';

const useStyles = makeStyles()(theme => {
    return {
        timer: {
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '17px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.85)',
            letterSpacing: '-0.24px',
            boxSizing: 'border-box',
        }
    };
});

/**
 * Returns web element to be rendered.
 *
 * @returns {ReactElement}
 */
export default function ConferenceTimerDisplayMobile({ timerValue, textStyle: _textStyle }: IDisplayProps) {
    const { classes } = useStyles();

    return (
        <span className = {classes.timer} style = {_textStyle}>{timerValue}</span>
    );
}
