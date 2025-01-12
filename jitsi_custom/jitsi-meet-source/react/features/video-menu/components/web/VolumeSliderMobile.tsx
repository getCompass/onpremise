import React, { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { makeStyles } from 'tss-react/mui';

import Icon from '../../../base/icons/components/Icon';
import { IconVolumeHigh, IconVolumeLow } from '../../../base/icons/svg';
import { VOLUME_SLIDER_SCALE } from '../../constants';
import { isMobileBrowser } from "../../../base/environment/utils";

/**
 * The type of the React {@code Component} props of {@link VolumeSliderMobile}.
 */
interface IProps {

    /**
     * The value of the audio slider should display at when the component first
     * mounts. Changes will be stored in state. The value should be a number
     * between 0 and 1.
     */
    initialValue: number;

    /**
     * The callback to invoke when the audio slider value changes.
     */
    onChange: Function;
}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            minWidth: '180px',
            width: '100%',
            boxSizing: 'border-box',
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            padding: '0px 16px',
        },

        iconVolumeLow: {
            minWidth: '18px',
            marginRight: '12px',
            position: 'relative',

            '& svg': {
                fill: 'rgba(255, 255, 255, 0.3) !important'
            },
        },

        iconVolumeHigh: {
            minWidth: '18px',
            marginLeft: '16px',
            position: 'relative',

            '& svg': {
                fill: 'rgba(255, 255, 255, 0.4) !important'
            },
        },

        sliderContainer: {
            position: 'relative',
            width: '100%',
            padding: '32px 0px',
        },

        slider: {
            position: 'absolute',
            width: '100%',
            top: '50%',
            transform: 'translate(0, -50%)'
        }
    };
});

const VolumeSliderMobile = ({
    initialValue,
    onChange
}: IProps) => {
    const { classes, cx } = useStyles();
    const { t } = useTranslation();

    const [ volumeLevel, setVolumeLevel ] = useState((initialValue || 0) * VOLUME_SLIDER_SCALE);
    const [ isDragging, setIsDragging ] = useState(false);

    const _updateVolumeFromMouseEvent = (event: React.MouseEvent<HTMLDivElement>) => {
        const rect = event.currentTarget.getBoundingClientRect();
        const x = event.clientX - rect.left; // x position within the slider container
        let newVolumeLevel = Math.round((x / rect.width) * VOLUME_SLIDER_SCALE);
        if (newVolumeLevel > 100) {
            newVolumeLevel = 100;
        }
        if (newVolumeLevel < 0) {
            newVolumeLevel = 0;
        }

        onChange(newVolumeLevel / VOLUME_SLIDER_SCALE);
        setVolumeLevel(newVolumeLevel);
    };

    const _onVolumeChange = useCallback((event: React.ChangeEvent<HTMLInputElement>) => {
        let newVolumeLevel = Number(event.currentTarget.value);
        if (newVolumeLevel > 100) {
            newVolumeLevel = 100;
        }
        if (newVolumeLevel < 0) {
            newVolumeLevel = 0;
        }

        onChange(newVolumeLevel / VOLUME_SLIDER_SCALE);
        setVolumeLevel(newVolumeLevel);
    }, [ onChange ]);

    const _onSliderContainerClick = useCallback((event: React.MouseEvent<HTMLDivElement>) => {
        _updateVolumeFromMouseEvent(event);
    }, []);

    const _onMouseDown = useCallback((event: React.MouseEvent<HTMLDivElement>) => {
        setIsDragging(true);
        _updateVolumeFromMouseEvent(event);
    }, []);

    const _onMouseMove = useCallback((event: React.MouseEvent<HTMLDivElement>) => {
        if (isDragging) {
            _updateVolumeFromMouseEvent(event);
        }
    }, [ isDragging ]);

    useEffect(() => {
        const handleMouseUp = () => {
            setIsDragging(false);
        };

        window.addEventListener('mouseup', handleMouseUp);

        return () => {
            window.removeEventListener('mouseup', handleMouseUp);
        };
    }, []);

    const _onClick = (e: React.MouseEvent) => {
        e.stopPropagation();
    };

    return (
        <div
            aria-label = {t('volumeSlider')}
            className = {cx('popupmenu__contents', classes.container)}
            onClick = {_onClick}>
                <span className = {classes.iconVolumeLow}>
                    <Icon
                        size = {22}
                        src = {IconVolumeLow} />
                </span>
            <div
                className = {classes.sliderContainer}
                onClick = {_onSliderContainerClick}
                onMouseDown = {_onMouseDown}
                onMouseMove = {_onMouseMove}>
                <input
                    aria-valuemax = {VOLUME_SLIDER_SCALE}
                    aria-valuemin = {0}
                    aria-valuenow = {volumeLevel}
                    className = {cx('popupmenu__volume-slider', classes.slider, isMobileBrowser() && 'is-mobile')}
                    max = {VOLUME_SLIDER_SCALE}
                    min = {0}
                    onChange = {_onVolumeChange}
                    style = {{ '--value': `${(volumeLevel / VOLUME_SLIDER_SCALE) * 100}%` } as any}
                    tabIndex = {0}
                    type = 'range'
                    value = {volumeLevel} />
            </div>
            <span className = {classes.iconVolumeHigh}>
                    <Icon
                        size = {22}
                        src = {IconVolumeHigh} />
                </span>
        </div>
    );
};

export default VolumeSliderMobile;
