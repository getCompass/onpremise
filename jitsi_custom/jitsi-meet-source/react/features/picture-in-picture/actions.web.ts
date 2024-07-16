import {SET_IS_IN_WEBVIEW_PICTURE_IN_PICTURE_MODE} from './actionTypes';

/**
 * Sets whether the picture in picture changed.
 *
 * @param {boolean} is_in_picture_in_picture_mode - Whether the picture in picture changed.
 * @returns {{
 *     type: SET_IS_IN_WEBVIEW_PICTURE_IN_PICTURE_MODE,
 *     is_in_picture_in_picture_mode: boolean
 * }}
 * @public
 */
export function setIsInPictureInPictureMode(is_in_picture_in_picture_mode: boolean) {
    return {
        type: SET_IS_IN_WEBVIEW_PICTURE_IN_PICTURE_MODE,
        is_in_picture_in_picture_mode
    };
}
