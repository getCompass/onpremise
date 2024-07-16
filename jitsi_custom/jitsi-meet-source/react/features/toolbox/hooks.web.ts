import { useEffect } from 'react';
import { batch, useDispatch, useSelector } from 'react-redux';

import { ACTION_SHORTCUT_TRIGGERED, createShortcutEvent } from '../analytics/AnalyticsEvents';
import { sendAnalytics } from '../analytics/functions';
import { IReduxState } from '../app/types';
import { toggleDialog } from '../base/dialog/actions';
import JitsiMeetJS from '../base/lib-jitsi-meet';
import { raiseHand } from '../base/participants/actions';
import { getLocalParticipant, hasRaisedHand } from '../base/participants/functions';
import { toggleChat } from '../chat/actions.web';
import { setGifMenuVisibility } from '../gifs/actions';
import { isGifEnabled } from '../gifs/function.any';
import { registerShortcut, unregisterShortcut } from '../keyboard-shortcuts/actions.any';
import {
    close as closeParticipantsPane,
    open as openParticipantsPane
} from '../participants-pane/actions.web';
import {
    getParticipantsPaneOpen,
    isParticipantsPaneEnabled
} from '../participants-pane/functions';
import { addReactionToBuffer } from '../reactions/actions.any';
import { toggleReactionsMenuVisibility } from '../reactions/actions.web';
import { REACTIONS } from '../reactions/constants';
import { shouldDisplayReactionsButtons } from '../reactions/functions.any';
import { startScreenShareFlow } from '../screen-share/actions.web';
import { isScreenVideoShared } from '../screen-share/functions';
import SpeakerStats from '../speaker-stats/components/web/SpeakerStats';
import { isSpeakerStatsDisabled } from '../speaker-stats/functions';
import { toggleTileView } from '../video-layout/actions.any';
import { shouldDisplayTileView } from '../video-layout/functions.any';
import VideoQualityDialog from '../video-quality/components/VideoQualityDialog.web';

import { setFullScreen } from './actions.web';
import { isButtonEnabled, isDesktopShareButtonDisabled } from './functions.web';

export const useKeyboardShortcuts = (toolbarButtons: Array<string>) => {
    const dispatch = useDispatch();
    const _isSpeakerStatsDisabled = useSelector(isSpeakerStatsDisabled);
    const _isParticipantsPaneEnabled = useSelector(isParticipantsPaneEnabled);
    const _shouldDisplayReactionsButtons = useSelector(shouldDisplayReactionsButtons);
    const _toolbarButtons = useSelector(
        (state: IReduxState) => toolbarButtons || state['features/toolbox'].toolbarButtons);
    const chatOpen = useSelector((state: IReduxState) => state['features/chat'].isOpen);
    const desktopSharingButtonDisabled = useSelector(isDesktopShareButtonDisabled);
    const desktopSharingEnabled = JitsiMeetJS.isDesktopSharingEnabled();
    const fullScreen = useSelector((state: IReduxState) => state['features/toolbox'].fullScreen);
    const gifsEnabled = useSelector(isGifEnabled);
    const participantsPaneOpen = useSelector(getParticipantsPaneOpen);
    const raisedHand = useSelector((state: IReduxState) => hasRaisedHand(getLocalParticipant(state)));
    const screenSharing = useSelector(isScreenVideoShared);
    const tileViewEnabled = useSelector(shouldDisplayTileView);

    /**
     * Creates an analytics keyboard shortcut event and dispatches an action for
     * toggling the display of chat.
     *
     * @private
     * @returns {void}
     */
    function onToggleChat() {
        sendAnalytics(createShortcutEvent(
            'toggle.chat',
            ACTION_SHORTCUT_TRIGGERED,
            {
                enable: !chatOpen
            }));

        // Checks if there was any text selected by the user.
        // Used for when we press simultaneously keys for copying
        // text messages from the chat board
        if (window.getSelection()?.toString() !== '') {
            return false;
        }

        dispatch(toggleChat());
    }

    /**
     * Creates an analytics keyboard shortcut event and dispatches an action for
     * toggling the display of the participants pane.
     *
     * @private
     * @returns {void}
     */
    function onToggleParticipantsPane() {
        sendAnalytics(createShortcutEvent(
            'toggle.participants-pane',
            ACTION_SHORTCUT_TRIGGERED,
            {
                enable: !participantsPaneOpen
            }));

        if (participantsPaneOpen) {
            dispatch(closeParticipantsPane());
        } else {
            dispatch(openParticipantsPane());
        }
    }

    /**
    * Creates an analytics keyboard shortcut event and dispatches an action for
    * toggling the display of Video Quality.
    *
    * @private
    * @returns {void}
    */
    function onToggleVideoQuality() {
        sendAnalytics(createShortcutEvent('video.quality'));

        dispatch(toggleDialog(VideoQualityDialog));
    }

    /**
     * Dispatches an action for toggling the tile view.
     *
     * @private
     * @returns {void}
     */
    function onToggleTileView() {
        sendAnalytics(createShortcutEvent(
            'toggle.tileview',
            ACTION_SHORTCUT_TRIGGERED,
            {
                enable: !tileViewEnabled
            }));

        dispatch(toggleTileView());
    }

    /**
     * Creates an analytics keyboard shortcut event and dispatches an action for
     * toggling full screen mode.
     *
     * @private
     * @returns {void}
     */
    function onToggleFullScreen() {
        sendAnalytics(createShortcutEvent(
            'toggle.fullscreen',
            ACTION_SHORTCUT_TRIGGERED,
            {
                enable: !fullScreen
            }));
        dispatch(setFullScreen(!fullScreen));
    }

    /**
     * Creates an analytics keyboard shortcut event and dispatches an action for
     * toggling raise hand.
     *
     * @private
     * @returns {void}
     */
    function onToggleRaiseHand() {
        sendAnalytics(createShortcutEvent(
            'toggle.raise.hand',
            ACTION_SHORTCUT_TRIGGERED,
            { enable: !raisedHand }));

        dispatch(raiseHand(!raisedHand));
    }

    /**
     * Creates an analytics keyboard shortcut event and dispatches an action for
     * toggling screensharing.
     *
     * @private
     * @returns {void}
     */
    function onToggleScreenshare() {
        // Ignore the shortcut if the button is disabled.
        if (desktopSharingButtonDisabled) {
            return;
        }
        sendAnalytics(createShortcutEvent(
            'toggle.screen.sharing',
            ACTION_SHORTCUT_TRIGGERED,
            {
                enable: !screenSharing
            }));

        if (desktopSharingEnabled && !desktopSharingButtonDisabled) {
            dispatch(startScreenShareFlow(!screenSharing));
        }
    }

    /**
     * Creates an analytics keyboard shortcut event and dispatches an action for
     * toggling speaker stats.
     *
     * @private
     * @returns {void}
     */
    function onSpeakerStats() {
        sendAnalytics(createShortcutEvent(
            'speaker.stats'
        ));

        dispatch(toggleDialog(SpeakerStats, {
            conference: APP.conference
        }));
    }

    useEffect(() => {
        const KEYBOARD_SHORTCUTS = [
            isButtonEnabled('chat', _toolbarButtons) && {
                character: 'C',
                exec: onToggleChat,
                helpDescription: 'keyboardShortcuts.toggleChat'
            },
            isButtonEnabled('chat', _toolbarButtons) && {
                character: 'С',
                exec: onToggleChat,
                helpDescription: 'keyboardShortcuts.toggleChat'
            },
            isButtonEnabled('desktop', _toolbarButtons) && {
                character: 'D',
                exec: onToggleScreenshare,
                helpDescription: 'keyboardShortcuts.toggleScreensharing'
            },
            isButtonEnabled('desktop', _toolbarButtons) && {
                character: 'В',
                exec: onToggleScreenshare,
                helpDescription: 'keyboardShortcuts.toggleScreensharing'
            },
            _isParticipantsPaneEnabled && isButtonEnabled('participants-pane', _toolbarButtons) && {
                character: 'P',
                exec: onToggleParticipantsPane,
                helpDescription: 'keyboardShortcuts.toggleParticipantsPane'
            },
            _isParticipantsPaneEnabled && isButtonEnabled('participants-pane', _toolbarButtons) && {
                character: 'З',
                exec: onToggleParticipantsPane,
                helpDescription: 'keyboardShortcuts.toggleParticipantsPane'
            },
            isButtonEnabled('raisehand', _toolbarButtons) && {
                character: 'R',
                exec: onToggleRaiseHand,
                helpDescription: 'keyboardShortcuts.raiseHand'
            },
            isButtonEnabled('raisehand', _toolbarButtons) && {
                character: 'К',
                exec: onToggleRaiseHand,
                helpDescription: 'keyboardShortcuts.raiseHand'
            },
            isButtonEnabled('fullscreen', _toolbarButtons) && {
                character: 'S',
                exec: onToggleFullScreen,
                helpDescription: 'keyboardShortcuts.fullScreen'
            },
            isButtonEnabled('fullscreen', _toolbarButtons) && {
                character: 'Ы',
                exec: onToggleFullScreen,
                helpDescription: 'keyboardShortcuts.fullScreen'
            },
            isButtonEnabled('tileview', _toolbarButtons) && {
                character: 'W',
                exec: onToggleTileView,
                helpDescription: 'keyboardShortcuts.tileViewToggle'
            },
            isButtonEnabled('tileview', _toolbarButtons) && {
                character: 'Ц',
                exec: onToggleTileView,
                helpDescription: 'keyboardShortcuts.tileViewToggle'
            },
            !_isSpeakerStatsDisabled && isButtonEnabled('stats', _toolbarButtons) && {
                character: 'T',
                exec: onSpeakerStats,
                helpDescription: 'keyboardShortcuts.showSpeakerStats'
            },
            !_isSpeakerStatsDisabled && isButtonEnabled('stats', _toolbarButtons) && {
                character: 'Е',
                exec: onSpeakerStats,
                helpDescription: 'keyboardShortcuts.showSpeakerStats'
            }
        ];

        KEYBOARD_SHORTCUTS.forEach(shortcut => {
            if (typeof shortcut === 'object') {
                dispatch(registerShortcut({
                    character: shortcut.character,
                    handler: shortcut.exec,
                    helpDescription: shortcut.helpDescription
                }));
            }
        });

        // If the buttons for sending reactions are not displayed we should disable the shortcuts too.
        if (_shouldDisplayReactionsButtons) {
            if (gifsEnabled) {
                const onGifShortcut = () => {
                    batch(() => {
                        dispatch(toggleReactionsMenuVisibility());
                        dispatch(setGifMenuVisibility(true));
                    });
                };

                dispatch(registerShortcut({
                    character: 'G',
                    handler: onGifShortcut,
                    helpDescription: 'keyboardShortcuts.giphyMenu'
                }));

                dispatch(registerShortcut({
                    character: 'П',
                    handler: onGifShortcut,
                    helpDescription: 'keyboardShortcuts.giphyMenu'
                }));
            }
        }

        return () => {
            [ 'A', 'C', 'D', 'P', 'R', 'S', 'W', 'T', 'G', 'Ф', 'С', 'В', 'З', 'К', 'Ы', 'Ц', 'Е', 'П' ].forEach(letter =>
                dispatch(unregisterShortcut(letter)));

            if (_shouldDisplayReactionsButtons) {
                Object.keys(REACTIONS).map(key => REACTIONS[key].shortcutChar)
                    .forEach(letter =>
                        dispatch(unregisterShortcut(letter, true)));
            }
        };
    }, [
        _shouldDisplayReactionsButtons,
        chatOpen,
        desktopSharingButtonDisabled,
        desktopSharingEnabled,
        fullScreen,
        gifsEnabled,
        participantsPaneOpen,
        raisedHand,
        screenSharing,
        tileViewEnabled
    ]);
};
