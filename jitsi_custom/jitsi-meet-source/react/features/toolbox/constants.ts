import { ToolbarButton } from './types';

/**
 * Thresholds for displaying toolbox buttons.
 */
export const THRESHOLDS = [
    {
        width: 565,
        order: [ 'microphone', 'camera', 'desktop', 'chat', 'raisehand', 'reactions', 'participants-pane', 'tileview' ]
    },
    {
        width: 520,
        order: [ 'microphone', 'camera', 'desktop', 'chat', 'raisehand', 'participants-pane', 'tileview' ]
    },
    {
        width: 470,
        order: [ 'microphone', 'camera', 'desktop', 'raisehand', 'participants-pane', 'tileview' ]
    },
    {
        width: 420,
        order: [ 'microphone', 'camera', 'desktop', 'participants-pane', 'tileview' ]
    },
    {
        width: 370,
        order: [ 'microphone', 'camera', 'participants-pane', 'tileview' ]
    },
    {
        width: 225,
        order: [ 'microphone', 'camera', 'participants-pane' ]
    },
    {
        width: 200,
        order: [ 'camera', 'microphone' ]
    }
];

/**
 * Thresholds for displaying compass toolbox buttons.
 */
export const COMPASS_THRESHOLDS = [
    {
        width: 565,
        order: [ 'chat', 'raisehand', 'desktop', 'tileview', 'fullscreen' ]
    },
    {
        width: 520,
        order: [ 'chat', 'raisehand', 'desktop', 'tileview', 'fullscreen' ]
    },
    {
        width: 470,
        order: [ 'chat', 'raisehand', 'desktop', 'tileview', 'fullscreen' ]
    },
    {
        width: 420,
        order: [ 'chat', 'raisehand', 'desktop', 'tileview', 'fullscreen' ]
    },
    {
        width: 370,
        order: [ 'chat', 'desktop', 'tileview', 'fullscreen' ]
    },
    {
        width: 225,
        order: [ 'desktop', 'tileview', 'fullscreen' ]
    },
    {
        width: 200,
        order: [ 'tileview', 'fullscreen' ]
    }
];

/**
 * Thresholds for displaying compass toolbox buttons.
 */
export const COMPASS_THRESHOLDS_MOBILE = [
    {
        width: 565,
        order: [ 'camera', 'microphone' ]
    },
    {
        width: 520,
        order: [ 'camera', 'microphone' ]
    },
    {
        width: 470,
        order: [ 'camera', 'microphone' ]
    },
    {
        width: 420,
        order: [ 'camera', 'microphone' ]
    },
    {
        width: 370,
        order: [ 'camera', 'microphone' ]
    },
    {
        width: 225,
        order: [ 'camera', 'microphone' ]
    },
    {
        width: 200,
        order: [ 'camera', 'microphone' ]
    }
];

/**
 * Main toolbar buttons priority used to determine which button should be picked to fill empty spaces for disabled
 * buttons.
 */
export const MAIN_TOOLBAR_BUTTONS_PRIORITY = [
    'microphone',
    'camera',
    'chat',
    'chat-mobile',
    'desktop',
    'raisehand',
    'reactions',
    'participants-pane',
    'tileview',
    'fullscreen',
    'recording',
    'recording_electron',
    'stats',
    'select-background',
    'settings'
];

/**
 * Main toolbar buttons priority used to determine which button should be picked to fill empty spaces for disabled
 * buttons.
 */
export const MAIN_COMPASS_TOOLBAR_BUTTONS_PRIORITY = [
    'microphone',
    'camera',
    'participants-pane',
    'chat',
    'chat-mobile',
    'raisehand',
    'desktop',
    'tileview',
    'moderatorSettings',
    'fullscreen',
];

export const TOOLBAR_TIMEOUT = 3000;

export const DRAWER_MAX_HEIGHT = '80dvh - 64px';

// Around 300 to be displayed above components like chat
export const ZINDEX_DIALOG_PORTAL = 302;

/**
 * Color for spinner displayed in the toolbar.
 */
export const SPINNER_COLOR = 'rgba(255, 255, 255, 0.6)';

/**
 * The list of all possible UI buttons.
 *
 * @protected
 * @type Array<string>
 */
export const TOOLBAR_BUTTONS: ToolbarButton[] = [
    'camera',
    'chat',
    'chat-mobile',
    'closedcaptions',
    'desktop',
    'download',
    'embedmeeting',
    'etherpad',
    'feedback',
    'filmstrip',
    'fullscreen',
    'hangup',
    'help',
    'highlight',
    'invite',
    'linktosalesforce',
    'livestreaming',
    'microphone',
    'moderatorSettings',
    'mute-everyone',
    'mute-video-everyone',
    'participants-pane',
    'profile',
    'raisehand',
    'recording',
    'recording_electron',
    'security',
    'select-background',
    'settings',
    'shareaudio',
    'noisesuppression',
    'sharedvideo',
    'shortcuts',
    'stats',
    'tileview',
    'toggle-camera',
    'videoquality',
    'whiteboard',
    'premeeting-microphone',
    'premeeting-camera',
    'premeeting-select-background'
];

/**
 * The toolbar buttons to show when in visitors mode.
 */
export const VISITORS_MODE_BUTTONS: ToolbarButton[] = [
    'chat',
    'chat-mobile',
    'closedcaptions',
    'hangup',
    'raisehand',
    'settings',
    'tileview',
    'fullscreen',
    'stats',
    'videoquality'
];
