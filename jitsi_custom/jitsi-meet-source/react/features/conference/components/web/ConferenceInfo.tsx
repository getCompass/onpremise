/* eslint-disable react/no-multi-comp */
import React, { Component } from 'react';
import { connect } from 'react-redux';

import { IReduxState, IStore } from '../../../app/types';
import { JitsiRecordingConstants } from '../../../base/lib-jitsi-meet';
import RecordingLabel from '../../../recording/components/web/RecordingLabel';
import { isToolboxVisible } from '../../../toolbox/functions.web';
import { getConferenceInfo } from '../functions.web';

import ConferenceInfoContainer from './ConferenceInfoContainer';
import ParticipantsRecordingLabel from "../../../recording/components/web/ParticipantsRecordingLabel";

/**
 * The type of the React {@code Component} props of {@link Subject}.
 */
interface IProps {

    /**
     * The conference info labels to be shown in the conference header.
     */
    _conferenceInfo: {
        alwaysVisible?: string[];
        autoHide?: string[];
    };

    /**
     * Indicates whether the component should be visible or not.
     */
    _visible: boolean;

    /**
     * Invoked to active other features of the app.
     */
    dispatch: IStore['dispatch'];
}

const COMPONENTS: Array<{
    Component: React.ComponentType<any>;
    id: string;
}> = [
    {
        Component: () => (
            <>
                <ParticipantsRecordingLabel />
                <RecordingLabel mode = {JitsiRecordingConstants.mode.FILE} />
                <RecordingLabel mode = {JitsiRecordingConstants.mode.STREAM} />
            </>
        ),
        id: 'recording'
    }
];

/**
 * The upper band of the meeing containing the conference name, timer and labels.
 *
 * @param {Object} props - The props of the component.
 * @returns {React$None}
 */
class ConferenceInfo extends Component<IProps> {
    /**
     * Initializes a new {@code ConferenceInfo} instance.
     *
     * @param {IProps} props - The read-only React {@code Component} props with
     * which the new instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        this._renderAutoHide = this._renderAutoHide.bind(this);
        this._renderAlwaysVisible = this._renderAlwaysVisible.bind(this);
    }

    /**
     * Renders auto-hidden info header labels.
     *
     * @returns {void}
     */
    _renderAutoHide() {
        const { autoHide } = this.props._conferenceInfo;

        if (!autoHide?.length) {
            return null;
        }

        return (
            <ConferenceInfoContainer
                id = 'autoHide'
                visible = {this.props._visible}>
                {
                    COMPONENTS
                        .filter(comp => autoHide.includes(comp.id))
                        .map(c =>
                            <c.Component key = {c.id} />
                        )
                }
            </ConferenceInfoContainer>
        );
    }

    /**
     * Renders the always visible info header labels.
     *
     * @returns {void}
     */
    _renderAlwaysVisible() {
        const { alwaysVisible } = this.props._conferenceInfo;

        if (!alwaysVisible?.length) {
            return null;
        }

        return (
            <ConferenceInfoContainer
                id = 'alwaysVisible'
                visible = {true}>
                {
                    COMPONENTS
                        .filter(comp => alwaysVisible.includes(comp.id))
                        .map(c =>
                            <c.Component key = {c.id} />
                        )
                }
            </ConferenceInfoContainer>
        );
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        return (
            <div
                className = {`details-container ${this.props._visible ? 'visible' : ''}`}>
                <div style = {{ display: 'flex', paddingLeft: '16px' }}>
                    {this._renderAlwaysVisible()}
                    {this._renderAutoHide()}
                </div>
            </div>
        );
    }
}

/**
 * Maps (parts of) the Redux state to the associated
 * {@code Subject}'s props.
 *
 * @param {Object} state - The Redux state.
 * @private
 * @returns {{
 *     _visible: boolean,
 *     _conferenceInfo: Object
 * }}
 */
function _mapStateToProps(state: IReduxState) {
    const { is_in_picture_in_picture_mode } = state['features/picture-in-picture'];

    return {
        _visible: isToolboxVisible(state) && !is_in_picture_in_picture_mode,
        _conferenceInfo: getConferenceInfo(state)
    };
}

export default connect(_mapStateToProps)(ConferenceInfo);
