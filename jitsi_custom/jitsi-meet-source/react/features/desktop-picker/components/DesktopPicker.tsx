import React, { PureComponent } from 'react';
import { WithTranslation } from 'react-i18next';
import {connect} from 'react-redux';

import {IReduxState, IStore} from '../../app/types';
import { hideDialog } from '../../base/dialog/actions';
import { translate } from '../../base/i18n/functions';
import Dialog from '../../base/ui/components/web/Dialog';
import { THUMBNAIL_SIZE } from '../constants';
import { obtainDesktopSources, stopObtainDesktopSources, isAudioScreenSharingSupported } from '../functions';
import logger from '../logger';

import DesktopPickerPane from './DesktopPickerPane';
import Checkbox from "../../base/ui/components/web/Checkbox";
import DesktopPickerUnsupportedSound from './DesktopPickerUnsupportedSound'
import { updateSettings } from '../../base/settings/actions';
import {isAudioSharingEnabled} from "../../base/settings/functions.any";

/**
 * The sources polling interval in ms.
 *
 * @type {int}
 */
const UPDATE_INTERVAL = 2000;

const TAB_LABELS = {
    screen: 'dialog.yourEntireScreen',
    window: 'dialog.applicationWindow'
};

const VALID_TYPES = Object.keys(TAB_LABELS);

/**
 * The type of the React {@code Component} props of {@link DesktopPicker}.
 */
interface IProps extends WithTranslation {
    _isAudioSharingEnabled: boolean;

    /**
     * An array with desktop sharing sources to be displayed.
     */
    desktopSharingSources: Array<string>;

    /**
     * Used to request DesktopCapturerSources.
     */
    dispatch: IStore['dispatch'];

    /**
     * The callback to be invoked when the component is closed or when a
     * DesktopCapturerSource has been chosen.
     */
    onSourceChoose: Function;
}

/**
 * The type of the React {@code Component} state of {@link DesktopPicker}.
 */
interface IState {

    /**
     * The currently highlighted DesktopCapturerSource.
     */
    selectedSource: any;

    /**
     * An object containing all the DesktopCapturerSources.
     */
    sources: any;

    /**
     * The desktop source types to fetch previews for.
     */
    types: Array<string>;
}


/**
 * React component for DesktopPicker.
 *
 * @augments Component
 */
class DesktopPicker extends PureComponent<IProps, IState> {
    /**
     * Implements React's {@link Component#getDerivedStateFromProps()}.
     *
     * @inheritdoc
     */
    static getDerivedStateFromProps(props: IProps) {
        return {
            types: DesktopPicker._getValidTypes(props.desktopSharingSources)
        };
    }

    /**
     * Extracts only the valid types from the passed {@code types}.
     *
     * @param {Array<string>} types - The types to filter.
     * @private
     * @returns {Array<string>} The filtered types.
     */
    static _getValidTypes(types: string[] = []) {
        return types.filter(
            type => VALID_TYPES.includes(type));
    }



    _poller: any = null;

    state: IState = {
        selectedSource: {},
        sources: {},
        types: []
    };

    /**
     * Initializes a new DesktopPicker instance.
     *
     * @param {Object} props - The read-only properties with which the new
     * instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        // Bind event handlers so they are only bound once per instance.
        this._onCloseModal = this._onCloseModal.bind(this);
        this._onPreviewClick = this._onPreviewClick.bind(this);
        this._onShareAudioChecked = this._onShareAudioChecked.bind(this);
        this._onSubmit = this._onSubmit.bind(this);
        this._updateSources = this._updateSources.bind(this);

        this.state.types
            = DesktopPicker._getValidTypes(this.props.desktopSharingSources);
    }

    /**
     * Starts polling.
     *
     * @inheritdoc
     * @returns {void}
     */
    componentDidMount() {
        this._startPolling();
    }

    /**
     * Clean up component and DesktopCapturerSource store state.
     *
     * @inheritdoc
     */
    componentWillUnmount() {
        this._stopPolling();
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     */
    render() {
        const { selectedSource, sources } = this.state;
        const { t, _isAudioSharingEnabled } = this.props;
        const isCheckboxDisabled = !isAudioScreenSharingSupported();

        const getButton = () => {
            if (isCheckboxDisabled) {
                const is_enabled = this.props._isAudioSharingEnabled;
                is_enabled && this.props.dispatch(updateSettings({
                    isAudioSharingEnabled: false,
                }))
                return  <DesktopPickerUnsupportedSound { ...this.props } />
            }

            return <Checkbox
                checked = {_isAudioSharingEnabled}
                className = 'desktop-picker-audio-checkbox'
                label = {t('dialog.screenSharingAudio')}
                name = 'share-system-audio'
                disabled={isCheckboxDisabled}
                onChange = {this._onShareAudioChecked} />
        }

        return (
            <Dialog
                classNameHeader = 'desktop-picker-dialog-header'
                classNameContent = 'desktop-picker-dialog-content'
                classNameFooter = 'desktop-picker-dialog-footer'
                ok = {{
                    disabled: Boolean(!this.state.selectedSource.id),
                    translationKey: 'dialog.Share'
                }}
                cancel = {{
                    hidden: true,
                }}
                customButton = {
                    getButton()
                }
                onCancel = {this._onCloseModal}
                onSubmit = {this._onSubmit}
                size = 'xxl'
                titleKey = 'dialog.shareYourScreen'>
                <div
                    aria-labelledby = 'all-windows-button'
                    id = 'all-windows-panel'
                    key = 'all-windows'
                    role = 'windowspanel'
                    tabIndex = {0}>
                    <DesktopPickerPane
                        key = 'all-windows-picker'
                        // @ts-ignore
                        onClick = {this._onPreviewClick}
                        onDoubleClick = {this._onSubmit}
                        selectedSourceId = {selectedSource.id}
                        sources = {sources} />
                </div>

            </Dialog>
        );
    }

    /**
     * Computes the selected source.
     *
     * @param {Object} sources - The available sources.
     * @returns {Object} The selectedSource value.
     */
    _getSelectedSource(sources: any = {}) {
        const { selectedSource } = this.state;

        /**
         * If there are no sources for this type (or no sources for any type)
         * we can't select anything.
         */
        if (!Array.isArray(sources)
            || sources.length <= 0) {
            return {};
        }

        /**
         * Select the first available source for this type in the following
         * scenarios:
         * 1) Nothing is yet selected.
         * 2) The selected source is no longer available.
         */
        if (!selectedSource // scenario 1)
            || !sources.some( // scenario 2)
                (source: any) => source.id === selectedSource.id)) {

            this._saveScreenParams(sources[0].type, sources[0].id);

            return {
                id: sources[0].id,
                type: sources[0].type
            };
        }

        this._saveScreenParams(selectedSource.type, selectedSource.id);

        /**
         * For all other scenarios don't change the selection.
         */
        return selectedSource;
    }

    /**
     * Dispatches an action to hide the DesktopPicker and invokes the passed in
     * callback with a selectedSource, if any.
     *
     * @param {string} [id] - The id of the DesktopCapturerSource to pass into
     * the onSourceChoose callback.
     * @param {string} type - The type of the DesktopCapturerSource to pass into
     * the onSourceChoose callback.
     * @param {boolean} screenShareAudio - Whether or not to add system audio to
     * screen sharing session.
     * @returns {void}
     */
    _onCloseModal(id = '', type?: string, screenShareAudio = this.props._isAudioSharingEnabled) {
        this.props.onSourceChoose(id, type, screenShareAudio);
        this.props.dispatch(hideDialog());
    }

    /**
     * Sets the currently selected DesktopCapturerSource.
     *
     * @param {string} id - The id of DesktopCapturerSource.
     * @param {string} type - The type of DesktopCapturerSource.
     * @returns {void}
     */
    _onPreviewClick(id: string, type: string) {

        this.setState({
            selectedSource: {
                id,
                type
            }
        });
        this._saveScreenParams(type, id);
    }

    /**
     * Request to close the modal and execute callbacks with the selected source
     * id.
     *
     * @returns {void}
     */
    _onSubmit() {
        const { selectedSource: { id, type } } = this.state;
        this._onCloseModal(id, type, this.props._isAudioSharingEnabled);
    }

    /**
     * Set the screenSharingAudio state indicating whether or not to also share
     * system audio.
     *
     * @returns {void}
     * @param event
     */
    _onShareAudioChecked(event: React.ChangeEvent<HTMLInputElement>) {
        this.props.dispatch(updateSettings({
            isAudioSharingEnabled: event.target.checked,
        }));
    }

    /**
     * Create an interval to update known available DesktopCapturerSources.
     *
     * @private
     * @returns {void}
     */
    _startPolling() {
        this._stopPolling();
        this._updateSources();
        this._poller = window.setInterval(this._updateSources, UPDATE_INTERVAL);
    }

    /**
     * Cancels the interval to update DesktopCapturerSources.
     *
     * @private
     * @returns {void}
     */
    _stopPolling() {
        window.clearInterval(this._poller);
        this._poller = null;
        stopObtainDesktopSources();
    }

    /**
     * Obtains the desktop sources and updates state with them.
     *
     * @private
     * @returns {void}
     */
    _updateSources() {
        const { types } = this.state;
        const options = {
            types,
            thumbnailSize: THUMBNAIL_SIZE
        };

        if (types.length > 0) {
            obtainDesktopSources(options)
                .then((sources: any) => {

                    // формируем единый список и добавляем внутрь type
                    const allSources = Object.entries(sources).flatMap(([ type, items ]) =>
                        // @ts-ignore
                        items.map(item => ({
                            ...item,
                            type: type
                        }))
                    );

                    const selectedSource = this._getSelectedSource(allSources);

                    this.setState({
                        selectedSource,
                        sources: allSources
                    });
                })
                .catch((error: any) => logger.log(error));
        }
    }

    _saveScreenParams(type: string, stream_id: string) {
        window.parent.postMessage({
            type: 'save_screen_share_params',
            data: {
                type,
                stream_id,
            }
        }, "*");
    }
}

/**
 * Maps part of the Redux state to the props of this component.
 *
 * @param {IReduxState} state - The Redux state.
 * @private
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState) {
    return {
        _isAudioSharingEnabled: Boolean(isAudioSharingEnabled(state))
    };
}

export default translate(connect(_mapStateToProps)(DesktopPicker));
