import React from 'react';
import { makeStyles } from 'tss-react/mui';

import Icon from '../../../base/icons/components/Icon';
import { IconRaiseHand } from '../../../base/icons/svg';

const useStyles = makeStyles()(theme => {
    return {
        indicator: {
            backgroundColor: 'rgba(255, 214, 56, 1)',
            borderRadius: `${Number(theme.shape.borderRadius) / 2}px`,
            height: '28px',
            width: '28px'
        }
    };
});

export const RaisedHandIndicator = () => {
    const { classes: styles, theme } = useStyles();

    return (
        <div className = { styles.indicator }>
            <Icon
                color = { theme.palette.icon04 }
                size = { 24 }
                src = { IconRaiseHand } />
        </div>
    );
};
