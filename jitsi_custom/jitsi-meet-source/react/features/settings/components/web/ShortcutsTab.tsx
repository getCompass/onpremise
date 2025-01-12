import { Theme } from '@mui/material';
import React from 'react';
import { WithTranslation } from 'react-i18next';
import { withStyles } from 'tss-react/mui';

import AbstractDialogTab, {
    IProps as AbstractDialogTabProps
} from '../../../base/dialog/components/web/AbstractDialogTab';
import { translate } from '../../../base/i18n/functions';
import Checkbox from '../../../base/ui/components/web/Checkbox';

/**
 * The type of the React {@code Component} props of {@link ShortcutsTab}.
 */
export interface IProps extends AbstractDialogTabProps, WithTranslation {

    /**
     * CSS classes object.
     */
    classes?: Partial<Record<keyof ReturnType<typeof styles>, string>>;

    /**
     * Whether to display the shortcuts or not.
     */
    displayShortcuts: boolean;

    /**
     * Wether the keyboard shortcuts are enabled or not.
     */
    keyboardShortcutsEnabled: boolean;

    /**
     * The keyboard shortcuts descriptions.
     */
    keyboardShortcutsHelpDescriptions: Map<string, string>;
}

const styles = (theme: Theme) => {
    return {
        container: {
            display: 'flex',
            flexDirection: 'column' as const,
            width: '100%',
            paddingBottom: '8px',
        },

        checkboxContainer: {
            position: 'absolute' as const,
            bottom: 0,
            backgroundColor: 'rgba(33, 33, 33, 1)',
            left: '2px',
            borderRadius: '0px 0px 7px 0px',
            width: 'calc(100% - 2px)',
        },

        checkbox: {
            padding: '16px 22px',
        },

        checkboxText: {
            lineHeight: '18px',
        },

        listContainer: {
            listStyleType: 'none',
            padding: 0,
            margin: 0
        },

        listItem: {
            display: 'flex',
            justifyContent: 'start',
            alignItems: 'center',
            padding: 0,
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '18px',
            color: 'rgba(255, 255, 255, 0.75)',
            gap: '12px',
            marginTop: '7px',

            '&:first-child': {
                marginTop: 0,
            }
        },

        listItemKey: {
            backgroundColor: 'rgba(255, 255, 255, 0.1)',
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '16px',
            lineHeight: '22px',
            padding: '0px 4px',
            borderRadius: '4px',
            minWidth: '16px',
            height: '23px',
            textAlign: 'center' as const,
        }
    };
};

/**
 * React {@code Component} for modifying the local user's profile.
 *
 * @augments Component
 */
class ShortcutsTab extends AbstractDialogTab<IProps, any> {
    /**
     * Initializes a new {@code MoreTab} instance.
     *
     * @param {Object} props - The read-only properties with which the new
     * instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        // Bind event handler so it is only bound once for every instance.
        this._onKeyboardShortcutEnableChanged = this._onKeyboardShortcutEnableChanged.bind(this);
        this._renderShortcutsListItem = this._renderShortcutsListItem.bind(this);
    }

    /**
     * Callback invoked to select if global keyboard shortcuts
     * should be enabled.
     *
     * @param {Object} e - The key event to handle.
     *
     * @returns {void}
     */
    _onKeyboardShortcutEnableChanged({ target: { checked } }: React.ChangeEvent<HTMLInputElement>) {
        super._onChange({ keyboardShortcutsEnabled: checked });
    }

    /**
     * Render a keyboard shortcut with key and description.
     *
     * @param {string} keyboardKey - The keyboard key for the shortcut.
     * @param {string} translationKey - The translation key for the shortcut description.
     * @returns {JSX}
     */
    _renderShortcutsListItem(keyboardKey: string, translationKey: string) {
        const { t } = this.props;
        const classes = withStyles.getClasses(this.props);
        let modifierKey = 'Alt';

        if (window.navigator?.platform) {
            if (window.navigator.platform.indexOf('Mac') !== -1) {
                modifierKey = '⌥';
            }
        }

        // не отображаем кириллические буквы
        if ([ 'А', 'Ь', 'М', 'С', 'В', 'З', 'К', 'Ы', 'Ц', 'Е' ].includes(keyboardKey)) {
            return <></>;
        }

        return (
            <li
                className = {classes.listItem}
                key = {keyboardKey}>
                <span className = {classes.listItemKey}>
                    {keyboardKey.startsWith(':')
                        ? `${modifierKey} + ${keyboardKey.slice(1)}`
                        : keyboardKey}
                </span>
                <span
                    aria-label = {t(translationKey)}>
                    {t(translationKey)}
                </span>
            </li>
        );
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const {
            displayShortcuts,
            keyboardShortcutsHelpDescriptions,
            keyboardShortcutsEnabled,
            t
        } = this.props;
        const classes = withStyles.getClasses(this.props);

        // порядок ключей
        const order = [ 'm', 'v', 'c', 'd', 'p', 'r', 's', 'w', 't', '0', '1-9', 'space' ];

        // сортировка
        const compareFunction = (a: [ string, string ], b: [ string, string ]) => {
            const keyA = a[0].toLowerCase(); // keyboardKey
            const keyB = b[0].toLowerCase(); // translationKey

            const indexA = order.indexOf(keyA);
            const indexB = order.indexOf(keyB);

            // Если ключ не найден в order, он будет помещен в конец
            const positionA = indexA === -1 ? order.length : indexA;
            const positionB = indexB === -1 ? order.length : indexB;

            return positionA - positionB;
        };

        const shortcutDescriptions: Map<string, string> = displayShortcuts
            ? keyboardShortcutsHelpDescriptions
            : new Map();

        return (
            <>
                <div className = {classes.container}>
                    {displayShortcuts && (
                        <ul className = {classes.listContainer}>
                            {Array.from(shortcutDescriptions)
                                .sort(compareFunction)
                                .map(description => this._renderShortcutsListItem(...description))}
                        </ul>
                    )}
                </div>
                <div className = {classes.checkboxContainer}>
                    <Checkbox
                        checked = {keyboardShortcutsEnabled}
                        className = {classes.checkbox}
                        classNameText = {classes.checkboxText}
                        label = {t('prejoin.keyboardShortcuts')}
                        name = 'enable-keyboard-shortcuts'
                        onChange = {this._onKeyboardShortcutEnableChanged} />
                </div>
            </>
        );
    }
}

export default withStyles(translate(ShortcutsTab), styles);
