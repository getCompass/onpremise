import React, { ReactNode } from 'react';
import { useTranslation } from 'react-i18next';
import { makeStyles } from 'tss-react/mui';

import Icon from '../../../icons/components/Icon';
import { withPixelLineHeight } from '../../../styles/functions.web';
import { BUTTON_TYPES } from '../../constants.web';
import { IButtonProps } from '../types';
import { isMobileBrowser } from "../../../environment/utils";

interface IProps extends IButtonProps {

    /**
     * Class name used for additional styles.
     */
    className?: string;

    /**
     * Whether or not the button should be full width.
     */
    fullWidth?: boolean;

    /**
     * Custom icon. If used, the icon prop is ignored.
     * Used to allow custom children instead of just the default icons.
     */
    customIcon?: ReactNode;

    /**
     * The id of the button.
     */
    id?: string;

    /**
     * Whether or not the button is a submit form button.
     */
    isSubmit?: boolean;

    /**
     * Text to be displayed on the component.
     * Used when there's no labelKey.
     */
    label?: string;

    /**
     * Which size the button should be.
     */
    size?: 'small' | 'medium' | 'large' | 'sendDesktop';

    /**
     * Data test id.
     */
    testId?: string;
}

const useStyles = makeStyles()(theme => {
    return {
        button: {
            backgroundColor: 'rgba(0, 107, 224, 1)',
            color: 'rgba(255, 255, 255, 1)',
            borderRadius: '5px',
            padding: '6px 16px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            border: 0,
            fontSize: '15px',
            lineHeight: '23px',
            transition: 'background .2s',
            cursor: 'pointer',
            outline: 'none',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: "rgba(0, 88, 184, 1)"
                },

                '&:active': {
                    backgroundColor: theme.palette.action01Active
                },
            },

            '& div > svg': {
                fill: 'rgba(255, 255, 255, 1)'
            },

            '&.is-mobile': {
                fontFamily: 'Lato SemiBold',
                fontWeight: 'normal' as const,
                fontSize: '17px',
                lineHeight: '26px',
                borderRadius: '8px',
                padding: '11px 16px',
                height: '44px',
            }
        },

        primary: {},

        secondary: {
            backgroundColor: 'rgba(255, 255, 255, 0.1)',
            color: 'rgba(180, 180, 180, 1)',
            borderRadius: '5px',

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: 'rgba(255, 255, 255, 0.2)'
                },

                '&:active': {
                    backgroundColor: 'rgba(255, 255, 255, 0.2)'
                },
            },

            '& div > svg': {
                fill: 'rgba(180, 180, 180, 1)'
            }
        },

        tertiary: {
            color: 'rgba(180, 180, 180, 1)',
            backgroundColor: 'rgba(255, 255, 255, 0.1)',

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: 'rgba(255, 255, 255, 0.2)'
                },

                '&:active': {
                    backgroundColor: 'rgba(255, 255, 255, 0.2)'
                }
            },
        },

        trigger: {
            backgroundColor: 'rgba(33, 33, 33, 0.9)',

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: 'rgba(33, 33, 33, 1)'
                },

                '&:active': {
                    backgroundColor: 'rgba(33, 33, 33, 1)'
                }
            },
        },

        destructive: {
            backgroundColor: 'rgba(255, 69, 61, 1)',

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: 'rgba(255, 39, 31, 1)'
                },

                '&:active': {
                    backgroundColor: 'rgba(255, 39, 31, 1)'
                },
            },

            '&.is-mobile': {
                backgroundColor: 'rgba(255, 79, 71, 0.1)',

                '&:hover': {
                    backgroundColor: 'rgba(255, 79, 71, 0.1)'
                },

                '&:active': {
                    backgroundColor: 'rgba(255, 79, 71, 0.1)'
                },
            }
        },

        destructive_gray: {
            backgroundColor: 'rgba(255, 255, 255, 0.2)',

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: 'rgba(255, 255, 255, 0.3)'
                },

                '&:active': {
                    backgroundColor: 'rgba(255, 255, 255, 0.3)'
                },
            },

            '&.is-mobile': {
                backgroundColor: 'rgba(245, 245, 245, 0.1)',
                color: 'rgba(180, 180, 180, 1)',

                '&:hover': {
                    backgroundColor: 'rgba(245, 245, 245, 0.2)',
                },

                '&:active': {
                    backgroundColor: 'rgba(245, 245, 245, 0.2)',
                },
            },
        },

        disabled: {
            opacity: '30%',

            '&:hover': {
                opacity: '30%',
            },

            '&:active': {
                opacity: '30%',
            },

            '& div > svg': {
                opacity: '30%',
            }
        },

        iconButton: {
            padding: '7px',

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: 'rgba(63, 63, 63, 0.9) !important'
                },

                '&:active': {
                    backgroundColor: 'rgba(63, 63, 63, 0.9) !important'
                }
            },

            '&.is-mobile': {
                padding: '9px',
            }
        },

        textWithIcon: {
            marginRight: '4px'
        },

        small: {
            padding: '8px 16px',
            ...withPixelLineHeight(theme.typography.labelBold),

            '&.iconButton': {
                padding: theme.spacing(1)
            }
        },

        medium: {},

        large: {
            padding: '13px 16px',
            ...withPixelLineHeight(theme.typography.bodyShortBoldLarge),

            '&.iconButton': {
                padding: '12px'
            }
        },

        fullWidth: {
            width: '100%'
        },

        sendDesktop: {
            background: 'none',
            backgroundColor: 'none',
            border: 'none',
            opacity: '100%',
            padding: 0,
            '--send-message-button-primary-color': 'rgba(255, 255, 255, 0.1)',
            '--send-message-button-secondary-color': 'rgba(255, 255, 255, 0.5)',

            '&:hover': {
                opacity: '100%',
                background: 'none',
                backgroundColor: 'none',
                '--send-message-button-primary-color': 'rgba(255, 255, 255, 0.1)',
                '--send-message-button-secondary-color': 'rgba(255, 255, 255, 0.5)',
            },

            '&:active': {
                opacity: '100%',
                background: 'none',
                backgroundColor: 'none',
                '--send-message-button-primary-color': 'rgba(255, 255, 255, 0.1)',
                '--send-message-button-secondary-color': 'rgba(255, 255, 255, 0.5)',
            },

            '&.populated': {
                opacity: '100%',
                background: 'none',
                backgroundColor: 'none',
                '--send-message-button-primary-color': 'rgba(0, 107, 224, 1)',
                '--send-message-button-secondary-color': 'rgba(255, 255, 255, 1)',
            },

            '&:not(:disabled)': {
                '&:hover': {
                    opacity: '100%',
                    background: 'none',
                    backgroundColor: 'none',
                    '--send-message-button-primary-color': 'rgba(255, 255, 255, 0.1)',
                    '--send-message-button-secondary-color': 'rgba(255, 255, 255, 0.5)',
                },

                '&:active': {
                    opacity: '100%',
                    background: 'none',
                    backgroundColor: 'none',
                    '--send-message-button-primary-color': 'rgba(255, 255, 255, 0.1)',
                    '--send-message-button-secondary-color': 'rgba(255, 255, 255, 0.5)',
                },

                '&.populated': {
                    opacity: '100%',
                    background: 'none',
                    backgroundColor: 'none',
                    '--send-message-button-primary-color': 'rgba(0, 107, 224, 1)',
                    '--send-message-button-secondary-color': 'rgba(255, 255, 255, 1)',
                },
            },

            '& div > svg': {
                opacity: '100%',
            }
        },

        sendMobile: {
            background: 'none',
            backgroundColor: 'none',
            border: 'none',
            opacity: '100%',
            padding: '7px !important',
            '--send-message-button-primary-color': 'rgba(255, 255, 255, 0.1)',
            '--send-message-button-secondary-color': 'rgba(255, 255, 255, 0.3)',

            '&:hover': {
                background: 'none',
                backgroundColor: 'none',
                '--send-message-button-primary-color': 'rgba(255, 255, 255, 0.1)',
                '--send-message-button-secondary-color': 'rgba(255, 255, 255, 0.3)',
            },

            '&:active': {
                background: 'none',
                backgroundColor: 'none',
                '--send-message-button-primary-color': 'rgba(255, 255, 255, 0.1)',
                '--send-message-button-secondary-color': 'rgba(255, 255, 255, 0.3)',
            },

            '&.populated': {
                background: 'none',
                backgroundColor: 'none',
                '--send-message-button-primary-color': 'rgba(0, 107, 224, 1)',
                '--send-message-button-secondary-color': 'rgba(255, 255, 255, 1)',
            },

            '& div > svg': {
                opacity: '100%',
            }
        },
    };
});

const Button = React.forwardRef<any, any>(({
    accessibilityLabel,
    autoFocus = false,
    className,
    disabled,
    fullWidth,
    customIcon,
    icon,
    id,
    isSubmit,
    label,
    labelKey,
    onClick = () => null,
    onKeyPress = () => null,
    size = 'medium',
    testId,
    type = BUTTON_TYPES.PRIMARY
}: IProps, ref) => {
    const { classes: styles, cx } = useStyles();
    const { t } = useTranslation();
    const isMobile = isMobileBrowser();

    return (
        <button
            aria-label = {accessibilityLabel}
            autoFocus = {autoFocus}
            className = {cx(styles.button, styles[type],
                disabled && styles.disabled,
                icon && !(labelKey || label) && `${styles.iconButton} iconButton`,
                styles[size], fullWidth && styles.fullWidth, className,
                isMobile && 'is-mobile')}
            data-testid = {testId}
            disabled = {disabled}
            {...(id ? { id } : {})}
            onClick = {onClick}
            onKeyPress = {onKeyPress}
            ref = {ref}
            type = {isSubmit ? 'submit' : 'button'}>
            {(labelKey || label) && <span className = {icon || customIcon ? styles.textWithIcon : ''}>
                {labelKey ? t(labelKey) : label}
            </span>}
            {customIcon ? customIcon
                : icon && <Icon
                size = {isMobile ? 26 : 21}
                src = {icon} />}
        </button>
    );
});

export default Button;
