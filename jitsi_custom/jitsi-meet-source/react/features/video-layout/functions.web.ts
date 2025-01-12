import { IReduxState } from '../app/types';
import { TILE_VIEW_MAX_COLUMNS_COUNT, TILE_VIEW_MAX_COLUMNS_COUNT_MOBILE } from '../filmstrip/constants';
import { getNumberOfPartipantsForTileView } from '../filmstrip/functions.web';
import { isMobileBrowser } from "../base/environment/utils";

export * from './functions.any';

/**
 * Returns how many columns should be displayed in tile view. The number
 * returned will be between 1 and 7, inclusive.
 *
 * @param {Object} state - The redux store state.
 * @param {Object} options - Object with custom values used to override the values that we get from redux by default.
 * @param {number} options.width - Custom width to be used.
 * @param {boolean} options.disableResponsiveTiles - Custom value to be used instead of config.disableResponsiveTiles.
 * @param {boolean} options.disableTileEnlargement - Custom value to be used instead of config.disableTileEnlargement.
 * @returns {number}
 */
export function getMaxColumnCount(state: IReduxState, options: {
    disableResponsiveTiles?: boolean; disableTileEnlargement?: boolean; width?: number | null;
} = {}) {
    return isMobileBrowser() ? TILE_VIEW_MAX_COLUMNS_COUNT_MOBILE : TILE_VIEW_MAX_COLUMNS_COUNT;
}

/**
 * Returns the cell count dimensions for tile view. Tile view tries to uphold
 * equal count of tiles for height and width, until maxColumn is reached in
 * which rows will be added but no more columns.
 *
 * @param {Object} state - The redux store state.
 * @param {boolean} stageFilmstrip - Whether the dimensions should be calculated for the stage filmstrip.
 * @returns {Object} An object is return with the desired number of columns,
 * rows, and visible rows (the rest should overflow) for the tile view layout.
 */
export function getNotResponsiveTileViewGridDimensions(state: IReduxState, stageFilmstrip = false) {
    const maxColumns = getMaxColumnCount(state);
    const { activeParticipants } = state['features/filmstrip'];
    const numberOfParticipants = stageFilmstrip ? activeParticipants.length : getNumberOfPartipantsForTileView(state);
    const rowsToMaintainASquare = Math.ceil(Math.sqrt(numberOfParticipants));
    const columns = Math.min(rowsToMaintainASquare, maxColumns);
    const rows = Math.ceil(numberOfParticipants / columns);
    const minVisibleRows = Math.min(maxColumns, rows);

    return {
        columns,
        minVisibleRows,
        rows
    };
}
