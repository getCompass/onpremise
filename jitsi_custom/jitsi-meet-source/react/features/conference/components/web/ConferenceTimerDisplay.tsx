import React from 'react';
import { makeStyles } from 'tss-react/mui';

import { withPixelLineHeight } from '../../../base/styles/functions.web';
import { IDisplayProps } from '../ConferenceTimer';

const useStyles = makeStyles()(theme => {
    return {
        timer: {
            fontFamily: 'Lato Medium',
            fontWeight: 'normal' as const,
            fontSize: '12px',
            lineHeight: '15px',
            color: 'rgba(255, 255, 255, 1)',
            padding: '4px 6px',
            backgroundColor: 'rgba(33, 33, 33, 0.9)',
            boxSizing: 'border-box',
            borderRadius: '4px',
            marginRight: '2px',

            '@media (max-width: 500px)': {
                fontFamily: 'Lato Regular',
                fontSize: '13px',
                lineHeight: '16px',
                padding: '5px 8px',
            },

            '@media (max-width: 300px)': {
                display: 'none'
            }
        }
    };
});

/**
 * Returns web element to be rendered.
 *
 * @returns {ReactElement}
 */
export default function ConferenceTimerDisplay({ timerValue, textStyle: _textStyle }: IDisplayProps) {
    const { classes } = useStyles();

    return (
        <span className = { classes.timer }>{ timerValue }</span>
    );
}
