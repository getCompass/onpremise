import React, { Component, PureComponent } from "react";
import { connect } from "react-redux";

import { translate } from "../../../../base/i18n/functions";
import Dialog from "../../../../base/ui/components/web/Dialog";
import { WithTranslation } from "react-i18next";

/**
 * The type of the React {@code Component} props of {@link DesktopPicker}.
 */
interface IProps extends WithTranslation  {
    /**
     * The callback to be invoked when the component is confirm finished
     */
    onConfirm: Function;
}

/**
 * The type of the React {@code Component} state of {@link DesktopPicker}.
 */
interface IState {
    /**
     * The state of the audio screen share checkbox.
     */
    screenShareAudio: boolean;

    /**
     * The currently highlighted DesktopCapturerSource.
     */
    selectedSource: any;

    /**
     * The desktop source type currently being displayed.
     */
    selectedTab: string;

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
 * React Component for getting confirmation to stop a file recording session in
 * progress.
 *
 * @augments Component
 */
class StopRecordingDialogElectron extends PureComponent<IProps, IState> {


    /**
     * Initializes a new DesktopPicker instance.
     *
     * @param {Object} props - The read-only properties with which the new
     * instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        // Bind event handlers so they are only bound once per instance.
        this._onSubmit = this._onSubmit.bind(this);
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const { t } = this.props;

        return (
            <Dialog ok={{ translationKey: "dialog.confirm" }} onSubmit={this._onSubmit} titleKey="dialog.recording">
                {t("dialog.stopRecordingWarning")}
            </Dialog>
        );
    }

    _onSubmit() {
        this.props.onConfirm();
    }
}

export default translate(connect()(StopRecordingDialogElectron));
