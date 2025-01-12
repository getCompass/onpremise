import { Theme } from '@mui/material';
import React from 'react';
import { WithTranslation } from 'react-i18next';
import { withStyles } from 'tss-react/mui';

import AbstractDialogTab, {
    IProps as AbstractDialogTabProps
} from '../../../base/dialog/components/web/AbstractDialogTab';
import { translate } from '../../../base/i18n/functions';
import Checkbox from '../../../base/ui/components/web/Checkbox';
import { isMobileBrowser } from "../../../base/environment/utils";
import Switch from "../../../base/ui/components/web/Switch";
import clsx from "clsx";

/**
 * The type of the React {@code Component} props of {@link NotificationsTab}.
 */
export interface IProps extends AbstractDialogTabProps, WithTranslation {

    /**
     * CSS classes object.
     */
    classes?: Partial<Record<keyof ReturnType<typeof styles>, string>>;

    /**
     * Array of disabled sounds ids.
     */
    disabledSounds: string[];

    /**
     * Whether or not the reactions feature is enabled.
     */
    enableReactions: Boolean;

    /**
     * The types of enabled notifications that can be configured and their specific visibility.
     */
    enabledNotifications: Object;

    /**
     * Whether or not moderator muted the sounds.
     */
    moderatorMutedSoundsReactions: Boolean;

    /**
     * Whether or not to display notifications settings.
     */
    showNotificationsSettings: boolean;

    /**
     * Whether sound settings should be displayed or not.
     */
    showSoundsSettings: boolean;

    /**
     * Whether or not the sound for the incoming message should play.
     */
    soundsIncomingMessage: Boolean;

    /**
     * Whether or not the sound for the participant joined should play.
     */
    soundsParticipantJoined: Boolean;

    /**
     * Whether or not the sound for the participant entering the lobby should play.
     */
    soundsParticipantKnocking: Boolean;

    /**
     * Whether or not the sound for the participant left should play.
     */
    soundsParticipantLeft: Boolean;

    /**
     * Whether or not the sound for reactions should play.
     */
    soundsReactions: Boolean;

    /**
     * Whether or not the sound for the talk while muted notification should play.
     */
    soundsTalkWhileMuted: Boolean;
}

const styles = (theme: Theme) => {
    return {
        container: {
            display: 'flex',
            width: '100%',
            flexDirection: 'column' as const
        },

        tabTitleContainer: {

            '&.is-mobile': {
                display: 'flex',
                flexDirection: 'column' as const,
                padding: '0 16px',
            }
        },

        tabTitle: {
            '&.is-mobile': {
                padding: '16px 0 6px 0',
                fontFamily: 'Lato Bold',
                fontWeight: 'normal' as const,
                fontSize: '20px',
                lineHeight: '28px',
                letterSpacing: '-0.3px',
                color: 'rgba(255, 255, 255, 0.8)',
            }
        },

        tabTitleDivider: {
            '&.is-mobile': {
                width: '100%',
                margin: '15px 0 20px 0',
                borderTop: '1px dashed rgba(255, 255, 255, 0.1)'
            }
        },

        column: {
            display: 'flex',
            flexDirection: 'column' as const,
            flex: 1,

            '&:first-child:not(:last-child)': {
                marginBottom: '24px'
            },

            '&.is-mobile': {
                padding: '0 16px',
                gap: '4px',

                '&:first-child:not(:last-child)': {
                    marginBottom: 0
                },

                '&:last-child': {
                    marginBottom: '24px'
                }
            },
        },

        title: {
            fontFamily: 'Lato Bold',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.75)',
            marginBottom: '16px',

            '&.is-mobile': {
                fontSize: '18px',
                lineHeight: '26px',
                color: 'rgba(255, 255, 255, 0.70)',
                letterSpacing: '-0.25px',
                marginTop: 0,
                marginBottom: '8px',
            }
        },

        controlRowContainer: {
            '&.is-mobile': {
                width: '100%',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                padding: '7px 0 6px 0',
            },

            '&> label': {
                '&.is-mobile': {
                    color: 'rgba(255, 255, 255, 0.7)',
                    fontFamily: 'Lato Regular',
                    fontWeight: 'normal' as const,
                    fontSize: '18px',
                    lineHeight: '26px',
                    letterSpacing: '-0.25px',
                },
            },
        },

        checkbox: {
            padding: 0,
            marginBottom: '12px'
        },

        text: {
            lineHeight: '18px',
        },
    };
};

/**
 * React {@code Component} for modifying the local user's sound settings.
 *
 * @augments Component
 */
class NotificationsTab extends AbstractDialogTab<IProps, any> {
    /**
     * Initializes a new {@code SoundsTab} instance.
     *
     * @param {IProps} props - The React {@code Component} props to initialize
     * the new {@code SoundsTab} instance with.
     */
    constructor(props: IProps) {
        super(props);

        // Bind event handlers so they are only bound once for every instance.
        this._onChange = this._onChange.bind(this);
        this._onEnabledNotificationsChanged = this._onEnabledNotificationsChanged.bind(this);
    }

    /**
     * Changes a sound setting state.
     *
     * @param {Object} e - The key event to handle.
     *
     * @returns {void}
     */
    _onChange({ target }: React.ChangeEvent<HTMLInputElement>) {
        super._onChange({ [target.name]: target.checked });
    }

    /**
     * Changes a sound setting state.
     *
     * @param {String} name - The key event to handle.
     * @param {Boolean} checked - The state event to handle.
     *
     * @returns {void}
     */
    _onChangeSwitch(name: string, checked: boolean) {

        super._onChange({ [name]: checked });
    }

    /**
     * Callback invoked to select if the given type of
     * notifications should be shown.
     *
     * @param {Object} e - The key event to handle.
     * @param {string} type - The type of the notification.
     *
     * @returns {void}
     */
    _onEnabledNotificationsChanged({ target: { checked } }: React.ChangeEvent<HTMLInputElement>, type: any) {
        super._onChange({
            enabledNotifications: {
                ...this.props.enabledNotifications,
                [type]: checked
            }
        });
    }

    /**
     * Callback invoked to select if the given type of
     * notifications should be shown.
     *
     * @param {string} type - The type of the notification.
     * @param {Boolean} checked - The state event to handle.
     *
     * @returns {void}
     */
    _onEnabledNotificationsChangedSwitch(type: any, checked: boolean) {
        super._onChange({
            enabledNotifications: {
                ...this.props.enabledNotifications,
                [type]: checked
            }
        });
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const {
            disabledSounds,
            enabledNotifications,
            showNotificationsSettings,
            showSoundsSettings,
            soundsIncomingMessage,
            soundsParticipantJoined,
            soundsParticipantKnocking,
            soundsParticipantLeft,
            soundsTalkWhileMuted,
            soundsReactions,
            enableReactions,
            moderatorMutedSoundsReactions,
            t
        } = this.props;
        const classes = withStyles.getClasses(this.props);
        const isMobile = isMobileBrowser();

        if (isMobile) {
            return (
                <div
                    className = {classes.container}
                    key = 'sounds'>
                    <div className = {clsx(classes.tabTitleContainer, 'is-mobile')}>
                        <div className = {clsx(classes.tabTitle, 'is-mobile')}>{t('settings.notifications')}</div>
                        <div className = {clsx(classes.tabTitleDivider, 'is-mobile')} />
                    </div>
                    {showSoundsSettings && (
                        <div className = {clsx(classes.column, 'is-mobile')}>
                            <h2 className = {clsx(classes.title, 'is-mobile')}>
                                {t('settings.playSounds')}
                            </h2>
                            <div className = {clsx(classes.controlRowContainer, 'is-mobile')}>
                                <label htmlFor = 'soundsIncomingMessage-switch' className = 'is-mobile'>
                                    {t('settings.incomingMessage')}
                                </label>
                                <Switch
                                    checked = {soundsIncomingMessage && !disabledSounds.includes('INCOMING_MSG_SOUND')}
                                    id = 'soundsIncomingMessage-switch'
                                    onChange = {checked => this._onChangeSwitch("soundsIncomingMessage", checked ?? false)} />
                            </div>
                            <div className = {clsx(classes.controlRowContainer, 'is-mobile')}>
                                <label htmlFor = 'soundsParticipantJoined-switch' className = 'is-mobile'>
                                    {t('settings.participantJoined')}
                                </label>
                                <Switch
                                    checked = {soundsParticipantJoined
                                        && !disabledSounds.includes('PARTICIPANT_JOINED_SOUND')}
                                    id = 'soundsParticipantJoined-switch'
                                    onChange = {checked => this._onChangeSwitch("soundsParticipantJoined", checked ?? false)} />
                            </div>
                            <div className = {clsx(classes.controlRowContainer, 'is-mobile')}>
                                <label htmlFor = 'soundsParticipantLeft-switch' className = 'is-mobile'>
                                    {t('settings.participantLeft')}
                                </label>
                                <Switch
                                    checked = {soundsParticipantLeft && !disabledSounds.includes('PARTICIPANT_LEFT_SOUND')}
                                    id = 'soundsParticipantLeft-switch'
                                    onChange = {checked => this._onChangeSwitch("soundsParticipantLeft", checked ?? false)} />
                            </div>
                            <div className = {clsx(classes.controlRowContainer, 'is-mobile')}>
                                <label htmlFor = 'soundsTalkWhileMuted-switch' className = 'is-mobile'>
                                    {t('settings.talkWhileMuted')}
                                </label>
                                <Switch
                                    checked = {soundsTalkWhileMuted && !disabledSounds.includes('TALK_WHILE_MUTED_SOUND')}
                                    id = 'soundsTalkWhileMuted-switch'
                                    onChange = {checked => this._onChangeSwitch("soundsTalkWhileMuted", checked ?? false)} />
                            </div>
                            <div className = {clsx(classes.controlRowContainer, 'is-mobile')}>
                                <label htmlFor = 'soundsParticipantKnocking-switch' className = 'is-mobile'>
                                    {t('settings.participantKnocking')}
                                </label>
                                <Switch
                                    checked = {soundsParticipantKnocking
                                        && !disabledSounds.includes('KNOCKING_PARTICIPANT_SOUND')}
                                    id = 'soundsParticipantKnocking-switch'
                                    onChange = {checked => this._onChangeSwitch("soundsParticipantKnocking", checked ?? false)} />
                            </div>
                        </div>
                    )}
                    {showNotificationsSettings && (
                        <div className = {clsx(classes.column, 'is-mobile')}>
                            {showSoundsSettings && (<div className = {clsx(classes.tabTitleDivider, 'is-mobile')} />)}
                            <h2 className = {clsx(classes.title, 'is-mobile')}>
                                {t('notify.displayNotifications')}
                            </h2>
                            {
                                Object.keys(enabledNotifications).map(key => (
                                    <div className = {clsx(classes.controlRowContainer, 'is-mobile')}>
                                        <label htmlFor = {`show-${key}-switch`} className = 'is-mobile'>
                                            {t(key)}
                                        </label>
                                        <Switch
                                            checked = {Boolean(enabledNotifications[key as
                                                keyof typeof enabledNotifications])}
                                            id = {`show-${key}-switch`}
                                            /* eslint-disable-next-line react/jsx-no-bind */
                                            onChange = {checked => this._onEnabledNotificationsChangedSwitch(key, checked ?? false)} />
                                    </div>
                                ))
                            }
                        </div>
                    )
                    }
                </div>
            )
                ;
        }

        return (
            <div
                className = {classes.container}
                key = 'sounds'>
                {showSoundsSettings && (
                    <div className = {classes.column}>
                        <h2 className = {classes.title}>
                            {t('settings.playSounds')}
                        </h2>
                        <Checkbox
                            checked = {soundsIncomingMessage && !disabledSounds.includes('INCOMING_MSG_SOUND')}
                            className = {classes.checkbox}
                            classNameText = {classes.text}
                            disabled = {disabledSounds.includes('INCOMING_MSG_SOUND')}
                            label = {t('settings.incomingMessage')}
                            name = 'soundsIncomingMessage'
                            onChange = {this._onChange} />
                        <Checkbox
                            checked = {soundsParticipantJoined
                                && !disabledSounds.includes('PARTICIPANT_JOINED_SOUND')}
                            className = {classes.checkbox}
                            classNameText = {classes.text}
                            disabled = {disabledSounds.includes('PARTICIPANT_JOINED_SOUND')}
                            label = {t('settings.participantJoined')}
                            name = 'soundsParticipantJoined'
                            onChange = {this._onChange} />
                        <Checkbox
                            checked = {soundsParticipantLeft && !disabledSounds.includes('PARTICIPANT_LEFT_SOUND')}
                            className = {classes.checkbox}
                            classNameText = {classes.text}
                            disabled = {disabledSounds.includes('PARTICIPANT_LEFT_SOUND')}
                            label = {t('settings.participantLeft')}
                            name = 'soundsParticipantLeft'
                            onChange = {this._onChange} />
                        <Checkbox
                            checked = {soundsTalkWhileMuted && !disabledSounds.includes('TALK_WHILE_MUTED_SOUND')}
                            className = {classes.checkbox}
                            classNameText = {classes.text}
                            disabled = {disabledSounds.includes('TALK_WHILE_MUTED_SOUND')}
                            label = {t('settings.talkWhileMuted')}
                            name = 'soundsTalkWhileMuted'
                            onChange = {this._onChange} />
                        <Checkbox
                            checked = {soundsParticipantKnocking
                                && !disabledSounds.includes('KNOCKING_PARTICIPANT_SOUND')}
                            className = {classes.checkbox}
                            classNameText = {classes.text}
                            disabled = {disabledSounds.includes('KNOCKING_PARTICIPANT_SOUND')}
                            label = {t('settings.participantKnocking')}
                            name = 'soundsParticipantKnocking'
                            onChange = {this._onChange} />
                    </div>
                )}
                {showNotificationsSettings && (
                    <div className = {classes.column}>
                        <h2 className = {classes.title}>
                            {t('notify.displayNotifications')}
                        </h2>
                        {
                            Object.keys(enabledNotifications).map(key => (
                                <Checkbox
                                    checked = {Boolean(enabledNotifications[key as
                                        keyof typeof enabledNotifications])}
                                    className = {classes.checkbox}
                                    classNameText = {classes.text}
                                    key = {key}
                                    label = {t(key)}
                                    name = {`show-${key}`}
                                    /* eslint-disable-next-line react/jsx-no-bind */
                                    onChange = {e => this._onEnabledNotificationsChanged(e, key)} />
                            ))
                        }
                    </div>
                )}
            </div>
        );
    }
}

export default withStyles(translate(NotificationsTab), styles);
