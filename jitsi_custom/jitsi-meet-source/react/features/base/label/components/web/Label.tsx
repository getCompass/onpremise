import React, {useCallback} from 'react';
import {makeStyles} from 'tss-react/mui';

import Icon from '../../../icons/components/Icon';
import {COLORS} from '../../constants';
import {isMobileBrowser} from "../../../environment/utils";

interface IProps {

    /**
     * Optional label for screen reader users, invisible in the UI.
     *
     * Note: if the text prop is set, a screen reader will first announce
     * the accessibilityText, then the text.
     */
    accessibilityText?: string;

    /**
     * Own CSS class name.
     */
    className?: string;

    /**
     * The color of the label.
     */
    color?: string;

    /**
     * An SVG icon to be rendered as the content of the label.
     */
    icon?: Function;

    /**
     * Color for the icon.
     */
    iconColor?: string;

    /**
     * HTML ID attribute to add to the root of {@code Label}.
     */
    id?: string;

    /**
     * Click handler if any.
     */
    onClick?: (e?: React.MouseEvent) => void;

    /**
     * String or component that will be rendered as the label itself.
     */
    text?: string;

}

const useStyles = makeStyles()(theme => {
    return {
        label: {
            fontFamily: 'Lato Medium',
            fontWeight: 'normal' as const,
            fontSize: '12px',
            lineHeight: '15px',
            alignItems: 'center',
            background: theme.palette.ui04,
            borderRadius: '4px',
            color: theme.palette.text01,
            display: 'flex',
            margin: '0 4px',
            padding: '3.5px 6px 3.5px 4px',
            boxSizing: 'border-box',

            '&.is-mobile': {
                fontFamily: 'Inter Medium',
                borderRadius: '6px',
                margin: '0 2px',
                padding: '4.5px 6px 5.5px 4px',
            },
        },
        withIcon: {
            marginLeft: '8px',

            '&.is-mobile': {
                marginLeft: '4px',
            },
        },
        clickable: {
            cursor: 'pointer'
        },
        [COLORS.white]: {
            background: theme.palette.ui09,
            color: theme.palette.text04,

            '& svg': {
                fill: theme.palette.icon04
            }
        },
        [COLORS.green]: {
            background: theme.palette.success02
        },
        [COLORS.red]: {
            background: theme.palette.actionDanger
        }
    };
});

const Label = ({
    accessibilityText,
    className,
    color,
    icon,
    iconColor,
    id,
    onClick,
    text
}: IProps) => {
    const { classes, cx } = useStyles();
    const isMobile = isMobileBrowser();

    const onKeyPress = useCallback(event => {
        if (!onClick) {
            return;
        }

        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            onClick();
        }
    }, [ onClick ]);

    return (
        <div
            className = { cx(classes.label, onClick && classes.clickable,
                color && classes[color], className, isMobile && 'is-mobile'
            ) }
            id = { id }
            onClick = { onClick }
            onKeyPress = { onKeyPress }
            role = { onClick ? 'button' : undefined }
            tabIndex = { onClick ? 0 : undefined }>
            {icon && <Icon
                color = { iconColor }
                size = '16'
                src = { icon } />}
            {accessibilityText && <span className = 'sr-only'>{accessibilityText}</span>}
            {text && <span className = { cx(icon && classes.withIcon, isMobile && 'is-mobile') }>{text}</span>}
        </div>
    );
};

export default Label;
