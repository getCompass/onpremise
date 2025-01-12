import React, { useCallback, useRef } from 'react';
import { WithTranslation } from 'react-i18next';
import { makeStyles } from 'tss-react/mui';
import { v4 as uuidv4 } from 'uuid';

import { translate } from '../../base/i18n/functions';
import Icon from '../../base/icons/components/Icon';
import { IconPlus } from '../../base/icons/svg';
import { withPixelLineHeight } from '../../base/styles/functions.web';
import { type Image, VIRTUAL_BACKGROUND_TYPE } from '../constants';
import { resizeImage } from '../functions';
import logger from '../logger';
import Tooltip from "../../base/tooltip/components/Tooltip";

interface IProps extends WithTranslation {

    /**
     * Callback used to set the 'loading' state of the parent component.
     */
    setLoading: Function;

    /**
     * Callback used to set the options.
     */
    setOptions: Function;

    /**
     * Callback used to set the storedImages array.
     */
    setStoredImages: Function;

    /**
     * If a label should be displayed alongside the button.
     */
    showLabel: boolean;

    /**
     * A list of images locally stored.
     */
    storedImages: Array<Image>;
}

const useStyles = makeStyles()(theme => {
    return {
        label: {
            ...withPixelLineHeight(theme.typography.bodyShortBold),
            color: theme.palette.link01,
            marginBottom: theme.spacing(3),
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center'
        },

        addBackground: {
            marginRight: theme.spacing(3),

            '& svg': {
                fill: `${theme.palette.link01} !important`
            }
        },

        input: {
            display: 'none'
        },

        thumbnail: {
            backgroundColor: 'rgba(255, 255, 255, 0.1)',
            height: '55px',
            width: '100%',
            borderRadius: '4px',
            boxSizing: 'border-box',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            textAlign: 'center',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '12px',
            lineHeight: '14px',
            color: 'rgba(255, 255, 255, 0.75)',
            objectFit: 'cover',

            [[ '&:hover', '&:focus' ] as any]: {
                opacity: 0.5,
                cursor: 'pointer',

                '& ~ .delete-image-icon': {
                    display: 'block'
                }
            },

            '@media (max-width: 607px)': {
                height: '70px'
            }
        },
    };
});

/**
 * Component used to upload an image.
 *
 * @param {Object} Props - The props of the component.
 * @returns {React$Node}
 */
function UploadImageButton({
    setLoading,
    setOptions,
    setStoredImages,
    showLabel,
    storedImages,
    t
}: IProps) {
    const { classes } = useStyles();
    const uploadImageButton = useRef<HTMLInputElement>(null);
    const uploadImageKeyPress = useCallback(e => {
        if (uploadImageButton.current && (e.key === ' ' || e.key === 'Enter')) {
            e.preventDefault();
            uploadImageButton.current.click();
        }
    }, [ uploadImageButton.current ]);


    const uploadImage = useCallback(async e => {
        const reader = new FileReader();
        const imageFile = e.target.files;

        reader.readAsDataURL(imageFile[0]);
        reader.onload = async () => {
            const url = await resizeImage(reader.result);
            const uuId = uuidv4();

            setStoredImages([
                ...storedImages,
                {
                    id: uuId,
                    src: url
                }
            ]);
            setOptions({
                backgroundEffectEnabled: true,
                backgroundType: VIRTUAL_BACKGROUND_TYPE.IMAGE,
                selectedThumbnail: uuId,
                virtualSource: url
            });
        };
        logger.info('New virtual background image uploaded!');

        reader.onerror = () => {
            setLoading(false);
            logger.error('Failed to upload virtual image!');
        };
    }, [ storedImages ]);

    return (
        <>
            <Tooltip
                content = {t('virtualBackground.addBackground')}
                position = {'top'}>
                <div
                    aria-checked = {false}
                    aria-label = {t('virtualBackground.addBackground')}
                    className = {classes.thumbnail}
                    onClick = {() => uploadImageButton?.current?.click()}
                    onKeyPress = {uploadImageKeyPress}
                    role = 'radio'
                    tabIndex = {0}>
                    <Icon
                        size = {30}
                        src = {IconPlus}
                        color = {'rgba(255, 255, 255, 0.7)'} />
                </div>
            </Tooltip>

            <input
                accept = 'image/*'
                className = {classes.input}
                id = 'file-upload'
                onChange = {uploadImage}
                ref = {uploadImageButton}
                type = 'file' />
        </>
    );
}

export default translate(UploadImageButton);
