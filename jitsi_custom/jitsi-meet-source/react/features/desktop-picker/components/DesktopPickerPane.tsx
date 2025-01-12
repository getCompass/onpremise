import React, { Component } from 'react';
import { WithTranslation } from 'react-i18next';

import { translate } from '../../base/i18n/functions';
import Platform from '../../base/react/Platform.web';
import Checkbox from '../../base/ui/components/web/Checkbox';
import Spinner from '../../base/ui/components/web/Spinner';

import DesktopSourcePreview from './DesktopSourcePreview';
import { isAudioScreenSharingSupported } from "../functions";

/**
 * The type of the React {@code Component} props of {@link DesktopPickerPane}.
 */
interface IProps extends WithTranslation {

    /**
     * The handler to be invoked when a DesktopSourcePreview is clicked.
     */
    onClick: Function;

    /**
     * The handler to be invoked when a DesktopSourcePreview is double clicked.
     */
    onDoubleClick: Function;

    /**
     * The id of the DesktopCapturerSource that is currently selected.
     */
    selectedSourceId: string;

    /**
     * An array of DesktopCapturerSources.
     */
    sources: Array<any>;
}

/**
 * React component for showing a grid of DesktopSourcePreviews.
 *
 * @augments Component
 */
class DesktopPickerPane extends Component<IProps> {

    /**
     * Initializes a new DesktopPickerPane instance.
     *
     * @param {Object} props - The read-only properties with which the new
     * instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const {
            onClick,
            onDoubleClick,
            selectedSourceId,
            sources,
            t
        } = this.props;

        const classNames
            = `desktop-picker-pane default-scrollbar source-type-all-windows invisible-scrollbar`;
        const previews
            = Array.isArray(sources) && sources.length > 0
                ? sources.map(source => (
                    <DesktopSourcePreview
                        key = { source.id }
                        onClick = { onClick }
                        onDoubleClick = { onDoubleClick }
                        selected = { source.id === selectedSourceId }
                        source = { source }/>))
                : (
                    <div className = 'desktop-picker-pane-spinner'>
                        <Spinner />
                    </div>
                );

        return (
            <div className = { classNames }>
                { previews }
            </div>
        );
    }
}

export default translate(DesktopPickerPane);
