import React, {ReactNode} from 'react';
import {makeStyles} from 'tss-react/mui';

import {ACTION_TRIGGER} from '../../../../participants-pane/constants';
import participantsPaneTheme from '../../../components/themes/participantsPaneTheme.json';
import {isMobileBrowser} from '../../../environment/utils';

interface IProps {

    /**
     * List item actions.
     */
    actions: ReactNode;

    /**
     * List item container class name.
     */
    className?: string;

    /**
     * The breakout name for aria-label.
     */
    defaultName?: string;

    /**
     * Whether or not the actions should be hidden.
     */
    hideActions?: boolean;

    /**
     * Icon to be displayed on the list item. (Avatar for participants).
     */
    icon: ReactNode;

    iconClassName?: string;

    /**
     * Id of the container.
     */
    id?: string;

    /**
     * Indicators to be displayed on the list item.
     */
    indicators?: ReactNode;

    /**
     * Whether or not the item is highlighted.
     */
    isHighlighted?: boolean;

    /**
     * Click handler.
     */
    onClick?: (e?: React.MouseEvent) => void;

    /**
     * Long press handler.
     */
    onLongPress?: (e?: EventTarget) => void;

    /**
     * Mouse leave handler.
     */
    onMouseLeave?: (e?: React.MouseEvent) => void;

    /**
     * Data test id.
     */
    testId?: string;

    /**
     * Text children to be displayed on the list item.
     */
    textChildren: ReactNode | string;

    /**
     * The actions trigger. Can be Hover or Permanent.
     */
    trigger: string;

}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            alignItems: 'center',
            color: 'rgba(255, 255, 255, 0.75)',
            display: 'flex',
            fontFamily: 'Lato Bold',
            fontSize: '15px',
            lineHeight: '20px',
            margin: `0 -${participantsPaneTheme.panePadding}px`,
            padding: '6px 8px 6px 24px',
            width: '100%',
            position: 'relative',

            '&:hover, &:focus-within': {
                backgroundColor: 'transparent',

                '& .actions': {
                    display: 'flex',
                    position: 'absolute',
                    right: '0',
                    top: 'auto',
                    backgroundColor: 'rgba(33, 33, 33, 1)'
                },
            },

            '&.is-mobile': {
                fontSize: '16px',
                margin: 0,
                padding: 0,

                '& .actions': {
                    display: 'flex',
                    position: 'absolute',
                    right: '0',
                    top: 'auto',
                    backgroundColor: 'rgba(28, 28, 28, 1)',
                },
            }
        },

        highlighted: {
            backgroundColor: theme.palette.ui02,

            '& .actions': {
                display: 'flex',
                position: 'relative',
                top: 'auto',
                backgroundColor: theme.palette.ui02
            }
        },

        detailsContainer: {
            display: 'flex',
            alignItems: 'center',
            flex: 1,
            height: '100%',
            overflow: 'hidden',
            position: 'relative',

            '&.is-mobile': {
                padding: '10px 16px 9.5px 0',
                borderBottom: '0.5px solid rgba(255, 255, 255, 0.08)',
            }
        },

        name: {
            display: 'flex',
            flex: 1,
            marginRight: '11px',
            overflow: 'hidden',
            flexDirection: 'column',
            justifyContent: 'flex-start'
        },

        indicators: {
            display: 'flex',
            justifyContent: 'flex-end',
            paddingRight: '3px',

            '&.is-mobile': {
                paddingRight: 0,
            },

            '& > *': {
                alignItems: 'center',
                display: 'flex',
                justifyContent: 'center'
            },

            '& > *:not(:last-child)': {
                marginRight: '12px',

                '&.is-mobile': {
                    marginRight: '16px',
                }
            },

            '& .jitsi-icon': {
                padding: '2px'
            }
        },

        indicatorsHidden: {
            display: 'none'
        },

        actionsContainer: {
            position: 'absolute',
            top: '-1000px',
            backgroundColor: theme.palette.ui02,

            '&.is-mobile': {
                backgroundColor: 'rgba(28, 28, 28, 1)',
                marginRight: '8px',
            }
        },

        actionsGradient: {
            background: 'linear-gradient(270deg, #212121 0%, rgba(33, 33, 33, 0) 33.5%)',
            width: '32px',
            left: '-32px',
            height: '35px',
            position: 'absolute',
        },

        actionsPermanent: {
            display: 'flex',
            backgroundColor: theme.palette.ui01
        },

        actionsVisible: {
            display: 'flex',
            backgroundColor: theme.palette.ui02
        }
    };
});

const ListItem = ({
                      actions,
                      className,
                      defaultName,
                      icon,
                      iconClassName,
                      id,
                      hideActions = false,
                      indicators,
                      isHighlighted,
                      onClick,
                      onLongPress,
                      onMouseLeave,
                      testId,
                      textChildren,
                      trigger
                  }: IProps) => {
    const {classes, cx} = useStyles();
    const isMobile = isMobileBrowser();
    let timeoutHandler: number;

    /**
     * Set calling long press handler after x milliseconds.
     *
     * @param {TouchEvent} e - Touch start event.
     * @returns {void}
     */
    function _onTouchStart(e: React.TouchEvent) {
        const target = e.touches[0].target;

        timeoutHandler = window.setTimeout(() => onLongPress?.(target), 600);
    }

    /**
     * Cancel calling on long press after x milliseconds if the number of milliseconds is not reached
     * before a touch move(drag), or just clears the timeout.
     *
     * @returns {void}
     */
    function _onTouchMove() {
        clearTimeout(timeoutHandler);
    }

    /**
     * Cancel calling on long press after x milliseconds if the number of milliseconds is not reached yet,
     * or just clears the timeout.
     *
     * @returns {void}
     */
    function _onTouchEnd() {
        clearTimeout(timeoutHandler);
    }

    return (
        <div
            aria-label={defaultName}
            className={cx('list-item-container',
                classes.container,
                isHighlighted && classes.highlighted,
                className,
                isMobileBrowser() && 'is-mobile'
            )}
            data-testid={testId}
            id={id}
            onClick={onClick}
            role='listitem'
            {...(isMobile
                    ? {
                        onTouchEnd: _onTouchEnd,
                        onTouchMove: _onTouchMove,
                        onTouchStart: _onTouchStart
                    }
                    : {
                        onMouseLeave
                    }
            )}>
            <div className={iconClassName ?? ''}> {icon} </div>
            <div className={cx(classes.detailsContainer, isMobile && 'is-mobile')}>
                <div className={classes.name}>
                    {textChildren}
                </div>
                {indicators && (
                    <div
                        className={cx('indicators',
                            classes.indicators,
                            (isHighlighted || trigger === ACTION_TRIGGER.PERMANENT) && classes.indicatorsHidden,
                            isMobile && 'is-mobile'
                        )}>
                        {indicators}
                    </div>
                )}
                {!hideActions && (
                    <div
                        className={cx('actions',
                            classes.actionsContainer,
                            isMobile && 'is-mobile',
                            trigger === ACTION_TRIGGER.PERMANENT && classes.actionsPermanent,
                            isHighlighted && classes.actionsVisible
                        )}>
                        {!isMobile && <div className={classes.actionsGradient}/>}
                        {actions}
                    </div>
                )}
            </div>
        </div>
    );
};

export default ListItem;
