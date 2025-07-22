import React, { Component } from 'react';
import { connect } from 'react-redux';
import { shouldComponentUpdate } from 'react-window';

import { IReduxState } from '../../../app/types';
import { getLocalParticipant } from '../../../base/participants/functions';
import { getHideSelfView } from '../../../base/settings/functions.any';
import { LAYOUTS } from '../../../video-layout/constants';
import { getCurrentLayout } from '../../../video-layout/functions.web';
import { FILMSTRIP_TYPE, TILE_ASPECT_RATIO_MOBILE, TILE_HORIZONTAL_MARGIN } from '../../constants';
import { getActiveParticipantsIds, showGridInVerticalView } from '../../functions.web';

import ThumbnailMobile from './ThumbnailMobile';

/**
 * The type of the React {@code Component} props of {@link ThumbnailWrapperMobile}.
 */
interface IProps {

    /**
     * Whether or not to hide the self view.
     */
    _disableSelfView?: boolean;

    /**
     * The type of filmstrip this thumbnail is displayed in.
     */
    _filmstripType?: string;

    /**
     * The horizontal offset in px for the thumbnail. Used to center the thumbnails in the last row in tile view.
     */
    _horizontalOffset?: number;

    /**
     * Whether or not the thumbnail is a local screen share.
     */
    _isLocalScreenShare?: boolean;

    /**
     * The ID of the participant associated with the ThumbnailMobile.
     */
    _participantID?: string;

    /**
     * The width of the thumbnail. Used for expanding the width of the thumbnails on last row in case
     * there is empty space.
     */
    _thumbnailWidth?: number;

    /**
     * The index of the column in tile view.
     */
    columnIndex?: number;

    /**
     * The index of the ThumbnailWrapperMobile in stage view.
     */
    index?: number;

    /**
     * The index of the row in tile view.
     */
    rowIndex?: number;

    /**
     * The styles coming from react-window.
     */
    style: Object;
}

/**
 * A wrapper Component for the ThumbnailMobile that translates the react-window specific props
 * to the ThumbnailMobile Component's props.
 */
class ThumbnailWrapperMobile extends Component<IProps> {
    shouldComponentUpdate: (p: any, s: any) => boolean;

    /**
     * Creates new ThumbnailWrapperMobile instance.
     *
     * @param {IProps} props - The props of the component.
     */
    constructor(props: IProps) {
        super(props);

        this.shouldComponentUpdate = shouldComponentUpdate.bind(this);
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const {
            _disableSelfView,
            _filmstripType = FILMSTRIP_TYPE.MAIN,
            _isLocalScreenShare = false,
            _horizontalOffset = 0,
            _participantID,
            _thumbnailWidth,
            style
        } = this.props;

        if (typeof _participantID !== 'string') {
            return null;
        }

        if (_participantID === 'local') {
            return _disableSelfView ? null : (
                <ThumbnailMobile
                    filmstripType = {_filmstripType}
                    horizontalOffset = {_horizontalOffset}
                    key = 'local'
                    style = {style}
                    width = {_thumbnailWidth} />);
        }

        if (_isLocalScreenShare) {
            return _disableSelfView ? null : (
                <ThumbnailMobile
                    filmstripType = {_filmstripType}
                    horizontalOffset = {_horizontalOffset}
                    key = 'localScreenShare'
                    participantID = {_participantID}
                    style = {style}
                    width = {_thumbnailWidth} />);
        }

        return (
            <ThumbnailMobile
                filmstripType = {_filmstripType}
                horizontalOffset = {_horizontalOffset}
                key = {`remote_${_participantID}`}
                participantID = {_participantID}
                style = {style}
                width = {_thumbnailWidth} />
        );
    }
}

/**
 * Maps (parts of) the Redux state to the associated {@code ThumbnailWrapperMobile}'s props.
 *
 * @param {Object} state - The Redux state.
 * @param {Object} ownProps - The props passed to the component.
 * @private
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState, ownProps: {
    columnIndex: number;
    data: {
        filmstripType: string;
        startIndex: number,
        itemsPerPage: number,
        totalItems: number,
        dataRemoteParticipants?: string[]
    };
    index?: number;
    rowIndex: number;
}) {
    const _currentLayout = getCurrentLayout(state);
    const { remoteParticipants: remote } = state['features/filmstrip'];
    const activeParticipants = getActiveParticipantsIds(state);
    const disableSelfView = getHideSelfView(state);
    const _verticalViewGrid = showGridInVerticalView(state);
    const filmstripType = ownProps.data?.filmstripType;
    const stageFilmstrip = filmstripType === FILMSTRIP_TYPE.STAGE;
    const sortedActiveParticipants = activeParticipants.sort();
    const remoteParticipants = ownProps.data?.dataRemoteParticipants ?? (stageFilmstrip ? sortedActiveParticipants : remote);
    const remoteParticipantsLength = remoteParticipants.length;
    const localId = getLocalParticipant(state)?.id;
    const isNeedCustomParticipantID = ownProps.data?.startIndex !== undefined && ownProps.data?.startIndex >= 0;
    const adjustedIndex = ownProps.data?.startIndex + (ownProps.index ?? 0);
    let participantID = null;
    if (adjustedIndex < ownProps.data?.totalItems) {
        participantID = remoteParticipants[adjustedIndex];
    }

    if (_currentLayout === LAYOUTS.TILE_VIEW || _verticalViewGrid || stageFilmstrip) {
        const { columnIndex, rowIndex } = ownProps;
        const { tileViewDimensions, stageFilmstripDimensions, verticalViewDimensions } = state['features/filmstrip'];
        const { gridView } = verticalViewDimensions;
        let gridDimensions = tileViewDimensions?.gridDimensions,
            thumbnailSize = tileViewDimensions?.thumbnailSize;

        if (stageFilmstrip) {
            gridDimensions = stageFilmstripDimensions.gridDimensions;
            thumbnailSize = stageFilmstripDimensions.thumbnailSize;
        } else if (_verticalViewGrid) {
            gridDimensions = gridView?.gridDimensions;
            thumbnailSize = gridView?.thumbnailSize;
        }
        const { columns = 1, rows = 1 } = gridDimensions ?? {};
        const index = (rowIndex * columns) + columnIndex;
        let horizontalOffset, thumbnailWidth;
        const { iAmRecorder, disableTileEnlargement } = state['features/base/config'];
        const { localScreenShare } = state['features/base/participants'];
        const disableSelfView = getHideSelfView(state);
        const isFirstPage = ownProps.data?.startIndex === 0;
        const localParticipantsLength = (!disableSelfView ? 1 : 0) + (localScreenShare ? 1 : 0);
        const adjustedIndex = ownProps.data?.startIndex + (ownProps.rowIndex * columns) + ownProps.columnIndex;

        let participantID = null;

        if (adjustedIndex < ownProps.data?.totalItems) {
            if (!iAmRecorder && !disableSelfView && adjustedIndex === 0 && isFirstPage) {
                participantID = 'local';
            } else if (!iAmRecorder && localScreenShare && adjustedIndex === 1 && isFirstPage) {
                participantID = localScreenShare.id;
            } else {
                const remoteIndex = adjustedIndex - localParticipantsLength;
                participantID = remoteParticipants[remoteIndex];
            }
        }

        let participantsLength;

        if (stageFilmstrip) {
            // We use the length of activeParticipants in stage filmstrip which includes local participants.
            participantsLength = remoteParticipantsLength;
        } else {
            // We need to include the local screenshare participant in tile view.
            participantsLength = remoteParticipantsLength

                // Add local camera and screen share to total participant count when self view is not disabled.
                + (disableSelfView ? 0 : localParticipantsLength)

                // Removes iAmRecorder from the total participants count.
                - (iAmRecorder ? 1 : 0);
        }

        if (adjustedIndex > participantsLength - 1) {
            return {};
        }

        if (stageFilmstrip) {
            return {
                _disableSelfView: disableSelfView,
                _filmstripType: filmstripType,
                _participantID: isNeedCustomParticipantID && participantID !== null ? (participantID === localId ? 'local' : participantID) : (remoteParticipants[index] === localId ? 'local' : remoteParticipants[index]),
                _horizontalOffset: horizontalOffset,
                _thumbnailWidth: thumbnailWidth
            };
        }

        // When the thumbnails are reordered, local participant is inserted at index 0.
        const localIndex = disableSelfView ? remoteParticipantsLength : 0;

        // Local screen share is inserted at index 1 after the local camera.
        const localScreenShareIndex = disableSelfView ? remoteParticipantsLength : 1;
        const remoteIndex = !iAmRecorder && !disableSelfView
            ? adjustedIndex - localParticipantsLength
            : adjustedIndex;

        if (!iAmRecorder && adjustedIndex === localIndex) {

            return {
                _disableSelfView: disableSelfView,
                _filmstripType: filmstripType,
                _participantID: 'local',
                _horizontalOffset: horizontalOffset,
                _thumbnailWidth: thumbnailWidth
            };
        }

        if (!iAmRecorder && localScreenShare && adjustedIndex === localScreenShareIndex) {
            return {
                _disableSelfView: disableSelfView,
                _filmstripType: filmstripType,
                _isLocalScreenShare: true,
                _participantID: localScreenShare?.id,
                _horizontalOffset: horizontalOffset,
                _thumbnailWidth: thumbnailWidth
            };
        }

        return {
            _filmstripType: filmstripType,
            _participantID: isNeedCustomParticipantID && participantID !== null ? participantID : remoteParticipants[remoteIndex],
            _horizontalOffset: horizontalOffset,
            _thumbnailWidth: thumbnailWidth
        };
    }

    if (_currentLayout === LAYOUTS.STAGE_FILMSTRIP_VIEW && filmstripType === FILMSTRIP_TYPE.SCREENSHARE) {
        const { screenshareFilmstripParticipantId } = state['features/filmstrip'];
        const screenshares = state['features/video-layout'].remoteScreenShares;
        let id = screenshares.find(sId => sId === screenshareFilmstripParticipantId);

        if (!id && screenshares.length) {
            id = screenshares[screenshares.length - 1];
        }

        return {
            _filmstripType: filmstripType,
            _participantID: id
        };
    }

    const { index } = ownProps;

    if (typeof index !== 'number' || remoteParticipantsLength <= index) {
        return {};
    }

    return {
        _participantID: isNeedCustomParticipantID && participantID !== null ? participantID : remoteParticipants[index]
    };
}

export default connect(_mapStateToProps)(ThumbnailWrapperMobile);
