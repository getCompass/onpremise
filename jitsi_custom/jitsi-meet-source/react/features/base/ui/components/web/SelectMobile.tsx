import React, { useCallback } from 'react';
import { makeStyles } from 'tss-react/mui';
import Icon from '../../../icons/components/Icon';
import { IconCheck, IconFlagDe, IconFlagEn, IconFlagEs, IconFlagFr, IconFlagIt, IconFlagRu } from '../../../icons/svg';

interface ISelectMobileProps {

    /**
     * Class name for additional styles.
     */
    className?: string;

    /**
     * Id of the <select> element.
     * Necessary for screen reader users, to link the label and error to the select.
     */
    id: string;

    /**
     * Left icon for action.
     */
    icon?: Function;

    /**
     * Select handler.
     */
    onSelect: Function;

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

    isLanguages: boolean;
}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            outline: 'none',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',

            '&:not(:empty)': {
                padding: 0
            },

            '&:first-of-type': {
                paddingTop: 0
            },

            '&:last-of-type': {
                paddingBottom: 0
            }
        },

        itemContainer: {
            padding: 0,
            display: 'flex',
        },

        item: {
            alignItems: 'center',
            cursor: 'pointer',
            display: 'flex',
            width: '100%',
            padding: 0,
            boxSizing: 'border-box',
            borderBottom: '0.5px solid rgba(255, 255, 255, 0.05)',

            '&:last-child': {
                '& > div': {
                    '& > *:last-child': {
                        borderBottom: '1px solid transparent',
                    }
                },
            },

            '& > .jitsi-icon': {
                padding: '16px 0px 16px 16px',
            },
        },

        icon: {
            padding: '16px 12px 16px 16px',

            '& svg': {
                fill: 'rgba(255, 255, 255, 0.3) !important'
            }
        },

        text: {
            width: '100%',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '17px',
            lineHeight: '22px',
            padding: '16px 16px 16px 0',
            color: 'rgba(255, 255, 255, 0.7)',

            '&.is-languages': {
                color: 'rgba(255, 255, 255, 0.8)',
            },
        },

        selectedIcon: {
            padding: '16px 16px 16px 0 !important',
            opacity: 0,

            '&.selected': {
                opacity: 1,
            }
        },
    };
});

const SelectMobile = ({
    className,
    id,
    icon,
    onSelect,
    options,
    value,
    isLanguages
}: ISelectMobileProps) => {
    const { classes, cx, theme } = useStyles();
    const getLanguageIcon = useCallback((language: string | number) => {

        switch (language) {

        case "ru": {
            return <Icon
                className = {classes.icon}
                size = {22}
                src = {IconFlagRu} />;
        }

        case "en": {
            return <Icon
                className = {classes.icon}
                size = {22}
                src = {IconFlagEn} />;
        }

        case "de": {
            return <Icon
                className = {classes.icon}
                size = {22}
                src = {IconFlagDe} />;
        }

        case "fr": {
            return <Icon
                className = {classes.icon}
                size = {22}
                src = {IconFlagFr} />;
        }

        case "es": {
            return <Icon
                className = {classes.icon}
                size = {22}
                src = {IconFlagEs} />;
        }

        case "it": {
            return <Icon
                className = {classes.icon}
                size = {22}
                src = {IconFlagIt} />;
        }

        default:
            return <></>;
        }
    }, []);

    return (
        <div id = {id} className = {cx(classes.container, className ?? '')}>
            {options.map(option => (
                <div
                    id = {`select_mobile_${option.value}`}
                    className = {classes.itemContainer}
                    onClick = {() => onSelect(option.value)}>
                    {icon && <Icon
                        className = {classes.icon}
                        size = {22}
                        src = {icon} />}
                    {isLanguages && getLanguageIcon(option.value)}
                    <div className = {classes.item}>
                        <div className = {cx(classes.text, isLanguages && 'is-languages')}>
                            {option.label}
                        </div>
                        <Icon
                            className = {cx(classes.selectedIcon, value === option.value && 'selected')}
                            size = {22}
                            src = {IconCheck}
                            color = {'rgba(0, 107, 224, 1)'} />
                    </div>
                </div>
            ))}
        </div>
    );
};

export default SelectMobile;
