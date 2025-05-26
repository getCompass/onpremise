import React, { Component } from 'react';
import { WithTranslation } from 'react-i18next';

import { translate } from '../../base/i18n/functions';
import { IconErrorLoadImg } from '../../base/icons/svg';
import Icon from '../../base/icons/components/Icon';


/**
 * The type of the React {@code Component} props of
 * {@link DesktopSourcePreview}.
 */
interface IProps extends WithTranslation {

    /**
     * The callback to invoke when the component is clicked. The id of the
     * clicked on DesktopCapturerSource will be passed in.
     */
    onClick: Function;

    /**
     * The callback to invoke when the component is double clicked. The id of
     * the DesktopCapturerSource will be passed in.
     */
    onDoubleClick: Function;

    /**
     * The indicator which determines whether this DesktopSourcePreview is
     * selected. If true, the 'is-selected' CSS class will be added to the root
     * of Component.
     */
    selected: boolean;

    /**
     * The DesktopCapturerSource to display.
     */
    source: any;
}

interface IState {
    srcImage: string;
    hasLoadError: boolean
}

/**
 * React component for displaying a preview of a DesktopCapturerSource.
 *
 * @augments Component
 */
class DesktopSourcePreview extends Component<IProps, IState> {
    state = {
        srcImage: '',
        hasLoadError: false
    }

    /**
     * Initializes a new DesktopSourcePreview instance.
     *
     * @param {Object} props - The read-only properties with which the new
     * instance is to be initialized.
     */
    constructor(props: IProps) {
        super(props);

        this._onClick = this._onClick.bind(this);
        this._onDoubleClick = this._onDoubleClick.bind(this);
        this._onErrorLoadThumbnailImage = this._onErrorLoadThumbnailImage.bind(this);
    }

    componentDidMount() {
        this.updateThumbnailURL();
    }

    componentDidUpdate() {
        if (!this.state.srcImage && this.state.hasLoadError) {
            this.updateThumbnailURL();
        }
    }

    updateThumbnailURL() {
        let srcImage = this.props.source.thumbnail.dataUrl;

        // legacy thumbnail image
        if (typeof this.props.source.thumbnail.toDataURL === 'function') {
            srcImage = this.props.source.thumbnail.toDataURL();
        }

        this.setState({ srcImage: srcImage || '', hasLoadError: false });
    }

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const selectedClass = this.props.selected ? 'is-selected' : '';
        const displayClasses = `desktop-picker-source ${selectedClass}`;

        return (
            <div className = {displayClasses}
                 onClick = {this._onClick}
                 onDoubleClick = {this._onDoubleClick}
            >
                {
                    this.state.hasLoadError
                        ? this._renderErrorLoadThumbnailImage()
                        : this._renderThumbnailImageContainer()
                }
                <div className = 'desktop-source-preview-label'>
                    {this.props.source.name}
                </div>
            </div>
        );
    }

    /**
     * Render thumbnail screenshare image.
     *
     * @returns {Object} - Thumbnail image.
     */
    _renderThumbnailImageContainer() {
        const srcImage = this.state.srcImage;

        return (
            <div style = {{
                height: '126px',
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
            }}>
                <div className = 'desktop-source-preview-image-container'>
                    {this._renderThumbnailImage(srcImage)}
                </div>
            </div>
        );
    }

    _renderErrorLoadThumbnailImage() {
        return (
            <div className = 'desktop-source-preview-thumbnail-error-stub'>
                <Icon src = {IconErrorLoadImg} />
            </div>
        )
    }

    /**
     * Render thumbnail screenshare image.
     *
     * @param {string} src - Of the image.
     * @returns {Object} - Thumbnail image.
     */
    _renderThumbnailImage(src: string) {
        return (
            <img
                alt = {this.props.t('welcomepage.logo.desktopPreviewThumbnail')}
                className = 'desktop-source-preview-thumbnail'
                src = {src}
                onError = {this._onErrorLoadThumbnailImage}
            />
        );
    }

    /**
     * Invokes the passed in onClick callback.
     *
     * @returns {void}
     */
    _onClick() {
        const { source } = this.props;

        this.props.onClick(source.id, source.type, source.dimensions);
    }

    /**
     * Invokes the passed in onDoubleClick callback.
     *
     * @returns {void}
     */
    _onDoubleClick() {
        const { source } = this.props;

        this.props.onDoubleClick(source.id, source.type, source.dimensions);
    }

    _onErrorLoadThumbnailImage() {
        this.setState({ hasLoadError: true })
    }
}

export default translate(DesktopSourcePreview);
