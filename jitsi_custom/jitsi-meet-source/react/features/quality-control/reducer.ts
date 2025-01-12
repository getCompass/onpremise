import { REDUCER_UPDATE_QUALITY } from './actionTypes';
import ReducerRegistry from "../base/redux/ReducerRegistry";

const DEFAULT_STATE = {
    qualityLevel: 'high',
};

export interface IQualityControlState {
    qualityLevel: string;
}

/**
 * Listen for actions that mutate the prejoin state.
 */
ReducerRegistry.register<IQualityControlState>(
    'features/quality-control', (state = DEFAULT_STATE, action): IQualityControlState => {
        switch (action.type) {
        case REDUCER_UPDATE_QUALITY:
            return {
                ...state,
                qualityLevel: action.value
            };

        default:
            return state;
        }
    }
);