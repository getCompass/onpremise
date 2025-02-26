import { Theme } from '@mui/material';
import React from 'react';
import { connect } from 'react-redux';
import { withStyles } from 'tss-react/mui';

import { translate } from '../../../base/i18n/functions';
import { IconFilledSquare, IconRecord, IconRecordInProcess, IconSites } from '../../../base/icons/svg';
import Label from '../../../base/label/components/web/Label';
import { browser, JitsiRecordingConstants } from '../../../base/lib-jitsi-meet';
import AbstractRecordingLabel, { _mapStateToProps, IProps as AbstractProps } from '../AbstractRecordingLabel';
import { openDialog } from "../../../base/dialog/actions";
import StopRecordingDialog from "../Recording/web/StopRecordingDialog";
import { stopLocalVideoRecording } from "../../actions.any";

interface IProps extends AbstractProps {

    /**
     * An object containing the CSS classes.
     */
    classes?: Partial<Record<keyof ReturnType<typeof styles>, string>>;

}

/**
 * Creates the styles for the component.
 *
 * @param {Object} theme - The current UI theme.
 *
 * @returns {Object}
 */
const styles = (theme: Theme) => {
    return {
        recordContainer: {
            background: "rgba(33, 33, 33, 0.9)",
            borderRadius: "4px",
            padding: "5px",
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
        },
        record: {
            background: "transparent",
            margin: 0,
            padding: 0,
        },
        recordText: {
            marginLeft: "4px",
            fontFamily: 'Inter Medium',
            fontWeight: 'normal' as const,
            fontSize: '12px',
            lineHeight: '15px',
            color: "rgba(255, 255, 255, 1)",
        },
        recordStopButton: {
            cursor: "pointer",
            background: "transparent",
            margin: "0px 0px 0px 8px",
            padding: 0,
        },

        dotContainer: {
            width: "14px",
            height: "14px",
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
            border: "1px solid rgba(255, 79, 71, 1)",
            borderRadius: "100%",
        },

        dotAnimated: {
            width: "6px",
            height: "6px",
            backgroundColor: "rgba(255, 79, 71, 1)",
            borderRadius: "100%",
            animation: "pulse 700ms ease-in-out infinite",
        },
    };
};

/**
 * Implements a React {@link Component} which displays the current state of
 * conference recording.
 *
 * @augments {Component}
 */
class RecordingLabel extends AbstractRecordingLabel<IProps> {
    /**
     * Renders the platform specific label component.
     *
     * @inheritdoc
     */
    _renderLabel() {
        const { _isTranscribing, _status, mode, t, dispatch, _isVisible, _iAmRecorder } = this.props;
        const classes = withStyles.getClasses(this.props);
        const isRecording = mode === JitsiRecordingConstants.mode.FILE;
        const icon = isRecording ? IconRecordInProcess : IconSites;
        let content;

        if (_status === JitsiRecordingConstants.status.ON) {
            content = t(isRecording ? 'videoStatus.recording' : 'videoStatus.streaming');

            if (_isTranscribing) {
                content += ` \u00B7 ${t('transcribing.labelToolTip')}`;
            }
        } else if (mode === JitsiRecordingConstants.mode.STREAM) {
            return null;
        } else if (_isTranscribing) {
            content = t('transcribing.labelToolTip');
        } else {
            return null;
        }

        return (
            <div className = {classes.recordContainer}>
                <div className = {classes.dotContainer}>
                    <div className = {classes.dotAnimated} />
                </div>
                <div className = {classes.recordText}>{t("videoStatus.recording")}</div>
                <Label
                    className = {classes.recordStopButton}
                    icon = {IconFilledSquare}
                    iconSize = "13"
                    iconColor = "rgba(255, 255, 255, 1)"
                    onClick = {() => browser.isElectron() ? postMessage({ type: "recorder_stop" }, "*") : dispatch(stopLocalVideoRecording())} />
            </div>
        );
    }
}

export default withStyles(translate(connect(_mapStateToProps)(RecordingLabel)), styles);
