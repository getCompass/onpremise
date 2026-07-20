import React, {ReactNode, useRef} from 'react';
import {keyframes} from 'tss-react';
import {makeStyles} from 'tss-react/mui';

import {TEXT_OVERFLOW_TYPES} from '../../constants.web';

interface ITextWithOverflowProps {
    children: ReactNode;
    className?: string;
    overflowType?: TEXT_OVERFLOW_TYPES;
}

const useStyles = makeStyles<{ translateDiff: number; shouldFade: boolean }>()(
    (_, {translateDiff, shouldFade}) => {
        return {
            animation: {
                '&:hover': {
                    animation: `${keyframes`
                        0%, 15% {
                            transform: translateX(0);
                        }
                        40% {
                            transform: translateX(-${translateDiff}px);
                        }
                        65% {
                            transform: translateX(-${translateDiff}px);
                        }
                        100% {
                            transform: translateX(0);
                        }
                    `} ${Math.max(translateDiff * 40, 2000)}ms linear infinite;`
                }
            },
            textContainer: {
                overflow: 'hidden',
                position: 'relative',
                display: 'inline-block',
                maxWidth: 'calc(100% - 40px)',
                ...(shouldFade && {
                    '&::before, &::after': {
                        content: '""',
                        position: 'absolute',
                        top: 0,
                        bottom: 0,
                        width: 10,
                        pointerEvents: 'none',
                        zIndex: 2,

                        opacity: 0,
                        visibility: 'hidden',
                        transition: 'opacity 200ms ease'
                    },
                    '&::before': {
                        left: 0,
                        background: 'linear-gradient(to right, rgba(33, 33, 33, 1) 0%, transparent 100%)'
                    },
                    '&::after': {
                        right: 0,
                        background: 'linear-gradient(to left, rgba(33, 33, 33, 1) 0%, transparent 100%)'
                    },

                    '&:hover::before, &:hover::after': {
                        opacity: 1,
                        visibility: 'visible'
                    }
                })
            },
            [TEXT_OVERFLOW_TYPES.ELLIPSIS]: {
                display: 'block',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                whiteSpace: 'nowrap'
            },
            [TEXT_OVERFLOW_TYPES.SCROLL_ON_HOVER]: {
                display: 'inline-block',
                overflow: 'visible',
                whiteSpace: 'nowrap'
            }
        };
    }
);

const TextWithOverflow = ({
                              className,
                              overflowType = TEXT_OVERFLOW_TYPES.ELLIPSIS,
                              children
                          }: ITextWithOverflowProps) => {
    const containerRef = useRef<HTMLDivElement>(null);
    const contentRef = useRef<HTMLSpanElement>(null);

    const shouldAnimateOnHover =
        overflowType === TEXT_OVERFLOW_TYPES.SCROLL_ON_HOVER &&
        containerRef.current &&
        contentRef.current &&
        containerRef.current.clientWidth < contentRef.current.clientWidth;

    const translateDiff = shouldAnimateOnHover
        ? contentRef.current.clientWidth - containerRef.current.clientWidth
        : 0;

    const {classes: styles, cx} = useStyles({
        translateDiff,
        shouldFade: !!shouldAnimateOnHover
    });

    return (
        <div
            className={cx(className, styles.textContainer)}
            ref={containerRef}
        >
      <span
          className={cx(
              styles[overflowType],
              shouldAnimateOnHover && styles.animation
          )}
          ref={contentRef}
      >
        {children}
      </span>
        </div>
    );
};

export default TextWithOverflow;