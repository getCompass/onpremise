import React, {useCallback, useEffect} from 'react';
import {makeStyles} from 'tss-react/mui';

import {isMobileBrowser} from '../../../environment/utils';
import {withPixelLineHeight} from '../../../styles/functions.web';

interface ITabProps {
    accessibilityLabel: string;
    className?: string;
    onChange: (id: string) => void;
    selected: string;
    tabs: Array<{
        accessibilityLabel: string;
        controlsId: string;
        countBadge?: number;
        disabled?: boolean;
        id: string;
        label: string;
    }>;
}

const useStyles = makeStyles()(theme => {
    return {
        paddingContainer: {
            padding: '0px 16px',
        },

        container: {
            display: 'flex',
            padding: '2px',
            background: 'rgba(28, 28, 28, 1)',
            borderRadius: '8px',

            '&.is-mobile': {
                background: 'rgba(23, 23, 23, 1)',
            }
        },

        tab: {
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '13px',
            lineHeight: '20px',
            color: 'rgba(255, 255, 255, 0.5)',
            flex: 1,
            padding: '2px 20px',
            background: 'rgba(28, 28, 28, 1)',
            border: 0,
            appearance: 'none',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            borderRadius: 0,
            outline: 'none',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',

            '&:hover': {
                color: 'rgba(255, 255, 255, 1)',
            },

            '&.focus-visible': {
                outline: 0,
                boxShadow: `0px 0px 0px 2px ${theme.palette.focus01}`,
                border: 0,
                color: theme.palette.text01
            },

            '&.selected': {
                color: 'rgba(255, 255, 255, 0.75)',
                background: 'rgba(33, 33, 33, 1)',
                borderRadius: '6px',
                cursor: 'default',
            },

            '&:disabled': {
                color: theme.palette.text03,
                borderColor: theme.palette.ui05
            },

            '&.is-mobile': {
                background: 'rgba(23, 23, 23, 1)',
                color: 'rgba(255, 255, 255, 0.75)',
                padding: '6px 8px',

                '&.selected': {
                    color: 'rgba(255, 255, 255, 0.75)',
                    background: 'rgba(33, 33, 33, 1)',
                    borderRadius: '7px',
                    cursor: 'default',
                },
            }
        },

        badge: {
            marginTop: '2px',
            fontFamily: 'Inter Semibold',
            fontWeight: 'normal' as const,
            fontSize: '9px',
            lineHeight: '13px',
            color: 'rgba(255, 255, 255, 0.9)',
            padding: '0 4px',
            borderRadius: '100%',
            backgroundColor: 'rgba(255, 79, 71, 1)',
            marginLeft: '4px',
            minWidth: '5px',
        }
    };
});


const Tabs = ({
                  accessibilityLabel,
                  className,
                  onChange,
                  selected,
                  tabs
              }: ITabProps) => {
    const {classes, cx} = useStyles();
    const isMobile = isMobileBrowser();
    const onClick = useCallback(id => () => {
        onChange(id);
    }, []);
    const onKeyDown = useCallback((index: number) => (event: React.KeyboardEvent<HTMLButtonElement>) => {
        let newIndex: number | null = null;

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            newIndex = index === 0 ? tabs.length - 1 : index - 1;
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            newIndex = index === tabs.length - 1 ? 0 : index + 1;
        }

        if (newIndex !== null) {
            onChange(tabs[newIndex].id);
        }
    }, [tabs]);

    useEffect(() => {
        // this test is needed to make sure the effect is triggered because of user actually changing tab
        if (document.activeElement?.getAttribute('role') === 'tab') {
            document.querySelector<HTMLButtonElement>(`#${selected}`)?.focus();
        }
    }, [selected]);

    return (
        <div className={classes.paddingContainer}>
            <div
                aria-label={accessibilityLabel}
                className={cx(classes.container, className, isMobile && 'is-mobile')}
                role='tablist'>
                {tabs.map((tab, index) => (
                    <button
                        aria-controls={tab.controlsId}
                        aria-label={tab.accessibilityLabel}
                        aria-selected={selected === tab.id}
                        className={cx(classes.tab, selected === tab.id && 'selected', isMobile && 'is-mobile')}
                        disabled={tab.disabled}
                        id={tab.id}
                        key={tab.id}
                        onClick={onClick(tab.id)}
                        onKeyDown={onKeyDown(index)}
                        role='tab'
                        tabIndex={selected === tab.id ? undefined : -1}>
                        {tab.label}
                        {tab.countBadge && <span className={classes.badge}>{tab.countBadge}</span>}
                    </button>
                ))}
            </div>
        </div>
    );
};

export default Tabs;
