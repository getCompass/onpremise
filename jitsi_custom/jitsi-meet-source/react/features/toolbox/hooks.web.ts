import { useEffect } from 'react';
import { batch, useDispatch, useSelector } from 'react-redux';

import { ACTION_SHORTCUT_TRIGGERED, createShortcutEvent } from '../analytics/AnalyticsEvents';
import { sendAnalytics } from '../analytics/functions';
import { IReduxState } from '../app/types';
import { toggleDialog } from '../base/dialog/actions';
import { isIosMobileBrowser, isIpadMobileBrowser, isMobileBrowser } from '../base/environment/utils';
import JitsiMeetJS from '../base/lib-jitsi-meet';
import { raiseHand } from '../base/participants/actions';
import { getLocalParticipant, hasRaisedHand } from '../base/participants/functions';
import { closeChat, toggleChat } from '../chat/actions.web';
import ChatButton from '../chat/components/web/ChatButton';
import { setGifMenuVisibility } from '../gifs/actions';
import { isGifEnabled } from '../gifs/function.any';
import { registerShortcut, unregisterShortcut } from '../keyboard-shortcuts/actions.web';
import { close as closeParticipantsPane, open as openParticipantsPane } from '../participants-pane/actions.web';
import { getParticipantsPaneOpen, isParticipantsPaneEnabled } from '../participants-pane/functions';
import { getParticipantPaneButton, useParticipantPaneButton } from '../participants-pane/hooks.web';
import { toggleReactionsMenuVisibility } from '../reactions/actions.web';
import RaiseHandContainerButton from '../reactions/components/web/RaiseHandContainerButtons';
import { REACTIONS } from '../reactions/constants';
import { shouldDisplayReactionsButtons } from '../reactions/functions.any';
import { useReactionsButton } from '../reactions/hooks.web';
import { useRecordingButton } from '../recording/hooks.web';
import { startScreenShareFlow } from '../screen-share/actions.web';
import { isScreenVideoShared } from '../screen-share/functions';
import SettingsButton from '../settings/components/web/SettingsButton';
import { useSpeakerStatsButton } from '../speaker-stats/hooks.web';
import { toggleTileView } from '../video-layout/actions.any';
import { shouldDisplayTileView } from '../video-layout/functions.web';
import { useTileViewButton } from '../video-layout/hooks';
import VideoQualityDialog from '../video-quality/components/VideoQualityDialog.web';
import { usePremeetingVirtualBackgroundButton, useVirtualBackgroundButton } from '../virtual-background/hooks';

import { setFullScreen } from './actions.web';
import AudioSettingsButton from './components/web/AudioSettingsButton';
import CustomOptionButton from './components/web/CustomOptionButton';
import FullscreenButton from './components/web/FullscreenButton';
import ShareDesktopButton from './components/web/ShareDesktopButton';
import VideoSettingsButton from './components/web/VideoSettingsButton';
import { isButtonEnabled, isDesktopShareButtonDisabled } from './functions.web';
import { ICustomToolbarButton, IToolboxButton, ToolbarButton } from './types';
import RecordButtonElectron from '../recording/components/Recording/web/RecordButtonElectron';
import ModeratorSettingsButton from "./components/web/ModeratorSettingsButton";
import PremeetingAudioMuteButton from "./components/web/PremeetingAudioMuteButton";
import PremeetingVideoMuteButton from "./components/web/PremeetingVideoMuteButton";
import AudioMuteButtonMobile from "./components/web/AudioMuteButtonMobile";
import VideoMuteButtonMobile from "./components/web/VideoMuteButtonMobile";
import AudioSettingsButtonMobile from "./components/web/AudioSettingsButtonMobile";
import VideoSettingsButtonMobile from "./components/web/VideoSettingsButtonMobile";
import RaiseHandButtonMobile from "./components/web/RaiseHandButtonMobile";
import ChatButtonMobile from "../chat/components/web/ChatButtonMobile";
import VisitorAudioSettingsButton from "./components/web/VisitorAudioSettingsButton";
import VisitorVideoSettingsButton from "./components/web/VisitorVideoSettingsButton";
import VisitorAudioSettingsButtonMobile from "./components/web/VisitorAudioSettingsButtonMobile";
import VisitorVideoSettingsButtonMobile from "./components/web/VisitorVideoSettingsButtonMobile";

const microphone = {
    key: 'microphone',
    Content: AudioSettingsButton,
    group: 0
};

const microphoneMobile = {
    key: 'microphone',
    Content: AudioMuteButtonMobile,
    group: 0
};

const microphoneSettingsMobile = {
    key: 'microphone',
    Content: AudioSettingsButtonMobile,
    group: 0
};

const premeetingMicrophone = {
    key: 'premeeting-microphone',
    Content: PremeetingAudioMuteButton,
    group: 0
};

const visitorMicrophone = {
    key: 'visitor-microphone',
    Content: VisitorAudioSettingsButton,
    group: 0
};

const visitorMicrophoneMobile = {
    key: 'visitor-microphone',
    Content: VisitorAudioSettingsButtonMobile,
    group: 0
};

const camera = {
    key: 'camera',
    Content: VideoSettingsButton,
    group: 0
};

const cameraMobile = {
    key: 'camera',
    Content: VideoMuteButtonMobile,
    group: 0
};

const cameraSettingsMobile = {
    key: 'camera',
    Content: VideoSettingsButtonMobile,
    group: 0
};

const premeetingCamera = {
    key: 'premeeting-camera',
    Content: PremeetingVideoMuteButton,
    group: 0
};

const visitorCamera = {
    key: 'visitor-camera',
    Content: VisitorVideoSettingsButton,
    group: 0
};

const visitorCameraMobile = {
    key: 'visitor-camera',
    Content: VisitorVideoSettingsButtonMobile,
    group: 0
};

const chat = {
    key: 'chat',
    Content: ChatButton,
    group: 0
};

const chatMobile = {
    key: 'chat-mobile',
    Content: ChatButtonMobile,
    group: 0
};

const desktop = {
    key: 'desktop',
    Content: ShareDesktopButton,
    group: 0
};

// In Narrow layout and mobile web we are using drawer for popups and that is why it is better to include
// all forms of reactions in the overflow menu. Otherwise the toolbox will be hidden and the reactions popup
// misaligned.
const raisehand = {
    key: 'raisehand',
    Content: RaiseHandContainerButton,
    group: 0
};

// In Narrow layout and mobile web we are using drawer for popups and that is why it is better to include
// all forms of reactions in the overflow menu. Otherwise the toolbox will be hidden and the reactions popup
// misaligned.
const raiseHandMobile = {
    key: 'raisehand',
    Content: RaiseHandButtonMobile,
    group: 0
};

const moderatorSettings = {
    key: 'moderatorSettings',
    Content: ModeratorSettingsButton,
    group: 1
};

const fullscreen = {
    key: 'fullscreen',
    Content: FullscreenButton,
    group: 1
};

const recording_electron = {
    key: 'recording_electron',
    Content: RecordButtonElectron,
    group: 1
};

const settings = {
    key: 'settings',
    Content: SettingsButton,
    group: 2
};

/**
 * A hook that returns the desktop sharing button if it is enabled and undefined otherwise.
 *
 *  @returns {Object | undefined}
 */
function getDesktopSharingButton() {
    if (JitsiMeetJS.isDesktopSharingEnabled()) {
        return desktop;
    }
}

/**
 A hook that returns the compass microphone button by device.
 *
 *  @returns {Object | undefined}
 */
function getCompassMicrophoneButton() {
    if (isMobileBrowser()) {
        return microphoneMobile;
    }

    return microphone;
}

/**
 * A hook that returns the compass camera button by device.
 *
 *  @returns {Object | undefined}
 */
function getCompassCameraButton() {
    if (isMobileBrowser()) {
        return cameraMobile;
    }

    return camera;
}

/**
 * A hook that returns the fullscreen button if it is enabled and undefined otherwise.
 *
 *  @returns {Object | undefined}
 */
function getFullscreenButton() {
    if (!isIosMobileBrowser() || isIpadMobileBrowser()) {
        return fullscreen;
    }
}

/**
 * A hook that returns the moderator settings button if it is enabled and undefined otherwise.
 *
 *  @returns {Object | undefined}
 */
function getModeratorSettingsButton() {
    if (!isMobileBrowser()) {
        return moderatorSettings;
    }
}

/**
 * A hook that returns the visitor microphone button if it is enabled and undefined otherwise.
 *
 *  @returns {Object | undefined}
 */
function getVisitorMicrophoneButton() {
    if (isMobileBrowser()) {
        return visitorMicrophoneMobile;
    }

    return visitorMicrophone;
}

/**
 * A hook that returns the visitor camera button if it is enabled and undefined otherwise.
 *
 *  @returns {Object | undefined}
 */
function getVisitorCameraButton() {
    if (isMobileBrowser()) {
        return visitorCameraMobile;
    }

    return visitorCamera;
}

/**
 * Returns all buttons that could be rendered.
 *
 * @param {Object} _customToolbarButtons - An array containing custom buttons objects.
 * @returns {Object} The button maps mainMenuButtons and overflowMenuButtons.
 */
export function useCompassToolboxButtons(
    _customToolbarButtons?: ICustomToolbarButton[]): { [key: string]: IToolboxButton; } {
    const dekstopSharing = getDesktopSharingButton();
    const _fullscreen = getFullscreenButton();
    const participants = useParticipantPaneButton();
    const tileview = useTileViewButton();
    const microphone = getCompassMicrophoneButton();
    const camera = getCompassCameraButton();
    const _moderatorSettings = getModeratorSettingsButton();
    const _visitorMicrophone = getVisitorMicrophoneButton();
    const _visitorCamera = getVisitorCameraButton();

    let buttons: { [key in ToolbarButton]?: IToolboxButton; };
    if (isMobileBrowser()) {
        buttons = {
            camera,
            microphone,
            'visitor-camera': _visitorCamera,
            'visitor-microphone': _visitorMicrophone,
        };
    } else {
        buttons = {
            microphone,
            'visitor-microphone': _visitorMicrophone,
            'visitor-camera': _visitorCamera,
            camera,
            chat,
            raisehand,
            desktop: dekstopSharing,
            tileview,
            fullscreen: _fullscreen,
            moderatorSettings: _moderatorSettings,
            'participants-pane': participants
        };
    }
    const buttonKeys = Object.keys(buttons) as ToolbarButton[];

    buttonKeys.forEach(
        key => typeof buttons[key] === 'undefined' && delete buttons[key]);

    const customButtons = _customToolbarButtons?.reduce((prev, { backgroundColor, icon, id, text }) => {
        prev[id] = {
            backgroundColor,
            key: id,
            id,
            Content: CustomOptionButton,
            group: 4,
            icon,
            text
        };

        return prev;
    }, {} as { [key: string]: ICustomToolbarButton; });

    return {
        ...buttons,
        ...customButtons
    };
}

/**
 * Returns all buttons that could be rendered.
 *
 * @returns {Object} The button maps mainMenuButtons and overflowMenuButtons.
 */
export function useCompassOverflowMenuButtons(participantsPaneEnabled: boolean, remoteParticipantsLength: number, isVisitor: boolean): IToolboxButton[] {
    const participants = getParticipantPaneButton();

    let buttons: { [key in ToolbarButton]?: IToolboxButton; } = {
        'chat-mobile': chatMobile,
        'camera': cameraSettingsMobile,
        'microphone': microphoneSettingsMobile,
    };
    if (participantsPaneEnabled) {
        buttons = {
            'participants-pane': participants,
            'chat-mobile': chatMobile,
            'camera': cameraSettingsMobile,
            'microphone': microphoneSettingsMobile,
        };
    }
    if (isVisitor) {
        buttons = {
            'chat-mobile': chatMobile,
            'microphone': microphoneSettingsMobile,
        };
    }
    if (remoteParticipantsLength > 1 || isVisitor) {
        buttons['raisehand'] = raiseHandMobile;
    }
    const buttonKeys = Object.keys(buttons) as ToolbarButton[];

    buttonKeys.forEach(
        key => typeof buttons[key] === 'undefined' && delete buttons[key]);

    return buttonKeys.reduce((acc, key) => {
        const button = buttons[key];
        if (button !== undefined) {
            acc.push(button);
        }
        return acc;
    }, [] as IToolboxButton[]);
}

/**
 * Returns all buttons that could be rendered.
 *
 * @param {Object} _customToolbarButtons - An array containing custom buttons objects.
 * @returns {Object} The button maps mainMenuButtons and overflowMenuButtons.
 */
export function useToolboxButtons(
    _customToolbarButtons?: ICustomToolbarButton[]): { [key: string]: IToolboxButton; } {
    const dekstopSharing = getDesktopSharingButton();
    const _fullscreen = getFullscreenButton();
    const reactions = useReactionsButton();
    const participants = useParticipantPaneButton();
    const tileview = useTileViewButton();
    const recording = useRecordingButton();
    const virtualBackground = useVirtualBackgroundButton();
    const premeetingVirtualBackground = usePremeetingVirtualBackgroundButton();
    const speakerStats = useSpeakerStatsButton();

    const buttons: { [key in ToolbarButton]?: IToolboxButton; } = {
        microphone,
        camera,
        desktop: dekstopSharing,
        chat,
        raisehand,
        reactions,
        'participants-pane': participants,
        tileview,
        fullscreen: _fullscreen,
        recording,
        recording_electron,
        stats: speakerStats,
        'select-background': virtualBackground,
        settings,
        'premeeting-microphone': premeetingMicrophone,
        'premeeting-camera': premeetingCamera,
        'premeeting-select-background': premeetingVirtualBackground,
    };
    const buttonKeys = Object.keys(buttons) as ToolbarButton[];

    buttonKeys.forEach(
        key => typeof buttons[key] === 'undefined' && delete buttons[key]);

    const customButtons = _customToolbarButtons?.reduce((prev, { backgroundColor, icon, id, text }) => {
        prev[id] = {
            backgroundColor,
            key: id,
            id,
            Content: CustomOptionButton,
            group: 4,
            icon,
            text
        };

        return prev;
    }, {} as { [key: string]: ICustomToolbarButton; });

    return {
        ...buttons,
        ...customButtons
    };
}


export const useKeyboardShortcuts = (toolbarButtons: Array<string>) => {
    const dispatch = useDispatch();
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

            if (chatOpen) {
                dispatch(closeChat());
            }
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
            [ 'A', 'C', 'D', 'P', 'R', 'S', 'W', 'G', 'Ф', 'С', 'В', 'З', 'К', 'Ы', 'Ц', 'П' ].forEach(letter =>
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
