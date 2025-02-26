import clsx from 'clsx';
import { throttle, debounce } from 'lodash-es';
import React, { PureComponent } from 'react';
import { WithTranslation } from 'react-i18next';
import { connect } from 'react-redux';
import { FixedSizeGrid, FixedSizeList } from 'react-window';
import { withStyles } from 'tss-react/mui';

import { ACTION_SHORTCUT_TRIGGERED, createShortcutEvent, createToolbarEvent } from '../../../analytics/AnalyticsEvents';
import { sendAnalytics } from '../../../analytics/functions';
import { IReduxState, IStore } from '../../../app/types';
import { isMobileBrowser } from '../../../base/environment/utils';
import { translate } from '../../../base/i18n/functions';
import Icon from '../../../base/icons/components/Icon';
import { IconArrowDownCustom, IconArrowUpCustom } from '../../../base/icons/svg';
import { getHideSelfView } from '../../../base/settings/functions.any';
import { showToolbox } from '../../../toolbox/actions.web';
import { isButtonEnabled, isToolboxVisible } from '../../../toolbox/functions.web';
import { LAYOUTS } from '../../../video-layout/constants';
import { getCurrentLayout } from '../../../video-layout/functions.web';
import {
    setFilmstripVisible,
    setTopPanelVisible,
    setUserFilmstripHeight,
    setUserFilmstripWidth,
    setUserIsResizing,
    setVisibleRemoteParticipants
} from '../../actions';
import {
    ASPECT_RATIO_BREAKPOINT,
    DEFAULT_FILMSTRIP_WIDTH,
    FILMSTRIP_TYPE,
    HORIZONTAL_MAX_PARTICIPANT_COUNT_PER_PAGE,
    MIN_STAGE_VIEW_HEIGHT,
    MIN_STAGE_VIEW_WIDTH,
    TILE_HORIZONTAL_MARGIN,
    TILE_VERTICAL_MARGIN,
    TOP_FILMSTRIP_HEIGHT
} from '../../constants';
import { getVerticalViewMaxWidth, isStageFilmstripTopPanel, shouldRemoteVideosBeVisible } from '../../functions';
import { isFilmstripDisabled } from '../../functions.web';

import AudioTracksContainer from './AudioTracksContainer';
import ThumbnailWrapperMobile from './ThumbnailWrapperMobile';
import { styles } from './styles';
import { setFilmstripHovered } from "../../actions.any";
import ThumbnailMobile from "./ThumbnailMobile";

/**
 * The type of the React {@code Component} props of {@link FilmstripMobile}.
 */
interface IProps extends WithTranslation {

    /**
     * Additional CSS class names top add to the root.
     */
    _className: string;

    /**
     * The number of columns in tile view.
     */
    _columns: number;

    /**
     * The current layout of the filmstrip.
     */
    _currentLayout?: string;

    /**
     * Whether or not to hide the self view.
     */
    _disableSelfView: boolean;

    /**
     * Whether vertical/horizontal filmstrip is disabled through config.
     */
    _filmstripDisabled: boolean;

    /**
     * The height of the filmstrip.
     */
    _filmstripHeight: number;

    /**
     * The width of the filmstrip.
     */
    _filmstripWidth: number;

    /**
     * Whether or not we have scroll on the filmstrip.
     */
    _hasScroll: boolean;

    /**
     * Whether this is a recorder or not.
     */
    _iAmRecorder: boolean;

    /**
     * Whether the filmstrip button is enabled.
     */
    _isFilmstripButtonEnabled: boolean;

    /**
     * Whether or not the toolbox is displayed.
     */
    _isToolboxVisible: Boolean;

    /**
     * Whether or not the current layout is vertical filmstrip.
     */
    _isVerticalFilmstrip: boolean;

    /**
     * The local screen share participant. This prop is behind the sourceNameSignaling feature flag.
     */
    _localScreenShareId: string | undefined;

    /**
     * Whether or not the filmstrip videos should currently be displayed.
     */
    _mainFilmstripVisible: boolean;

    /**
     * The maximum width of the vertical filmstrip.
     */
    _maxFilmstripWidth: number;

    /**
     * The maximum height of the top panel.
     */
    _maxTopPanelHeight: number;

    /**
     * The participants in the call.
     */
    _remoteParticipants: Array<string>;

    /**
     * The length of the remote participants array.
     */
    _remoteParticipantsLength: number;

    /**
     * The filtered participants in the call.
     */
    _filteredRemoteParticipants: Array<string>;

    /**
     * The length of the filtered remote participants array.
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
    _thumbnailHeight: number;

    /**
     * The width of the thumbnail.
     */
    _thumbnailWidth: number;

    /**
     * Whether or not the filmstrip is top panel.
     */
    _topPanelFilmstrip: boolean;

    /**
     * The height of the top panel (user resized).
     */
    _topPanelHeight?: number | null;

    /**
     * The max height of the top panel.
     */
    _topPanelMaxHeight: number;

    /**
     * Whether or not the top panel is visible.
     */
    _topPanelVisible: boolean;

    /**
     * The width of the vertical filmstrip (user resized).
     */
    _verticalFilmstripWidth?: number | null;

    /**
     * Whether or not the vertical filmstrip should have a background color.
     */
    _verticalViewBackground: boolean;

    /**
     * Whether or not the vertical filmstrip should be displayed as grid.
     */
    _verticalViewGrid: boolean;

    /**
     * The max width of the vertical filmstrip.
     */
    _verticalViewMaxWidth: number;

    /**
     * Additional CSS class names to add to the container of all the thumbnails.
     */
    _videosClassName: string;

    /**
     * True if is in pip mode.
     */
    _isInPipMode: boolean;

    _largeVideoParticipantId: string;
    _localParticipantId: string;

    /**
     * An object containing the CSS classes.
     */
    classes?: Partial<Record<keyof ReturnType<typeof styles>, string>>;

    /**
     * The redux {@code dispatch} function.
     */
    dispatch: IStore['dispatch'];

    /**
     * The type of filmstrip to be displayed.
     */
    filmstripType: string;
}

interface IState {

    /**
     * Initial top panel height on drag handle mouse down.
     */
    dragFilmstripHeight?: number;

    /**
     * Initial filmstrip width on drag handle mouse down.
     */
    dragFilmstripWidth?: number | null;

    startIndex: number;

    totalPages: number;

    currentPageIndex: number;

    itemsPerPage: number;

    startIndexTileView: number;

    itemsPerPageTileView: number;

    totalPagesNumberTileView: number;

    currentPageNumberTileView: number;

    /**
     * Whether or not the mouse is pressed.
     */
    isMouseDown: boolean;

    /**
     * Initial mouse position on drag handle mouse down.
     */
    mousePosition?: number | null;
}

/**
 * Implements a React {@link Component} which represents the filmstrip on
 * Web/React.
 *
 * @augments Component
 */
class FilmstripMobile extends PureComponent <IProps, IState> {

    _throttledResize: Function;
    _debouncedCountTileViewPages: Function;
    _debouncedOnRecountStageFilmstripTotalPages: Function;

    /**
     * Initializes a new {@code FilmstripMobile} instance.
     *
     * @param {Object} props - The read-only properties with which the new
     * instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        this.state = {
            isMouseDown: false,
            mousePosition: null,
            dragFilmstripWidth: null,
            startIndex: 0,
            totalPages: 1,
            currentPageIndex: 0,
            itemsPerPage: HORIZONTAL_MAX_PARTICIPANT_COUNT_PER_PAGE,
            startIndexTileView: 0,
            itemsPerPageTileView: 4,
            totalPagesNumberTileView: 1,
            currentPageNumberTileView: 1
        };

        // Bind event handlers so they are only bound once for every instance.
        this._onShortcutToggleFilmstrip = this._onShortcutToggleFilmstrip.bind(this);
        this._onToolbarToggleFilmstrip = this._onToolbarToggleFilmstrip.bind(this);
        this._onTabIn = this._onTabIn.bind(this);
        this._gridItemKey = this._gridItemKey.bind(this);
        this._listItemKey = this._listItemKey.bind(this);
        this._onGridItemsRendered = this._onGridItemsRendered.bind(this);
        this._onListItemsRendered = this._onListItemsRendered.bind(this);
        this._onToggleButtonTouch = this._onToggleButtonTouch.bind(this);
        this._onDragHandleMouseDown = this._onDragHandleMouseDown.bind(this);
        this._onDragMouseUp = this._onDragMouseUp.bind(this);
        this._onFilmstripResize = this._onFilmstripResize.bind(this);
        this._onRecountStageFilmstripTotalPages = this._onRecountStageFilmstripTotalPages.bind(this);
        this._onNextPage = this._onNextPage.bind(this);
        this._onPrevPage = this._onPrevPage.bind(this);
        this._onNextPageTileView = this._onNextPageTileView.bind(this);
        this._onPrevPageTileView = this._onPrevPageTileView.bind(this);
        this._countTileViewPages = this._countTileViewPages.bind(this);
        this._debouncedCountTileViewPages = debounce(this._countTileViewPages, 200);
        this._debouncedOnRecountStageFilmstripTotalPages = debounce(this._onRecountStageFilmstripTotalPages, 200);

        this._throttledResize = throttle(
            this._onFilmstripResize,
            50,
            {
                leading: true,
                trailing: false
            });
    }

    /**
     * Implements React's {@link Component#componentDidMount}.
     *
     * @inheritdoc
     */
    componentDidMount() {
        document.addEventListener('mouseup', this._onDragMouseUp);

        // @ts-ignore
        document.addEventListener('mousemove', this._throttledResize);
    }

    /**
     * Implements React's {@link Component#componentDidUpdate}.
     *
     * @inheritdoc
     */
    componentWillUnmount() {
        document.removeEventListener('mouseup', this._onDragMouseUp);

        // @ts-ignore
        document.removeEventListener('mousemove', this._throttledResize);
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const filmstripStyle: any = {};
        const {
            _currentLayout,
            _disableSelfView,
            _filmstripDisabled,
            _localScreenShareId,
            _mainFilmstripVisible,
            _resizableFilmstrip,
            _topPanelFilmstrip,
            _topPanelMaxHeight,
            _topPanelVisible,
            _verticalViewBackground,
            _verticalViewGrid,
            _verticalViewMaxWidth,
            _remoteParticipantsLength,
            filmstripType,
            t
        } = this.props;
        const classes = withStyles.getClasses(this.props);
        const { isMouseDown } = this.state;
        const tileViewActive = _currentLayout === LAYOUTS.TILE_VIEW;

        if (_currentLayout === LAYOUTS.STAGE_FILMSTRIP_VIEW && filmstripType === FILMSTRIP_TYPE.STAGE) {
            if (_topPanelFilmstrip) {
                filmstripStyle.maxHeight = `${_topPanelMaxHeight}px`;
                filmstripStyle.zIndex = 1;

                if (!_topPanelVisible) {
                    filmstripStyle.top = `-${_topPanelMaxHeight}px`;
                }
            }
            if (_mainFilmstripVisible) {
                filmstripStyle.maxWidth = `calc(100% - ${_verticalViewMaxWidth}px)`;
            }
        } else if (_currentLayout === LAYOUTS.STAGE_FILMSTRIP_VIEW && filmstripType === FILMSTRIP_TYPE.SCREENSHARE) {
            if (_mainFilmstripVisible) {
                filmstripStyle.maxWidth = `calc(100% - ${_verticalViewMaxWidth}px)`;
            }
            if (_topPanelVisible) {
                filmstripStyle.maxHeight = `calc(100% - ${_topPanelMaxHeight}px)`;
            }
            filmstripStyle.bottom = 0;
            filmstripStyle.top = 'auto';
        } else if (_currentLayout === LAYOUTS.VERTICAL_FILMSTRIP_VIEW
            || (_currentLayout === LAYOUTS.STAGE_FILMSTRIP_VIEW && filmstripType === FILMSTRIP_TYPE.MAIN)) {
            filmstripStyle.maxWidth = _verticalViewMaxWidth;
            if (!_mainFilmstripVisible) {
                filmstripStyle.right = `-${filmstripStyle.maxWidth}px`;
            }
        }

        if (_currentLayout === LAYOUTS.VERTICAL_FILMSTRIP_VIEW || _currentLayout === LAYOUTS.HORIZONTAL_FILMSTRIP_VIEW) {
            return <AudioTracksContainer />;
        }

        let toolbar = null;

        if (!this.props._iAmRecorder && this.props._isFilmstripButtonEnabled
            && _currentLayout !== LAYOUTS.TILE_VIEW
            && ((filmstripType === FILMSTRIP_TYPE.MAIN && !_filmstripDisabled)
                || (filmstripType === FILMSTRIP_TYPE.STAGE && _topPanelFilmstrip))) {
            toolbar = this._renderToggleButton();
        }

        const filmstrip = (<>
            <div
                className = {clsx(this.props._videosClassName,
                    !tileViewActive && (filmstripType === FILMSTRIP_TYPE.MAIN
                        || (filmstripType === FILMSTRIP_TYPE.STAGE && _topPanelFilmstrip))
                    && !_resizableFilmstrip && 'filmstrip-hover',
                    _verticalViewGrid && 'vertical-view-grid')}
                id = 'remoteVideos'>
                {
                    this._renderRemoteParticipants()
                }
            </div>
        </>);

        return (
            <div
                className = {clsx('filmstrip',
                    this.props._className,
                    classes.filmstrip,
                    _verticalViewGrid && 'no-vertical-padding',
                    _verticalViewBackground && classes.filmstripBackground,
                    (_remoteParticipantsLength < 1 && _currentLayout !== LAYOUTS.TILE_VIEW) && 'display-none')}
                style = {filmstripStyle}
                onMouseEnter = {() => this.props.dispatch(setFilmstripHovered(true))}
                onMouseLeave = {() => this.props.dispatch(setFilmstripHovered(false))}>
                {toolbar}
                {_resizableFilmstrip
                    ? <div
                        className = {clsx('resizable-filmstrip', classes.resizableFilmstripContainer,
                            _topPanelFilmstrip && 'top-panel-filmstrip')}>
                        <div style = {{ width: '9px' }}></div>
                        {filmstrip}
                    </div>
                    : filmstrip
                }
                <AudioTracksContainer />
            </div>
        );
    }

    /**
     * Handles mouse down on the drag handle.
     *
     * @param {MouseEvent} e - The mouse down event.
     * @returns {void}
     */
    _onDragHandleMouseDown(e: React.MouseEvent) {
        const { _topPanelFilmstrip, _topPanelHeight, _verticalFilmstripWidth } = this.props;

        this.setState({
            isMouseDown: true,
            mousePosition: _topPanelFilmstrip ? e.clientY : e.clientX,
            dragFilmstripWidth: _verticalFilmstripWidth || DEFAULT_FILMSTRIP_WIDTH,
            dragFilmstripHeight: _topPanelHeight || TOP_FILMSTRIP_HEIGHT
        });
        this.props.dispatch(setUserIsResizing(true));
    }

    /**
     * Drag handle mouse up handler.
     *
     * @returns {void}
     */
    _onDragMouseUp() {
        if (this.state.isMouseDown) {
            this.setState({
                isMouseDown: false
            });
            this.props.dispatch(setUserIsResizing(false));
        }
    }

    /**
     * Handles drag handle mouse move.
     *
     * @param {MouseEvent} e - The mousemove event.
     * @returns {void}
     */
    _onFilmstripResize(e: React.MouseEvent) {
        if (this.state.isMouseDown) {
            const {
                dispatch,
                _verticalFilmstripWidth,
                _maxFilmstripWidth,
                _topPanelHeight,
                _maxTopPanelHeight,
                _topPanelFilmstrip
            } = this.props;
            const { dragFilmstripWidth, dragFilmstripHeight, mousePosition } = this.state;

            if (_topPanelFilmstrip) {
                const diff = e.clientY - (mousePosition ?? 0);
                const height = Math.max(
                    Math.min((dragFilmstripHeight ?? 0) + diff, _maxTopPanelHeight),
                    TOP_FILMSTRIP_HEIGHT
                );

                if (height !== _topPanelHeight) {
                    dispatch(setUserFilmstripHeight(height));
                }
            } else {
                const diff = (mousePosition ?? 0) - e.clientX;
                const width = Math.max(
                    Math.min((dragFilmstripWidth ?? 0) + diff, _maxFilmstripWidth),
                    DEFAULT_FILMSTRIP_WIDTH
                );

                if (width !== _verticalFilmstripWidth) {
                    dispatch(setUserFilmstripWidth(width));
                }
            }
        }
    }

    /**
     * Calculates the start and stop indices based on whether the thumbnails need to be reordered in the filmstrip.
     *
     * @param startIndex - The start index.
     * @param itemsPerPage
     * @returns {Object}
     */
    _calculateVisibleRemoteParticipantsIndices(startIndex: number, itemsPerPage: number) {

        let tempStartPageIndex = startIndex > 0 ? (startIndex - 2) : startIndex;
        if (tempStartPageIndex < 0) {
            tempStartPageIndex = 0;
        }

        return {
            startPageIndex: tempStartPageIndex,
            stopPageIndex: startIndex + itemsPerPage
        };
    }

    /**
     * Toggle the toolbar visibility when tabbing into it.
     *
     * @returns {void}
     */
    _onTabIn() {
        if (!this.props._isToolboxVisible && this.props._mainFilmstripVisible) {
            this.props.dispatch(showToolbox());
        }
    }

    /**
     * The key to be used for every ThumbnailWrapperMobile element in stage view.
     *
     * @param {number} index - The index of the ThumbnailWrapperMobile instance.
     * @returns {string} - The key.
     */
    _listItemKey(index: number) {
        const {
            _disableSelfView,
            _columns,
            _iAmRecorder,
            _filteredRemoteParticipants,
            _filteredRemoteParticipantsLength
        } = this.props;

        // When the thumbnails are reordered, local participant is inserted at index 0.
        const localIndex = _disableSelfView ? _filteredRemoteParticipantsLength : 0;
        const remoteIndex = !_iAmRecorder && !_disableSelfView ? index - 1 : index;

        if (typeof index !== 'number' || _filteredRemoteParticipantsLength <= index) {
            return `empty-${index}`;
        }

        if (!_iAmRecorder && index === localIndex) {
            return 'local';
        }

        return _filteredRemoteParticipants[remoteIndex];
    }

    /**
     * The key to be used for every ThumbnailWrapperMobile element in tile views.
     *
     * @param {Object} data - An object with the indexes identifying the ThumbnailWrapperMobile instance.
     * @returns {string} - The key.
     */
    _gridItemKey({ columnIndex, rowIndex }: { columnIndex: number; rowIndex: number; }): string {
        const {
            _disableSelfView,
            _columns,
            _iAmRecorder,
            _filteredRemoteParticipants,
            _filteredRemoteParticipantsLength
        } = this.props;
        const index = (rowIndex * _columns) + columnIndex;

        // When the thumbnails are reordered, local participant is inserted at index 0.
        const localIndex = _disableSelfView ? _filteredRemoteParticipantsLength : 0;
        const remoteIndex = !_iAmRecorder && !_disableSelfView ? index - 1 : index;

        if (index > _filteredRemoteParticipantsLength - (_iAmRecorder ? 1 : 0)) {
            return `empty-${index}`;
        }

        if (!_iAmRecorder && index === localIndex) {
            return 'local';
        }

        return _filteredRemoteParticipants[remoteIndex];
    }

    /**
     * Handles items rendered changes in stage view.
     *
     * @param {Object} data - Information about the rendered items.
     * @returns {void}
     */
    _onListItemsRendered({ visibleStartIndex, visibleStopIndex }: {
        visibleStartIndex: number; visibleStopIndex: number;
    }) {
        const { dispatch } = this.props;
        const { startIndex, itemsPerPage } = this.state;

        const {
            startPageIndex,
            stopPageIndex
        } = this._calculateVisibleRemoteParticipantsIndices(startIndex, itemsPerPage);
        dispatch(setVisibleRemoteParticipants(startPageIndex, stopPageIndex));
    }

    /**
     * Handles items rendered changes in tile view.
     *
     * @param {Object} data - Information about the rendered items.
     * @returns {void}
     */
    _onGridItemsRendered({
        visibleColumnStartIndex,
        visibleColumnStopIndex,
        visibleRowStartIndex,
        visibleRowStopIndex
    }: {
        visibleColumnStartIndex: number;
        visibleColumnStopIndex: number;
        visibleRowStartIndex: number;
        visibleRowStopIndex: number;
    }) {
        const { dispatch } = this.props;
        const { startIndexTileView, itemsPerPageTileView } = this.state;

        const {
            startPageIndex,
            stopPageIndex
        } = this._calculateVisibleRemoteParticipantsIndices(startIndexTileView, itemsPerPageTileView);
        dispatch(setVisibleRemoteParticipants(startPageIndex, stopPageIndex));
    }

    _computePageStartIndices(totalItems: number, itemsPerPage: number) {
        const indices: number[] = [];

        // в разговоре 1х1 такое может быть, не падаем на fullPages = infinity
        if (itemsPerPage < 1) {
            return indices;
        }

        const fullPages = Math.floor(totalItems / itemsPerPage);
        const remainder = totalItems % itemsPerPage;

        // добавляем startIndex для полных страниц
        for (let i = 0; i < fullPages; i++) {
            indices.push(i * itemsPerPage);
        }

        // добавляем startIndex для последней страницы, если есть остаток
        if (remainder !== 0 && totalItems > itemsPerPage) {
            const lastIndex = totalItems - itemsPerPage;
            if (!indices.includes(lastIndex)) {
                indices.push(lastIndex);
            }
        }

        return indices;
    }

    _onRecountStageFilmstripTotalPages() {
        const { _filteredRemoteParticipantsLength: totalItems, dispatch, _currentLayout, filmstripType } = this.props;
        const { itemsPerPage, startIndex, totalPages, currentPageIndex } = this.state;

        if (_currentLayout !== LAYOUTS.TILE_VIEW || filmstripType === FILMSTRIP_TYPE.MAIN) {
            return;
        }

        // вычисляем допустимые startIndex для страниц
        const pageStartIndices = this._computePageStartIndices(totalItems, itemsPerPage);

        const pageIndex = currentPageIndex >= pageStartIndices.length
            ? pageStartIndices.length - 1
            : currentPageIndex;
        const newStartIndex = pageStartIndices[pageIndex];

        // пересчитываем startIndex, если количество страниц уменьшилось
        // например когда на последней странице всего 1 участник
        // и его закрепили на место самого себя
        if ((pageStartIndices.length != totalPages || startIndex != newStartIndex) && totalPages > 0 && pageStartIndices.length > 0) {
            this.setState({
                startIndex: newStartIndex,
                totalPages: pageStartIndices.length,
                currentPageIndex: pageIndex
            });

            const {
                startPageIndex,
                stopPageIndex
            } = this._calculateVisibleRemoteParticipantsIndices(newStartIndex, itemsPerPage);
            dispatch(setVisibleRemoteParticipants(startPageIndex, stopPageIndex));
        }
    }

    _onPrevPage() {
        const { _filteredRemoteParticipantsLength: totalItems, dispatch } = this.props;
        const { itemsPerPage, startIndex } = this.state;

        // вычисляем допустимые startIndex для страниц
        const pageStartIndices = this._computePageStartIndices(totalItems, itemsPerPage);

        // находим текущий индекс страницы
        const newCurrentPageIndex = pageStartIndices.indexOf(startIndex) === -1 ? ((pageStartIndices.length - 1) < 0 ? 0 : (pageStartIndices.length - 1)) : pageStartIndices.indexOf(startIndex);

        if (newCurrentPageIndex > 0) {
            const newPageIndex = newCurrentPageIndex - 1;
            const newStartIndex = pageStartIndices[newPageIndex];
            this.setState({
                startIndex: newStartIndex, totalPages: pageStartIndices.length,
                currentPageIndex: newPageIndex
            });

            const {
                startPageIndex,
                stopPageIndex
            } = this._calculateVisibleRemoteParticipantsIndices(newStartIndex, itemsPerPage);
            dispatch(setVisibleRemoteParticipants(startPageIndex, stopPageIndex));
        }
    }

    _onNextPage() {
        const { _filteredRemoteParticipantsLength: totalItems, dispatch } = this.props;
        const { itemsPerPage, startIndex } = this.state;

        // вычисляем допустимые startIndex для страниц
        const pageStartIndices = this._computePageStartIndices(totalItems, itemsPerPage);

        // находим текущий индекс страницы
        const newCurrentPageIndex = pageStartIndices.indexOf(startIndex) === -1 ? ((pageStartIndices.length - 1) < 0 ? 0 : (pageStartIndices.length - 1)) : pageStartIndices.indexOf(startIndex);

        if (newCurrentPageIndex < pageStartIndices.length - 1) {
            const newPageIndex = newCurrentPageIndex + 1;
            const newStartIndex = pageStartIndices[newPageIndex];
            this.setState({
                startIndex: newStartIndex,
                totalPages: pageStartIndices.length,
                currentPageIndex: newPageIndex
            });

            const {
                startPageIndex,
                stopPageIndex
            } = this._calculateVisibleRemoteParticipantsIndices(newStartIndex, itemsPerPage);
            dispatch(setVisibleRemoteParticipants(startPageIndex, stopPageIndex));
        }
    }

    _countTileViewPages() {
        const { itemsPerPageTileView, startIndexTileView } = this.state;
        const { _disableSelfView, _localScreenShareId, dispatch } = this.props;
        const localParticipantsLength = (!_disableSelfView ? 1 : 0) + (_localScreenShareId ? 1 : 0);
        const totalItems = this.props._filteredRemoteParticipantsLength + localParticipantsLength;

        // вычисляем допустимые startIndex для страниц
        const pageStartIndices = this._computePageStartIndices(totalItems, itemsPerPageTileView);

        // общее количество страниц равно длине массива startIndex
        const totalPagesNumberTileView = pageStartIndices.length || 1;

        // текущий номер страницы определяется позицией текущего startIndex в массиве
        const currentPageIndex = pageStartIndices.indexOf(startIndexTileView) === -1 ? ((pageStartIndices.length - 1) < 0 ? 0 : (pageStartIndices.length - 1)) : pageStartIndices.indexOf(startIndexTileView);
        const currentPageNumberTileView = currentPageIndex + 1; // добавляем 1, чтобы получить номер страницы, начиная с 1
        const newStartIndex = pageStartIndices[currentPageIndex] === undefined ? 0 : pageStartIndices[currentPageIndex];

        if (newStartIndex !== startIndexTileView) {
            const {
                startPageIndex: calculatedStartPageIndex,
                stopPageIndex: calculatedStopPageIndex
            } = this._calculateVisibleRemoteParticipantsIndices(newStartIndex, itemsPerPageTileView);
            dispatch(setVisibleRemoteParticipants(calculatedStartPageIndex, calculatedStopPageIndex));
        }

        this.setState({
            startIndexTileView: newStartIndex,
            totalPagesNumberTileView: totalPagesNumberTileView,
            currentPageNumberTileView: currentPageNumberTileView
        });
    }

    _onPrevPageTileView() {
        const { itemsPerPageTileView, startIndexTileView } = this.state;
        const { _disableSelfView, _localScreenShareId, dispatch } = this.props;
        const localParticipantsLength = (!_disableSelfView ? 1 : 0) + (_localScreenShareId ? 1 : 0);
        const totalItems = this.props._filteredRemoteParticipantsLength + localParticipantsLength;

        // вычисляем допустимые startIndex для страниц
        const pageStartIndices = this._computePageStartIndices(totalItems, itemsPerPageTileView);

        // общее количество страниц равно длине массива startIndex
        const totalPagesNumberTileView = pageStartIndices.length || 1;

        // находим текущий индекс страницы и текущий номер страницы
        const currentPageIndex = pageStartIndices.indexOf(startIndexTileView) === -1 ? ((pageStartIndices.length - 1) < 0 ? 0 : (pageStartIndices.length - 1)) : pageStartIndices.indexOf(startIndexTileView);
        const currentPageNumberTileView = currentPageIndex + 1; // добавляем 1, чтобы получить номер страницы, начиная с 1

        if (currentPageIndex > 0) {
            const newStartIndex = pageStartIndices[currentPageIndex - 1];
            this.setState({
                startIndexTileView: newStartIndex,
                totalPagesNumberTileView: totalPagesNumberTileView,
                currentPageNumberTileView: currentPageNumberTileView
            });

            const {
                startPageIndex,
                stopPageIndex
            } = this._calculateVisibleRemoteParticipantsIndices(newStartIndex, itemsPerPageTileView);
            dispatch(setVisibleRemoteParticipants(startPageIndex, stopPageIndex));
        }
    }

    _onNextPageTileView() {
        const { itemsPerPageTileView, startIndexTileView } = this.state;
        const { _disableSelfView, _localScreenShareId, dispatch } = this.props;
        const localParticipantsLength = (!_disableSelfView ? 1 : 0) + (_localScreenShareId ? 1 : 0);
        const totalItems = this.props._filteredRemoteParticipantsLength + localParticipantsLength;

        // вычисляем допустимые startIndex для страниц
        const pageStartIndices = this._computePageStartIndices(totalItems, itemsPerPageTileView);

        // общее количество страниц равно длине массива startIndex
        const totalPagesNumberTileView = pageStartIndices.length || 1;

        // находим текущий индекс страницы и текущий номер страницы
        const currentPageIndex = pageStartIndices.indexOf(startIndexTileView) === -1 ? ((pageStartIndices.length - 1) < 0 ? 0 : (pageStartIndices.length - 1)) : pageStartIndices.indexOf(startIndexTileView);
        const currentPageNumberTileView = currentPageIndex + 1; // добавляем 1, чтобы получить номер страницы, начиная с 1

        if (currentPageIndex < pageStartIndices.length - 1) {
            const newStartIndex = pageStartIndices[currentPageIndex + 1];
            this.setState({
                startIndexTileView: newStartIndex,
                totalPagesNumberTileView: totalPagesNumberTileView,
                currentPageNumberTileView: currentPageNumberTileView
            });

            const {
                startPageIndex,
                stopPageIndex
            } = this._calculateVisibleRemoteParticipantsIndices(newStartIndex, itemsPerPageTileView);
            dispatch(setVisibleRemoteParticipants(startPageIndex, stopPageIndex));
        }
    }

    /**
     * Renders the thumbnails for remote participants.
     *
     * @returns {ReactElement}
     */
    _renderRemoteParticipants() {
        const {
            _columns,
            _rows,
            _currentLayout,
            _filmstripHeight,
            _filmstripWidth,
            _hasScroll,
            _isVerticalFilmstrip,
            _filteredRemoteParticipantsLength,
            _thumbnailHeight,
            _thumbnailWidth,
            _verticalViewGrid,
            filmstripType,
            _disableSelfView,
            _localScreenShareId,
            _filteredRemoteParticipants,
            _largeVideoParticipantId,
            _localParticipantId
        } = this.props;

        const classes = withStyles.getClasses(this.props);
        const isLocalParticipantVisible = !_disableSelfView && !_verticalViewGrid;
        const isLocalScreenshareParticipantVisible = _localScreenShareId && !_disableSelfView && !_verticalViewGrid;
        const tileViewActive = _currentLayout === LAYOUTS.TILE_VIEW;

        if (!_thumbnailWidth || isNaN(_thumbnailWidth) || !_thumbnailHeight
            || isNaN(_thumbnailHeight) || !_filmstripHeight || isNaN(_filmstripHeight) || !_filmstripWidth
            || isNaN(_filmstripWidth)) {

            if (_currentLayout === LAYOUTS.HORIZONTAL_FILMSTRIP_VIEW) {
                return (
                    <>
                        {isLocalParticipantVisible && (
                            <div
                                className = 'filmstrip__videos'
                                id = 'filmstripLocalVideo'
                                style = {{
                                    display: _largeVideoParticipantId === _localParticipantId ? 'none' : 'block',
                                    marginRight: isLocalScreenshareParticipantVisible || _filteredRemoteParticipantsLength > 0 ? '4px' : '0px',
                                }}>
                                {
                                    !tileViewActive && filmstripType === FILMSTRIP_TYPE.MAIN
                                    && <div id = 'filmstripLocalVideoThumbnail'>
                                        <ThumbnailMobile
                                            filmstripType = {FILMSTRIP_TYPE.MAIN}
                                            key = 'local' />
                                    </div>
                                }
                            </div>
                        )}
                        {isLocalScreenshareParticipantVisible && (
                            <div
                                className = 'filmstrip__videos'
                                id = 'filmstripLocalScreenShare'
                                style = {{
                                    marginRight: _filteredRemoteParticipantsLength > 0 ? '4px' : '0px',
                                }}>
                                <div id = 'filmstripLocalScreenShareThumbnail'>
                                    {
                                        !tileViewActive && filmstripType === FILMSTRIP_TYPE.MAIN && <ThumbnailMobile
                                            key = 'localScreenShare'
                                            participantID = {_localScreenShareId} />
                                    }
                                </div>
                            </div>
                        )}
                    </>
                );
            }

            return null;
        }

        const localParticipantsLength = (!_disableSelfView ? 1 : 0) + (_localScreenShareId ? 1 : 0);
        const totalItemsTileView = _filteredRemoteParticipantsLength + localParticipantsLength;
        const isPageButtonsVisible = totalItemsTileView > this.state.itemsPerPageTileView;
        const isLeftButtonTileViewEnabled = this.state.startIndexTileView > 0 && totalItemsTileView > this.state.itemsPerPageTileView;
        const isRightButtonTileViewEnabled = ((this.state.startIndexTileView + this.state.itemsPerPageTileView) < totalItemsTileView) && totalItemsTileView > this.state.itemsPerPageTileView;
        const columns = _columns;
        const rows = _rows > 2 ? 2 : _rows;

        if (_currentLayout === LAYOUTS.TILE_VIEW || _verticalViewGrid || filmstripType !== FILMSTRIP_TYPE.MAIN) {

            this._debouncedCountTileViewPages();

            return (
                <div style = {{
                    marginTop: '68px', // tile-view panel height(60px) + margin(8px)
                }}>
                    <FixedSizeGrid
                        className = 'filmstrip__videos remote-videos'
                        columnCount = {columns}
                        columnWidth = {_thumbnailWidth + TILE_HORIZONTAL_MARGIN}
                        height = {_filmstripHeight}
                        initialScrollLeft = {0}
                        initialScrollTop = {0}
                        itemData = {{
                            filmstripType: filmstripType,
                            startIndex: this.state.startIndexTileView,
                            itemsPerPage: this.state.itemsPerPageTileView,
                            totalItems: totalItemsTileView,
                        }}
                        itemKey = {this._gridItemKey}
                        onItemsRendered = {this._onGridItemsRendered}
                        overscanRowCount = {0}
                        rowCount = {rows}
                        rowHeight = {_thumbnailHeight + TILE_VERTICAL_MARGIN}
                        width = {_filmstripWidth}>
                        {
                            ThumbnailWrapperMobile
                        }
                    </FixedSizeGrid>
                    {isPageButtonsVisible && (
                        <div className = {classes.tileViewPaginationButtonsMobile}>
                            <div
                                className = {clsx(classes.tileViewPaginationButtonContainerMobile, isLeftButtonTileViewEnabled && classes.tileViewPaginationButtonContainerMobileEnabled)}
                                onTouchStart = {(e: React.TouchEvent) => {
                                    e.stopPropagation();
                                    this._onPrevPageTileView()
                                }}
                                style = {{
                                    cursor: `${isLeftButtonTileViewEnabled ? "pointer" : "default"}`,
                                }}>
                                <div
                                    className = {clsx(!isLeftButtonTileViewEnabled && classes.tileViewPaginationButtonDisabledMobile)}>
                                    <svg width = "24" height = "24" viewBox = "0 0 24 24" fill = "none"
                                         xmlns = "http://www.w3.org/2000/svg">
                                        <path fillRule = "evenodd" clipRule = "evenodd"
                                              d = "M14.9795 19.0001C14.6866 19.2929 14.2117 19.2929 13.9188 19.0001L7.44903 12.5305C7.15614 12.2376 7.15614 11.7628 7.44903 11.4699L13.9189 5.00008C14.2118 4.70718 14.6867 4.70718 14.9795 5.00008C15.2724 5.29297 15.2724 5.76784 14.9795 6.06074L9.04002 12.0002L14.9795 17.9394C15.2724 18.2323 15.2724 18.7072 14.9795 19.0001Z"
                                              fill = "white" />
                                    </svg>
                                </div>
                                <div
                                    className = {clsx(classes.tileViewPaginationButtonPageMobile, !isLeftButtonTileViewEnabled && classes.tileViewPaginationButtonDisabledMobile)}>
                                    {`${Math.max(this.state.currentPageNumberTileView - 1, 1)}/${this.state.totalPagesNumberTileView}`}
                                </div>
                            </div>
                            <div
                                className = {clsx(classes.tileViewPaginationButtonContainerMobile, isRightButtonTileViewEnabled && classes.tileViewPaginationButtonContainerMobileEnabled)}
                                onTouchStart = {(e: React.TouchEvent) => {
                                    e.stopPropagation();
                                    this._onNextPageTileView()
                                }}
                                style = {{
                                    cursor: `${isRightButtonTileViewEnabled ? "pointer" : "default"}`,
                                }}>
                                <div
                                    className = {clsx(!isRightButtonTileViewEnabled && classes.tileViewPaginationButtonDisabledMobile)}>
                                    <svg width = "24" height = "24" viewBox = "0 0 24 24" fill = "none"
                                         xmlns = "http://www.w3.org/2000/svg">
                                        <path fillRule = "evenodd" clipRule = "evenodd"
                                              d = "M9.02053 19.0001C9.31342 19.2929 9.7883 19.2929 10.0812 19.0001L16.551 12.5305C16.8439 12.2376 16.8439 11.7628 16.551 11.4699L10.0811 5.00008C9.78822 4.70718 9.31334 4.70718 9.02045 5.00008C8.72756 5.29297 8.72756 5.76784 9.02045 6.06074L14.96 12.0002L9.02053 17.9394C8.72763 18.2323 8.72763 18.7072 9.02053 19.0001Z"
                                              fill = "white" />
                                    </svg>
                                </div>
                                <div
                                    className = {clsx(classes.tileViewPaginationButtonPageMobile, !isRightButtonTileViewEnabled && classes.tileViewPaginationButtonDisabledMobile)}>
                                    {`${Math.min(this.state.currentPageNumberTileView + 1, this.state.totalPagesNumberTileView)}/${this.state.totalPagesNumberTileView}`}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            );
        }

        this._debouncedOnRecountStageFilmstripTotalPages();

        const totalItems = _filteredRemoteParticipantsLength;
        const itemsPerPageByWidth = Math.floor(_filmstripWidth / 164);
        const containerWidth = Math.min(itemsPerPageByWidth * 164, _filmstripWidth);

        this.setState({
            itemsPerPage: Math.min(totalItems, HORIZONTAL_MAX_PARTICIPANT_COUNT_PER_PAGE, itemsPerPageByWidth)
        });

        const showLeftButton = this.state.startIndex > 0 && totalItems > this.state.itemsPerPage;
        const showRightButton = ((this.state.startIndex + this.state.itemsPerPage) < totalItems) && totalItems > this.state.itemsPerPage;

        const props: any = {
            itemCount: this.state.itemsPerPage,
            className: `filmstrip__videos remote-videos invisible-scrollbar ${_filteredRemoteParticipantsLength < 1 ? 'display-none' : ''}`,
            height: _filmstripHeight,
            itemKey: this._listItemKey,
            itemSize: 0,
            itemData: {
                filmstripType: filmstripType,
                startIndex: this.state.startIndex,
                itemsPerPage: this.state.itemsPerPage,
                totalItems,
                dataRemoteParticipants: _filteredRemoteParticipants,
            },
            onItemsRendered: this._onListItemsRendered,
            overscanCount: HORIZONTAL_MAX_PARTICIPANT_COUNT_PER_PAGE,
            width: containerWidth,
            style: {
                willChange: 'auto'
            }
        };

        if (_currentLayout === LAYOUTS.HORIZONTAL_FILMSTRIP_VIEW) {
            const itemSize = _thumbnailWidth + TILE_HORIZONTAL_MARGIN;
            const isNotOverflowing = !_hasScroll;

            props.itemSize = itemSize;
            props.layout = 'horizontal';
            if (isNotOverflowing) {
                props.className += ' is-not-overflowing';
            }

        } else if (_isVerticalFilmstrip) {
            const itemSize = _thumbnailHeight + TILE_VERTICAL_MARGIN;
            const isNotOverflowing = !_hasScroll;

            if (isNotOverflowing) {
                props.className += ' is-not-overflowing';
            }

            props.itemSize = itemSize;
        }

        return (
            <>
                <div style = {{
                    display: "flex",
                    justifyContent: "center",
                    alignItems: "center",
                    minWidth: "32px",
                    marginRight: "8px",
                }}>
                    {showLeftButton &&
                        <div
                            className = {classes.filmstripPaginationButton}
                            onClick = {this._onPrevPage}>
                            <svg width = "24" height = "24" viewBox = "0 0 24 24" fill = "none"
                                 xmlns = "http://www.w3.org/2000/svg">
                                <g opacity = "0.5">
                                    <path fillRule = "evenodd" clipRule = "evenodd"
                                          d = "M14.9795 19.0001C14.6866 19.2929 14.2117 19.2929 13.9188 19.0001L7.44903 12.5305C7.15614 12.2376 7.15614 11.7628 7.44903 11.4699L13.9189 5.00008C14.2118 4.70718 14.6867 4.70718 14.9795 5.00008C15.2724 5.29297 15.2724 5.76784 14.9795 6.06074L9.04002 12.0002L14.9795 17.9394C15.2724 18.2323 15.2724 18.7072 14.9795 19.0001Z"
                                          fill = "white" />
                                </g>
                            </svg>
                        </div>
                    }
                </div>
                {isLocalParticipantVisible && (
                    <div
                        className = 'filmstrip__videos'
                        id = 'filmstripLocalVideo'
                        style = {{
                            display: _largeVideoParticipantId === _localParticipantId ? 'none' : 'block',
                        }}>
                        {
                            !tileViewActive && filmstripType === FILMSTRIP_TYPE.MAIN
                            && <div id = 'filmstripLocalVideoThumbnail'>
                                <ThumbnailMobile
                                    filmstripType = {FILMSTRIP_TYPE.MAIN}
                                    key = 'local' />
                            </div>
                        }
                    </div>
                )}
                {isLocalScreenshareParticipantVisible && (
                    <div
                        className = 'filmstrip__videos'
                        id = 'filmstripLocalScreenShare'>
                        <div id = 'filmstripLocalScreenShareThumbnail'>
                            {
                                !tileViewActive && filmstripType === FILMSTRIP_TYPE.MAIN && <ThumbnailMobile
                                    key = 'localScreenShare'
                                    participantID = {_localScreenShareId} />
                            }
                        </div>
                    </div>
                )}
                <FixedSizeList {...props}>
                    {
                        ThumbnailWrapperMobile
                    }
                </FixedSizeList>
                <div style = {{
                    display: "flex",
                    justifyContent: "center",
                    alignItems: "center",
                    minWidth: "32px",
                    marginLeft: "4px",
                }}>
                    {showRightButton &&
                        <div
                            className = {classes.filmstripPaginationButton}
                            onClick = {this._onNextPage}>
                            <svg width = "24" height = "24" viewBox = "0 0 24 24" fill = "none"
                                 xmlns = "http://www.w3.org/2000/svg">
                                <g opacity = "0.5">
                                    <path fillRule = "evenodd" clipRule = "evenodd"
                                          d = "M9.02053 19.0001C9.31342 19.2929 9.7883 19.2929 10.0812 19.0001L16.551 12.5305C16.8439 12.2376 16.8439 11.7628 16.551 11.4699L10.0811 5.00008C9.78822 4.70718 9.31334 4.70718 9.02045 5.00008C8.72756 5.29297 8.72756 5.76784 9.02045 6.06074L14.96 12.0002L9.02053 17.9394C8.72763 18.2323 8.72763 18.7072 9.02053 19.0001Z"
                                          fill = "white" />
                                </g>
                            </svg>
                        </div>
                    }
                </div>
            </>
        );
    }

    /**
     * Dispatches an action to change the visibility of the filmstrip.
     *
     * @private
     * @returns {void}
     */
    _doToggleFilmstrip() {
        const { dispatch, _mainFilmstripVisible, _topPanelFilmstrip, _topPanelVisible } = this.props;

        _topPanelFilmstrip
            ? dispatch(setTopPanelVisible(!_topPanelVisible))
            : dispatch(setFilmstripVisible(!_mainFilmstripVisible));
    }

    /**
     * Creates an analytics keyboard shortcut event and dispatches an action for
     * toggling filmstrip visibility.
     *
     * @private
     * @returns {void}
     */
    _onShortcutToggleFilmstrip() {
        sendAnalytics(createShortcutEvent(
            'toggle.filmstrip',
            ACTION_SHORTCUT_TRIGGERED,
            {
                enable: this.props._mainFilmstripVisible
            }));

        this._doToggleFilmstrip();
    }

    /**
     * Creates an analytics toolbar event and dispatches an action for opening
     * the speaker stats modal.
     *
     * @private
     * @returns {void}
     */
    _onToolbarToggleFilmstrip() {
        sendAnalytics(createToolbarEvent(
            'toggle.filmstrip.button',
            {
                enable: this.props._mainFilmstripVisible
            }));

        this._doToggleFilmstrip();
    }

    /**
     * Handler for touch start event of the 'toggle button'.
     *
     * @private
     * @param {Object} e - The synthetic event.
     * @returns {void}
     */
    _onToggleButtonTouch(e: React.TouchEvent) {
        // Don't propagate the touchStart event so the toolbar doesn't get toggled.
        e.stopPropagation();
        this._onToolbarToggleFilmstrip();
    }

    /**
     * Creates a React Element for changing the visibility of the filmstrip when
     * clicked.
     *
     * @private
     * @returns {ReactElement}
     */
    _renderToggleButton() {
        const {
            t,
            _isVerticalFilmstrip,
            _mainFilmstripVisible,
            _topPanelFilmstrip,
            _topPanelVisible,
            _isInPipMode,
            _currentLayout
        } = this.props;
        const classes = withStyles.getClasses(this.props);
        const icon = (_topPanelFilmstrip ? _topPanelVisible : _mainFilmstripVisible) ? IconArrowDownCustom : IconArrowUpCustom;
        const actions = isMobileBrowser()
            ? { onTouchStart: this._onToggleButtonTouch }
            : { onClick: this._onToolbarToggleFilmstrip };

        if (_isInPipMode) {
            return <></>;
        }

        if (_currentLayout === LAYOUTS.HORIZONTAL_FILMSTRIP_VIEW) {
            return <></>;
        }

        return (
            <div
                className = {clsx(classes.toggleFilmstripContainer,
                    _isVerticalFilmstrip && classes.toggleVerticalFilmstripContainer,
                    _topPanelFilmstrip && classes.toggleTopPanelContainer,
                    _topPanelFilmstrip && !_topPanelVisible && classes.toggleTopPanelContainerHidden,
                    'toggleFilmstripContainer')}>
                <button
                    aria-expanded = {this.props._mainFilmstripVisible}
                    aria-label = {t('toolbar.accessibilityLabel.toggleFilmstrip')}
                    className = {classes.toggleFilmstripButton}
                    id = 'toggleFilmstripButton'
                    onFocus = {this._onTabIn}
                    tabIndex = {0}
                    {...actions}>
                    <Icon
                        aria-label = {t('toolbar.accessibilityLabel.toggleFilmstrip')}
                        size = {24}
                        src = {icon} />
                </button>
            </div>
        );
    }
}

/**
 * Maps (parts of) the Redux state to the associated {@code FilmstripMobile}'s props.
 *
 * @param {Object} state - The Redux state.
 * @param {Object} ownProps - The own props of the component.
 * @private
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState, ownProps: any) {
    const {
        _hasScroll = false,
        filmstripType,
        _topPanelFilmstrip,
        _remoteParticipants,
        _filteredRemoteParticipants
    } = ownProps;
    const { toolbarButtons } = state['features/toolbox'];
    const { iAmRecorder } = state['features/base/config'];
    const {
        topPanelHeight,
        topPanelVisible,
        visible,
        width: verticalFilmstripWidth,
        hovered
    } = state['features/filmstrip'];
    const { localScreenShare } = state['features/base/participants'];
    const reduceHeight = state['features/toolbox'].visible && toolbarButtons?.length;
    const remoteVideosVisible = shouldRemoteVideosBeVisible(state);
    const { isOpen: shiftRight } = state['features/chat'];
    const disableSelfView = getHideSelfView(state);
    const { clientWidth, clientHeight } = state['features/base/responsive-ui'];
    const filmstripDisabled = isFilmstripDisabled(state);
    const isMobile = isMobileBrowser();
    const { is_in_picture_in_picture_mode } = state['features/picture-in-picture'];

    const collapseTileView = reduceHeight
        && isMobile
        && clientWidth <= ASPECT_RATIO_BREAKPOINT;

    const shouldReduceHeight = reduceHeight && isMobileBrowser();
    const _topPanelVisible = isStageFilmstripTopPanel(state) && topPanelVisible;

    const notDisabled = visible && !filmstripDisabled;
    let isVisible = notDisabled || filmstripType !== FILMSTRIP_TYPE.MAIN;

    if (_topPanelFilmstrip) {
        isVisible = _topPanelVisible;
    }
    const videosClassName = `filmstrip__videos${isVisible ? '' : ' hidden'}${_hasScroll ? ' has-scroll' : ''}`;
    const className = `${
        shouldReduceHeight ? 'reduce-height' : ''} ${isMobile ? 'is-mobile' : ''
    } ${shiftRight ? 'shift-right' : ''} ${collapseTileView ? 'collapse' : ''} ${isVisible ? '' : 'hidden'}`.trim();

    const _currentLayout = getCurrentLayout(state);
    const _isVerticalFilmstrip = _currentLayout === LAYOUTS.VERTICAL_FILMSTRIP_VIEW
        || (filmstripType === FILMSTRIP_TYPE.MAIN && _currentLayout === LAYOUTS.STAGE_FILMSTRIP_VIEW);

    return {
        _className: className,
        _chatOpen: state['features/chat'].isOpen,
        _currentLayout,
        _disableSelfView: disableSelfView,
        _filmstripDisabled: filmstripDisabled,
        _hasScroll,
        _iAmRecorder: Boolean(iAmRecorder),
        _isFilmstripButtonEnabled: isButtonEnabled('filmstrip', state),
        _isToolboxVisible: isToolboxVisible(state),
        _isVerticalFilmstrip,
        _localScreenShareId: localScreenShare?.id,
        _mainFilmstripVisible: notDisabled,
        _maxFilmstripWidth: clientWidth - MIN_STAGE_VIEW_WIDTH,
        _maxTopPanelHeight: clientHeight - MIN_STAGE_VIEW_HEIGHT,
        _remoteParticipantsLength: _remoteParticipants?.length ?? 0,
        _filteredRemoteParticipantsLength: _filteredRemoteParticipants?.length ?? 0,
        _topPanelHeight: topPanelHeight.current,
        _topPanelMaxHeight: topPanelHeight.current || TOP_FILMSTRIP_HEIGHT,
        _topPanelVisible,
        _verticalFilmstripWidth: verticalFilmstripWidth.current,
        _verticalViewMaxWidth: getVerticalViewMaxWidth(state),
        _videosClassName: videosClassName,
        _isInPipMode: is_in_picture_in_picture_mode,
    };
}

export default withStyles(translate(connect(_mapStateToProps)(FilmstripMobile)), styles);
