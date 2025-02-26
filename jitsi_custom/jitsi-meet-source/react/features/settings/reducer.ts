import ReducerRegistry from '../base/redux/ReducerRegistry';

import {
    SET_AUDIO_SETTINGS_VISIBILITY, SET_DESKTOP_SHARE_QUALITY_SETTINGS_VISIBILITY, SET_MODERATOR_SETTINGS_VISIBILITY,
    SET_VIDEO_SETTINGS_VISIBILITY
} from './actionTypes';

export interface ISettingsState {
    audioSettingsVisible?: boolean;
    videoSettingsVisible?: boolean;
}

ReducerRegistry.register('features/settings', (state: ISettingsState = {}, action) => {
    switch (action.type) {
    case SET_AUDIO_SETTINGS_VISIBILITY:
        return {
            ...state,
            audioSettingsVisible: action.value
        };
    case SET_MODERATOR_SETTINGS_VISIBILITY:
        return {
            ...state,
            moderatorSettingsVisible: action.value
        };
    case SET_DESKTOP_SHARE_QUALITY_SETTINGS_VISIBILITY:
        return {
            ...state,
            desktopShareQualitySettingsVisible: action.value
        };
    case SET_VIDEO_SETTINGS_VISIBILITY:
        return {
            ...state,
            videoSettingsVisible: action.value
        };
    }

    return state;
});
