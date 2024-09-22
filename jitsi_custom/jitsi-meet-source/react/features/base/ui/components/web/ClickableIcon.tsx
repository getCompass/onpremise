import React from 'react';
import {makeStyles} from 'tss-react/mui';

import {isMobileBrowser} from '../../../environment/utils';
import Icon from '../../../icons/components/Icon';

interface IProps {
    accessibilityLabel: string;
    className?: string;
    icon: Function;
    id?: string;
    onClick: () => void;
}

const useStyles = makeStyles()(theme => {
    return {
        button: {
            padding: '0px',
            backgroundColor: 'transparent',
            border: 0,
            outline: 0,

            '&:hover': {
                '& svg': {
                    fill: 'rgba(255, 255, 255, 1) !important'
                }
            },

            '&:active': {
                backgroundColor: 'transparent',
            },

            '&.is-mobile': {
                padding: '0px'
            },

            '& svg': {
                fill: 'rgba(255, 255, 255, 0.7) !important'
            }
        }
    };
});

const ClickableIcon = ({accessibilityLabel, className, icon, id, onClick}: IProps) => {
    const {classes: styles, cx} = useStyles();
    const isMobile = isMobileBrowser();

    return (
        <button
            aria-label={accessibilityLabel}
            className={cx(styles.button, isMobile && 'is-mobile', className)}
            id={id}
            onClick={onClick}>
            <Icon
                size={24}
                src={icon}/>
        </button>
    );
};

export default ClickableIcon;
