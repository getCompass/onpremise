import ReducerRegistry from '../base/redux/ReducerRegistry';
import {SET_IS_IN_WEBVIEW_PICTURE_IN_PICTURE_MODE} from "./actionTypes";

/**
 * Initial state of pip's part of Redux store.
 */
const INITIAL_STATE = {
    /**
     * The indicator that determines whether the pip is enabled.
     *
     * @type {boolean}
     */
    is_in_picture_in_picture_mode: false
};

export interface IPictureInPictureState {
    is_in_picture_in_picture_mode: boolean;
}

ReducerRegistry.register<IPictureInPictureState>('features/picture-in-picture', (state = INITIAL_STATE, action): IPictureInPictureState => {
        switch (action.type) {
            case SET_IS_IN_WEBVIEW_PICTURE_IN_PICTURE_MODE:
                return {
                    ...state,
                    is_in_picture_in_picture_mode: action.is_in_picture_in_picture_mode,
                };
        }

        return state;
    });
