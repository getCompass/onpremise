/* eslint-disable react/jsx-no-bind */
import React, { useEffect, useState } from 'react';
import { makeStyles } from 'tss-react/mui';
import { copyText } from '../util/copyText.web';
import { isMobileBrowser } from "../environment/utils";

const useStyles = makeStyles()(theme => {
    return {
        copyButton: {
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '23px',
            borderRadius: '6px',
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            padding: `6px 14px`,
            width: '100%',
            boxSizing: 'border-box',
            background: 'rgba(0, 107, 224, 1)',
            cursor: 'pointer',
            color: 'rgba(255, 255, 255, 1)',
            outline: 'none',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: "rgba(0, 88, 184, 1)"
                },
            },

            '&.clicked': {
                background: theme.palette.success02
            },

            '&.is-mobile': {
                borderRadius: '8px',
                padding: `9px 16px`,
                fontSize: '17px',
                lineHeight: '26px',
            }
        },

        content: {
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap' as const,
            width: '100%',
            textAlign: 'center'
        }
    };
});

let mounted: boolean;

interface IProps {

    /**
     * The invisible text for screen readers.
     *
     * Intended to give the same info as `displayedText`, but can be customized to give more necessary context.
     * If not given, `displayedText` will be used.
     */
    accessibilityText?: string;

    /**
     * Css class to apply on container.
     */
    className?: string;

    /**
     * The displayed text.
     */
    displayedText: string;

    /**
     * The id of the button.
     */
    id?: string;

    /**
     * The text displayed on copy success.
     */
    textOnCopySuccess: string;

    /**
     * The text displayed on mouse hover.
     */
    textOnHover: string;

    /**
     * The text that needs to be copied (might differ from the displayedText).
     */
    textToCopy: string;
}

/**
 * Component meant to enable users to copy the conference URL.
 *
 * @returns {React$Element<any>}
 */
function CopyButton({
                        accessibilityText,
                        className = '',
                        displayedText,
                        textToCopy,
                        textOnHover,
                        textOnCopySuccess,
                        id
                    }: IProps) {
    const { classes, cx } = useStyles();
    const [ isClicked, setIsClicked ] = useState(false);
    const [ isHovered, setIsHovered ] = useState(false);

    useEffect(() => {
        mounted = true;

        return () => {
            mounted = false;
        };
    }, []);

    /**
     * Click handler for the element.
     *
     * @returns {void}
     */
    async function onClick() {
        setIsHovered(false);

        const isCopied = await copyText(textToCopy);

        if (isCopied) {
            setIsClicked(true);

            setTimeout(() => {
                // avoid: Can't perform a React state update on an unmounted component
                if (mounted) {
                    setIsClicked(false);
                }
            }, 2500);
        }
    }

    /**
     * Hover handler for the element.
     *
     * @returns {void}
     */
    function onHoverIn() {
        if (!isClicked) {
            setIsHovered(true);
        }
    }

    /**
     * Hover handler for the element.
     *
     * @returns {void}
     */
    function onHoverOut() {
        setIsHovered(false);
    }

    /**
     * KeyPress handler for accessibility.
     *
     * @param {React.KeyboardEventHandler<HTMLDivElement>} e - The key event to handle.
     *
     * @returns {void}
     */
    function onKeyPress(e: React.KeyboardEvent) {
        if (onClick && (e.key === ' ' || e.key === 'Enter')) {
            e.preventDefault();
            onClick();
        }
    }

    /**
     * Renders the content of the link based on the state.
     *
     * @returns {React$Element<any>}
     */
    function renderContent() {
        if (isClicked) {
            return (
                <div className = {cx(classes.content, 'selected')}>
                    <span role = {'alert'}>{textOnCopySuccess}</span>
                </div>
            );
        }

        return (
            <div className = {classes.content}>
                <span> {isHovered ? textOnHover : displayedText} </span>
            </div>
        );
    }

    return (
        <>
            <div
                aria-describedby = {displayedText === textOnHover
                    ? undefined
                    : `${id}-sr-text`}
                aria-label = {displayedText === textOnHover ? accessibilityText : textOnHover}
                className = {cx(className, classes.copyButton, isClicked ? ' clicked' : '', isMobileBrowser() ? 'is-mobile' : '')}
                id = {id}
                onBlur = {onHoverOut}
                onClick = {onClick}
                onFocus = {onHoverIn}
                onKeyPress = {onKeyPress}
                onMouseOut = {onHoverOut}
                onMouseOver = {onHoverIn}
                role = 'button'
                tabIndex = {0}>
                {renderContent()}
            </div>

            {displayedText !== textOnHover && (
                <span
                    className = 'sr-only'
                    id = {`${id}-sr-text`}>
                    {accessibilityText}
                </span>
            )}
        </>
    );
}

export default CopyButton;
