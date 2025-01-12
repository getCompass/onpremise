import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { makeStyles } from 'tss-react/mui';
import { getDecodedURI } from '../../../../base/util/uri';
import { isMobileBrowser } from "../../../../base/environment/utils";
import { copyText } from "../../../../base/util/copyText.web";


interface IProps {

    /**
     * The URL of the conference.
     */
    url: string;
}

const useStyles = makeStyles()(theme => {
    return {
        urlInput: {
            wordBreak: 'break-all',
            marginBottom: '12px',
            padding: '6px 8px',
            color: 'rgba(255, 255, 255, 0.7)',
            fontSize: '15px',
            lineHeight: '18px',
            backgroundColor: 'rgba(23, 23, 23, 1)',
            borderRadius: '8px',
            border: '1px solid rgba(255, 255, 255, 0.05)',

            '&.is-mobile': {
                padding: '11px 15px',
                fontSize: '16px',
                lineHeight: '20px',
                color: 'rgba(255, 255, 255, 0.8)'
            },

            '&.is-copied': {
                backgroundColor: 'rgba(4, 164, 90, 0.08)',
                border: '1px solid rgba(4, 164, 90, 0.5)',
            },
        },
        copiedPopoverContainer: {
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'center',
            alignItems: 'center',
            marginTop: '-41px',
            position: 'absolute',
            left: 0,
            right: 0,
        },
        copiedPopover: {
            padding: '6px 12px',
            backgroundColor: 'rgba(4, 164, 90, 1)',
            borderRadius: '5px',
            color: 'rgba(248, 248, 248, 1)',
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '14px',
            lineHeight: '20px',
            letterSpacing: '-0.16px',
        },

        copyButton: {
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '23px',
            borderRadius: '6px',
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            padding: `6px 14px`,
            width: '100%',
            boxSizing: 'border-box',
            background: 'rgba(0, 107, 224, 1)',
            cursor: 'pointer',
            color: 'rgba(255, 255, 255, 1)',
            outline: 'none',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: "rgba(0, 88, 184, 1)"
                },
            },

            '&.is-mobile': {
                borderRadius: '8px',
                padding: `9px 16px`,
                fontSize: '17px',
                lineHeight: '26px',
            }
        },

        copyButtonContent: {
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap' as const,
            width: '100%',
            textAlign: 'center'
        }
    };
});

/**
 * Component meant to enable users to copy the conference URL.
 *
 * @returns {React$Element<any>}
 */
function CopyMeetingLinkSection({ url }: IProps) {
    const { classes, cx } = useStyles();
    const { t } = useTranslation();
    const [ isCopied, setIsCopied ] = useState(false);

    /**
     * Click handler for the element.
     *
     * @returns {void}
     */
    async function onClick() {
        const isCopied = await copyText(url);

        if (isCopied) {
            setIsCopied(true);
        }
    }

    /**
     * KeyPress handler for accessibility.
     *
     * @param {React.KeyboardEventHandler<HTMLDivElement>} e - The key event to handle.
     *
     * @returns {void}
     */
    function onKeyPress(e: React.KeyboardEvent) {
        if (onClick && (e.key === ' ' || e.key === 'Enter')) {
            e.preventDefault();
            onClick();
        }
    }

    useEffect(() => {

        if (isCopied) {
            setTimeout(() => setIsCopied(false), 2500);
        }
    }, [ isCopied ]);

    return (
        <div className = 'invite-more-dialog'>
            {isCopied && (
                <div className = {classes.copiedPopoverContainer}>
                    <div className = {classes.copiedPopover}>
                        {t('addPeople.linkCopied')}
                    </div>
                    <div>
                        <svg width = "40" height = "5" viewBox = "0 0 40 5" fill = "none"
                             xmlns = "http://www.w3.org/2000/svg">
                            <path d = "M16 0L20 5L24 0H16Z" fill = "#04A45A" />
                        </svg>
                    </div>
                </div>
            )}
            <div
                className = {cx(classes.urlInput, isMobileBrowser() && 'is-mobile', isCopied && 'is-copied')}>{url}
            </div>
            <div
                aria-label = {t('addPeople.accessibilityLabel.meetingLink', { url: getDecodedURI(url) })}
                className = {cx('invite-more-dialog-conference-url', classes.copyButton, isMobileBrowser() ? 'is-mobile' : '')}
                id = 'add-people-copy-link-button'
                onClick = {onClick}
                onKeyPress = {onKeyPress}
                role = 'button'
                tabIndex = {0}>
                <div className = {classes.copyButtonContent}>
                    <span> {t('addPeople.copyLink')} </span>
                </div>
            </div>
        </div>
    );
}

export default CopyMeetingLinkSection;
