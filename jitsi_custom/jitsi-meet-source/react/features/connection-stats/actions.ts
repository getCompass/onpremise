import { SEND_UPDATED_STATS } from './actionTypes';

/**
 * Action used to send updated stats
 *
 * @param {Object} stats - The config options that override the default ones (if any).
 * @returns {Function}
 */
export function sendUpdatedStats(stats: object) {
    return {
        type: SEND_UPDATED_STATS,
        stats,
    };
}