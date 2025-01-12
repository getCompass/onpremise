import React, { ReactNode, useCallback } from 'react';
import { useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { showOverflowDrawer } from '../../../../toolbox/functions.web';
import Icon from '../../../icons/components/Icon';
import { TEXT_OVERFLOW_TYPES } from '../../constants.any';

import TextWithOverflow from './TextWithOverflow';
import { isMobileBrowser } from "../../../environment/utils";
import TextWithOverflowMobile from "./TextWithOverflowMobile";

export interface IProps {

    /**
     * Label used for accessibility.
     */
    accessibilityLabel: string;

    /**
     * The context menu item background color.
     */
    backgroundColor?: string;

    /**
     * Component children.
     */
    children?: ReactNode;

    /**
     * CSS class name used for custom styles.
     */
    containerClassName?: string;

    /**
     * CSS class name used for custom styles.
     */
    className?: string;

    /**
     * Id of dom element controlled by this item. Matches aria-controls.
     * Useful if you need this item as a tab element.
     *
     */
    controls?: string;

    /**
     * Custom icon. If used, the icon prop is ignored.
     * Used to allow custom children instead of just the default icons.
     */
    customIcon?: ReactNode;

    /**
     * Whether or not the action is disabled.
     */
    disabled?: boolean;

    /**
     * Default icon for action.
     */
    icon?: Function;

    /**
     * Id of the action container.
     */
    id?: string;

    /**
     * Click handler.
     */
    onClick?: (e?: React.MouseEvent<any>) => void;

    /**
     * Keydown handler.
     */
    onKeyDown?: (e: React.KeyboardEvent<HTMLDivElement>) => void;

    /**
     * Keypress handler.
     */
    onKeyPress?: (e?: React.KeyboardEvent) => void;

    /**
     * Overflow type for item text.
     */
    overflowType?: TEXT_OVERFLOW_TYPES;

    /**
     * You can use this item as a tab. Defaults to button if not set.
     *
     * If no onClick handler is provided, we assume the context menu item is
     * not interactive and no role will be set.
     */
    role?: 'tab' | 'button' | 'menuitem';

    /**
     * Whether the item is marked as selected.
     */
    selected?: boolean;

    /**
     * Whether the item is marked as selected as hover.
     */
    hoverSelected?: boolean;

    /**
     * TestId of the element, if any.
     */
    testId?: string;

    /**
     * Action text.
     */
    text?: string;

    /**
     * Class name for the text.
     */
    textClassName?: string;

    textIcon?: ReactNode;
}

const useStyles = makeStyles()(theme => {
    return {
        contextMenuItemContainer: {
            padding: '2px 12px 0px 12px',
            outline: 'none',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',

            '&.is-mobile': {
                padding: 0,

                '&:last-child': {
                    '& > div': {
                        '& > *:last-child': {
                            borderBottom: '1px solid transparent',
                        }
                    },
                },

                '&.last-context-menu-item': {
                    '& > div': {
                        '& > *:last-child': {
                            borderBottom: '1px solid transparent',
                        }
                    },
                }
            }
        },
        contextMenuItem: {
            alignItems: 'center',
            cursor: 'pointer',
            display: 'flex',
            padding: '6px 12px 6px 12px',
            boxSizing: 'border-box',

            '& > *:not(:last-child)': {
                marginRight: '8px',

                '&.is-mobile': {
                    marginRight: '12px',
                }
            },

            '&.is-mobile': {
                '& > .jitsi-icon': {
                    padding: '16px 0px 16px 16px',
                },
            },

            '&:hover': {
                borderRadius: '0 !important',

                '.context-text-item': {
                    color: 'rgba(255, 255, 255, 1)',
                },
            }
        },

        selected: {
            '& svg': {
                fill: 'rgba(0, 107, 224, 1) !important'
            }
        },

        hoverSelected: {
            borderRadius: '5px',
            backgroundColor: 'rgba(255, 255, 255, 0.05)'
        },

        contextMenuItemDisabled: {
            pointerEvents: 'none'
        },

        contextMenuItemIconDisabled: {
            '& svg': {
                fill: 'rgba(255, 255, 255, 0.1) !important'
            }
        },

        contextMenuItemLabelDisabled: {
            color: 'rgba(255, 255, 255, 0.3)',

            '&:hover': {
                background: 'none'
            },

            '& svg': {
                fill: 'rgba(255, 255, 255, 0.3)'
            }
        },

        contextMenuItemDrawer: {
            padding: '13px 16px',

            '&.is-mobile': {
                padding: 0
            }
        },

        contextMenuItemIcon: {
            '&.is-mobile': {
                padding: '16px 0px 16px 16px',
            },

            '& svg': {
                fill: 'rgba(255, 255, 255, 0.3) !important'
            }
        },

        text: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.75)'
        },

        drawerText: {
            width: '100%',
            padding: '16px 0 15px 0',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '17px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.7)',
            borderBottom: '0.5px solid rgba(255, 255, 255, 0.05)',
        }
    };
});

const ContextMenuItemMobile = ({
    accessibilityLabel,
    backgroundColor,
    children,
    containerClassName,
    className,
    controls,
    customIcon,
    disabled,
    id,
    icon,
    onClick,
    onKeyDown,
    onKeyPress,
    overflowType,
    role = 'button',
    selected,
    hoverSelected,
    testId,
    text,
    textClassName,
    textIcon
}: IProps) => {
    const { classes: styles, cx } = useStyles();
    const _overflowDrawer: boolean = useSelector(showOverflowDrawer);
    const style = backgroundColor ? { backgroundColor } : {};
    const onKeyPressHandler = useCallback(e => {
        // only trigger the fallback behavior (onClick) if we dont have any explicit keyboard event handler
        if (onClick && !onKeyPress && !onKeyDown && (e.key === 'Enter' || e.key === ' ')) {
            e.preventDefault();
            onClick(e);
        }

        if (onKeyPress) {
            onKeyPress(e);
        }
    }, [ onClick, onKeyPress, onKeyDown ]);
    const isMobile = isMobileBrowser();

    let tabIndex: undefined | 0 | -1;

    if (role === 'tab') {
        tabIndex = selected ? 0 : -1;
    }

    if (role === 'button' && !disabled) {
        tabIndex = 0;
    }

    return (
        <div className = {cx(containerClassName, styles.contextMenuItemContainer, isMobile && 'is-mobile')}>
            <div
                aria-controls = {controls}
                aria-disabled = {disabled}
                aria-label = {accessibilityLabel}
                aria-selected = {role === 'tab' ? selected : undefined}
                className = {cx(styles.contextMenuItem,
                    _overflowDrawer && styles.contextMenuItemDrawer,
                    disabled && styles.contextMenuItemDisabled,
                    selected && styles.selected,
                    hoverSelected && styles.hoverSelected,
                    className,
                    isMobile && 'is-mobile'
                )}
                data-testid = {testId}
                id = {id}
                key = {text}
                onClick = {disabled ? undefined : onClick}
                onKeyDown = {disabled ? undefined : onKeyDown}
                onKeyPress = {disabled ? undefined : onKeyPressHandler}
                role = {onClick ? role : undefined}
                style = {style}
                tabIndex = {onClick ? tabIndex : undefined}>
                {customIcon ? customIcon
                    : icon && <Icon
                    className = {cx(styles.contextMenuItemIcon,
                        disabled && styles.contextMenuItemIconDisabled,
                        isMobile && 'is-mobile')}
                    size = {isMobile ? 22 : 18}
                    src = {icon} />}
                {text && (
                    <TextWithOverflowMobile
                        className = {cx(styles.text,
                            _overflowDrawer && styles.drawerText,
                            disabled && styles.contextMenuItemLabelDisabled,
                            textClassName,
                            !isMobile && 'context-text-item',
                            isMobile && 'is-mobile')}
                        overflowType = {overflowType}
                        textIcon = {textIcon}>
                        {text}
                    </TextWithOverflowMobile>
                )}
                {children}
            </div>
        </div>
    );
};

export default ContextMenuItemMobile;
