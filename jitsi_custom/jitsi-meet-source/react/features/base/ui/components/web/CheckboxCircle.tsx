import React from 'react';
import { makeStyles } from 'tss-react/mui';
import Icon from '../../../icons/components/Icon';
import { IconCheck, IconCheckSmall } from '../../../icons/svg';

interface ICheckboxCircleProps {

    /**
     * Whether the input is checked or not.
     */
    checked?: boolean;

    /**
     * Class name for additional styles.
     */
    className?: string;

    /**
     * Whether the input is disabled or not.
     */
    disabled?: boolean;

    /**
     * The title of the input.
     */
    title: string;

    /**
     * The description of the input.
     */
    description: string;

    /**
     * The name of the input.
     */
    name?: string;
}

const useStyles = makeStyles()(theme => {
    return {
        formControl: {
            color: theme.palette.text01,
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'start',
            gap: '6px',
            padding: 0,
            cursor: 'pointer',
            '-webkit-tap-highlight-color': 'transparent',
        },

        disabled: {
            cursor: 'not-allowed'
        },

        activeArea: {
            display: 'grid',
            placeContent: 'center',
            width: '20px',
            height: '20px',
            backgroundColor: 'transparent',
            position: 'relative',
            cursor: 'pointer',

            '& input[type="checkbox"]': {
                appearance: 'none',
                backgroundColor: 'transparent',
                margin: '3px',
                font: 'inherit',
                color: theme.palette.icon03,
                width: '20px',
                height: '20px',
                border: '1px solid rgba(0, 107, 224, 1)',
                borderRadius: '100%',

                display: 'grid',
                placeContent: 'center',

                '&::before': {
                    content: 'url("")',
                    width: '20px',
                    height: '20px',
                    opacity: 0,
                    backgroundColor: theme.palette.action01,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    border: 0,
                    borderRadius: '100%',
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
        },

        headerContainer: {
            display: 'flex',
            flexDirection: 'row',
            gap: '10px',
            width: '100%',
            alignItems: 'center',
        },

        title: {
            fontFamily: 'Lato Bold',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.75)',
            userSelect: 'none',
        },

        description: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '14px',
            lineHeight: '20px',
            color: 'rgba(255, 255, 255, 0.5)',
            userSelect: 'none',
            letterSpacing: '-0.15px',
            paddingLeft: '30px',
        },
    };
});

const CheckboxCircle = ({
    checked,
    className,
    disabled,
    title,
    description,
    name
}: ICheckboxCircleProps) => {
    const { classes: styles, cx, theme } = useStyles();

    return (
        <label className = {cx(styles.formControl, className)}>
            <div className = {styles.headerContainer}>
                <div className = {cx(styles.activeArea, disabled && styles.disabled)}>
                    <input
                        checked = {checked}
                        disabled = {disabled}
                        name = {name}
                        type = 'checkbox' />
                    <Icon
                        aria-hidden = {true}
                        className = 'checkmark'
                        color = {disabled ? theme.palette.icon03 : theme.palette.icon01}
                        size = {20}
                        src = {IconCheckSmall} />
                </div>
                <div className = {styles.title}>{title}</div>
            </div>
            <div className = {styles.description}>{description}</div>
        </label>
    );
};

export default CheckboxCircle;
