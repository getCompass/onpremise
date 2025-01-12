import React from 'react';
import { connect } from 'react-redux';

import { IReduxState } from '../../../app/types';
import { isMobileBrowser } from '../../../base/environment/utils';
import { LAYOUTS } from '../../../video-layout/constants';
import { getCurrentLayout } from '../../../video-layout/functions.web';
import {
    ASPECT_RATIO_BREAKPOINT,
    FILMSTRIP_BREAKPOINT,
    FILMSTRIP_BREAKPOINT_OFFSET,
    FILMSTRIP_TYPE,
    TOOLBAR_HEIGHT,
    TOOLBAR_HEIGHT_MOBILE
} from '../../constants';
import { isFilmstripResizable, showGridInVerticalView } from '../../functions.web';

import Filmstrip from './Filmstrip';
import { getLocalParticipant } from "../../../base/participants/functions";
import FilmstripMobile from "./FilmstripMobile";

interface IProps {

    /**
     * The number of columns in tile view.
     */
    _columns: number;

    /**
     * The height of the filmstrip.
     */
    _filmstripHeight?: number;

    /**
     * The width of the filmstrip.
     */
    _filmstripWidth?: number;

    /**
     * Whether the filmstrip has scroll or not.
     */
    _hasScroll: boolean;

    /**
     * Whether or not the current layout is vertical filmstrip.
     */
    _isVerticalFilmstrip: boolean;

    /**
     * The participants in the call.
     */
    _filteredRemoteParticipants: Array<Object>;

    /**
     * The length of the remote participants array.
     */
    _filteredRemoteParticipantsLength: number;

    /**
     * Whether or not the filmstrip should be user-resizable.
     */
    _resizableFilmstrip: boolean;

    /**
     * The number of rows in tile view.
     */
    _rows: number;

    /**
     * The height of the thumbnail.
     */
    _thumbnailHeight?: number;

    /**
     * The width of the thumbnail.
     */
    _thumbnailWidth?: number;

    /**
     * Whether or not the vertical filmstrip should have a background color.
     */
    _verticalViewBackground: boolean;

    /**
     * Whether or not the vertical filmstrip should be displayed as grid.
     */
    _verticalViewGrid: boolean;

    /**
     * Additional CSS class names to add to the container of all the thumbnails.
     */
    _videosClassName: string;
}

const MainFilmstripMobile = (props: IProps) => (

    <span>
        <FilmstripMobile
            {...props}
            filmstripType = {FILMSTRIP_TYPE.MAIN} />
    </span>
);

/**
 * Maps (parts of) the Redux state to the associated {@code Filmstrip}'s props.
 *
 * @param {Object} state - The Redux state.
 * @param {any} _ownProps - Components' own props.
 * @private
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState, _ownProps: any) {
    const { toolbarButtons } = state['features/toolbox'];
    const { remoteParticipants, width: verticalFilmstripWidth } = state['features/filmstrip'];
    const reduceHeight = state['features/toolbox'].visible && toolbarButtons?.length;
    const {
        gridDimensions: dimensions = {
            columns: undefined,
            rows: undefined
        },
        filmstripHeight,
        filmstripWidth,
        hasScroll: tileViewHasScroll,
        thumbnailSize: tileViewThumbnailSize
    } = state['features/filmstrip'].tileViewDimensions ?? {};
    const _currentLayout = getCurrentLayout(state);
    const _resizableFilmstrip = isFilmstripResizable(state);
    const _verticalViewGrid = showGridInVerticalView(state);
    const localParticipant = getLocalParticipant(state);
    const localParticipantId = localParticipant?.id ?? '';
    let gridDimensions = dimensions;
    let _hasScroll = false;

    const { clientHeight, clientWidth } = state['features/base/responsive-ui'];
    const availableSpace = clientHeight - Number(filmstripHeight);
    let filmstripPadding = 0;

    if (availableSpace > 0) {
        const paddingValue = TOOLBAR_HEIGHT_MOBILE - availableSpace;

        if (paddingValue > 0) {
            filmstripPadding = paddingValue;
        }
    } else {
        filmstripPadding = TOOLBAR_HEIGHT_MOBILE;
    }

    const collapseTileView = reduceHeight
        && isMobileBrowser()
        && clientWidth <= ASPECT_RATIO_BREAKPOINT;

    const shouldReduceHeight = reduceHeight && (
        isMobileBrowser() || (_currentLayout !== LAYOUTS.VERTICAL_FILMSTRIP_VIEW
            && _currentLayout !== LAYOUTS.STAGE_FILMSTRIP_VIEW));

    let _thumbnailSize, remoteFilmstripHeight, remoteFilmstripWidth;

    switch (_currentLayout) {
    case LAYOUTS.TILE_VIEW:
        _hasScroll = Boolean(tileViewHasScroll);
        _thumbnailSize = tileViewThumbnailSize;
        remoteFilmstripHeight = Number(filmstripHeight) - (
            collapseTileView && filmstripPadding > 0 ? filmstripPadding : 0);
        remoteFilmstripWidth = filmstripWidth;
        break;
    case LAYOUTS.VERTICAL_FILMSTRIP_VIEW:
    case LAYOUTS.STAGE_FILMSTRIP_VIEW: {
        const {
            remote,
            remoteVideosContainer,
            gridView,
            hasScroll
        } = state['features/filmstrip'].verticalViewDimensions;

        _hasScroll = Boolean(hasScroll);
        remoteFilmstripHeight = Number(remoteVideosContainer?.height) - (!_verticalViewGrid && shouldReduceHeight
            ? TOOLBAR_HEIGHT : 0);
        remoteFilmstripWidth = remoteVideosContainer?.width;

        if (_verticalViewGrid) {
            gridDimensions = gridView?.gridDimensions ?? {
                columns: undefined,
                rows: undefined
            };
            _thumbnailSize = gridView?.thumbnailSize;
            _hasScroll = Boolean(gridView?.hasScroll);
        } else {
            _thumbnailSize = remote;
        }
        break;
    }
    case LAYOUTS.HORIZONTAL_FILMSTRIP_VIEW: {
        const { remote, remoteVideosContainer, hasScroll } = state['features/filmstrip'].horizontalViewDimensions;

        _hasScroll = Boolean(hasScroll);
        _thumbnailSize = remote;
        remoteFilmstripHeight = remoteVideosContainer?.height;
        remoteFilmstripWidth = remoteVideosContainer?.width;
        break;
    }
    }

    const tileViewActive = _currentLayout === LAYOUTS.TILE_VIEW;
    const largeVideoParticipantId = state['features/large-video'].participantId;
    let filteredRemoteParticipants = remoteParticipants;

    // фильтруем пользователя, который открыт в режиме спикера
    if (!tileViewActive) {
        filteredRemoteParticipants = remoteParticipants.filter(participant => participant !== largeVideoParticipantId);
    }

    return {
        _columns: gridDimensions.columns ?? 1,
        _filmstripHeight: remoteFilmstripHeight,
        _filmstripWidth: remoteFilmstripWidth,
        _hasScroll,
        _remoteParticipants: remoteParticipants,
        _filteredRemoteParticipants: filteredRemoteParticipants,
        _resizableFilmstrip,
        _rows: gridDimensions.rows ?? 1,
        _thumbnailWidth: _thumbnailSize?.width,
        _thumbnailHeight: _thumbnailSize?.height,
        _verticalViewGrid,
        _verticalViewBackground: Number(verticalFilmstripWidth.current)
            + FILMSTRIP_BREAKPOINT_OFFSET >= FILMSTRIP_BREAKPOINT,
        _largeVideoParticipantId: largeVideoParticipantId,
        _localParticipantId: localParticipantId,
    };
}

export default connect(_mapStateToProps)(MainFilmstripMobile);
