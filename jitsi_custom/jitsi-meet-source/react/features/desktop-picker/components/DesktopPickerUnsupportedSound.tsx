// @ts-ignore
import React, {useState} from 'react';
import { makeStyles } from 'tss-react/mui';
import { WithTranslation } from 'react-i18next';

import Icon from '../../base/icons/components/Icon';
import { IconQuestionCircle } from '../../base/icons/svg';
import Popover from "../../base/popover/components/Popover.web";

const useStyles = makeStyles()(() => {
    return {
        parent: {
            display: 'inline',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            marginRight: '25px',
            // Apply display: inline to all child elements
            '& *': {
                display: 'inline !important',
                verticalAlign: 'middle',
            },
        },
        iconWrapper: {
            marginLeft: '6px',
            marginTop: '2px'
        },
        popoverContent: {
            padding: '5px 8px',
            fontSize: '13px',
            lineHeight: '18px',
            backgroundColor: '#272727',
            maxWidth: '186px',
            borderRadius: '5px',
            marginBottom: '9px',
            cursor: 'pointer'
        },
        popoverTriangle: {
            position: 'absolute',
            left: '50%',
            bottom: '-12px',
            transform: 'translateY(-50%)',
            width: '0',
            height: '0',
            borderLeft: '5px solid transparent',
            borderRight: '5px solid transparent',
            borderTop: '8px solid #272727',
        }
    };
});

function getOS() {
    const userAgent = navigator.userAgent.toLowerCase();
    if (/win(dows)?/i.test(userAgent)) {
        return 'Windows&nbsp;10+';
    } else if (/macintosh|mac os x/i.test(userAgent)) {
        return 'MacOS&nbsp;13+';
    }
    return 'Ubuntu&nbsp;18+, Debian&nbsp;10+, Fedora&nbsp;32+, Centos&nbsp;8+, Alt&nbsp;10+';
}

const unsupportedText = (props: WithTranslation) => {
    const {classes: styles, cx, theme} = useStyles();
    const {t} = props;
    return (<div className={cx(styles.popoverContent)}>
        <div dangerouslySetInnerHTML={{__html: t('dialog.screenSharingAudioNotAvailableOS', {os: getOS()})}}/>
        <span className={cx(styles.popoverTriangle)}></span>
    </div>)
}

const DesktopPickerUnsupportedSound = (props: WithTranslation) => {
    const {classes: styles, cx, theme} = useStyles();
    const { t } = props;
    const [isOpen, changeOpenState] = useState(false);

    return (<div className = {cx(styles.parent)}>
            <span>
            { t('dialog.screenSharingAudioNotAvailable') }
            </span>
            <Popover
                content = {unsupportedText(props)}
                onPopoverClose = {() => changeOpenState(false)}
                position = 'top'
                trigger = 'click'
                visible = {isOpen}>
                <div
                    className = {cx(styles.iconWrapper)}
                    onMouseLeave = {() => changeOpenState(false)}
                    onClick = {() => changeOpenState(true)}
                    onMouseEnter = {() => changeOpenState(true)}>
                    <Icon
                        color = {theme.palette.icon03}
                        size = {20}
                        src = {IconQuestionCircle} />
                </div>
            </Popover>
        </div>

    );
};

export default DesktopPickerUnsupportedSound;
