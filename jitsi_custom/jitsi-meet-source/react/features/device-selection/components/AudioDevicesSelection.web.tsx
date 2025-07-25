import { Theme } from '@mui/material';
import React from 'react';
import { WithTranslation } from 'react-i18next';
import { connect } from 'react-redux';
import { withStyles } from 'tss-react/mui';

import { IReduxState, IStore } from '../../app/types';
import { getAvailableDevices } from '../../base/devices/actions.web';
import AbstractDialogTab, {
    type IProps as AbstractDialogTabProps
} from '../../base/dialog/components/web/AbstractDialogTab';
import { translate } from '../../base/i18n/functions';
import { createLocalTrack } from '../../base/lib-jitsi-meet/functions.web';
import { iAmVisitor as iAmVisitorCheck } from '../../visitors/functions';
import logger from '../logger';

import AudioInputPreview from './AudioInputPreview';
import DeviceSelector from './DeviceSelector.web';
import { IconMicFilled, IconVolumeHighFilled } from "../../base/icons/svg";
import { isMobileBrowser } from "../../base/environment/utils";
import Icon from "../../base/icons/components/Icon";

/**
 * The type of the React {@code Component} props of {@link AudioDevicesSelection}.
 */
interface IProps extends AbstractDialogTabProps, WithTranslation {

    /**
     * All known audio and video devices split by type. This prop comes from
     * the app state.
     */
    availableDevices: {
        audioInput?: MediaDeviceInfo[];
        audioOutput?: MediaDeviceInfo[];
    };

    /**
     * CSS classes object.
     */
    classes?: Partial<Record<keyof ReturnType<typeof styles>, string>>;

    /**
     * Whether or not the audio selector can be interacted with. If true,
     * the audio input selector will be rendered as disabled. This is
     * specifically used to prevent audio device changing in Firefox, which
     * currently does not work due to a browser-side regression.
     */
    disableAudioInputChange: boolean;

    /**
     * True if device changing is configured to be disallowed. Selectors
     * will display as disabled.
     */
    disableDeviceChange: boolean;

    /**
     * Redux dispatch function.
     */
    dispatch: IStore['dispatch'];

    /**
     * Whether or not the audio permission was granted.
     */
    hasAudioPermission: boolean;

    /**
     * If true, the audio meter will not display. Necessary for browsers or
     * configurations that do not support local stats to prevent a
     * non-responsive mic preview from displaying.
     */
    hideAudioInputPreview: boolean;

    /**
     * If true, the button to play a test sound on the selected speaker will not be displayed.
     * This needs to be hidden on browsers that do not support selecting an audio output device.
     */
    hideAudioOutputPreview: boolean;

    /**
     * Whether or not the audio output source selector should display. If
     * true, the audio output selector and test audio link will not be
     * rendered.
     */
    hideAudioOutputSelect: boolean;

    /**
     * Whether or not the hid device container should display.
     */
    hideDeviceHIDContainer: boolean;

    /**
     * Whether to hide noise suppression checkbox or not.
     */
    hideNoiseSuppression: boolean;

    /**
     * Whether we are in visitors mode.
     */
    iAmVisitor: boolean;

    /**
     * Wether noise suppression is on or not.
     */
    noiseSuppressionEnabled: boolean;

    /**
     * The id of the audio input device to preview.
     */
    selectedAudioInputId: string;

    /**
     * The id of the audio output device to preview.
     */
    selectedAudioOutputId: string;
}

/**
 * The type of the React {@code Component} state of {@link AudioDevicesSelection}.
 */
interface IState {

    /**
     * The JitsiTrack to use for previewing audio input.
     */
    previewAudioTrack?: any | null;
}

const styles = (theme: Theme) => {
    return {
        container: {
            display: 'flex',
            flexDirection: 'column' as const,
            width: '100%'
        },

        inputContainer: {},

        outputContainer: {
            marginTop: '32px',
            display: 'flex',
            flexDirection: 'column' as const,
            width: '100%',
        },

        outputContainerMobile: {
            marginTop: 0,
            display: 'flex',
            flexDirection: 'column' as const,
            width: '100%',
        },

        outputButton: {
            marginLeft: theme.spacing(3)
        },

        noiseSuppressionContainer: {
            marginBottom: theme.spacing(5)
        },

        changeNotSupportedContainer: {
            padding: 0,
            display: 'flex',
        },

        changeNotSupportedIcon: {
            padding: '16px 12px 16px 16px',

            '& svg': {
                fill: 'rgba(255, 255, 255, 0.3) !important'
            }
        },

        changeNotSupportedItem: {
            alignItems: 'center',
            cursor: 'pointer',
            display: 'flex',
            width: '100%',
            padding: 0,
            boxSizing: 'border-box' as const,
        },

        changeNotSupportedText: {
            width: '100%',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '17px',
            lineHeight: '22px',
            padding: '16px 16px 16px 0',
            color: 'rgba(255, 255, 255, 0.7)',
        },
    };
};

/**
 * React {@code Component} for previewing audio and video input/output devices.
 *
 * @augments Component
 */
class AudioDevicesSelection extends AbstractDialogTab<IProps, IState> {

    /**
     * Whether current component is mounted or not.
     *
     * In component did mount we start a Promise to create tracks and
     * set the tracks in the state, if we unmount the component in the meanwhile
     * tracks will be created and will never been disposed (dispose tracks is
     * in componentWillUnmount). When tracks are created and component is
     * unmounted we dispose the tracks.
     */
    _unMounted: boolean;

    /**
     * Initializes a new DeviceSelection instance.
     *
     * @param {Object} props - The read-only React Component props with which
     * the new instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        this.state = {
            previewAudioTrack: null
        };
        this._unMounted = true;
    }

    /**
     * Generate the initial previews for audio input and video input.
     *
     * @inheritdoc
     */
    componentDidMount() {
        this._unMounted = false;
        Promise.all([
            this._createAudioInputTrack(this.props.selectedAudioInputId)
        ])
            .catch(err => logger.warn('Failed to initialize preview tracks', err))
            .then(() => {
                this.props.dispatch(getAvailableDevices());
            });
    }

    /**
     * Checks if audio / video permissions were granted. Updates audio input and
     * video input previews.
     *
     * @param {Object} prevProps - Previous props this component received.
     * @returns {void}
     */
    componentDidUpdate(prevProps: IProps) {
        if (prevProps.selectedAudioInputId
            !== this.props.selectedAudioInputId) {
            this._createAudioInputTrack(this.props.selectedAudioInputId);
        }
    }

    /**
     * Ensure preview tracks are destroyed to prevent continued use.
     *
     * @inheritdoc
     */
    componentWillUnmount() {
        this._unMounted = true;
        this._disposeAudioInputPreview();
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     */
    render() {
        const {
            hasAudioPermission,
            hideAudioInputPreview,
            hideAudioOutputPreview,
            hideDeviceHIDContainer,
            hideNoiseSuppression,
            iAmVisitor,
            noiseSuppressionEnabled,
            selectedAudioOutputId,
            t
        } = this.props;
        const { audioInput, audioOutput } = this._getSelectors();

        const classes = withStyles.getClasses(this.props);
        const isMobile = isMobileBrowser();

        return (
            <div className = {classes.container}>
                {iAmVisitor && this.props.hideAudioOutputSelect ? (
                    <div className = {classes.changeNotSupportedContainer}>
                        <Icon
                            className = {classes.changeNotSupportedIcon}
                            size = {22}
                            src = {IconVolumeHighFilled}
                        />
                        <div className = {classes.changeNotSupportedItem}>
                            <div className = {classes.changeNotSupportedText}>
                                {t('settings.audioOutputChangeNotSupported')}
                            </div>
                        </div>
                    </div>
                ) : (
                    <>
                        {!iAmVisitor && <div
                            aria-live = 'polite'
                            className = {classes.inputContainer}>
                            {this._renderSelector(audioInput)}
                        </div>}
                        {!hideAudioInputPreview && hasAudioPermission && !iAmVisitor && !isMobile
                            && <AudioInputPreview
                                track = {this.state.previewAudioTrack} />}
                        <div
                            aria-live = 'polite'
                            className = {isMobile ? classes.outputContainerMobile : classes.outputContainer}>
                            {this._renderSelector(audioOutput)}
                        </div>
                    </>
                )}
            </div>
        );
    }

    /**
     * Creates the JitsiTrack for the audio input preview.
     *
     * @param {string} deviceId - The id of audio input device to preview.
     * @private
     * @returns {void}
     */
    _createAudioInputTrack(deviceId: string) {
        const { hideAudioInputPreview } = this.props;

        if (hideAudioInputPreview) {
            return;
        }

        return this._disposeAudioInputPreview()
            .then(() => createLocalTrack('audio', deviceId, 5000))
            .then(jitsiLocalTrack => {
                if (this._unMounted) {
                    jitsiLocalTrack.dispose();

                    return;
                }

                this.setState({
                    previewAudioTrack: jitsiLocalTrack
                });
            })
            .catch(() => {
                this.setState({
                    previewAudioTrack: null
                });
            });
    }

    /**
     * Utility function for disposing the current audio input preview.
     *
     * @private
     * @returns {Promise}
     */
    _disposeAudioInputPreview(): Promise<any> {
        return this.state.previewAudioTrack
            ? this.state.previewAudioTrack.dispose() : Promise.resolve();
    }

    /**
     * Creates a DeviceSelector instance based on the passed in configuration.
     *
     * @private
     * @param {Object} deviceSelectorProps - The props for the DeviceSelector.
     * @returns {ReactElement}
     */
    _renderSelector(deviceSelectorProps: any) {
        return deviceSelectorProps ? (
            <DeviceSelector
                {...deviceSelectorProps}
                key = {deviceSelectorProps.id} />
        ) : null;
    }

    /**
     * Returns object configurations for audio input and output.
     *
     * @private
     * @returns {Object} Configurations.
     */
    _getSelectors() {
        const { availableDevices, hasAudioPermission } = this.props;

        const audioInput = {
            devices: availableDevices.audioInput,
            hasPermission: hasAudioPermission,
            isDisabled: this.props.disableAudioInputChange || this.props.disableDeviceChange,
            key: 'audioInput',
            id: 'audioInput',
            label: 'settings.selectMic',
            icon: IconMicFilled,
            onSelect: (selectedAudioInputId: string) => super._onChange({ selectedAudioInputId }),
            selectedDeviceId: this.state.previewAudioTrack
                ? this.state.previewAudioTrack.getDeviceId() : this.props.selectedAudioInputId
        };
        let audioOutput;

        if (!this.props.hideAudioOutputSelect) {
            audioOutput = {
                devices: availableDevices.audioOutput,
                hasPermission: hasAudioPermission,
                isDisabled: this.props.disableDeviceChange,
                key: 'audioOutput',
                id: 'audioOutput',
                label: 'settings.selectAudioOutput',
                icon: IconVolumeHighFilled,
                onSelect: (selectedAudioOutputId: string) => super._onChange({ selectedAudioOutputId }),
                selectedDeviceId: this.props.selectedAudioOutputId
            };
        }

        return {
            audioInput,
            audioOutput
        };
    }
}

const mapStateToProps = (state: IReduxState) => {
    return {
        availableDevices: state['features/base/devices'].availableDevices ?? {},
        iAmVisitor: iAmVisitorCheck(state)
    };
};

export default connect(mapStateToProps)(withStyles(translate(AudioDevicesSelection), styles));
