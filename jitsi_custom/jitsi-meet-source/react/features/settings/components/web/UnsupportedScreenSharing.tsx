// @ts-ignore
import React, {useState, ReactNode} from 'react';
import { makeStyles } from 'tss-react/mui';
import {useTranslation} from 'react-i18next';
import Popover from "../../../base/popover/components/Popover.web";

const useStyles = makeStyles()(() => {
    return {
        popoverContent: {
            padding: '5px 8px',
            fontSize: '13px',
            lineHeight: '18px',
            backgroundColor: '#272727',
            maxWidth: '255px',
            borderRadius: '5px',
            marginBottom: '9px',
            cursor: 'pointer',
            textAlign: 'center',
        },
        popoverContentRecording: {
            marginBottom: '10',
        },
        popoverTriangle: {
            position: 'absolute',
            left: '48.5%',
            bottom: '-12px',
            transform: 'translateY(-50%)',
            width: '0',
            height: '0',
            borderLeft: '5px solid transparent',
            borderRight: '5px solid transparent',
            borderTop: '8px solid #272727',
        },
        popoverTriangleRecording: {
            left: '69%',
            bottom: '-12px',
            opacity: 0,
        }
    };
});

function getOS(isRecording = false) {
    const { t } = useTranslation();
    if (isRecording) {
        return `Ubuntu&nbsp;18+, Debian&nbsp;10+, Fedora&nbsp;32+, Centos&nbsp;8+ ${t('prejoin.or')} Alt&nbsp;10+`;
    }

    return `Ubuntu&nbsp;18+, Debian&nbsp;10+, Fedora&nbsp;32+, Centos&nbsp;8+ ${t('prejoin.or')}&nbsp;Alt&nbsp;10+`;

}

const unsupportedText = ({ isRecording }: Partial<IProps>) => {
    const { t } = useTranslation();
    const {classes: styles, cx, theme} = useStyles();
    const text = isRecording ?
        t('dialog.screenRecordingNotAvailableOS', {os: getOS(isRecording)}) :
        t('dialog.screenSharingNotAvailableOS', {os: getOS(isRecording)});
    const combineStyles = isRecording ? cx(styles.popoverTriangle, styles.popoverTriangleRecording) : cx(styles.popoverTriangle)
    const combineStylesContent = isRecording ? cx(styles.popoverContent, styles.popoverContentRecording) : cx(styles.popoverContent)
    return (<div className={combineStylesContent}>
        <div dangerouslySetInnerHTML={{__html: text}}/>
        <span className={combineStyles}></span>
    </div>)
}

interface IProps {
    isRecording: boolean
    children: ReactNode;
    isVisible: boolean
}

const UnsupportedScreenSharing = ({ children, isRecording, isVisible }: IProps) => {
    const {classes: styles, cx, theme} = useStyles();
    const close = () => {
        isVisible = false;
    }

    return (<Popover
            content = {unsupportedText({ isRecording })}
            onPopoverClose = {() => close()}
            position = 'top'
            trigger = 'click'
            visible = {isVisible}>
            {children}
        </Popover>

    );
};

export default UnsupportedScreenSharing;
