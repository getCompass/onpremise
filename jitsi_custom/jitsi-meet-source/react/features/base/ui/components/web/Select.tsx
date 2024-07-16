import React, {ChangeEvent} from 'react';
import {makeStyles} from 'tss-react/mui';

import {isMobileBrowser} from '../../../environment/utils';
import Icon from '../../../icons/components/Icon';
import {IconArrowDown} from '../../../icons/svg';
import {withPixelLineHeight} from '../../../styles/functions.web';

interface ISelectProps {

    /**
     * Helper text to be displayed below the select.
     */
    bottomLabel?: string;

    /**
     * Class name for additional styles.
     */
    className?: string;

    /**
     * Wether or not the select is disabled.
     */
    disabled?: boolean;

    /**
     * Wether or not the select is in the error state.
     */
    error?: boolean;

    /**
     * Id of the <select> element.
     * Necessary for screen reader users, to link the label and error to the select.
     */
    id: string;

    /**
     * Label to be displayed above the select.
     */
    label?: string;

    /**
     * Left icon for action.
     */
    icon?: Function;

    /**
     * Change handler.
     */
    onChange: (e: ChangeEvent<HTMLSelectElement>) => void;

    /**
     * The options of the select.
     */
    options: Array<{
        label: string;
        value: number | string;
    }>;

    /**
     * The value of the select.
     */
    value: number | string;
}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            display: 'flex',
            flexDirection: 'column'
        },

        label: {
            color: theme.palette.text01,
            ...withPixelLineHeight(theme.typography.bodyShortRegular),
            marginBottom: theme.spacing(2),

            '&.is-mobile': {
                ...withPixelLineHeight(theme.typography.bodyShortRegularLarge)
            }
        },

        leftIcon: {
            position: 'absolute',
            top: '8px',
            left: '12px',
            pointerEvents: 'none',

            '&.is-mobile': {
                top: '14px',
                left: '12px'
            }
        },

        selectContainer: {
            position: 'relative'
        },

        select: {
            backgroundColor: 'rgba(255, 255, 255, 0.05)',
            borderRadius: '5px',
            width: '100%',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '18px',
            color: 'rgba(255, 255, 255, 1)',
            padding: '8px 12px',
            paddingRight: '34px',
            border: 0,
            appearance: 'none',
            overflow: 'hidden',
            whiteSpace: 'nowrap',
            textOverflow: 'ellipsis',

            '&:focus': {
                outline: 0,
            },

            '&:disabled': {
                color: theme.palette.text03
            },

            '&.is-mobile': {
                ...withPixelLineHeight(theme.typography.bodyShortRegularLarge),
                padding: '12px 16px',
                paddingRight: '46px'
            },

            '&.is-left-icon': {
                paddingLeft: '38px',
            },

            '&.error': {
                boxShadow: `0px 0px 0px 2px ${theme.palette.textError}`
            }
        },

        icon: {
            position: 'absolute',
            top: '8px',
            right: '8px',
            pointerEvents: 'none',

            '&.is-mobile': {
                top: '12px',
                right: '12px'
            }
        },

        bottomLabel: {
            marginTop: theme.spacing(2),
            ...withPixelLineHeight(theme.typography.labelRegular),
            color: theme.palette.text02,

            '&.is-mobile': {
                ...withPixelLineHeight(theme.typography.bodyShortRegular)
            },

            '&.error': {
                color: theme.palette.textError
            }
        }
    };
});

const Select = ({
                    bottomLabel,
                    className,
                    disabled,
                    error,
                    id,
                    label,
                    icon,
                    onChange,
                    options,
                    value
                }: ISelectProps) => {
    const {classes, cx, theme} = useStyles();
    const isMobile = isMobileBrowser();

    return (
        <div className={classes.container}>
            {label && <label
                className={cx(classes.label, isMobile && 'is-mobile')}
                htmlFor={id}>
                {label}
            </label>}
            <div className={classes.selectContainer}>
                {icon && (
                    <Icon
                        className={cx(classes.leftIcon, isMobile && 'is-mobile')}
                        color={disabled ? theme.palette.icon03 : 'rgba(255, 255, 255, 0.75)'}
                        size={18}
                        src={icon}/>
                )}
                <select
                    aria-describedby={bottomLabel ? `${id}-description` : undefined}
                    className={cx(classes.select, isMobile && 'is-mobile', className, error && 'error', icon && 'is-left-icon')}
                    disabled={disabled}
                    id={id}
                    onChange={onChange}
                    value={value}>
                    {options.map(option => (<option
                        key={option.value}
                        value={option.value}>{option.label}</option>))}
                </select>
                <Icon
                    className={cx(classes.icon, isMobile && 'is-mobile')}
                    color={disabled ? theme.palette.icon03 : 'rgba(255, 255, 255, 0.75)'}
                    size={18}
                    src={IconArrowDown}/>
            </div>
            {bottomLabel && (
                <span
                    className={cx(classes.bottomLabel, isMobile && 'is-mobile', error && 'error')}
                    id={`${id}-description`}>
                    {bottomLabel}
                </span>
            )}
        </div>
    );
};

export default Select;
