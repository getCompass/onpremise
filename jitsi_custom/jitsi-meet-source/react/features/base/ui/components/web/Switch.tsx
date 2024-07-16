import React, { useCallback } from 'react';
import { makeStyles } from 'tss-react/mui';

import { isMobileBrowser } from '../../../environment/utils';
import { ISwitchProps } from '../types';

interface IProps extends ISwitchProps {

    className?: string;

    /**
     * Id of the toggle.
     */
    id?: string;
}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            position: 'relative',
            backgroundColor: 'rgba(255, 255, 255, 0.06)',
            borderRadius: '12px',
            width: '36px',
            height: '23px',
            border: 0,
            outline: 0,
            cursor: 'pointer',
            transition: '.3s',
            display: 'inline-block',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',

            '&.disabled': {
                backgroundColor: theme.palette.ui05,
                cursor: 'default',

                '& .toggle': {
                    backgroundColor: theme.palette.ui03
                }
            },

            '&.is-mobile': {
                backgroundColor: 'rgba(255, 255, 255, 0.08)',
                height: '31px',
                width: '51px',
                borderRadius: '32px'
            }
        },

        containerOn: {
            backgroundColor: 'rgba(4, 164, 90, 0.7)',

            '&.is-mobile': {
                backgroundColor: 'rgba(76, 217, 100, 0.7)',
            }
        },

        toggle: {
            width: '19px',
            height: '19px',
            position: 'absolute',
            zIndex: 5,
            top: '2px',
            left: '2px',
            backgroundColor: 'rgba(188, 188, 188, 1)',
            borderRadius: '100%',
            transition: '.3s',

            '&.is-mobile': {
                width: '27px',
                height: '27px'
            }
        },

        toggleOn: {
            left: '15px',

            '&.is-mobile': {
                left: '22px'
            }
        },

        checkbox: {
            position: 'absolute',
            zIndex: 10,
            cursor: 'pointer',
            left: 0,
            right: 0,
            top: 0,
            bottom: 0,
            width: '100%',
            height: '100%',
            opacity: 0,

            '&.focus-visible + .toggle-checkbox-ring': {
                outline: 0,
                boxShadow: `0px 0px 0px 2px ${theme.palette.focus01}`
            }
        },

        checkboxRing: {
            position: 'absolute',
            pointerEvents: 'none',
            zIndex: 6,
            left: 0,
            right: 0,
            top: 0,
            bottom: 0,
            width: '100%',
            height: '100%',
            borderRadius: '12px',

            '&.is-mobile': {
                borderRadius: '32px'
            }
        }
    };
});

const Switch = ({ className, id, checked, disabled, onChange }: IProps) => {
    const { classes: styles, cx } = useStyles();
    const isMobile = isMobileBrowser();

    const change = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
        onChange(e.target.checked);
    }, []);

    return (
        <span
            className = { cx('toggle-container', styles.container, checked && styles.containerOn,
                isMobile && 'is-mobile', disabled && 'disabled', className) }>
            <input
                type = 'checkbox'
                { ...(id ? { id } : {}) }
                checked = { checked }
                className = { styles.checkbox }
                disabled = { disabled }
                onChange = { change } />
            <div className = { cx('toggle-checkbox-ring', styles.checkboxRing, isMobile && 'is-mobile') } />
            <div className = { cx('toggle', styles.toggle, checked && styles.toggleOn, isMobile && 'is-mobile') } />
        </span>
    );
};

export default Switch;
