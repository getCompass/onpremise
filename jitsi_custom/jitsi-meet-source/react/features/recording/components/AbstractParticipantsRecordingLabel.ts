import React, { Component } from 'react';
import { WithTranslation } from 'react-i18next';

import { IReduxState, IStore } from '../../app/types';
import { isRecordingRunning } from '../functions';

export interface IProps extends WithTranslation {

    /**
     * Whether the recording/livestreaming/transcriber is currently running.
     */
    _isVisible: boolean;

    /**
     * The redux dispatch function.
     */
    dispatch: IStore['dispatch'];
}

/**
 * Abstract class for the {@code ParticipantsRecordingLabel} component.
 */
export default class AbstractParticipantsRecordingLabel<P extends IProps = IProps> extends Component<P> {
    /**
     * Implements React {@code Component}'s render.
     *
     * @inheritdoc
     */
    render() {
        const { _isVisible } = this.props;

        return _isVisible ? this._renderLabel() : null;
    }

    /**
     * Renders the platform specific label component.
     *
     * @protected
     * @returns {React$Element}
     */
    _renderLabel(): React.ReactNode | null {
        return null;
    }
}

/**
 * Maps (parts of) the Redux state to the associated
 * {@code AbstractParticipantsRecordingLabel}'s props.
 *
 * @param {Object} state - The Redux state.
 * @param {IProps} ownProps - The component's own props.
 * @private
 * @returns {{
 *     _isVisible: boolean
 * }}
 */
export function _mapStateToProps(state: IReduxState, ownProps: any) {
    const userRecordingCount = state['features/recording'].userRecordingCount;
    const isLocalRecording = isRecordingRunning(state);
    const _isVisible = userRecordingCount > 0 && !isLocalRecording;

    return {
        _isVisible,
    };
}
