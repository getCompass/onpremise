import React from 'react';
import { makeStyles } from 'tss-react/mui';

import { isMobileBrowser } from '../../../environment/utils';
import Icon from '../../../icons/components/Icon';
import { IconCheck } from '../../../icons/svg';
import { withPixelLineHeight } from '../../../styles/functions.web';

interface ICheckboxProps {

    /**
     * Whether the input is checked or not.
     */
    checked?: boolean;

    /**
     * Class name for additional styles.
     */
    className?: string;

    /**
     * Class name for additional styles for text.
     */
    classNameText?: string;

    /**
     * Whether the input is disabled or not.
     */
    disabled?: boolean;

    /**
     * The label of the input.
     */
    label: string;

    /**
     * The name of the input.
     */
    name?: string;

    /**
     * Change callback.
     */
    onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
}

const useStyles = makeStyles()(theme => {
    return {
        formControl: {
            ...withPixelLineHeight(theme.typography.bodyLongRegular),
            color: theme.palette.text01,
            display: 'inline-flex',
            alignItems: 'center',
            padding: '8px 12px 6px 12px',
            cursor: 'pointer',
            '-webkit-tap-highlight-color': 'transparent',

            '&.is-mobile': {
                ...withPixelLineHeight(theme.typography.bodyLongRegularLarge)

            }
        },

        disabled: {
            cursor: 'not-allowed'
        },

        activeArea: {
            display: 'grid',
            placeContent: 'center',
            width: '18px',
            height: '18px',
            backgroundColor: 'transparent',
            marginRight: '8px',
            position: 'relative',
            cursor: 'pointer',

            '& input[type="checkbox"]': {
                appearance: 'none',
                backgroundColor: 'transparent',
                margin: '3px',
                font: 'inherit',
                color: theme.palette.icon03,
                width: '18px',
                height: '18px',
                border: '1px solid rgba(0, 107, 224, 1)',
                borderRadius: '5px',

                display: 'grid',
                placeContent: 'center',

                '&::before': {
                    content: 'url("")',
                    width: '18px',
                    height: '18px',
                    opacity: 0,
                    backgroundColor: theme.palette.action01,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    border: 0,
                    borderRadius: '5px',
                    transition: '.2s'
                },

                '&:checked::before': {
                    opacity: 1
                },

                '&:disabled': {
                    backgroundColor: theme.palette.ui03,
                    borderColor: theme.palette.ui04,

                    '&::before': {
                        backgroundColor: theme.palette.ui04
                    }
                },

                '&:checked+.checkmark': {
                    opacity: 1
                }
            },

            '& .checkmark': {
                position: 'absolute',
                opacity: 0,
                transition: '.2s'
            },

            '&.is-mobile': {
                width: '24px',
                height: '24px',
                marginRight: '12px',

                '& input[type="checkbox"]': {
                    width: '24px',
                    height: '24px',

                    '&::before': {
                        width: '24px',
                        height: '24px'
                    }
                },

                '& .checkmark': {
                    left: '4px',
                    top: '3px'
                }
            }
        },

        text: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.75)',
            userSelect: 'none',

            '&.is-mobile': {
                fontSize: '16px',
                lineHeight: '22px',
                color: 'rgba(255, 255, 255, 0.7)',
            }
        },
    };
});

const Checkbox = ({
    checked,
    className,
    classNameText,
    disabled,
    label,
    name,
    onChange
}: ICheckboxProps) => {
    const { classes: styles, cx, theme } = useStyles();
    const isMobile = isMobileBrowser();

    return (
        <label className = {cx(styles.formControl, isMobile && 'is-mobile', className)}>
            <div className = {cx(styles.activeArea, isMobile && 'is-mobile', disabled && styles.disabled)}>
                <input
                    checked = {checked}
                    disabled = {disabled}
                    name = {name}
                    onChange = {onChange}
                    type = 'checkbox' />
                <Icon
                    aria-hidden = {true}
                    className = 'checkmark'
                    color = {disabled ? theme.palette.icon03 : theme.palette.icon01}
                    size = {18}
                    src = {IconCheck} />
            </div>
            <div className = {cx(styles.text, isMobile && 'is-mobile', classNameText)}>{label}</div>
        </label>
    );
};

export default Checkbox;
