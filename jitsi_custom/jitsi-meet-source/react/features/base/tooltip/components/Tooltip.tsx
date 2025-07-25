import React, { ReactElement, useCallback, useEffect, useRef, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { keyframes } from 'tss-react';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { isMobileBrowser } from '../../environment/utils';
import Popover from '../../popover/components/Popover.web';
import { TOOLTIP_POSITION } from '../../ui/constants.any';
import { hideTooltip, showTooltip } from '../actions';

const TOOLTIP_DELAY = 300;
const ANIMATION_DURATION = 0.2;

interface IProps {
    children: ReactElement;
    containerClassName?: string;
    tooltipContainerClassName?: string;
    tooltipContentClassName?: string;
    content: string | ReactElement;
    tail?: ReactElement;
    position?: TOOLTIP_POSITION;
}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            '&.mounting-animation': {
                animation: `${keyframes`
                    0% {
                        opacity: 0;
                    }
                    100% {
                        opacity: 1;
                    }
                `} ${ANIMATION_DURATION}s forwards ease-in`
            },

            '&.unmounting': {
                animation: `${keyframes`
                    0% {
                        opacity: 1;
                    }
                    100% {
                        opacity: 0;
                    }
                `} ${ANIMATION_DURATION}s forwards ease-out`
            }
        },
        content: {
            backgroundColor: 'rgba(33, 33, 33, 0.9)',
            borderRadius: '3px',
            padding: '4px 8px 6px 8px',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '13px',
            lineHeight: '18px',
            color: 'rgba(248, 248, 248, 1)',
            position: 'relative',
        },
        tail: {
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
        },
    };
});

const Tooltip = ({
    containerClassName,
    tooltipContainerClassName,
    tooltipContentClassName,
    content,
    children,
    tail,
    position = 'top'
}: IProps) => {
    const dispatch = useDispatch();
    const [ visible, setVisible ] = useState(false);
    const [ isUnmounting, setIsUnmounting ] = useState(false);
    const overflowDrawer = useSelector((state: IReduxState) => state['features/toolbox'].overflowDrawer) && isMobileBrowser();
    const { classes, cx } = useStyles();
    const timeoutID = useRef({
        open: 0,
        close: 0
    });
    const {
        content: storeContent,
        previousContent,
        visible: isVisible
    } = useSelector((state: IReduxState) => state['features/base/tooltip']);

    const contentComponent = (
        <div
            className = {cx(classes.container, tooltipContainerClassName, previousContent === '' && 'mounting-animation',
                isUnmounting && 'unmounting')}>
            {position === "bottom" && (
                <div className = {classes.tail}>
                    {tail ? tail : (
                        <svg width = "9" height = "5" viewBox = "0 0 9 5" fill = "none"
                             xmlns = "http://www.w3.org/2000/svg">
                            <path d = "M0.5 0L4.5 5L8.5 0H0.5Z" fill = "#212121" fillOpacity = "0.9" />
                        </svg>
                    )}
                </div>
            )}
            <div
                className = {cx(classes.content, tooltipContentClassName)}>
                {content}
            </div>
            {position === "top" && (
                <div className = {classes.tail}>
                    {tail ? tail : (
                        <svg width = "9" height = "5" viewBox = "0 0 9 5" fill = "none"
                             xmlns = "http://www.w3.org/2000/svg">
                            <path d = "M0.5 0L4.5 5L8.5 0H0.5Z" fill = "#212121" fillOpacity = "0.9" />
                        </svg>
                    )}
                </div>
            )}
        </div>
    );

    const openPopover = () => {
        setVisible(true);
        dispatch(showTooltip(content));
    };

    const closePopover = () => {
        setVisible(false);
        dispatch(hideTooltip(content));
        setIsUnmounting(false);
    };

    const onPopoverOpen = useCallback(() => {
        if (isUnmounting) {
            return;
        }

        clearTimeout(timeoutID.current.close);
        timeoutID.current.close = 0;
        if (!visible) {
            if (isVisible) {
                openPopover();
            } else {
                timeoutID.current.open = window.setTimeout(() => {
                    openPopover();
                }, TOOLTIP_DELAY);
            }
        }
    }, [ visible, isVisible, isUnmounting ]);

    const onPopoverClose = useCallback(() => {
        clearTimeout(timeoutID.current.open);
        if (visible) {
            timeoutID.current.close = window.setTimeout(() => {
                setIsUnmounting(true);
            }, TOOLTIP_DELAY);
        }
    }, [ visible ]);

    useEffect(() => {
        if (isUnmounting) {
            setTimeout(() => {
                if (timeoutID.current.close !== 0) {
                    closePopover();
                }
            }, (ANIMATION_DURATION * 1000) + 10);
        }
    }, [ isUnmounting ]);

    useEffect(() => {
        if (storeContent !== content) {
            closePopover();
            clearTimeout(timeoutID.current.close);
            timeoutID.current.close = 0;
        }
    }, [ storeContent ]);


    if (isMobileBrowser() || overflowDrawer) {
        return children;
    }

    return (
        <Popover
            allowClick = {true}
            className = {containerClassName}
            content = {contentComponent}
            focusable = {false}
            onPopoverClose = {onPopoverClose}
            onPopoverOpen = {onPopoverOpen}
            position = {position}
            visible = {visible}>
            {children}
        </Popover>
    );
};

export default Tooltip;
