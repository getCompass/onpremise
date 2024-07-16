import React, { useRef } from 'react';
import { makeStyles } from 'tss-react/mui';

import { IconCheck } from '../../../../base/icons/svg';
import Button from '../../../../base/ui/components/web/Button';
import ContextMenuItem from '../../../../base/ui/components/web/ContextMenuItem';
import { BUTTON_TYPES, TEXT_OVERFLOW_TYPES } from '../../../../base/ui/constants.any';
import logger from '../../../logger';

const TEST_SOUND_PATH = 'sounds/ring.mp3';

/**
 * The type of the React {@code Component} props of {@link SpeakerEntry}.
 */
interface IProps {

    /**
     * The text label for the entry.
     */
    children: string;

    /**
     * The deviceId of the speaker.
     */
    deviceId: string;

    /**
     * Flag controlling the selection state of the entry.
     */
    index: number;

    /**
     * Flag controlling the selection state of the entry.
     */
    isSelected: boolean;

    /**
     * Flag controlling the selection state of the entry.
     */
    length: number;

    /**
     * Click handler for the component.
     */
    onClick: Function;
}

const useStyles = makeStyles()(() => {
    return {
        container: {
            position: 'relative',

            [[ '&:hover', '&:focus', '&:focus-within' ] as any]: {
                '& .entryText': {
                    maxWidth: '240px',
                    marginRight: 0
                },

                '& .testButton': {
                    display: 'inline-block'
                }
            }
        },

        entryText: {
            maxWidth: '240px',

            '&.left-margin': {
                marginLeft: '26px'
            }
        },

        testButton: {
            display: 'none',
            fontSize: '12px',
            lineHeight: '15px',
            padding: '4.5px 8px',
            position: 'absolute',
            right: '17px',
            top: '6px'
        }
    };
});

/**
 * Implements a React {@link Component} which displays an audio
 * output settings entry. The user can click and play a test sound.
 *
 * @param {IProps} props - Component props.
 * @returns {JSX.Element}
 */
const SpeakerEntry = (props: IProps) => {
    const audioRef = useRef<HTMLAudioElement | null>(null);
    const { classes, cx } = useStyles();

    /**
     * Click handler for the entry.
     *
     * @returns {void}
     */
    function _onClick() {
        props.onClick(props.deviceId);
    }

    /**
     * Key pressed handler for the entry.
     *
     * @param {Object} e - The event.
     * @private
     *
     * @returns {void}
     */
    function _onKeyPress(e: React.KeyboardEvent) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            props.onClick(props.deviceId);
        }
    }

    /**
     * Click handler for Test button.
     * Sets the current audio output id and plays a sound.
     *
     * @param {Object} e - The synthetic event.
     * @returns {void}
     */
    async function _onTestButtonClick(e: React.KeyboardEvent | React.MouseEvent) {
        e.stopPropagation();

        try {
            await audioRef.current?.setSinkId(props.deviceId);
            audioRef.current?.play();
        } catch (err) {
            logger.log('Could not set sink id', err);
        }
    }

    const { children, isSelected, index, length } = props;

    /* eslint-disable react/jsx-no-bind */
    return (
        <li
            aria-checked = { isSelected }
            aria-posinset = { index }
            aria-setsize = { length }
            className = { classes.container }
            onClick = { _onClick }
            onKeyPress = { _onKeyPress }
            role = 'radio'
            tabIndex = { 0 }>
            <ContextMenuItem
                accessibilityLabel = { children }
                icon = { isSelected ? IconCheck : undefined }
                overflowType = { TEXT_OVERFLOW_TYPES.ELLIPSIS }
                selected = { isSelected }
                text = { children }
                textClassName = { cx(classes.entryText, 'entryText', !isSelected && 'left-margin') }>
                <Button
                    className = { cx(classes.testButton, 'testButton') }
                    label = 'Тест'
                    onClick = { _onTestButtonClick }
                    onKeyPress = { _onTestButtonClick }
                    type = { BUTTON_TYPES.PRIMARY } />
            </ContextMenuItem>
            <audio
                preload = 'auto'
                ref = { audioRef }
                src = { TEST_SOUND_PATH } />
        </li>
    );
};


export default SpeakerEntry;
