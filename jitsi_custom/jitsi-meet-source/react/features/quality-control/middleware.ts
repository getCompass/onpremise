import { setNewReceiverQuality, setReducerQuality } from './actions';
import { ENDPOINT_MESSAGE_RECEIVED, NON_PARTICIPANT_MESSAGE_RECEIVED } from "../base/conference/actionTypes";
import { REDUCER_UPDATE_QUALITY } from "./actionTypes";
import MiddlewareRegistry from "../base/redux/MiddlewareRegistry";
import { COMMAND_QUALITY_LEVEL } from "./constants";
import { getNumberOfPartipantsForTileView } from "../filmstrip/functions.web";
import { getCurrentConference } from "../base/conference/functions";
import { isLocalTrackMuted } from "../base/tracks/functions.any";
import { MEDIA_TYPE } from "../base/media/constants";

MiddlewareRegistry.register(store => next => action => {
    const { dispatch } = store;

    switch (action.type) {
    case REDUCER_UPDATE_QUALITY: {

        const numberOfParticipants = getNumberOfPartipantsForTileView(store.getState());
        setNewReceiverQuality(numberOfParticipants, getCurrentConference(store.getState())?.getParticipants(), isLocalTrackMuted(store.getState()['features/base/tracks'], MEDIA_TYPE.VIDEO), dispatch, action.value);
        break;
    }

    case ENDPOINT_MESSAGE_RECEIVED: {
        const { data } = action;

        if (data.type === COMMAND_QUALITY_LEVEL) {
            dispatch(setReducerQuality(data.value));
        }
        break;
    }

    case NON_PARTICIPANT_MESSAGE_RECEIVED: {
        const { json: data } = action;

        if (data.type === COMMAND_QUALITY_LEVEL) {
            dispatch(setReducerQuality(data.value));
        }
        break;
    }
    default:
        break;
    }

    return next(action);
});
